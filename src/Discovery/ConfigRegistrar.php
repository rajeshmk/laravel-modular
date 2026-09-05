<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Discovery;

use Hatchyu\Modular\Support\Module;
use Illuminate\Contracts\Config\Repository as ConfigRepository;

final readonly class ConfigRegistrar
{
    public function __construct(
        private ConfigRepository $config
    ) {}

    public function register(ModuleRegistry $registry): void
    {
        /** @var Module $module */
        foreach ($registry->all() as $module) {
            if ($module->hasConfig()) {
                /** @var array<string, mixed> $moduleConfig */
                $moduleConfig = require $module->getConfigPath();
                $key = $module->getSlug();

                $current = (array) $this->config->get($key, []);
                $this->config->set($key, array_merge($moduleConfig, $current));
            }
        }
    }
}
