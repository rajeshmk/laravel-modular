<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Console\Commands;

use Hatchyu\Modular\Console\GeneratorCommand;
use Illuminate\Support\Str;

class RepositoryMakeCommand extends GeneratorCommand
{
    protected $signature = 'module:make-repository
                            {module : The name of the module}
                            {name : The name of the repository class (e.g. OrderRepository)}
                            {--model= : The name of the model to bind to}
                            {--no-contract : Skip generating the domain contract interface}
                            {--force : Overwrite the file if it already exists}';

    protected $description = 'Create a new Repository class in Infrastructure/Repositories and optionally its Contract in Domain/Contracts';

    public function handle(): int
    {
        $module = $this->getModule();

        /** @var string $rawName */
        $rawName = $this->argument('name');
        [$className, $subNamespace, $relativeDir] = $this->parseClassInput($rawName);

        if (! Str::endsWith($className, 'Repository')) {
            $className .= 'Repository';
        }

        /** @var string|null $modelOption */
        $modelOption = $this->option('model');
        $modelName = $modelOption !== null && $modelOption !== ''
            ? Str::studly($modelOption)
            : Str::beforeLast($className, 'Repository');

        $interfaceName = "{$className}Interface";
        $force = (bool) $this->option('force');

        // 1. Generate Domain Contract unless --no-contract is set
        if (! (bool) $this->option('no-contract')) {
            $contractSubPath = $relativeDir !== '' ? $relativeDir . '/' . $interfaceName : $interfaceName;
            $contractFilePath = $module->getPath("Domain/Contracts/{$contractSubPath}.php");

            $contractReplacements = [
                'namespace' => rtrim($this->registry->getNamespace(), '\\'),
                'module' => $module->getName(),
                'class' => $interfaceName,
                'model' => $modelName,
                'subNamespace' => $subNamespace !== '' ? '\\' . $subNamespace : '',
            ];

            $contractContent = $this->replacePlaceholders($this->getStub('repository.contract'), $contractReplacements);
            $this->writeFile($contractFilePath, $contractContent, $force);
        }

        // 2. Generate Infrastructure Repository
        $repoSubPath = $relativeDir !== '' ? $relativeDir . '/' . $className : $className;
        $repoFilePath = $module->getPath("Infrastructure/Repositories/{$repoSubPath}.php");

        $repoReplacements = [
            'namespace' => rtrim($this->registry->getNamespace(), '\\'),
            'module' => $module->getName(),
            'class' => $className,
            'interface' => $interfaceName,
            'model' => $modelName,
            'subNamespace' => $subNamespace !== '' ? '\\' . $subNamespace : '',
        ];

        $repoContent = $this->replacePlaceholders($this->getStub('repository'), $repoReplacements);

        if ($this->writeFile($repoFilePath, $repoContent, $force)) {
            $this->components->info("Repository [{$className}] created successfully at [{$repoFilePath}].");

            return self::SUCCESS;
        }

        return self::FAILURE;
    }
}
