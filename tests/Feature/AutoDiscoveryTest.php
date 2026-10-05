<?php

declare(strict_types=1);

use Hatchyu\Modular\Discovery\CommandRegistrar;
use Hatchyu\Modular\Discovery\FactoryGuesser;
use Hatchyu\Modular\Discovery\ModuleRegistry;
use Hatchyu\Modular\Discovery\PolicyGuesser;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Database\Eloquent\Factories\Factory;

it('discovers modules dynamically from the filesystem', function () {
    $this->artisan('module:make', ['name' => 'Inventory'])->assertSuccessful();
    $this->artisan('module:make', ['name' => 'Shipping'])->assertSuccessful();

    $registry = app(ModuleRegistry::class);
    $modules = $registry->all();

    expect($modules)->toHaveCount(2)
        ->and($registry->has('Inventory'))->toBeTrue()
        ->and($registry->has('Shipping'))->toBeTrue()
        ->and($registry->has('NonExistent'))->toBeFalse()
    ;
});

it('lists all modules via module:list', function () {
    $this->artisan('module:make', ['name' => 'Sales'])->assertSuccessful();

    $this->artisan('module:list')
        ->assertSuccessful()
    ;
});

it('correctly guesses factory names for modular models in DDD, nested, and flat layouts', function () {
    $guesser = new FactoryGuesser();
    $guesser->register('Modules\\');

    // Standard DDD 4-Layer layout
    $guessedDdd = Factory::resolveFactoryName('Modules\\Order\\Domain\\Models\\Order');
    expect($guessedDdd)->toBe('Modules\\Order\\Database\\Factories\\OrderFactory');

    // Nested DDD model in sub-namespace
    $guessedNested = Factory::resolveFactoryName('Modules\\Order\\Domain\\Models\\Relations\\OrderItem');
    expect($guessedNested)->toBe('Modules\\Order\\Database\\Factories\\Relations\\OrderItemFactory');

    // Deep nested DDD model
    $guessedDeep = Factory::resolveFactoryName('Modules\\Customer\\Domain\\Models\\Address\\Sub\\CustomerAddress');
    expect($guessedDeep)->toBe('Modules\\Customer\\Database\\Factories\\Address\\Sub\\CustomerAddressFactory');

    // Legacy flat layout
    $guessedFlat = Factory::resolveFactoryName('Modules\\Order\\Models\\Order');
    expect($guessedFlat)->toBe('Modules\\Order\\Database\\Factories\\OrderFactory');
});

it('correctly guesses policy names for modular models in root and nested sub-namespaces', function () {
    $guesser = new PolicyGuesser();
    $guesser->register('Modules\\');

    $gate = app(Gate::class);
    $reflection = new ReflectionProperty($gate, 'guessPolicyNamesUsingCallback');

    /** @var callable $callback */
    $callback = $reflection->getValue($gate);

    $root = $callback('Modules\\Order\\Domain\\Models\\Order');
    expect($root)->toBe(['Modules\\Order\\Domain\\Policies\\OrderPolicy']);

    $nested = $callback('Modules\\Order\\Domain\\Models\\Relations\\OrderItem');
    expect($nested)->toBe([
        'Modules\\Order\\Domain\\Policies\\Relations\\OrderItemPolicy',
        'Modules\\Order\\Domain\\Policies\\OrderItemPolicy',
    ]);
});

it('creates and checks migration file for module in Database/Migrations', function () {
    $this->artisan('module:make', ['name' => 'Finance'])->assertSuccessful();
    $this->artisan('module:make-migration', [
        'module' => 'Finance',
        'name' => 'create_invoices_table',
    ])->assertSuccessful();

    $migrations = glob(__DIR__ . '/../tmp/modules/Finance/Database/Migrations/*_create_invoices_table.php');
    expect($migrations)->toHaveCount(1);
});

it('finds modules by studly name, kebab slug, snake_case, and camelCase', function () {
    $this->artisan('module:make', ['name' => 'RealEstate'])->assertSuccessful();

    $registry = app(ModuleRegistry::class);

    expect($registry->find('RealEstate'))->not->toBeNull()
        ->and($registry->find('real-estate'))->not->toBeNull()
        ->and($registry->find('real_estate'))->not->toBeNull()
        ->and($registry->find('realEstate'))->not->toBeNull()
        ->and($registry->find('real-estate')?->getName())->toBe('RealEstate')
        ->and($registry->find('non-existent'))->toBeNull()
    ;
});

it('filters out ignored directories and dotfiles during discovery', function () {
    $modulesPath = config('modular.path');
    mkdir($modulesPath . '/node_modules', 0o755, true);
    mkdir($modulesPath . '/.cache', 0o755, true);

    $registry = app(ModuleRegistry::class);
    $registry->flush();

    expect($registry->has('node_modules'))->toBeFalse()
        ->and($registry->has('.cache'))->toBeFalse()
    ;
});

it('prevents accidental duplicate migration creation without --force', function () {
    $this->artisan('module:make', ['name' => 'Warehouse'])->assertSuccessful();

    // 1. Initial creation
    $this->artisan('module:make-migration', [
        'module' => 'Warehouse',
        'name' => 'create_pallets_table',
    ])->assertSuccessful();

    // 2. Duplicate without force should fail
    $this->artisan('module:make-migration', [
        'module' => 'Warehouse',
        'name' => 'create_pallets_table',
    ])->assertFailed();

    // 3. Duplicate with force should succeed
    $this->artisan('module:make-migration', [
        'module' => 'Warehouse',
        'name' => 'create_pallets_table',
        '--force' => true,
    ])->assertSuccessful();
});

it('executes module:seed command for specific module and all modules', function () {
    $this->artisan('module:make', ['name' => 'SeedingModule'])->assertSuccessful();

    $this->artisan('module:seed', ['module' => 'SeedingModule'])
        ->assertSuccessful()
    ;

    $this->artisan('module:seed')
        ->assertSuccessful()
    ;
});

it('discovers and registers module console commands', function () {
    $this->artisan('module:make', ['name' => 'Commander'])->assertSuccessful();
    $this->artisan('module:make-command', [
        'module' => 'Commander',
        'name' => 'GreetUserCommand',
        '--command' => 'commander:greet',
    ])->assertSuccessful();

    $commandFile = __DIR__ . '/../tmp/modules/Commander/Interface/Console/Commands/GreetUserCommand.php';
    expect(file_exists($commandFile))->toBeTrue();

    require_once $commandFile;

    $registry = app(ModuleRegistry::class)->flush();
    (new CommandRegistrar(app()))->register($registry);

    $this->artisan('commander:greet')->assertSuccessful();
});
