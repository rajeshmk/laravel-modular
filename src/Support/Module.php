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

    public function getDomainPath(?string $subPath = null): string
    {
        return $this->getPath('Domain' . ($subPath !== null && $subPath !== '' ? DIRECTORY_SEPARATOR . ltrim($subPath, '/\\') : ''));
    }

    public function getApplicationPath(?string $subPath = null): string
    {
        return $this->getPath('Application' . ($subPath !== null && $subPath !== '' ? DIRECTORY_SEPARATOR . ltrim($subPath, '/\\') : ''));
    }

    public function getInterfacePath(?string $subPath = null): string
    {
        return $this->getPath('Interface' . ($subPath !== null && $subPath !== '' ? DIRECTORY_SEPARATOR . ltrim($subPath, '/\\') : ''));
    }

    public function getInfrastructurePath(?string $subPath = null): string
    {
        return $this->getPath('Infrastructure' . ($subPath !== null && $subPath !== '' ? DIRECTORY_SEPARATOR . ltrim($subPath, '/\\') : ''));
    }

    public function getDatabasePath(?string $subPath = null): string
    {
        $base = is_dir($this->getPath('Database')) ? 'Database' : 'database';

        return $this->getPath($base . ($subPath !== null && $subPath !== '' ? DIRECTORY_SEPARATOR . ltrim($subPath, '/\\') : ''));
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
        $capitalized = $this->getPath('Database/Migrations');
        if (is_dir($capitalized)) {
            return $capitalized;
        }

        return $this->getPath('database/migrations');
    }

    public function hasFactories(): bool
    {
        return is_dir($this->getFactoriesPath());
    }

    public function getFactoriesPath(): string
    {
        $capitalized = $this->getPath('Database/Factories');
        if (is_dir($capitalized)) {
            return $capitalized;
        }

        return $this->getPath('database/factories');
    }

    public function hasSeeders(): bool
    {
        return is_dir($this->getSeedersPath());
    }

    public function getSeedersPath(): string
    {
        $capitalized = $this->getPath('Database/Seeders');
        if (is_dir($capitalized)) {
            return $capitalized;
        }

        return $this->getPath('database/seeders');
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
            'has_factories' => $this->hasFactories(),
            'has_seeders' => $this->hasSeeders(),
            'has_views' => $this->hasViews(),
            'has_config' => $this->hasConfig(),
        ];
    }
}
