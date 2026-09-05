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
            if ($module->hasMigrations()) {
                /* @phpstan-ignore-next-line */
                if (method_exists($this->app, 'loadMigrationsFrom')) {
                    $this->app->loadMigrationsFrom($module->getMigrationsPath());
                } elseif ($this->app->bound('migrator')) {
                    /** @var Migrator $migrator */
                    $migrator = $this->app->make('migrator');
                    $migrator->path($module->getMigrationsPath());
                }
            }
        }
    }
}
