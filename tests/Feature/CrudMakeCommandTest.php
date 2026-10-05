<?php

declare(strict_types=1);

use Hatchyu\Modular\Discovery\ModuleRegistry;

it('generates a complete DDD CRUD slice via module:make-crud', function () {
    $this->artisan('module:make', ['name' => 'RealEstate'])->assertSuccessful();

    $this->artisan('module:make-crud', [
        'module' => 'RealEstate',
        'name' => 'Property',
    ])->assertSuccessful();

    $registry = app(ModuleRegistry::class);
    $module = $registry->find('RealEstate');

    expect($module)->not->toBeNull();

    // Domain: Model & Contract
    $modelFile = $module->getPath('Domain/Models/Property.php');
    $contractFile = $module->getPath('Domain/Contracts/PropertyRepositoryInterface.php');
    expect(file_exists($modelFile))->toBeTrue()
        ->and(file_get_contents($modelFile))->toContain('class Property extends Model')
        ->and(file_exists($contractFile))->toBeTrue()
        ->and(file_get_contents($contractFile))->toContain('interface PropertyRepositoryInterface')
    ;

    // Infrastructure: Repository & Factory
    $repoFile = $module->getPath('Infrastructure/Repositories/PropertyRepository.php');
    $factoryFile = $module->getPath('Database/Factories/PropertyFactory.php');
    expect(file_exists($repoFile))->toBeTrue()
        ->and(file_get_contents($repoFile))->toContain('class PropertyRepository implements PropertyRepositoryInterface')
        ->and(file_exists($factoryFile))->toBeTrue()
    ;

    // Application: DTO & Actions
    $dataFile = $module->getPath('Application/Data/PropertyData.php');
    $createAction = $module->getPath('Application/Actions/CreatePropertyAction.php');
    $updateAction = $module->getPath('Application/Actions/UpdatePropertyAction.php');
    $deleteAction = $module->getPath('Application/Actions/DeletePropertyAction.php');
    expect(file_exists($dataFile))->toBeTrue()
        ->and(file_exists($createAction))->toBeTrue()
        ->and(file_exists($updateAction))->toBeTrue()
        ->and(file_exists($deleteAction))->toBeTrue()
    ;

    // Interface: Requests, Resource & Controller
    $storeReq = $module->getPath('Interface/Requests/StorePropertyRequest.php');
    $updateReq = $module->getPath('Interface/Requests/UpdatePropertyRequest.php');
    $resource = $module->getPath('Interface/Resources/PropertyResource.php');
    $controller = $module->getPath('Interface/Controllers/Api/V1/PropertyController.php');
    expect(file_exists($storeReq))->toBeTrue()
        ->and(file_exists($updateReq))->toBeTrue()
        ->and(file_exists($resource))->toBeTrue()
        ->and(file_exists($controller))->toBeTrue()
        ->and(file_get_contents($controller))->toContain('class PropertyController')
    ;

    // Test & Route
    $testFile = $module->getPath('tests/Feature/PropertyControllerTest.php');
    expect(file_exists($testFile))->toBeTrue();

    $apiRoutes = file_get_contents($module->getApiRoutesPath());
    expect($apiRoutes)->toContain("Route::apiResource('properties'");
});

it('supports repository generation independently via module:make-repository', function () {
    $this->artisan('module:make', ['name' => 'Agency'])->assertSuccessful();

    $this->artisan('module:make-repository', [
        'module' => 'Agency',
        'name' => 'AgentRepository',
    ])->assertSuccessful();

    $module = app(ModuleRegistry::class)->find('Agency');
    expect($module)->not->toBeNull();

    $repoFile = $module->getPath('Infrastructure/Repositories/AgentRepository.php');
    $contractFile = $module->getPath('Domain/Contracts/AgentRepositoryInterface.php');

    expect(file_exists($repoFile))->toBeTrue()
        ->and(file_exists($contractFile))->toBeTrue()
        ->and(file_get_contents($contractFile))->toContain('interface AgentRepositoryInterface')
        ->and(file_get_contents($repoFile))->toContain('class AgentRepository implements AgentRepositoryInterface')
    ;
});
