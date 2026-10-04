<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Console\Commands;

use Hatchyu\Modular\Console\GeneratorCommand;
use Illuminate\Support\Str;

class ServiceMakeCommand extends GeneratorCommand
{
    protected $signature = 'module:make-service
                            {module : The name of the module}
                            {name : The name of the service class (e.g. OrderPricingService)}';

    protected $description = 'Create a new Application Service class inside Application/Services of a module';

    public function handle(): int
    {
        $module = $this->getModule();

        /** @var string $rawName */
        $rawName = $this->argument('name');
        $className = Str::studly($rawName);

        if (! Str::endsWith($className, 'Service')) {
            $className .= 'Service';
        }

        $filePath = $module->getPath("Application/Services/{$className}.php");

        $replacements = [
            'namespace' => rtrim($this->registry->getNamespace(), '\\'),
            'module' => $module->getName(),
            'class' => $className,
        ];

        $content = $this->replacePlaceholders($this->getStub('service'), $replacements);

        if ($this->writeFile($filePath, $content)) {
            $this->components->info("Application Service [{$className}] created successfully at [{$filePath}].");

            return self::SUCCESS;
        }

        return self::FAILURE;
    }
}
