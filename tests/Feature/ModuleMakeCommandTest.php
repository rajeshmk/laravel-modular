<?php

declare(strict_types=1);

use Hatchyu\Modular\Discovery\ModuleRegistry;

it('scaffolds a complete domain module', function () {
    $this->artisan('module:make', ['name' => 'Billing'])
        ->assertSuccessful();

    $registry = app(ModuleRegistry::class);
    $module = $registry->find('Billing');

    expect($module)->not->toBeNull()
        ->and($module->getName())->toBe('Billing')
        ->and($module->getSlug())->toBe('billing')
        ->and(file_exists($module->getProviderPath()))->toBeTrue()
        ->and(file_exists($module->getWebRoutesPath()))->toBeTrue()
        ->and(file_exists($module->getApiRoutesPath()))->toBeTrue()
        ->and(file_exists($module->getConfigPath()))->toBeTrue()
        ->and(is_dir($module->getPath('Actions')))->toBeTrue()
        ->and(is_dir($module->getPath('Queries')))->toBeTrue()
        ->and(is_dir($module->getPath('Controllers/Api/V1')))->toBeTrue();
});

it('generates individual module components via cli', function () {
    $this->artisan('module:make', ['name' => 'Catalog'])->assertSuccessful();

    // Action
    $this->artisan('module:make-action', ['module' => 'Catalog', 'name' => 'CreateProductAction'])
        ->assertSuccessful();
    expect(file_exists(__DIR__.'/../tmp/modules/Catalog/Actions/CreateProductAction.php'))->toBeTrue();

    // Query
    $this->artisan('module:make-query', ['module' => 'Catalog', 'name' => 'GetProductListQuery'])
        ->assertSuccessful();
    expect(file_exists(__DIR__.'/../tmp/modules/Catalog/Queries/GetProductListQuery.php'))->toBeTrue();

    // DTO
    $this->artisan('module:make-dto', ['module' => 'Catalog', 'name' => 'ProductData'])
        ->assertSuccessful();
    expect(file_exists(__DIR__.'/../tmp/modules/Catalog/DTOs/ProductData.php'))->toBeTrue();

    // API Controller
    $this->artisan('module:make-controller', ['module' => 'Catalog', 'name' => 'ProductController', '--api' => true])
        ->assertSuccessful();
    expect(file_exists(__DIR__.'/../tmp/modules/Catalog/Controllers/Api/V1/ProductController.php'))->toBeTrue();

    // Form Request
    $this->artisan('module:make-request', ['module' => 'Catalog', 'name' => 'StoreProductRequest'])
        ->assertSuccessful();
    expect(file_exists(__DIR__.'/../tmp/modules/Catalog/Requests/StoreProductRequest.php'))->toBeTrue();

    // Model
    $this->artisan('module:make-model', ['module' => 'Catalog', 'name' => 'Product'])
        ->assertSuccessful();
    expect(file_exists(__DIR__.'/../tmp/modules/Catalog/Models/Product.php'))->toBeTrue();
});
