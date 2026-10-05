<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Console\Commands;

use Hatchyu\Modular\Console\GeneratorCommand;

class EnumMakeCommand extends GeneratorCommand
{
    protected $signature = 'module:make-enum
                            {module : The name of the module}
                            {name : The name of the enum (e.g. OrderStatus or V1/OrderStatus)}
                            {--force : Overwrite the file if it already exists}';

    protected $description = 'Create a new backed Enum inside Domain/Enums of a module';

    public function handle(): int
    {
        $module = $this->getModule();

        /** @var string $rawName */
        $rawName = $this->argument('name');
        [$className, $subNamespace, $relativeDir] = $this->parseClassInput($rawName);

        $subPath = $relativeDir !== '' ? $relativeDir . '/' . $className : $className;
        $filePath = $module->getPath("Domain/Enums/{$subPath}.php");

        $replacements = [
            'namespace' => rtrim($this->registry->getNamespace(), '\\'),
            'module' => $module->getName(),
            'class' => $className,
            'subNamespace' => $subNamespace !== '' ? '\\' . $subNamespace : '',
        ];

        $content = $this->replacePlaceholders($this->getStub('enum'), $replacements);
        $force = (bool) $this->option('force');

        if ($this->writeFile($filePath, $content, $force)) {
            $this->components->info("Enum [{$className}] created successfully at [{$filePath}].");

            return self::SUCCESS;
        }

        return self::FAILURE;
    }
}
