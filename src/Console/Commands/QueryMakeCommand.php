<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Console\Commands;

use Hatchyu\Modular\Console\GeneratorCommand;
use Illuminate\Support\Str;

class QueryMakeCommand extends GeneratorCommand
{
    protected $signature = 'module:make-query
                            {module : The name of the module}
                            {name : The name of the query class (e.g. GetOrderListQuery)}';

    protected $description = 'Create a new Query class inside a module';

    public function handle(): int
    {
        $module = $this->getModule();

        /** @var string $rawName */
        $rawName = $this->argument('name');
        $className = Str::studly($rawName);

        if (! Str::endsWith($className, 'Query')) {
            $className .= 'Query';
        }

        $filePath = $module->getPath("Queries/{$className}.php");

        $replacements = [
            'namespace' => rtrim($this->registry->getNamespace(), '\\'),
            'module' => $module->getName(),
            'class' => $className,
        ];

        $content = $this->replacePlaceholders($this->getStub('query'), $replacements);

        if ($this->writeFile($filePath, $content)) {
            $this->components->info("Query [{$className}] created successfully at [{$filePath}].");

            return self::SUCCESS;
        }

        return self::FAILURE;
    }
}
