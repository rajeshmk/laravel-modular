<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Discovery;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

final readonly class PolicyGuesser
{
    public function register(string $moduleNamespace): void
    {
        $cleanNamespace = rtrim($moduleNamespace, '\\') . '\\';

        Gate::guessPolicyNamesUsing(function (string $modelClass) use ($cleanNamespace): array|string {
            if (Str::startsWith($modelClass, $cleanNamespace)) {
                $after = Str::after($modelClass, $cleanNamespace);

                if (Str::contains($after, '\\Domain\\Models\\')) {
                    $module = Str::before($after, '\\Domain\\Models\\');
                    $relativeModel = Str::after($after, '\\Domain\\Models\\');
                } elseif (Str::contains($after, '\\Models\\')) {
                    $module = Str::before($after, '\\Models\\');
                    $relativeModel = Str::after($after, '\\Models\\');
                } else {
                    $module = Str::before($after, '\\');
                    $relativeModel = class_basename($modelClass);
                }

                $candidates = [
                    "{$cleanNamespace}{$module}\\Domain\\Policies\\{$relativeModel}Policy",
                ];

                if (Str::contains($relativeModel, '\\')) {
                    $baseModel = class_basename($modelClass);
                    $candidates[] = "{$cleanNamespace}{$module}\\Domain\\Policies\\{$baseModel}Policy";
                }

                return $candidates;
            }

            // Fallback for non-modular models
            $classDirname = str_replace('/', '\\', dirname(str_replace('\\', '/', $modelClass)));

            return $classDirname . '\\Policies\\' . class_basename($modelClass) . 'Policy';
        });
    }
}
