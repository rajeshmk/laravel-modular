<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Discovery;

use Hatchyu\Modular\Support\Module;
use Illuminate\Console\Application as Artisan;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Foundation\Application;

final readonly class CommandRegistrar
{
    public function __construct(
        private ?Application $app = null
    ) {}

    public function register(ModuleRegistry $registry): void
    {
        $discovered = [];

        /** @var Module $module */
        foreach ($registry->all() as $module) {
            foreach ($module->getCommandClasses() as $commandClass) {
                $discovered[] = $commandClass;
            }
        }

        if (! empty($discovered)) {
            Artisan::starting(function ($artisan) use ($discovered): void {
                $artisan->resolveCommands($discovered);
            });

            if ($this->app !== null && $this->app->bound(ConsoleKernel::class)) {
                $kernel = $this->app->make(ConsoleKernel::class);
                if (method_exists($kernel, 'registerCommand')) {
                    foreach ($discovered as $commandClass) {
                        /** @var Command $instance */
                        $instance = $this->app->make($commandClass);
                        $kernel->registerCommand($instance);
                    }
                }
            }
        }
    }
}
