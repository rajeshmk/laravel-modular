<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Console\Commands;

use Hatchyu\Modular\Console\GeneratorCommand;
use Illuminate\Support\Str;

class RuleMakeCommand extends GeneratorCommand
{
    protected $signature = 'module:make-rule
                            {module : The name of the module}
                            {name : The name of the validation rule class (e.g. ValidPhoneNumberRule or V1/ValidPhoneNumberRule)}
                            {--force : Overwrite the file if it already exists}';

    protected $description = 'Create a new Validation Rule class inside Application/Rules of a module';

    public function handle(): int
    {
        $module = $this->getModule();

        /** @var string $rawName */
        $rawName = $this->argument('name');
        [$className, $subNamespace, $relativeDir] = $this->parseClassInput($rawName);

        if (! Str::endsWith($className, 'Rule')) {
            $className .= 'Rule';
        }

        $subPath = $relativeDir !== '' ? $relativeDir . '/' . $className : $className;
        $filePath = $module->getPath("Application/Rules/{$subPath}.php");

        $replacements = [
            'namespace' => rtrim($this->registry->getNamespace(), '\\'),
            'module' => $module->getName(),
            'class' => $className,
            'subNamespace' => $subNamespace !== '' ? '\\' . $subNamespace : '',
        ];

        $content = $this->replacePlaceholders($this->getStub('rule'), $replacements);
        $force = (bool) $this->option('force');

        if ($this->writeFile($filePath, $content, $force)) {
            $this->components->info("Validation Rule [{$className}] created successfully at [{$filePath}].");

            return self::SUCCESS;
        }

        return self::FAILURE;
    }
}
