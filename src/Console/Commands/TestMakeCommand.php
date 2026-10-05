<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Console\Commands;

use Hatchyu\Modular\Console\GeneratorCommand;
use Illuminate\Support\Str;

class TestMakeCommand extends GeneratorCommand
{
    protected $signature = 'module:make-test
                            {module : The name of the module}
                            {name : The name of the test class (e.g. OrderApiTest or V1/OrderApiTest)}
                            {--unit : Create a unit test instead of a feature test}
                            {--arch : Create a Pest architecture test}
                            {--phpunit : Generate standard PHPUnit test instead of Pest}
                            {--force : Overwrite the file if it already exists}';

    protected $description = 'Create a new Pest or PHPUnit test inside a module';

    public function handle(): int
    {
        $module = $this->getModule();

        /** @var string $rawName */
        $rawName = $this->argument('name');
        [$className, $subNamespace, $relativeDir] = $this->parseClassInput($rawName);

        if (! Str::endsWith($className, 'Test')) {
            $className .= 'Test';
        }

        $isArch = (bool) $this->option('arch');
        $isPest = ! (bool) $this->option('phpunit');

        if ($isArch) {
            $type = 'Feature';
            $stubName = 'test.arch';
        } else {
            $type = (bool) $this->option('unit') ? 'Unit' : 'Feature';
            $stubName = $isPest ? 'test.pest' : 'test.phpunit';
        }

        $subPath = $relativeDir !== '' ? "tests/{$type}/{$relativeDir}/{$className}" : "tests/{$type}/{$className}";
        $filePath = $module->getPath("{$subPath}.php");

        $replacements = [
            'namespace' => rtrim($this->registry->getNamespace(), '\\'),
            'module' => $module->getName(),
            'class' => $className,
            'type' => $type,
            'subNamespace' => $subNamespace !== '' ? '\\' . $subNamespace : '',
        ];

        $content = $this->replacePlaceholders($this->getStub($stubName), $replacements);
        $force = (bool) $this->option('force');

        if ($this->writeFile($filePath, $content, $force)) {
            $this->components->info("Test [{$className}] created successfully at [{$filePath}].");

            return self::SUCCESS;
        }

        return self::FAILURE;
    }
}
