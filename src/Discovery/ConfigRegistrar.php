<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Discovery;

use Hatchyu\Modular\Support\Module;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Str;

final readonly class ConfigRegistrar
{
    public function __construct(
        private ConfigRepository $config,
        private ?Application $app = null
    ) {}

    public function register(ModuleRegistry $registry): void
    {
        // Skip filesystem reads if Laravel global configuration is cached
        if ($this->app !== null && method_exists($this->app, 'configurationIsCached') && $this->app->configurationIsCached()) {
            return;
        }

        /** @var Module $module */
        foreach ($registry->all() as $module) {
            if ($module->hasConfig()) {
                /** @var mixed $moduleConfig */
                $moduleConfig = require $module->getConfigPath();
                if (! is_array($moduleConfig)) {
                    continue;
                }

                $slugKey = $module->getSlug();
                $snakeKey = Str::snake($module->getName());

                $currentSlug = (array) $this->config->get($slugKey, []);
                $merged = array_replace_recursive($moduleConfig, $currentSlug);
                $this->config->set($slugKey, $merged);

                if ($snakeKey !== $slugKey) {
                    $currentSnake = (array) $this->config->get($snakeKey, []);
                    $this->config->set($snakeKey, array_replace_recursive($merged, $currentSnake));
                }
            }
        }
    }
}
