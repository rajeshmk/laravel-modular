<?php

declare(strict_types=1);

use Hatchyu\Modular\Discovery\ModuleRegistry;

it('scaffolds a complete domain module conforming to DDD 4-layer architecture', function () {
    $this->artisan('module:make', ['name' => 'Billing'])
        ->assertSuccessful()
    ;

    $registry = app(ModuleRegistry::class);
    $module = $registry->find('Billing');

    expect($module)->not->toBeNull()
        ->and($module->getName())->toBe('Billing')
        ->and($module->getSlug())->toBe('billing')
        ->and(file_exists($module->getProviderPath()))->toBeTrue()
        ->and(file_exists($module->getWebRoutesPath()))->toBeTrue()
        ->and(file_exists($module->getApiRoutesPath()))->toBeTrue()
        ->and(file_exists($module->getConfigPath()))->toBeTrue()
        ->and(is_dir($module->getPath('Domain/Models')))->toBeTrue()
        ->and(is_dir($module->getPath('Domain/Policies')))->toBeTrue()
        ->and(is_dir($module->getPath('Domain/Enums')))->toBeTrue()
        ->and(is_dir($module->getPath('Domain/Events')))->toBeTrue()
        ->and(is_dir($module->getPath('Domain/ValueObjects')))->toBeTrue()
        ->and(is_dir($module->getPath('Domain/Observers')))->toBeTrue()
        ->and(is_dir($module->getPath('Application/Actions')))->toBeTrue()
        ->and(is_dir($module->getPath('Application/Queries')))->toBeTrue()
        ->and(is_dir($module->getPath('Application/Data')))->toBeTrue()
        ->and(is_dir($module->getPath('Application/Services')))->toBeTrue()
        ->and(is_dir($module->getPath('Application/Rules')))->toBeTrue()
        ->and(is_dir($module->getPath('Interface/Controllers/Api/V1')))->toBeTrue()
        ->and(is_dir($module->getPath('Interface/Controllers/Admin')))->toBeTrue()
        ->and(is_dir($module->getPath('Interface/Requests')))->toBeTrue()
        ->and(is_dir($module->getPath('Interface/Resources')))->toBeTrue()
        ->and(is_dir($module->getPath('Interface/Console/Commands')))->toBeTrue()
        ->and(is_dir($module->getPath('Infrastructure/Jobs')))->toBeTrue()
        ->and(is_dir($module->getPath('Infrastructure/Mails')))->toBeTrue()
        ->and(is_dir($module->getPath('Infrastructure/Notifications')))->toBeTrue()
        ->and(is_dir($module->getPath('Database/Migrations')))->toBeTrue()
        ->and(is_dir($module->getPath('Database/Factories')))->toBeTrue()
        ->and(is_dir($module->getPath('Database/Seeders')))->toBeTrue()
    ;
});

