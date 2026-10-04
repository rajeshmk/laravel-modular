<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Console\Commands;

use Hatchyu\Modular\Discovery\ModuleRegistry;
use Hatchyu\Modular\Support\Module;
use Illuminate\Console\Command;

class ModuleCheckCommand extends Command
{
    protected $signature = 'module:check
                            {--strict : Return non-zero exit code if warnings or missing mappings are detected}';

    protected $description = 'Verify module configuration, PSR-4 composer mappings, and health';

    public function __construct(
        private readonly ModuleRegistry $registry
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->components->info('Running Laravel Modular health checks...');
        $hasIssues = false;

        // 1. Check modules directory
        $modulesPath = $this->registry->getModulesPath();
        if (! is_dir($modulesPath)) {
            $this->components->error("Modules directory does not exist at [{$modulesPath}].");
            $hasIssues = true;
        } else {
            $this->components->twoColumnDetail('Modules Directory', "<info>{$modulesPath}</info>");
        }

        // 2. Check composer.json PSR-4 mapping (supports both autoload and autoload-dev)
        $composerJsonPath = base_path('composer.json');
        if (file_exists($composerJsonPath)) {
            /** @var array<string, mixed>|null $composerData */
            $composerData = json_decode((string) file_get_contents($composerJsonPath), true);
            $psr4 = array_merge(
                (array) ($composerData['autoload']['psr-4'] ?? []),
                (array) ($composerData['autoload-dev']['psr-4'] ?? [])
            );
            $expectedNamespace = $this->registry->getNamespace();

            if (isset($psr4[$expectedNamespace])) {
                $mappedPath = $psr4[$expectedNamespace];
                $this->components->twoColumnDetail('Composer PSR-4 Mapping', "<info>{$expectedNamespace} => {$mappedPath}</info>");
            } else {
                $this->components->warn("Missing [\"{$expectedNamespace}\": \"modules/\"] in composer.json under autoload.psr-4. Run 'composer dump-autoload' after adding it.");
                $hasIssues = true;
            }
        }

        // 3. Cache status
        $isCached = $this->registry->isCached();
        $this->components->twoColumnDetail(
            'Discovery Cache',
            $isCached ? '<info>Active (Production Optimized)</info>' : '<comment>Disabled (Runtime Discovery)</comment>'
        );

        // 4. Verify discovered modules
        $modules = $this->registry->all();
        if ($modules->isEmpty()) {
            $this->components->warn('No modules detected. Run [php artisan module:make <Name>] to scaffold your first module.');

            return self::SUCCESS;
        }

        $rows = [];

        /** @var Module $module */
        foreach ($modules as $module) {
            $providerStatus = 'N/A';
            if ($module->hasProvider()) {
                $providerClass = $module->getProviderClass();
                $providerStatus = class_exists($providerClass)
                    ? '<info>OK</info>'
                    : '<comment>Unloaded</comment>';

                if (! class_exists($providerClass)) {
                    $hasIssues = true;
                }
            }

            $rows[] = [
                'name' => $module->getName(),
                'provider' => $providerStatus,
                'web_routes' => $module->hasWebRoutes() ? '<info>Yes</info>' : '<comment>No</comment>',
                'api_routes' => $module->hasApiRoutes() ? '<info>Yes</info>' : '<comment>No</comment>',
                'migrations' => $module->hasMigrations() ? '<info>Yes</info>' : '<comment>No</comment>',
            ];
        }

        $this->newLine();
        $this->table(['Module', 'Provider Class', 'Web Routes', 'API Routes', 'Migrations'], $rows);

        if ($hasIssues && (bool) $this->option('strict')) {
            $this->components->warn('Health check found one or more warnings in strict mode.');

            return self::FAILURE;
        }

        $this->components->info('Module check completed.');

        return self::SUCCESS;
    }
}
