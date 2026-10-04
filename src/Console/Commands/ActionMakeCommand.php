<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Console\Commands;

use Hatchyu\Modular\Console\GeneratorCommand;
use Illuminate\Support\Str;

class ActionMakeCommand extends GeneratorCommand
{
    protected $signature = 'module:make-action
                            {module : The name of the module}
                            {name : The name of the action class (e.g. CreateOrderAction)}';

    protected $description = 'Create a new Action class inside Application/Actions of a module';

    public function handle(): int
    {
        $module = $this->getModule();

        /** @var string $rawName */
        $rawName = $this->argument('name');
        $className = Str::studly($rawName);

        if (! Str::endsWith($className, 'Action')) {
            $className .= 'Action';
        }

        $filePath = $module->getPath("Application/Actions/{$className}.php");

        $replacements = [
            'namespace' => rtrim($this->registry->getNamespace(), '\\'),
            'module' => $module->getName(),
            'class' => $className,
        ];

        $content = $this->replacePlaceholders($this->getStub('action'), $replacements);

        if ($this->writeFile($filePath, $content)) {
            $this->components->info("Action [{$className}] created successfully at [{$filePath}].");

            return self::SUCCESS;
        }

        return self::FAILURE;
    }
}
