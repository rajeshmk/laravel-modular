<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Discovery;

use Hatchyu\Modular\Support\Module;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Router;

final readonly class RouteRegistrar
{
    public function __construct(
        private Router $router,
        private ConfigRepository $config,
        private ?Application $app = null
    ) {}

    public function register(ModuleRegistry $registry): void
    {
        // Skip registering routes from filesystem if Laravel routes are already cached
        if ($this->app !== null && method_exists($this->app, 'routesAreCached') && $this->app->routesAreCached()) {
            return;
        }

        /** @var array<int, string> $webMiddleware */
        $webMiddleware = $this->config->get('modular.routing.web_middleware', ['web']);

        /** @var array<int, string> $apiMiddleware */
        $apiMiddleware = $this->config->get('modular.routing.api_middleware', ['api']);

        /** @var string|null $apiPrefix */
        $apiPrefix = $this->config->get('modular.routing.api_prefix', 'api');

        /** @var Module $module */
        foreach ($registry->all() as $module) {
            if ($module->hasWebRoutes()) {
                $this->router->middleware($webMiddleware)->group($module->getWebRoutesPath());
            }

            if ($module->hasApiRoutes()) {
                $route = $this->router->middleware($apiMiddleware);
                if ($apiPrefix !== null && $apiPrefix !== '') {
                    $route = $route->prefix($apiPrefix);
                }
                $route->group($module->getApiRoutesPath());
            }
        }
    }
}
