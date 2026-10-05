<?php

declare(strict_types=1);

use Hatchyu\Modular\Discovery\ModuleRegistry;

it('caches and clears module discovery manifest', function () {
    $this->artisan('module:make', ['name' => 'Reports'])->assertSuccessful();

    $cachePath = config('modular.cache_path');
    expect(file_exists($cachePath))->toBeFalse();

    $this->artisan('module:cache')->assertSuccessful();
    expect(file_exists($cachePath))->toBeTrue();

    // Registry should indicate cached state and maintain clean namespaces
    $registry = new ModuleRegistry(app('config'));
    $module = $registry->find('Reports');

    expect($registry->isCached())->toBeTrue()
        ->and($registry->all())->toHaveCount(1)
        ->and($registry->has('Reports'))->toBeTrue()
        ->and($module)->not->toBeNull()
        ->and($module->getNamespace())->toBe('Modules\Reports')
        ->and($module->getProviderClass())->toBe('Modules\Reports\ReportsServiceProvider')
        ->and($module->hasMigrations())->toBeTrue()
        ->and($module->hasFactories())->toBeTrue()
    ;

    // Clear cache
    $this->artisan('module:clear')->assertSuccessful();
    expect(file_exists($cachePath))->toBeFalse();
});

it('clears cache via module:clear-cache alias', function () {
    $this->artisan('module:make', ['name' => 'Cacher'])->assertSuccessful();
    $this->artisan('module:cache')->assertSuccessful();

    $cacheFile = config('modular.cache_path');
    expect(file_exists($cacheFile))->toBeTrue();

    $this->artisan('module:clear-cache')->assertSuccessful();
    expect(file_exists($cacheFile))->toBeFalse();
});

it('caches discovered command classes in module manifest for zero-IO loading', function () {
    $this->artisan('module:make', ['name' => 'CliModule'])->assertSuccessful();
    $this->artisan('module:make-command', [
        'module' => 'CliModule',
        'name' => 'TestRunnerCommand',
    ])->assertSuccessful();

    $this->artisan('module:cache')->assertSuccessful();

    $cacheFile = config('modular.cache_path');
    $cached = require $cacheFile;

    $cliData = collect($cached)->firstWhere('name', 'CliModule');
    expect($cliData)->not->toBeNull()
        ->and($cliData['command_classes'])->toContain('Modules\CliModule\Interface\Console\Commands\TestRunnerCommand')
    ;
});
