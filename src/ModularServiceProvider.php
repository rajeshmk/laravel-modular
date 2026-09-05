<?php

declare(strict_types=1);

namespace Hatchyu\Modular;

use Hatchyu\Modular\Console\Commands\ActionMakeCommand;
use Hatchyu\Modular\Console\Commands\ControllerMakeCommand;
use Hatchyu\Modular\Console\Commands\DtoMakeCommand;
use Hatchyu\Modular\Console\Commands\MigrationMakeCommand;
use Hatchyu\Modular\Console\Commands\ModelMakeCommand;
use Hatchyu\Modular\Console\Commands\ModuleCacheCommand;
use Hatchyu\Modular\Console\Commands\ModuleClearCommand;
use Hatchyu\Modular\Console\Commands\ModuleListCommand;
use Hatchyu\Modular\Console\Commands\ModuleMakeCommand;
use Hatchyu\Modular\Console\Commands\QueryMakeCommand;
use Hatchyu\Modular\Console\Commands\RequestMakeCommand;
use Hatchyu\Modular\Console\Commands\ResourceMakeCommand;
use Hatchyu\Modular\Discovery\ConfigRegistrar;
use Hatchyu\Modular\Discovery\FactoryGuesser;
use Hatchyu\Modular\Discovery\MigrationRegistrar;
use Hatchyu\Modular\Discovery\ModuleRegistry;
use Hatchyu\Modular\Discovery\ProviderRegistrar;
use Hatchyu\Modular\Discovery\RouteRegistrar;
use Hatchyu\Modular\Discovery\ViewRegistrar;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\ServiceProvider;

class ModularServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/modular.php', 'modular');

        $this->app->singleton(ModuleRegistry::class, function ($app): ModuleRegistry {
            return new ModuleRegistry($app->make(ConfigRepository::class));
        });

        // Register module service providers early in the registration lifecycle
        /** @var ConfigRepository $config */
        $config = $this->app->make(ConfigRepository::class);

        if ((bool) $config->get('modular.autodiscover.providers', true)) {
            $providerRegistrar = new ProviderRegistrar($this->app);
            $providerRegistrar->register($this->app->make(ModuleRegistry::class));
        }
    }

    public function boot(): void
    {
        /** @var ConfigRepository $config */
        $config = $this->app->make(ConfigRepository::class);
        /** @var ModuleRegistry $registry */
        $registry = $this->app->make(ModuleRegistry::class);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/modular.php' => config_path('modular.php'),
            ], 'modular-config');

            $this->publishes([
                __DIR__.'/stubs' => base_path('stubs/modular'),
            ], 'modular-stubs');

            $this->commands([
                ModuleMakeCommand::class,
                ActionMakeCommand::class,
                QueryMakeCommand::class,
                DtoMakeCommand::class,
                ControllerMakeCommand::class,
                RequestMakeCommand::class,
                ResourceMakeCommand::class,
                ModelMakeCommand::class,
                MigrationMakeCommand::class,
                ModuleListCommand::class,
                ModuleCacheCommand::class,
                ModuleClearCommand::class,
            ]);
        }

        // Auto-discover Model Factories
        if ((bool) $config->get('modular.autodiscover.factories', true)) {
            (new FactoryGuesser)->register($registry->getNamespace());
        }

        // Auto-discover Module Routes
        if ((bool) $config->get('modular.autodiscover.routes', true)) {
            (new RouteRegistrar($this->app->make('router'), $config))->register($registry);
        }

        // Auto-discover Module Migrations
        if ((bool) $config->get('modular.autodiscover.migrations', true)) {
            (new MigrationRegistrar($this->app))->register($registry);
        }

        // Auto-discover Module Views
        if ((bool) $config->get('modular.autodiscover.views', true) && $this->app->bound('view')) {
            (new ViewRegistrar($this->app->make('view')))->register($registry);
        }

        // Auto-discover Module Configs
        if ((bool) $config->get('modular.autodiscover.configs', true)) {
            (new ConfigRegistrar($config))->register($registry);
        }
    }
}
