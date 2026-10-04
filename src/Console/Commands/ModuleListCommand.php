<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Console\Commands;

use Hatchyu\Modular\Discovery\ModuleRegistry;
use Hatchyu\Modular\Support\Module;
use Illuminate\Console\Command;

class ModuleListCommand extends Command
{
    protected $signature = 'module:list';

    protected $description = 'Display a list of all detected domain modules';

    public function __construct(
        private readonly ModuleRegistry $registry
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $modules = $this->registry->all();

        if ($modules->isEmpty()) {
            $this->components->info('No modules found in [' . $this->registry->getModulesPath() . '].');

            return self::SUCCESS;
        }

        $cached = $this->registry->isCached() ? ' (Cached)' : '';
        $this->components->info("Discovered {$modules->count()} module(s){$cached}:");

        $rows = $modules->map(function (Module $module): array {
            $databaseParts = array_filter([
                $module->hasMigrations() ? 'Migrations' : null,
                $module->hasFactories() ? 'Factories' : null,
                $module->hasSeeders() ? 'Seeders' : null,
            ]);
            $database = ! empty($databaseParts) ? implode(', ', $databaseParts) : '<comment>None</comment>';

            return [
                'name' => $module->getName(),
                'slug' => $module->getSlug(),
                'provider' => $module->hasProvider() ? '<info>Registered</info>' : '<comment>None</comment>',
                'routes' => ($module->hasWebRoutes() ? 'Web ' : '') . ($module->hasApiRoutes() ? 'API' : '') ?: '<comment>None</comment>',
                'database' => $database,
                'tests' => $module->hasTests() ? '<info>Yes</info>' : '<comment>None</comment>',
                'views' => $module->hasViews() ? '<info>Yes</info>' : '<comment>None</comment>',
                'config' => $module->hasConfig() ? '<info>Yes</info>' : '<comment>None</comment>',
            ];
        })->all();

        $this->table(
            ['Module', 'Slug', 'Provider', 'Routes', 'Database', 'Tests', 'Views', 'Config'],
            $rows
        );

        return self::SUCCESS;
    }
}
