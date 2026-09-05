<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Console;

use Hatchyu\Modular\Discovery\ModuleRegistry;
use Hatchyu\Modular\Support\Module;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;

abstract class GeneratorCommand extends Command
{
    public function __construct(
        protected readonly ModuleRegistry $registry
    ) {
        parent::__construct();
    }

    protected function getModule(): Module
    {
        /** @var string $name */
        $name = $this->argument('module');

        $module = $this->registry->find($name);

        if ($module !== null) {
            return $module;
        }

        // Return a virtual module instance pointing to expected location
        return new Module(
            name: Str::studly($name),
            path: $this->registry->getModulesPath() . DIRECTORY_SEPARATOR . Str::studly($name),
            namespace: $this->registry->getNamespace()
        );
    }

    protected function getStub(string $stubName): string
    {
        /** @var string|null $customPath */
        $customPath = config('modular.stubs_path');

        if ($customPath !== null && file_exists("{$customPath}/{$stubName}.stub")) {
            $content = file_get_contents("{$customPath}/{$stubName}.stub");
            if ($content !== false) {
                return $content;
            }
        }

        $defaultPath = __DIR__ . "/../stubs/{$stubName}.stub";

        if (! file_exists($defaultPath)) {
            throw new FileNotFoundException("Stub file [{$stubName}.stub] not found at [{$defaultPath}].");
        }

        $content = file_get_contents($defaultPath);

        if ($content === false) {
            throw new FileNotFoundException("Unable to read stub [{$defaultPath}].");
        }

        return $content;
    }

    /**
     * @param array<string, string> $replacements
     */
    protected function replacePlaceholders(string $stub, array $replacements): string
    {
        foreach ($replacements as $key => $value) {
            $stub = str_replace(["{{{$key}}}", "{{ {$key} }}"], $value, $stub);
        }

        return $stub;
    }

    protected function ensureDirectoryExists(string $path): void
    {
        if (! is_dir($path)) {
            mkdir($path, 0o755, true);
        }
    }

    protected function writeFile(string $path, string $content, bool $force = false): bool
    {
        if (file_exists($path) && ! $force) {
            $this->components->warn("File [{$path}] already exists.");

            return false;
        }

        $this->ensureDirectoryExists(dirname($path));
        file_put_contents($path, $content);

        return true;
    }
}
