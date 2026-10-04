<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Discovery;

use Hatchyu\Modular\Support\Module;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Migrations\Migrator;

final readonly class MigrationRegistrar
{
    public function __construct(
        private Application $app
    ) {}

    public function register(ModuleRegistry $registry): void
    {
        /** @var Module $module */
        foreach ($registry->all() as $module) {
            $paths = array_unique(array_filter([
                $module->getPath('Database/Migrations'),
                $module->getPath('database/migrations'),
            ], 'is_dir'));

            foreach ($paths as $path) {
                /* @phpstan-ignore-next-line */
                if (method_exists($this->app, 'loadMigrationsFrom')) {
                    $this->app->loadMigrationsFrom($path);
                } elseif ($this->app->bound('migrator')) {
                    /** @var Migrator $migrator */
                    $migrator = $this->app->make('migrator');
                    $migrator->path($path);
                }
            }
        }
    }
}
