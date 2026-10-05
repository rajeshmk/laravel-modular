<?php

declare(strict_types=1);

namespace Hatchyu\Modular;

use Hatchyu\Modular\Console\Commands\ActionMakeCommand;
use Hatchyu\Modular\Console\Commands\CommandMakeCommand;
use Hatchyu\Modular\Console\Commands\ContractMakeCommand;
use Hatchyu\Modular\Console\Commands\ControllerMakeCommand;
use Hatchyu\Modular\Console\Commands\CrudMakeCommand;
use Hatchyu\Modular\Console\Commands\DataMakeCommand;
use Hatchyu\Modular\Console\Commands\DtoMakeCommand;
use Hatchyu\Modular\Console\Commands\EnumMakeCommand;
use Hatchyu\Modular\Console\Commands\EventMakeCommand;
use Hatchyu\Modular\Console\Commands\JobMakeCommand;
use Hatchyu\Modular\Console\Commands\MailMakeCommand;
use Hatchyu\Modular\Console\Commands\MigrationMakeCommand;
use Hatchyu\Modular\Console\Commands\ModelMakeCommand;
use Hatchyu\Modular\Console\Commands\ModuleCacheCommand;
use Hatchyu\Modular\Console\Commands\ModuleCheckCommand;
use Hatchyu\Modular\Console\Commands\ModuleClearCommand;
use Hatchyu\Modular\Console\Commands\ModuleDoctorCommand;
use Hatchyu\Modular\Console\Commands\ModuleListCommand;
use Hatchyu\Modular\Console\Commands\ModuleMakeCommand;
use Hatchyu\Modular\Console\Commands\ModuleRenameCommand;
use Hatchyu\Modular\Console\Commands\ModuleSeedCommand;
use Hatchyu\Modular\Console\Commands\NotificationMakeCommand;
use Hatchyu\Modular\Console\Commands\ObserverMakeCommand;
use Hatchyu\Modular\Console\Commands\PolicyMakeCommand;
use Hatchyu\Modular\Console\Commands\QueryMakeCommand;
use Hatchyu\Modular\Console\Commands\RepositoryMakeCommand;
use Hatchyu\Modular\Console\Commands\RequestMakeCommand;
use Hatchyu\Modular\Console\Commands\ResourceMakeCommand;
use Hatchyu\Modular\Console\Commands\RuleMakeCommand;
use Hatchyu\Modular\Console\Commands\SeederMakeCommand;
use Hatchyu\Modular\Console\Commands\ServiceMakeCommand;
use Hatchyu\Modular\Console\Commands\TestMakeCommand;
use Hatchyu\Modular\Console\Commands\ValueObjectMakeCommand;
use Hatchyu\Modular\Discovery\CommandRegistrar;
use Hatchyu\Modular\Discovery\ConfigRegistrar;
use Hatchyu\Modular\Discovery\FactoryGuesser;
use Hatchyu\Modular\Discovery\MigrationRegistrar;
use Hatchyu\Modular\Discovery\ModuleRegistry;
use Hatchyu\Modular\Discovery\PolicyGuesser;
use Hatchyu\Modular\Discovery\ProviderRegistrar;
use Hatchyu\Modular\Discovery\RouteRegistrar;
use Hatchyu\Modular\Discovery\ViewRegistrar;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\ServiceProvider;

class ModularServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/modular.php', 'modular');

        $this->app->singleton(ModuleRegistry::class, function ($app): ModuleRegistry {
            return new ModuleRegistry($app->make(ConfigRepository::class));
        });

        /** @var ConfigRepository $config */
        $config = $this->app->make(ConfigRepository::class);

        /** @var ModuleRegistry $registry */
        $registry = $this->app->make(ModuleRegistry::class);

        // 1. Auto-discover Module Configs early during registration
        if ((bool) $config->get('modular.autodiscover.configs', true)) {
            (new ConfigRegistrar($config, $this->app))->register($registry);
        }

        // 2. Register module service providers early in the registration lifecycle
        if ((bool) $config->get('modular.autodiscover.providers', true)) {
            $providerRegistrar = new ProviderRegistrar($this->app);
            $providerRegistrar->register($registry);
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
                __DIR__ . '/../config/modular.php' => config_path('modular.php'),
            ], 'modular-config');

            $this->publishes([
                __DIR__ . '/stubs' => base_path('stubs/modular'),
            ], 'modular-stubs');

            $this->commands([
                ModuleMakeCommand::class,
                ActionMakeCommand::class,
                QueryMakeCommand::class,
                DtoMakeCommand::class,
                DataMakeCommand::class,
                ControllerMakeCommand::class,
                RequestMakeCommand::class,
                ResourceMakeCommand::class,
                ModelMakeCommand::class,
                PolicyMakeCommand::class,
                ObserverMakeCommand::class,
                EnumMakeCommand::class,
                EventMakeCommand::class,
                JobMakeCommand::class,
                MailMakeCommand::class,
                NotificationMakeCommand::class,
                RuleMakeCommand::class,
                ContractMakeCommand::class,
                ValueObjectMakeCommand::class,
                ServiceMakeCommand::class,
                CommandMakeCommand::class,
                SeederMakeCommand::class,
                ModuleSeedCommand::class,
                TestMakeCommand::class,
                MigrationMakeCommand::class,
                ModuleListCommand::class,
                ModuleCheckCommand::class,
                ModuleDoctorCommand::class,
                ModuleRenameCommand::class,
                ModuleCacheCommand::class,
                ModuleClearCommand::class,
                CrudMakeCommand::class,
                RepositoryMakeCommand::class,
            ]);

            // Auto-discover Module Console Commands
            if ((bool) $config->get('modular.autodiscover.commands', true)) {
                (new CommandRegistrar($this->app))->register($registry);
            }

            $this->optimizes(
                optimize: 'module:cache',
                clear: 'module:clear',
                key: 'modular'
            );
        }

        // Auto-discover Model Factories
        if ((bool) $config->get('modular.autodiscover.factories', true)) {
            (new FactoryGuesser())->register($registry->getNamespace());
        }

        // Auto-discover Authorization Policies
        if ((bool) $config->get('modular.autodiscover.policies', true)) {
            (new PolicyGuesser())->register($registry->getNamespace());
        }

        // Auto-discover Module Routes
        if ((bool) $config->get('modular.autodiscover.routes', true)) {
            (new RouteRegistrar($this->app->make('router'), $config, $this->app))->register($registry);
        }

        // Auto-discover Module Migrations
        if ((bool) $config->get('modular.autodiscover.migrations', true)) {
            (new MigrationRegistrar($this->app))->register($registry);
        }

        // Auto-discover Module Views
        if ((bool) $config->get('modular.autodiscover.views', true) && $this->app->bound('view')) {
            (new ViewRegistrar($this->app->make('view')))->register($registry);
        }
    }
}
