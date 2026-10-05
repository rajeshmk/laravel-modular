<?php

declare(strict_types=1);

use Hatchyu\Modular\Discovery\ModuleRegistry;

it('safely renames a module and refactors namespaces, files, and references', function () {
    $this->artisan('module:make', ['name' => 'Billing'])->assertSuccessful();
    $this->artisan('module:make-action', ['module' => 'Billing', 'name' => 'ChargeInvoiceAction'])->assertSuccessful();

    $actionPath = __DIR__ . '/../tmp/modules/Billing/Application/Actions/ChargeInvoiceAction.php';
    expect(file_exists($actionPath))->toBeTrue();

    // Perform rename
    $this->artisan('module:rename', [
        'module' => 'Billing',
        'new_name' => 'Payments',
    ])->assertSuccessful();

    $registry = app(ModuleRegistry::class);
    $registry->flush();

    expect($registry->find('Billing'))->toBeNull()
        ->and($registry->find('Payments'))->not->toBeNull()
        ->and(is_dir(__DIR__ . '/../tmp/modules/Billing'))->toBeFalse()
        ->and(is_dir(__DIR__ . '/../tmp/modules/Payments'))->toBeTrue()
        ->and(file_exists(__DIR__ . '/../tmp/modules/Payments/PaymentsServiceProvider.php'))->toBeTrue()
        ->and(file_exists(__DIR__ . '/../tmp/modules/Payments/Database/Seeders/PaymentsDatabaseSeeder.php'))->toBeTrue()
    ;

    // Check refactored content inside action
    $refactoredActionPath = __DIR__ . '/../tmp/modules/Payments/Application/Actions/ChargeInvoiceAction.php';
    expect(file_exists($refactoredActionPath))->toBeTrue();
    $actionContent = file_get_contents($refactoredActionPath);
    expect($actionContent)->toContain('namespace Modules\Payments\Application\Actions;');
});

it('rejects invalid module names during rename', function () {
    $this->artisan('module:make', ['name' => 'OldModule'])->assertSuccessful();

    $this->artisan('module:rename', [
        'module' => 'OldModule',
        'new_name' => '123-Invalid!',
    ])->assertFailed();
});
