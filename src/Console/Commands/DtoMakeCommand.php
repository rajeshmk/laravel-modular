<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Console\Commands;

use Hatchyu\Modular\Console\GeneratorCommand;
use Illuminate\Support\Str;

class DtoMakeCommand extends GeneratorCommand
{
    protected $signature = 'module:make-dto
                            {module : The name of the module}
                            {name : The name of the DTO/Data class (e.g. OrderData or CreateCustomerData)}';

    protected $description = 'Create a new Data/DTO class inside Application/Data of a module';

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
            $this->components->info("Data/DTO [{$className}] created successfully at [{$filePath}].");

            return self::SUCCESS;
        }

        return self::FAILURE;
    }
}
