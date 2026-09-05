<?php

declare(strict_types=1);

use Hatchyu\Modular\Discovery\FactoryGuesser;
use Hatchyu\Modular\Discovery\ModuleRegistry;
use Illuminate\Database\Eloquent\Factories\Factory;

it('discovers modules dynamically from the filesystem', function () {
    $this->artisan('module:make', ['name' => 'Inventory'])->assertSuccessful();
    $this->artisan('module:make', ['name' => 'Shipping'])->assertSuccessful();

    $registry = app(ModuleRegistry::class);
    $modules = $registry->all();

    expect($modules)->toHaveCount(2)
        ->and($registry->has('Inventory'))->toBeTrue()
        ->and($registry->has('Shipping'))->toBeTrue()
        ->and($registry->has('NonExistent'))->toBeFalse();
});

it('lists all modules via module:list', function () {
    $this->artisan('module:make', ['name' => 'Sales'])->assertSuccessful();

    $this->artisan('module:list')
        ->assertSuccessful();
});

it('correctly guesses factory names for modular models', function () {
    $guesser = new FactoryGuesser;
    $guesser->register('Modules\\');

    $guessed = Factory::resolveFactoryName('Modules\\Order\\Models\\Order');

    expect($guessed)->toBe('Modules\\Order\\Database\\Factories\\OrderFactory');
});

it('creates and checks migration file for module', function () {
    $this->artisan('module:make', ['name' => 'Finance'])->assertSuccessful();
    $this->artisan('module:make-migration', [
        'module' => 'Finance',
        'name' => 'create_invoices_table',
    ])->assertSuccessful();

    $migrations = glob(__DIR__.'/../tmp/modules/Finance/database/migrations/*_create_invoices_table.php');
    expect($migrations)->toHaveCount(1);
});
