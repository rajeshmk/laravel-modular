<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Console\Commands;

use Hatchyu\Modular\Console\GeneratorCommand;
use Illuminate\Support\Str;

class ModelMakeCommand extends GeneratorCommand
{
    protected $signature = 'module:make-model
                            {module : The name of the module}
                            {name : The name of the model class}
                            {--m|migration : Create a new migration file for the model}
                            {--f|factory : Create a new factory for the model}';

    protected $description = 'Create a new Eloquent Model class inside a module';

    public function handle(): int
    {
        $module = $this->getModule();
        /** @var string $rawName */
        $rawName = $this->argument('name');
        $className = Str::studly($rawName);

        $filePath = $module->getPath("Models/{$className}.php");

        $replacements = [
            'namespace' => rtrim($this->registry->getNamespace(), '\\'),
            'module' => $module->getName(),
            'class' => $className,
        ];

        $content = $this->replacePlaceholders($this->getStub('model'), $replacements);

        if (! $this->writeFile($filePath, $content)) {
            return self::FAILURE;
        }

        $this->components->info("Model [{$className}] created successfully at [{$filePath}].");

        if ((bool) $this->option('migration')) {
            $tableName = Str::snake(Str::pluralStudly($className));
            $this->call('module:make-migration', [
                'module' => $module->getName(),
                'name' => "create_{$tableName}_table",
            ]);
        }

        if ((bool) $this->option('factory')) {
            $factoryPath = $module->getPath("database/factories/{$className}Factory.php");
            $factoryReplacements = [
                'namespace' => rtrim($this->registry->getNamespace(), '\\'),
                'module' => $module->getName(),
                'model' => $className,
            ];
            $factoryContent = $this->replacePlaceholders($this->getStub('factory'), $factoryReplacements);
            $this->writeFile($factoryPath, $factoryContent);
            $this->components->info("Factory [{$className}Factory] created successfully at [{$factoryPath}].");
        }

        return self::SUCCESS;
    }
}
