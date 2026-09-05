<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Tests;

use Hatchyu\Modular\ModularServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ModularServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('modular.path', __DIR__.'/tmp/modules');
        $app['config']->set('modular.cache_path', __DIR__.'/tmp/cache/modules.php');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $tmpDir = __DIR__.'/tmp';
        if (is_dir($tmpDir)) {
            $this->removeDirectory($tmpDir);
        }

        mkdir($tmpDir.'/modules', 0755, true);
        mkdir($tmpDir.'/cache', 0755, true);
    }

    protected function tearDown(): void
    {
        $tmpDir = __DIR__.'/tmp';
        if (is_dir($tmpDir)) {
            $this->removeDirectory($tmpDir);
        }

        parent::tearDown();
    }

    protected function removeDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir) ?: [], ['.', '..']);

        foreach ($files as $file) {
            $path = $dir.DIRECTORY_SEPARATOR.$file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }
}
