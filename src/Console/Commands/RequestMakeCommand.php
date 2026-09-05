<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Console\Commands;

use Hatchyu\Modular\Console\GeneratorCommand;
use Illuminate\Support\Str;

class RequestMakeCommand extends GeneratorCommand
{
    protected $signature = 'module:make-request
                            {module : The name of the module}
                            {name : The name of the FormRequest class}';

    protected $description = 'Create a new FormRequest class inside a module';

    public function handle(): int
    {
        $module = $this->getModule();
        /** @var string $rawName */
        $rawName = $this->argument('name');
        $className = Str::studly($rawName);

        if (! Str::endsWith($className, 'Request')) {
            $className .= 'Request';
        }

        $filePath = $module->getPath("Requests/{$className}.php");

        $replacements = [
            'namespace' => rtrim($this->registry->getNamespace(), '\\'),
            'module' => $module->getName(),
            'class' => $className,
        ];

        $content = $this->replacePlaceholders($this->getStub('request'), $replacements);

        if ($this->writeFile($filePath, $content)) {
            $this->components->info("Request [{$className}] created successfully at [{$filePath}].");

            return self::SUCCESS;
        }

        return self::FAILURE;
    }
}
