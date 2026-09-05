<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Discovery;

use Hatchyu\Modular\Support\Module;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\Collection;

final class ModuleRegistry
{
    /**
     * @var Collection<int, Module>|null
     */
    private ?Collection $modules = null;

    public function __construct(
        private readonly ConfigRepository $config
    ) {}

    public function flush(): self
    {
        $this->modules = null;

        return $this;
    }

    /**
     * @return Collection<int, Module>
     */
    public function all(): Collection
    {
        if ($this->modules !== null) {
            return $this->modules;
        }

        $cachePath = $this->getCachePath();

        if (file_exists($cachePath)) {
            /** @var array<int, array<string, string>> $cached */
            $cached = require $cachePath;
            $this->modules = collect($cached)->map(
                fn (array $data): Module => new Module(
                    name: $data['name'],
                    path: $data['path'],
                    namespace: $data['namespace']
                )
            );

            return $this->modules;
        }

        $this->modules = $this->discover();

        return $this->modules;
    }

    public function find(string $name): ?Module
    {
        return $this->all()->first(
            fn (Module $module): bool => strcasecmp($module->getName(), $name) === 0
        );
    }

    public function has(string $name): bool
    {
        return $this->find($name) !== null;
    }

    public function isCached(): bool
    {
        return file_exists($this->getCachePath());
    }

    public function getCachePath(): string
    {
        /** @var string $path */
        return $this->config->get('modular.cache_path', base_path('bootstrap/cache/modules.php'));
    }

    public function getModulesPath(): string
    {
        /** @var string $path */
        return $this->config->get('modular.path', base_path('modules'));
    }

    public function getNamespace(): string
    {
        /** @var string $namespace */
        return $this->config->get('modular.namespace', 'Modules\\');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function toCacheArray(): array
    {
        return $this->discover()->map(
            fn (Module $module): array => [
                'name' => $module->getName(),
                'path' => $module->getPath(),
                'namespace' => $this->getNamespace(),
            ]
        )->values()->all();
    }

    /**
     * Discover modules from the filesystem.
     *
     * @return Collection<int, Module>
     */
    private function discover(): Collection
    {
        $basePath = $this->getModulesPath();

        if (! is_dir($basePath)) {
            return collect();
        }

        $directories = glob($basePath . '/*', GLOB_ONLYDIR);

        if ($directories === false) {
            return collect();
        }

        return collect($directories)
            ->map(function (string $dir): Module {
                $name = basename($dir);

                return new Module(
                    name: $name,
                    path: $dir,
                    namespace: $this->getNamespace()
                );
            })
            ->sortBy(fn (Module $m): string => $m->getName())
            ->values()
        ;
    }
}
