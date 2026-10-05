<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Console\Commands;

use Hatchyu\Modular\Discovery\ModuleRegistry;
use Illuminate\Console\Command;

class ModuleEnableCommand extends Command
{
    protected $signature = 'module:enable
                            {module : The name or slug of the module to enable}
                            {--force : Force enabling even if dependencies are missing or disabled}';

    protected $description = 'Enable a module and register its routes, providers, and migrations';

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

        if ($module->isEnabled()) {
            $this->components->info("Module [{$module->getName()}] is already enabled.");

            return self::SUCCESS;
        }

        $force = (bool) $this->option('force');
        $dependencies = $module->getDependencies();

        if (! empty($dependencies) && ! $force) {
            foreach ($dependencies as $depName) {
                $depModule = $this->registry->find($depName);
                if ($depModule === null) {
                    $this->components->error("Cannot enable [{$module->getName()}]: required dependency [{$depName}] is missing. Install [{$depName}] first, or use --force.");

                    return self::FAILURE;
                }

                if ($depModule->isDisabled()) {
                    $this->components->error("Cannot enable [{$module->getName()}]: required dependency [{$depModule->getName()}] is currently disabled. Run [php artisan module:enable {$depModule->getName()}] first, or use --force.");

                    return self::FAILURE;
                }
            }
        }

        $module->setEnabled(true);
        $this->registry->flush();

        if ($this->registry->isCached()) {
            $this->callSilent('module:cache');
        }

        $this->components->info("Module [{$module->getName()}] has been enabled.");

        return self::SUCCESS;
    }
}
