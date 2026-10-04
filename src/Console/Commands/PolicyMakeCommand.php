<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Console\Commands;

use Hatchyu\Modular\Console\GeneratorCommand;
use Illuminate\Support\Str;

class PolicyMakeCommand extends GeneratorCommand
{
    protected $signature = 'module:make-policy
                            {module : The name of the module}
                            {name : The name of the policy class (e.g. CustomerPolicy)}
                            {--m|model= : The name of the model class}';

    protected $description = 'Create a new Authorization Policy class inside Domain/Policies of a module';

    public function handle(): int
    {
        $module = $this->getModule();

        /** @var string $rawName */
        $rawName = $this->argument('name');
        $className = Str::studly($rawName);

        if (! Str::endsWith($className, 'Policy')) {
            $className .= 'Policy';
        }

        /** @var string|null $modelOption */
        $modelOption = $this->option('model');
        $modelName = $modelOption !== null && $modelOption !== ''
            ? Str::studly($modelOption)
            : Str::before($className, 'Policy');

        $filePath = $module->getPath("Domain/Policies/{$className}.php");

        $replacements = [
            'namespace' => rtrim($this->registry->getNamespace(), '\\'),
            'module' => $module->getName(),
            'class' => $className,
            'modelClass' => $modelName,
            'modelVariable' => Str::camel($modelName),
            'modelImport' => "use {$this->registry->getNamespace()}{$module->getName()}\\Domain\\Models\\{$modelName};",
        ];

        $content = $this->replacePlaceholders($this->getStub('policy'), $replacements);

        if ($this->writeFile($filePath, $content)) {
            $this->components->info("Policy [{$className}] created successfully at [{$filePath}].");

            return self::SUCCESS;
        }

        return self::FAILURE;
    }
}
