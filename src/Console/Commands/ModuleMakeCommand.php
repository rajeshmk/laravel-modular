<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Console\Commands;

use Hatchyu\Modular\Console\GeneratorCommand;
use Hatchyu\Modular\Support\Module;
use Illuminate\Support\Str;

class ModuleMakeCommand extends GeneratorCommand
{
    protected $signature = 'module:make
                            {name : The name of the module (e.g. Order, Customer, Agent)}
                            {--force : Overwrite existing module files}';

    protected $description = 'Scaffold a new domain module conforming to the Modular Monolith blueprint';

    public function handle(): int
    {
        /** @var string $rawName */
        $rawName = $this->argument('name');
        $moduleName = Str::studly($rawName);
        $force = (bool) $this->option('force');

        $modulePath = $this->registry->getModulesPath().DIRECTORY_SEPARATOR.$moduleName;
        $module = new Module(
            name: $moduleName,
            path: $modulePath,
            namespace: $this->registry->getNamespace()
        );

        if (is_dir($modulePath) && ! $force) {
            $this->components->error("Module [{$moduleName}] already exists at [{$modulePath}]. Use --force to overwrite.");

            return self::FAILURE;
        }

        $this->components->info("Scaffolding module [{$moduleName}]...");

        $directories = [
            'Actions',
            'Queries/Filters',
            'Queries/Searches',
            'Controllers/Api/V1',
            'Controllers/Admin',
            'Requests',
            'Resources',
            'Models',
            'Exceptions',
            'Contracts',
            'DTOs',
            'Enums',
            'Events',
            'Listeners',
            'Middleware',
            'Jobs',
            'Console',
            'config',
            'database/migrations',
            'database/factories',
            'resources/views',
            'routes',
        ];

        foreach ($directories as $dir) {
            $this->ensureDirectoryExists($module->getPath($dir));
        }

        $replacements = [
            'namespace' => rtrim($this->registry->getNamespace(), '\\'),
            'module' => $moduleName,
            'slug' => $module->getSlug(),
        ];

        // 1. Service Provider
        $providerContent = $this->replacePlaceholders($this->getStub('provider'), $replacements);
        $this->writeFile($module->getProviderPath(), $providerContent, $force);

        // 2. Web routes
        $webRouteContent = $this->replacePlaceholders($this->getStub('routes.web'), $replacements);
        $this->writeFile($module->getWebRoutesPath(), $webRouteContent, $force);

        // 3. API routes
        $apiRouteContent = $this->replacePlaceholders($this->getStub('routes.api'), $replacements);
        $this->writeFile($module->getApiRoutesPath(), $apiRouteContent, $force);

        // 4. Config file
        $configContent = "<?php\n\ndeclare(strict_types=1);\n\nreturn [\n    // Configuration for {$moduleName} module\n];\n";
        $this->writeFile($module->getConfigPath(), $configContent, $force);

        // 5. Example View
        $viewContent = "<div>\n    <h1>Welcome to {$moduleName} Module</h1>\n</div>\n";
        $this->writeFile($module->getPath('resources/views/index.blade.php'), $viewContent, $force);

        // 6. Gitkeep empty directories
        $emptyDirs = [
            'Actions',
            'Queries/Filters',
            'Queries/Searches',
            'Controllers/Api/V1',
            'Controllers/Admin',
            'Requests',
            'Resources',
            'Models',
            'Exceptions',
            'Contracts',
            'DTOs',
            'Enums',
            'Events',
            'Listeners',
            'Middleware',
            'Jobs',
            'Console',
            'database/migrations',
            'database/factories',
        ];

        foreach ($emptyDirs as $dir) {
            $gitkeepPath = $module->getPath("{$dir}/.gitkeep");
            if (! file_exists($gitkeepPath)) {
                touch($gitkeepPath);
            }
        }

        $this->registry->flush();

        $this->components->info("Module [{$moduleName}] successfully scaffolded at [{$modulePath}].");
        $this->components->bulletList([
            "Namespace: {$module->getNamespace()}",
            "Routes: {$module->getWebRoutesPath()}",
            "Provider: {$module->getProviderClass()}",
        ]);

        return self::SUCCESS;
    }
}
