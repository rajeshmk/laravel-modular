<?php

declare(strict_types=1);

it('runs doctor health inspection successfully across all modules', function () {
    $this->artisan('module:make', ['name' => 'Reports'])->assertSuccessful();

    $this->artisan('module:doctor')
        ->assertSuccessful()
    ;

    $this->artisan('module:doctor', ['module' => 'Reports'])
        ->assertSuccessful()
    ;
});

it('auto-repairs missing layer directories with --fix', function () {
    $this->artisan('module:make', ['name' => 'Analytics'])->assertSuccessful();

    $domainPath = __DIR__ . '/../tmp/modules/Analytics/Domain';
    if (is_dir($domainPath)) {
        // Remove a layer to simulate corruption or missing directory
        unlink(__DIR__ . '/../tmp/modules/Analytics/Domain/Models/.gitkeep');
        rmdir(__DIR__ . '/../tmp/modules/Analytics/Domain/Models');
    }

    $this->artisan('module:doctor', ['--fix' => true])
        ->assertSuccessful()
    ;

    expect(is_dir(__DIR__ . '/../tmp/modules/Analytics/Domain/Models'))->toBeTrue();
});
