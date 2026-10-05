<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Console\Commands;

use Hatchyu\Modular\Console\GeneratorCommand;
use Illuminate\Support\Str;

class ObserverMakeCommand extends GeneratorCommand
{
    protected $signature = 'module:make-observer
                            {module : The name of the module}
                            {name : The name of the observer class (e.g. OrderObserver or V1/OrderObserver)}
                            {--m|model= : The name of the model being observed}
                            {--force : Overwrite the file if it already exists}';

    protected $description = 'Create a new Eloquent Model Observer inside Domain/Observers of a module';

    public function handle(): int
    {
        $module = $this->getModule();

        /** @var string $rawName */
        $rawName = $this->argument('name');
        [$className, $subNamespace, $relativeDir] = $this->parseClassInput($rawName);

        if (! Str::endsWith($className, 'Observer')) {
            $className .= 'Observer';
        }

        /** @var string|null $modelOption */
        $modelOption = $this->option('model');

        if ($modelOption !== null && $modelOption !== '') {
            [$modelName, $explicitSub] = $this->parseClassInput($modelOption);
            if ($explicitSub !== '') {
                $modelSubNamespace = $explicitSub;
            } elseif ($subNamespace !== '' && file_exists($module->getPath("Domain/Models/{$modelName}.php")) && ! file_exists($module->getPath("Domain/Models/{$relativeDir}/{$modelName}.php"))) {
                $modelSubNamespace = '';
            } else {
                $modelSubNamespace = $subNamespace;
            }
        } else {
            $modelName = Str::before($className, 'Observer');
            if ($subNamespace !== '' && file_exists($module->getPath("Domain/Models/{$modelName}.php")) && ! file_exists($module->getPath("Domain/Models/{$relativeDir}/{$modelName}.php"))) {
                $modelSubNamespace = '';
            } else {
                $modelSubNamespace = $subNamespace;
            }
        }

        $subPath = $relativeDir !== '' ? $relativeDir . '/' . $className : $className;
        $filePath = $module->getPath("Domain/Observers/{$subPath}.php");

        $modelImport = '';
        if ($modelName !== '') {
            $sub = $modelSubNamespace !== '' ? "\\{$modelSubNamespace}" : '';
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

        $content = $this->replacePlaceholders($this->getStub('observer'), $replacements);
        $force = (bool) $this->option('force');

        if ($this->writeFile($filePath, $content, $force)) {
            $this->components->info("Observer [{$className}] created successfully at [{$filePath}].");

            return self::SUCCESS;
        }

        return self::FAILURE;
    }
}
