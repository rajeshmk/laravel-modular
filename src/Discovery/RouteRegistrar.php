<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Discovery;

use Hatchyu\Modular\Support\Module;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Routing\Registrar as Router;
use Illuminate\Support\Facades\Route;

final readonly class RouteRegistrar
{
    public function __construct(
        private Router $router,
        private ConfigRepository $config
    ) {}

    public function register(ModuleRegistry $registry): void
    {
        /** @var array<int, string> $webMiddleware */
        $webMiddleware = $this->config->get('modular.routing.web_middleware', ['web']);

        /** @var array<int, string> $apiMiddleware */
        $apiMiddleware = $this->config->get('modular.routing.api_middleware', ['api']);

        /** @var Module $module */
        foreach ($registry->all() as $module) {
            if ($module->hasWebRoutes()) {
                Route::middleware($webMiddleware)->group($module->getWebRoutesPath());
            }

            if ($module->hasApiRoutes()) {
                Route::middleware($apiMiddleware)->group($module->getApiRoutesPath());
            }
        }
    }
}
