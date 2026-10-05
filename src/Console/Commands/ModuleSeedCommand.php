<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Console\Commands;

use Hatchyu\Modular\Discovery\ModuleRegistry;
use Hatchyu\Modular\Support\Module;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class ModuleSeedCommand extends Command
{
    protected $signature = 'module:seed
                            {module? : The name or slug of the module to seed}
                            {--class= : The class name of the root seeder}
                            {--database= : The database connection to seed}
                            {--force : Force the operation to run when in production}';

    protected $description = 'Seed the database with records for a specific module or all modules';

    public function __construct(
        private readonly ModuleRegistry $registry
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        /** @var string|null $moduleName */
        $moduleName = $this->argument('module');

        if ($moduleName !== null && $moduleName !== '') {
            $module = $this->registry->find($moduleName);

            if ($module === null) {
                $this->components->error("Module [{$moduleName}] does not exist.");

                return self::FAILURE;
            }

            return $this->seedModule($module);
        }

        /** @var Collection<int, Module> $modules */
        $modules = $this->registry->all()->filter(fn (Module $module): bool => $module->hasSeeders());

        if ($modules->isEmpty()) {
            $this->components->info('No module seeders found to run.');

            return self::SUCCESS;
        }

        $this->components->info("Running seeders for {$modules->count()} module(s)...");

        $status = self::SUCCESS;
        foreach ($modules as $module) {
            $exitCode = $this->seedModule($module);
            if ($exitCode !== self::SUCCESS) {
                $status = $exitCode;
            }
        }

        return $status;
    }

    protected function seedModule(Module $module): int
    {
        /** @var string|null $classOption */
        $classOption = $this->option('class');

        if ($classOption !== null && $classOption !== '') {
            $potentialFile = $module->getPath("Database/Seeders/{$classOption}.php");
            if (file_exists($potentialFile)) {
                require_once $potentialFile;
            }

            if (class_exists($classOption)) {
                $seederClass = $classOption;
            } else {
                $namespaced = $module->getNamespace("Database\\Seeders\\{$classOption}");
                $seederClass = class_exists($namespaced) ? $namespaced : $classOption;
            }

            $this->components->info("Seeding module [{$module->getName()}] using [{$seederClass}]...");

            return $this->runDbSeed($seederClass);
        }

        // Require module seeder files if they exist to support dynamic execution
        $moduleSeederFile = $module->getPath("Database/Seeders/{$module->getName()}DatabaseSeeder.php");
        if (file_exists($moduleSeederFile)) {
            require_once $moduleSeederFile;
        }

        $generalSeederFile = $module->getPath('Database/Seeders/DatabaseSeeder.php');
        if (file_exists($generalSeederFile)) {
            require_once $generalSeederFile;
        }

        $moduleSeeder = $module->getNamespace("Database\\Seeders\\{$module->getName()}DatabaseSeeder");
        $generalSeeder = $module->getNamespace('Database\\Seeders\\DatabaseSeeder');

        if (class_exists($moduleSeeder)) {
            $this->components->info("Seeding module [{$module->getName()}] using [{$moduleSeeder}]...");

            return $this->runDbSeed($moduleSeeder);
        }

        if (class_exists($generalSeeder)) {
            $this->components->info("Seeding module [{$module->getName()}] using [{$generalSeeder}]...");

            return $this->runDbSeed($generalSeeder);
        }

        $seedersPath = $module->getSeedersPath();
        $files = glob($seedersPath . '/*Seeder.php') ?: [];

        if (empty($files)) {
            $this->components->warn("No seeders found for module [{$module->getName()}].");

            return self::SUCCESS;
        }

        $this->components->info("Seeding module [{$module->getName()}]...");
        $status = self::SUCCESS;

        foreach ($files as $file) {
            require_once $file;
            $className = basename($file, '.php');
            $fqcn = $module->getNamespace("Database\\Seeders\\{$className}");
            $exitCode = $this->runDbSeed($fqcn);
            if ($exitCode !== self::SUCCESS) {
                $status = $exitCode;
            }
        }

        return $status;
    }

    protected function runDbSeed(string $class): int
    {
        $params = ['--class' => $class];

        if ($this->option('database')) {
            $params['--database'] = $this->option('database');
        }

        if ((bool) $this->option('force')) {
            $params['--force'] = true;
        }

        return $this->call('db:seed', $params);
    }
}