it('generates individual module components via cli into proper DDD layers', function () {
    $this->artisan('module:make', ['name' => 'Catalog'])->assertSuccessful();

    // Application: Action
    $this->artisan('module:make-action', ['module' => 'Catalog', 'name' => 'CreateProductAction'])
        ->assertSuccessful()
    ;
    $actionFile = __DIR__ . '/../tmp/modules/Catalog/Application/Actions/CreateProductAction.php';
    expect(file_exists($actionFile))->toBeTrue()
        ->and(file_get_contents($actionFile))->toContain('namespace Modules\Catalog\Application\Actions;')
    ;

    // Application: Query
    $this->artisan('module:make-query', ['module' => 'Catalog', 'name' => 'GetProductListQuery'])
        ->assertSuccessful()
    ;
    $queryFile = __DIR__ . '/../tmp/modules/Catalog/Application/Queries/GetProductListQuery.php';
    expect(file_exists($queryFile))->toBeTrue()
        ->and(file_get_contents($queryFile))->toContain('namespace Modules\Catalog\Application\Queries;')
    ;

    // Application: DTO / Data
    $this->artisan('module:make-dto', ['module' => 'Catalog', 'name' => 'ProductData'])
        ->assertSuccessful()
    ;
    $dtoFile = __DIR__ . '/../tmp/modules/Catalog/Application/Data/ProductData.php';
    expect(file_exists($dtoFile))->toBeTrue()
        ->and(file_get_contents($dtoFile))->toContain('namespace Modules\Catalog\Application\Data;')
    ;

    $this->artisan('module:make-data', ['module' => 'Catalog', 'name' => 'CreateProductData'])
        ->assertSuccessful()
    ;
    $dataFile = __DIR__ . '/../tmp/modules/Catalog/Application/Data/CreateProductData.php';
    expect(file_exists($dataFile))->toBeTrue()
        ->and(file_get_contents($dataFile))->toContain('namespace Modules\Catalog\Application\Data;')
    ;

    // Application: Rule
    $this->artisan('module:make-rule', ['module' => 'Catalog', 'name' => 'ValidSkuRule'])
        ->assertSuccessful()
    ;
    $ruleFile = __DIR__ . '/../tmp/modules/Catalog/Application/Rules/ValidSkuRule.php';
    expect(file_exists($ruleFile))->toBeTrue()
        ->and(file_get_contents($ruleFile))->toContain('namespace Modules\Catalog\Application\Rules;')
    ;

    // Application: Service
    $this->artisan('module:make-service', ['module' => 'Catalog', 'name' => 'ProductPricingService'])
        ->assertSuccessful()
    ;
    $serviceFile = __DIR__ . '/../tmp/modules/Catalog/Application/Services/ProductPricingService.php';
    expect(file_exists($serviceFile))->toBeTrue()
        ->and(file_get_contents($serviceFile))->toContain('namespace Modules\Catalog\Application\Services;')
    ;

    // Interface: API Controller
    $this->artisan('module:make-controller', ['module' => 'Catalog', 'name' => 'ProductController', '--api' => true])
        ->assertSuccessful()
    ;
    $apiControllerFile = __DIR__ . '/../tmp/modules/Catalog/Interface/Controllers/Api/V1/ProductController.php';
    expect(file_exists($apiControllerFile))->toBeTrue()
        ->and(file_get_contents($apiControllerFile))->toContain('namespace Modules\Catalog\Interface\Controllers\Api\V1;')
    ;

    // Interface: Admin Controller
    $this->artisan('module:make-controller', ['module' => 'Catalog', 'name' => 'ProductAdminController', '--admin' => true])
        ->assertSuccessful()
    ;
    $adminControllerFile = __DIR__ . '/../tmp/modules/Catalog/Interface/Controllers/Admin/ProductAdminController.php';
    expect(file_exists($adminControllerFile))->toBeTrue()
        ->and(file_get_contents($adminControllerFile))->toContain('namespace Modules\Catalog\Interface\Controllers\Admin;')
    ;

    // Interface: Form Request
    $this->artisan('module:make-request', ['module' => 'Catalog', 'name' => 'StoreProductRequest'])
        ->assertSuccessful()
    ;
    $requestFile = __DIR__ . '/../tmp/modules/Catalog/Interface/Requests/StoreProductRequest.php';
    expect(file_exists($requestFile))->toBeTrue()
        ->and(file_get_contents($requestFile))->toContain('namespace Modules\Catalog\Interface\Requests;')
    ;

    // Interface: Resource
    $this->artisan('module:make-resource', ['module' => 'Catalog', 'name' => 'ProductResource'])
        ->assertSuccessful()
    ;
    $resourceFile = __DIR__ . '/../tmp/modules/Catalog/Interface/Resources/ProductResource.php';
    expect(file_exists($resourceFile))->toBeTrue()
        ->and(file_get_contents($resourceFile))->toContain('namespace Modules\Catalog\Interface\Resources;')
    ;

    // Domain: Model with factory & migration
    $this->artisan('module:make-model', ['module' => 'Catalog', 'name' => 'Product', '-m' => true, '-f' => true])
        ->assertSuccessful()
    ;
    $modelFile = __DIR__ . '/../tmp/modules/Catalog/Domain/Models/Product.php';
    expect(file_exists($modelFile))->toBeTrue()
        ->and(file_get_contents($modelFile))->toContain('namespace Modules\Catalog\Domain\Models;')
    ;

    $factoryFile = __DIR__ . '/../tmp/modules/Catalog/Database/Factories/ProductFactory.php';
    expect(file_exists($factoryFile))->toBeTrue()
        ->and(file_get_contents($factoryFile))->toContain('namespace Modules\Catalog\Database\Factories;')
        ->and(file_get_contents($factoryFile))->toContain('use Modules\Catalog\Domain\Models\Product;')
    ;

    // Domain: Policy
    $this->artisan('module:make-policy', ['module' => 'Catalog', 'name' => 'ProductPolicy'])
        ->assertSuccessful()
    ;
    $policyFile = __DIR__ . '/../tmp/modules/Catalog/Domain/Policies/ProductPolicy.php';
    expect(file_exists($policyFile))->toBeTrue()
        ->and(file_get_contents($policyFile))->toContain('namespace Modules\Catalog\Domain\Policies;')
    ;

    // Domain: Enum
    $this->artisan('module:make-enum', ['module' => 'Catalog', 'name' => 'ProductStatus'])
        ->assertSuccessful()
    ;
    $enumFile = __DIR__ . '/../tmp/modules/Catalog/Domain/Enums/ProductStatus.php';
    expect(file_exists($enumFile))->toBeTrue()
        ->and(file_get_contents($enumFile))->toContain('namespace Modules\Catalog\Domain\Enums;')
    ;

    // Domain: Event
    $this->artisan('module:make-event', ['module' => 'Catalog', 'name' => 'ProductCreatedEvent'])
        ->assertSuccessful()
    ;
    $eventFile = __DIR__ . '/../tmp/modules/Catalog/Domain/Events/ProductCreatedEvent.php';
    expect(file_exists($eventFile))->toBeTrue()
        ->and(file_get_contents($eventFile))->toContain('namespace Modules\Catalog\Domain\Events;')
    ;

    // Infrastructure: Job
    $this->artisan('module:make-job', ['module' => 'Catalog', 'name' => 'SyncProductJob'])
        ->assertSuccessful()
    ;
    $jobFile = __DIR__ . '/../tmp/modules/Catalog/Infrastructure/Jobs/SyncProductJob.php';
    expect(file_exists($jobFile))->toBeTrue()
        ->and(file_get_contents($jobFile))->toContain('namespace Modules\Catalog\Infrastructure\Jobs;')
    ;

    // Database: Seeder
    $this->artisan('module:make-seeder', ['module' => 'Catalog', 'name' => 'ProductSeeder'])
        ->assertSuccessful()
    ;
    $seederFile = __DIR__ . '/../tmp/modules/Catalog/Database/Seeders/ProductSeeder.php';
    expect(file_exists($seederFile))->toBeTrue()
        ->and(file_get_contents($seederFile))->toContain('namespace Modules\Catalog\Database\Seeders;')
    ;
});

