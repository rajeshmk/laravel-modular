<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Console\Commands;

use Hatchyu\Modular\Console\GeneratorCommand;
use Illuminate\Support\Str;

class ActionMakeCommand extends GeneratorCommand
{
    protected $signature = 'module:make-action
                            {module : The name of the module}
                            {name : The name of the action class (e.g. CreateOrderAction or V1/CreateOrderAction)}
                            {--force : Overwrite the file if it already exists}';

    protected $description = 'Create a new Action class inside Application/Actions of a module';

    public function handle(): int
    {
        $module = $this->getModule();

        /** @var string $rawName */
        $rawName = $this->argument('name');
        [$className, $subNamespace, $relativeDir] = $this->parseClassInput($rawName);

        if (! Str::endsWith($className, 'Action')) {
            $className .= 'Action';
        }

        $subPath = $relativeDir !== '' ? $relativeDir . '/' . $className : $className;
        $filePath = $module->getPath("Application/Actions/{$subPath}.php");

        $replacements = [
            'namespace' => rtrim($this->registry->getNamespace(), '\\'),
            'module' => $module->getName(),
            'class' => $className,
            'subNamespace' => $subNamespace !== '' ? '\\' . $subNamespace : '',
        ];

        $content = $this->replacePlaceholders($this->getStub('action'), $replacements);
        $force = (bool) $this->option('force');

        if ($this->writeFile($filePath, $content, $force)) {
            $this->components->info("Action [{$className}] created successfully at [{$filePath}].");

            return self::SUCCESS;
        }

        return self::FAILURE;
    }
}
