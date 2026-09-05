<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Support;

use Illuminate\Support\Str;

final readonly class Module
{
    public function __construct(
        private string $name,
        private string $path,
        private string $namespace
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return Str::kebab($this->name);
    }

    public function getPath(?string $subPath = null): string
    {
        if ($subPath === null || $subPath === '') {
            return $this->path;
        }

        return rtrim($this->path, '/\\') . DIRECTORY_SEPARATOR . ltrim($subPath, '/\\');
    }

    public function getNamespace(?string $subNamespace = null): string
    {
        $base = rtrim($this->namespace, '\\') . '\\' . $this->name;

        if ($subNamespace === null || $subNamespace === '') {
            return $base;
        }

        return $base . '\\' . ltrim($subNamespace, '\\');
    }

    public function hasProvider(): bool
    {
        return file_exists($this->getProviderPath());
    }

    public function getProviderPath(): string
    {
        return $this->getPath("{$this->name}ServiceProvider.php");
    }

    public function getProviderClass(): string
    {
        return $this->getNamespace("{$this->name}ServiceProvider");
    }

    public function hasWebRoutes(): bool
    {
        return file_exists($this->getWebRoutesPath());
    }

    public function getWebRoutesPath(): string
    {
        return $this->getPath('routes/web.php');
    }

    public function hasApiRoutes(): bool
    {
        return file_exists($this->getApiRoutesPath());
    }

    public function getApiRoutesPath(): string
    {
        return $this->getPath('routes/api.php');
    }

    public function hasMigrations(): bool
    {
        return is_dir($this->getMigrationsPath());
    }

    public function getMigrationsPath(): string
    {
        return $this->getPath('database/migrations');
    }

    public function hasFactories(): bool
    {
        return is_dir($this->getFactoriesPath());
    }

    public function getFactoriesPath(): string
    {
        return $this->getPath('database/factories');
    }

    public function hasViews(): bool
    {
        return is_dir($this->getViewsPath());
    }

    public function getViewsPath(): string
    {
        return $this->getPath('resources/views');
    }

    public function hasConfig(): bool
    {
        return file_exists($this->getConfigPath());
    }

    public function getConfigPath(): string
    {
        return $this->getPath('config/config.php');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->getSlug(),
            'path' => $this->path,
            'namespace' => $this->getNamespace(),
            'provider' => $this->hasProvider() ? $this->getProviderClass() : null,
            'has_web_routes' => $this->hasWebRoutes(),
            'has_api_routes' => $this->hasApiRoutes(),
            'has_migrations' => $this->hasMigrations(),
            'has_views' => $this->hasViews(),
            'has_config' => $this->hasConfig(),
        ];
    }
}
