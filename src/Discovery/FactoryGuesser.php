<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Discovery;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

final readonly class FactoryGuesser
{
    public function register(string $moduleNamespace): void
    {
        $cleanNamespace = rtrim($moduleNamespace, '\\') . '\\';

        Factory::guessFactoryNamesUsing(function (string $modelName) use ($cleanNamespace): string {
            if (Str::startsWith($modelName, $cleanNamespace)) {
                $after = Str::after($modelName, $cleanNamespace);

                if (Str::contains($after, '\\Domain\\Models\\')) {
                    $module = Str::before($after, '\\Domain\\Models\\');
                } elseif (Str::contains($after, '\\Models\\')) {
                    $module = Str::before($after, '\\Models\\');
                } else {
                    $module = Str::before($after, '\\');
                }

                $modelBasename = class_basename($modelName);

                return "{$cleanNamespace}{$module}\\Database\\Factories\\{$modelBasename}Factory";
            }

            return 'Database\\Factories\\' . class_basename($modelName) . 'Factory';
        });
    }
}
