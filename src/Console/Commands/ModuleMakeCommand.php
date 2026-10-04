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

    protected $description = 'Scaffold a new domain module conforming to the DDD 4-layer architecture';

    public function handle(): int
    {
        /** @var string $rawName */
        $rawName = $this->argument('name');
        $moduleName = Str::studly($rawName);
        $force = (bool) $this->option('force');

        $modulePath = $this->registry->getModulesPath() . DIRECTORY_SEPARATOR . $moduleName;
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
            // 1. Domain Layer
            'Domain/Models',
            'Domain/ValueObjects',
            'Domain/Enums',
            'Domain/Events',
            'Domain/Policies',
            'Domain/Observers',

            // 2. Application Layer
            'Application/Actions',
            'Application/Queries',
            'Application/Data',
            'Application/Services',
            'Application/Rules',

            // 3. Interface Layer
            'Interface/Controllers/Api/V1',
            'Interface/Controllers/Admin',
            'Interface/Requests',
            'Interface/Resources',
            'Interface/Console/Commands',

            // 4. Infrastructure Layer
            'Infrastructure/Jobs',
            'Infrastructure/Mails',
            'Infrastructure/Notifications',

            // 5. Database Layer
            'Database/Migrations',
            'Database/Factories',
            'Database/Seeders',

            // 6. Tests
            'tests/Feature',
            'tests/Unit',

            // 7. Routes, Config & Views
            'routes',
            'config',
            'resources/views',
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
            'Domain/Models',
            'Domain/ValueObjects',
            'Domain/Enums',
            'Domain/Events',
            'Domain/Policies',
            'Domain/Observers',
            'Application/Actions',
            'Application/Queries',
            'Application/Data',
            'Application/Services',
            'Application/Rules',
            'Interface/Controllers/Api/V1',
            'Interface/Controllers/Admin',
            'Interface/Requests',
            'Interface/Resources',
            'Interface/Console/Commands',
            'Infrastructure/Jobs',
            'Infrastructure/Mails',
            'Infrastructure/Notifications',
            'Database/Migrations',
            'Database/Factories',
            'Database/Seeders',
            'tests/Feature',
            'tests/Unit',
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
