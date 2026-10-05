<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Exceptions;

use RuntimeException;

class ModuleDependencyException extends RuntimeException
{
    public static function disabledDependency(string $module, string $dependency): self
    {
        return new self("Module [{$module}] depends on module [{$dependency}], but [{$dependency}] is currently disabled. Enable it using: php artisan module:enable {$dependency}");
    }

    public static function missingDependency(string $module, string $dependency): self
    {
        return new self("Module [{$module}] depends on module [{$dependency}], but [{$dependency}] is missing or not installed.");
    }

    public static function circularDependency(string $cycle): self
    {
        return new self("Circular module dependency detected: [{$cycle}].");
    }

    public static function dependentModuleActive(string $module, string $dependent): self
    {
        return new self("Cannot disable module [{$module}] because active module [{$dependent}] depends on it. Disable [{$dependent}] first, or use --force.");
    }
}
