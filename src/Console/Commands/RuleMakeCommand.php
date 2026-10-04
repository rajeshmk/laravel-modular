<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Console\Commands;

use Hatchyu\Modular\Console\GeneratorCommand;
use Illuminate\Support\Str;

class RuleMakeCommand extends GeneratorCommand
{
    protected $signature = 'module:make-rule
                            {module : The name of the module}
                            {name : The name of the validation rule class (e.g. ValidPhoneNumberRule)}';

    protected $description = 'Create a new Validation Rule class inside Application/Rules of a module';

    public function handle(): int
    {
        $module = $this->getModule();

        /** @var string $rawName */
        $rawName = $this->argument('name');
        $className = Str::studly($rawName);

        if (! Str::endsWith($className, 'Rule')) {
            $className .= 'Rule';
        }

        $filePath = $module->getPath("Application/Rules/{$className}.php");

        $replacements = [
            'namespace' => rtrim($this->registry->getNamespace(), '\\'),
            'module' => $module->getName(),
            'class' => $className,
        ];

        $content = $this->replacePlaceholders($this->getStub('rule'), $replacements);

        if ($this->writeFile($filePath, $content)) {
            $this->components->info("Validation Rule [{$className}] created successfully at [{$filePath}].");

            return self::SUCCESS;
        }

        return self::FAILURE;
    }
}