it('generates components in nested sub-namespaces with proper namespaces', function () {
    $this->artisan('module:make', ['name' => 'Ordering'])->assertSuccessful();

    // Nested Action: V1/CreateOrderAction
    $this->artisan('module:make-action', [
        'module' => 'Ordering',
        'name' => 'V1/CreateOrderAction',
    ])->assertSuccessful();

    $nestedActionFile = __DIR__ . '/../tmp/modules/Ordering/Application/Actions/V1/CreateOrderAction.php';
    expect(file_exists($nestedActionFile))->toBeTrue()
        ->and(file_get_contents($nestedActionFile))->toContain('namespace Modules\Ordering\Application\Actions\V1;')
        ->and(file_get_contents($nestedActionFile))->toContain('class CreateOrderAction')
    ;

    // Nested Controller: Api/V2/OrderController
    $this->artisan('module:make-controller', [
        'module' => 'Ordering',
        'name' => 'V2/OrderController',
        '--api' => true,
    ])->assertSuccessful();

    $nestedControllerFile = __DIR__ . '/../tmp/modules/Ordering/Interface/Controllers/Api/V1/V2/OrderController.php';
    expect(file_exists($nestedControllerFile))->toBeTrue()
        ->and(file_get_contents($nestedControllerFile))->toContain('namespace Modules\Ordering\Interface\Controllers\Api\V1\V2;')
        ->and(file_get_contents($nestedControllerFile))->toContain('class OrderController')
    ;
});

it('supports --force flag to overwrite existing generated files', function () {
    $this->artisan('module:make', ['name' => 'Payments'])->assertSuccessful();

    $actionPath = __DIR__ . '/../tmp/modules/Payments/Application/Actions/ProcessPaymentAction.php';

    // 1. First generation
    $this->artisan('module:make-action', [
        'module' => 'Payments',
        'name' => 'ProcessPaymentAction',
    ])->assertSuccessful();

    file_put_contents($actionPath, '// Custom modification');
    expect(file_get_contents($actionPath))->toBe('// Custom modification');

    // 2. Without force should not overwrite
    $this->artisan('module:make-action', [
        'module' => 'Payments',
        'name' => 'ProcessPaymentAction',
    ])->assertFailed();
    expect(file_get_contents($actionPath))->toBe('// Custom modification');

    // 3. With --force should overwrite
    $this->artisan('module:make-action', [
        'module' => 'Payments',
        'name' => 'ProcessPaymentAction',
        '--force' => true,
    ])->assertSuccessful();
    expect(file_get_contents($actionPath))->toContain('class ProcessPaymentAction');
});

it('generates pest and phpunit tests via module:make-test', function () {
    $this->artisan('module:make', ['name' => 'Support'])->assertSuccessful();

    // Pest Feature test
    $this->artisan('module:make-test', [
        'module' => 'Support',
        'name' => 'TicketApiTest',
    ])->assertSuccessful();

    $pestTest = __DIR__ . '/../tmp/modules/Support/tests/Feature/TicketApiTest.php';
    expect(file_exists($pestTest))->toBeTrue()
        ->and(file_get_contents($pestTest))->toContain("it('performs expected Support behavior'")
    ;

    // PHPUnit Unit test
    $this->artisan('module:make-test', [
        'module' => 'Support',
        'name' => 'TicketModelTest',
        '--unit' => true,
        '--phpunit' => true,
    ])->assertSuccessful();

    $unitTest = __DIR__ . '/../tmp/modules/Support/tests/Unit/TicketModelTest.php';
    expect(file_exists($unitTest))->toBeTrue()
        ->and(file_get_contents($unitTest))->toContain('namespace Modules\Support\Tests\Unit;')
        ->and(file_get_contents($unitTest))->toContain('class TicketModelTest extends TestCase')
    ;
});

it('executes module:check diagnostic command', function () {
    $this->artisan('module:make', ['name' => 'Analytics'])->assertSuccessful();

    $this->artisan('module:check')
        ->assertSuccessful();
});
