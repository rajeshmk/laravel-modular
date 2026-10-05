<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Console\Commands;

use Hatchyu\Modular\Console\GeneratorCommand;
use Illuminate\Support\Str;

class ResourceMakeCommand extends GeneratorCommand
{
    protected $signature = 'module:make-resource
                            {module : The name of the module}
                            {name : The name of the JsonResource class (e.g. OrderResource or V1/OrderResource)}
                            {--force : Overwrite the file if it already exists}';

    protected $description = 'Create a new JsonResource class inside Interface/Resources of a module';

    public function handle(): int
    {
        $module = $this->getModule();

        /** @var string $rawName */
        $rawName = $this->argument('name');
        [$className, $subNamespace, $relativeDir] = $this->parseClassInput($rawName);

        if (! Str::endsWith($className, 'Resource')) {
            $className .= 'Resource';
        }

        $subPath = $relativeDir !== '' ? $relativeDir . '/' . $className : $className;
        $filePath = $module->getPath("Interface/Resources/{$subPath}.php");

        $replacements = [
            'namespace' => rtrim($this->registry->getNamespace(), '\\'),
            'module' => $module->getName(),
            'class' => $className,
            'subNamespace' => $subNamespace !== '' ? '\\' . $subNamespace : '',
        ];

        $content = $this->replacePlaceholders($this->getStub('resource'), $replacements);
        $force = (bool) $this->option('force');

        if ($this->writeFile($filePath, $content, $force)) {
            $this->components->info("Resource [{$className}] created successfully at [{$filePath}].");

            return self::SUCCESS;
        }

        return self::FAILURE;
    }
}
