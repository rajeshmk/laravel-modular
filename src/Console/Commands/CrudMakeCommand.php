<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Console\Commands;

use Hatchyu\Modular\Console\GeneratorCommand;
use Illuminate\Support\Str;

class CrudMakeCommand extends GeneratorCommand
{
    protected $signature = 'module:make-crud
                            {module : The name of the module}
                            {name : The name of the entity / model (e.g. Property, Order)}
                            {--api-version=1 : The API version for controller scaffolding}
                            {--no-migration : Skip generating migration file}
                            {--no-routes : Skip appending api resource route}
                            {--force : Overwrite existing files}';

    protected $aliases = ['module:crud'];

    protected $description = 'Scaffold a complete DDD vertical slice (Model, Migration, Repository, DTO, Actions, Controller, Requests, Resource, Test)';

    public function handle(): int
    {
        $module = $this->getModule();

        /** @var string $rawName */
        $rawName = $this->argument('name');
        [$entityName, $subNamespace, $relativeDir] = $this->parseClassInput($rawName);

        $force = (bool) $this->option('force');
        $moduleName = $module->getName();
        $namespace = rtrim($this->registry->getNamespace(), '\\');
        $slugPlural = Str::plural(Str::kebab($entityName));
        $slugSingle = Str::kebab($entityName);
        $tableName = Str::snake(Str::pluralStudly($entityName));

        /** @var string $versionOption */
        $versionOption = (string) ($this->option('api-version') ?? '1');
        $version = 'V' . ltrim(Str::upper($versionOption), 'V');

        $this->components->info("Scaffolding DDD CRUD vertical slice for [{$entityName}] in [{$moduleName}]...");

        $commonReplacements = [
            'namespace' => $namespace,
            'module' => $moduleName,
            'model' => $entityName,
            'class' => $entityName,
            'slug' => $slugSingle,
            'slugPlural' => $slugPlural,
            'subNamespace' => $subNamespace !== '' ? '\\' . $subNamespace : '',
            'version' => $version,
        ];

        // 1. Domain: Model
        $modelPath = $module->getPath("Domain/Models/{$entityName}.php");
        $modelContent = $this->replacePlaceholders($this->getStub('model'), $commonReplacements);
        $this->writeFile($modelPath, $modelContent, $force);

        // 2. Database: Migration
        if (! (bool) $this->option('no-migration')) {
            $this->callSilent('module:make-migration', [
                'module' => $moduleName,
                'name' => "create_{$tableName}_table",
                '--force' => $force,
            ]);
        }

        // 3. Database: Factory
        $factoryPath = $module->getPath("Database/Factories/{$entityName}Factory.php");
        $factoryReplacements = array_merge($commonReplacements, [
            'class' => "{$entityName}Factory",
        ]);
        $factoryContent = $this->replacePlaceholders($this->getStub('factory'), $factoryReplacements);
        $this->writeFile($factoryPath, $factoryContent, $force);

        // 4. Domain: Repository Contract Interface
        $interfaceName = "{$entityName}RepositoryInterface";
        $contractPath = $module->getPath("Domain/Contracts/{$interfaceName}.php");
        $contractReplacements = array_merge($commonReplacements, [
            'class' => $interfaceName,
        ]);
        $contractContent = $this->replacePlaceholders($this->getStub('repository.contract'), $contractReplacements);
        $this->writeFile($contractPath, $contractContent, $force);

        // 5. Infrastructure: Repository
        $repoName = "{$entityName}Repository";
        $repoPath = $module->getPath("Infrastructure/Repositories/{$repoName}.php");
        $repoReplacements = array_merge($commonReplacements, [
            'class' => $repoName,
            'interface' => $interfaceName,
        ]);
        $repoContent = $this->replacePlaceholders($this->getStub('repository'), $repoReplacements);
        $this->writeFile($repoPath, $repoContent, $force);

        // 6. Application: Data DTO
        $dataName = "{$entityName}Data";
        $dataPath = $module->getPath("Application/Data/{$dataName}.php");
        $dataContent = $this->replacePlaceholders($this->getStub('crud.data'), $commonReplacements);
        $this->writeFile($dataPath, $dataContent, $force);

        // 7. Application: Actions (Create, Update, Delete)
        $createActionPath = $module->getPath("Application/Actions/Create{$entityName}Action.php");
        $this->writeFile($createActionPath, $this->replacePlaceholders($this->getStub('crud.action.create'), $commonReplacements), $force);

        $updateActionPath = $module->getPath("Application/Actions/Update{$entityName}Action.php");
        $this->writeFile($updateActionPath, $this->replacePlaceholders($this->getStub('crud.action.update'), $commonReplacements), $force);

        $deleteActionPath = $module->getPath("Application/Actions/Delete{$entityName}Action.php");
        $this->writeFile($deleteActionPath, $this->replacePlaceholders($this->getStub('crud.action.delete'), $commonReplacements), $force);

        // 8. Interface: Requests (Store, Update)
        $storeReqPath = $module->getPath("Interface/Requests/Store{$entityName}Request.php");
        $storeReqReplacements = array_merge($commonReplacements, ['class' => "Store{$entityName}Request"]);
        $this->writeFile($storeReqPath, $this->replacePlaceholders($this->getStub('request'), $storeReqReplacements), $force);

        $updateReqPath = $module->getPath("Interface/Requests/Update{$entityName}Request.php");
        $updateReqReplacements = array_merge($commonReplacements, ['class' => "Update{$entityName}Request"]);
        $this->writeFile($updateReqPath, $this->replacePlaceholders($this->getStub('request'), $updateReqReplacements), $force);

        // 9. Interface: Resource
        $resourcePath = $module->getPath("Interface/Resources/{$entityName}Resource.php");
        $resourceReplacements = array_merge($commonReplacements, ['class' => "{$entityName}Resource"]);
        $this->writeFile($resourcePath, $this->replacePlaceholders($this->getStub('resource'), $resourceReplacements), $force);

        // 10. Interface: Controller
        $controllerPath = $module->getPath("Interface/Controllers/Api/{$version}/{$entityName}Controller.php");
        $controllerReplacements = array_merge($commonReplacements, ['class' => "{$entityName}Controller"]);
        $this->writeFile($controllerPath, $this->replacePlaceholders($this->getStub('crud.controller.api'), $controllerReplacements), $force);

        // 11. Test: Feature Test
        $testPath = $module->getPath("tests/Feature/{$entityName}ControllerTest.php");
        $this->writeFile($testPath, $this->replacePlaceholders($this->getStub('crud.test'), $commonReplacements), $force);

        // 12. UI Routes: Append API Route
        if (! (bool) $this->option('no-routes')) {
            $apiRoutesPath = $module->getApiRoutesPath();
            if (file_exists($apiRoutesPath)) {
                $routesContent = file_get_contents($apiRoutesPath);
                $controllerFqcn = "\\{$namespace}\\{$moduleName}\\Interface\\Controllers\\Api\\{$version}\\{$entityName}Controller::class";

                if ($routesContent !== false && ! Str::contains($routesContent, $controllerFqcn) && ! Str::contains($routesContent, "{$entityName}Controller")) {
                    $routeSnippet = "\nRoute::apiResource('{$slugPlural}', {$controllerFqcn});\n";
                    file_put_contents($apiRoutesPath, $routeSnippet, FILE_APPEND);
                    $this->components->twoColumnDetail('API Route Registered', "<info>api/{$slugPlural}</info>");
                }
            }
        }

        $this->components->info("CRUD slice for [{$entityName}] created successfully!");
        $this->components->bulletList([
            "Domain Model: Domain/Models/{$entityName}.php",
            "Contract: Domain/Contracts/{$interfaceName}.php",
            "Repository: Infrastructure/Repositories/{$repoName}.php",
            "Application DTO: Application/Data/{$dataName}.php",
            "Actions: Create{$entityName}Action, Update{$entityName}Action, Delete{$entityName}Action",
            "API Controller: Interface/Controllers/Api/{$version}/{$entityName}Controller.php",
            "Requests: Store{$entityName}Request, Update{$entityName}Request",
            "Resource: Interface/Resources/{$entityName}Resource.php",
            "Feature Test: tests/Feature/{$entityName}ControllerTest.php",
        ]);

        return self::SUCCESS;
    }
}
