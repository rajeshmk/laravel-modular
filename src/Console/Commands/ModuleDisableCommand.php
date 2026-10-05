<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Console\Commands;

use Hatchyu\Modular\Discovery\ModuleRegistry;
use Illuminate\Console\Command;

class ModuleDisableCommand extends Command
{
    protected $signature = 'module:disable
                            {module : The name or slug of the module to disable}
                            {--force : Force disabling even if other active modules depend on it}';

    protected $description = 'Disable a module, preventing its routes, providers, and migrations from booting';

    public function __construct(
        private readonly ModuleRegistry $registry
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        /** @var string $name */
        $name = $this->argument('module');
        $module = $this->registry->find($name);

        if ($module === null) {
            $this->components->error("Module [{$name}] not found.");

            return self::FAILURE;
        }

        if ($module->isDisabled()) {
            $this->components->info("Module [{$module->getName()}] is already disabled.");

            return self::SUCCESS;
        }

        $force = (bool) $this->option('force');

        // Check if any other enabled module depends on this module
        if (! $force) {
            $dependents = [];
            foreach ($this->registry->all() as $otherModule) {
                if ($otherModule->isEnabled() && strcasecmp($otherModule->getName(), $module->getName()) !== 0) {
                    foreach ($otherModule->getDependencies() as $dep) {
                        if (strcasecmp($dep, $module->getName()) === 0 || strcasecmp($dep, $module->getSlug()) === 0) {
                            $dependents[] = $otherModule->getName();
                        }
                    }
                }
            }

            if (! empty($dependents)) {
                $depList = implode(', ', array_unique($dependents));
                $this->components->error("Cannot disable [{$module->getName()}]: active module(s) [{$depList}] depend on it. Disable them first, or use --force.");

                return self::FAILURE;
            }
        }

        $module->setEnabled(false);
        $this->registry->flush();

        if ($this->registry->isCached()) {
            $this->callSilent('module:cache');
        }

        $this->components->info("Module [{$module->getName()}] has been disabled.");

        return self::SUCCESS;
    }
}
