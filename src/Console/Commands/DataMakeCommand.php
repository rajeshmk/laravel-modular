<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Console\Commands;

use Hatchyu\Modular\Console\GeneratorCommand;
use Illuminate\Support\Str;

class DataMakeCommand extends GeneratorCommand
{
    protected $signature = 'module:make-data
                            {module : The name of the module}
                            {name : The name of the Data class (e.g. CreateCustomerData or OrderData)}';

    protected $description = 'Create a new Application Data transfer object inside Application/Data of a module';

    public function handle(): int
    {
        $module = $this->getModule();

        /** @var string $rawName */
        $rawName = $this->argument('name');
        $className = Str::studly($rawName);

        $filePath = $module->getPath("Application/Data/{$className}.php");

        $replacements = [
            'namespace' => rtrim($this->registry->getNamespace(), '\\'),
            'module' => $module->getName(),
            'class' => $className,
        ];

        $content = $this->replacePlaceholders($this->getStub('data'), $replacements);

        if ($this->writeFile($filePath, $content)) {
            $this->components->info("Data object [{$className}] created successfully at [{$filePath}].");

            return self::SUCCESS;
        }

        return self::FAILURE;
    }
}
