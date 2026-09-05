<?php

declare(strict_types=1);

use Hatchyu\Modular\Discovery\ModuleRegistry;

it('caches and clears module discovery manifest', function () {
    $this->artisan('module:make', ['name' => 'Reports'])->assertSuccessful();

    $cachePath = config('modular.cache_path');
    expect(file_exists($cachePath))->toBeFalse();

    $this->artisan('module:cache')->assertSuccessful();
    expect(file_exists($cachePath))->toBeTrue();

    // Registry should indicate cached state
    $registry = new ModuleRegistry(app('config'));
    expect($registry->isCached())->toBeTrue()
        ->and($registry->all())->toHaveCount(1)
        ->and($registry->has('Reports'))->toBeTrue();

    // Clear cache
    $this->artisan('module:clear')->assertSuccessful();
    expect(file_exists($cachePath))->toBeFalse();
});
