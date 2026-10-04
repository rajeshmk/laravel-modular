<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Console\Commands;

use Hatchyu\Modular\Console\GeneratorCommand;
use Illuminate\Support\Str;

class ResourceMakeCommand extends GeneratorCommand
{
    protected $signature = 'module:make-resource
                            {module : The name of the module}
                            {name : The name of the JsonResource class}';

    protected $description = 'Create a new JsonResource class inside Interface/Resources of a module';

    public function handle(): int
    {
        $module = $this->getModule();

        /** @var string $rawName */
        $rawName = $this->argument('name');
        $className = Str::studly($rawName);

        if (! Str::endsWith($className, 'Resource')) {
            $className .= 'Resource';
        }

        $filePath = $module->getPath("Interface/Resources/{$className}.php");

        $replacements = [
            'namespace' => rtrim($this->registry->getNamespace(), '\\'),
            'module' => $module->getName(),
            'class' => $className,
        ];

        $content = $this->replacePlaceholders($this->getStub('resource'), $replacements);

        if ($this->writeFile($filePath, $content)) {
            $this->components->info("Resource [{$className}] created successfully at [{$filePath}].");

            return self::SUCCESS;
        }

        return self::FAILURE;
    }
}
