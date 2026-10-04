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
