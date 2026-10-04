<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Console\Commands;

use Hatchyu\Modular\Console\GeneratorCommand;
use Illuminate\Support\Str;

class PolicyMakeCommand extends GeneratorCommand
{
    protected $signature = 'module:make-policy
                            {module : The name of the module}
                            {name : The name of the policy class (e.g. CustomerPolicy or V1/CustomerPolicy)}
                            {--m|model= : The name of the model class}
                            {--force : Overwrite the file if it already exists}';

    protected $description = 'Create a new Authorization Policy class inside Domain/Policies of a module';

    public function handle(): int
    {
        $module = $this->getModule();

        /** @var string $rawName */
        $rawName = $this->argument('name');
        [$className, $subNamespace, $relativeDir] = $this->parseClassInput($rawName);

        if (! Str::endsWith($className, 'Policy')) {
            $className .= 'Policy';
        }

        /** @var string|null $modelOption */
        $modelOption = $this->option('model');
        $modelName = $modelOption !== null && $modelOption !== ''
            ? Str::studly($modelOption)
            : Str::before($className, 'Policy');

        $subPath = $relativeDir !== '' ? $relativeDir . '/' . $className : $className;
        $filePath = $module->getPath("Domain/Policies/{$subPath}.php");

        $modelImport = '';
        if ($modelName !== '') {
            $sub = $subNamespace !== '' ? "\\{$subNamespace}" : '';
            $modelImport = "use {$this->registry->getNamespace()}{$module->getName()}\\Domain\\Models{$sub}\\{$modelName};";
        }

        $replacements = [
            'namespace' => rtrim($this->registry->getNamespace(), '\\'),
            'module' => $module->getName(),
            'class' => $className,
            'subNamespace' => $subNamespace !== '' ? '\\' . $subNamespace : '',
            'modelClass' => $modelName !== '' ? $modelName : 'mixed',
            'modelVariable' => $modelName !== '' ? Str::camel($modelName) : 'record',
            'modelImport' => $modelImport,
        ];

        $content = $this->replacePlaceholders($this->getStub('policy'), $replacements);
        $force = (bool) $this->option('force');

        if ($this->writeFile($filePath, $content, $force)) {
            $this->components->info("Policy [{$className}] created successfully at [{$filePath}].");

            return self::SUCCESS;
        }

        return self::FAILURE;
    }
}
