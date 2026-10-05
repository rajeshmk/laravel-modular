<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Discovery;

use Hatchyu\Modular\Support\Module;
use Illuminate\Console\Application as Artisan;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use SplFileInfo;

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
            if (! $module->hasCommands()) {
                continue;
            }

            $commandsPath = $module->getCommandsPath();
            if (! is_dir($commandsPath)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($commandsPath, RecursiveDirectoryIterator::SKIP_DOTS)
            );

            /** @var SplFileInfo $file */
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $pathName = $file->getPathname();
                    if (file_exists($pathName)) {
                        require_once $pathName;
                    }

                    $relativePath = Str::after($pathName, rtrim($commandsPath, '/\\') . DIRECTORY_SEPARATOR);
                    $classPath = str_replace(['/', '\\'], '\\', Str::beforeLast($relativePath, '.php'));

                    $relativeNamespace = Str::contains($commandsPath, 'Interface')
                        ? 'Interface\\Console\\Commands\\'
                        : 'Console\\Commands\\';

                    $fullClass = $module->getNamespace($relativeNamespace . $classPath);

                    if (! class_exists($fullClass)) {
                        continue;
                    }

                    $reflection = new ReflectionClass($fullClass);
                    if ($reflection->isSubclassOf(Command::class) && ! $reflection->isAbstract()) {
                        $discovered[] = $fullClass;
                    }
                }
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
