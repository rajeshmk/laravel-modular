<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Console\Commands;

use Hatchyu\Modular\Console\GeneratorCommand;
use Illuminate\Support\Str;

class CommandMakeCommand extends GeneratorCommand
{
    protected $signature = 'module:make-command
                            {module : The name of the module}
                            {name : The name of the command class (e.g. SyncOrdersCommand or V1/SyncOrdersCommand)}
                            {--command= : The terminal command that will be used to invoke the class}
                            {--force : Overwrite the file if it already exists}';

    protected $description = 'Create a new Artisan Console Command inside Interface/Console/Commands of a module';

    public function handle(): int
    {
        $module = $this->getModule();

        /** @var string $rawName */
        $rawName = $this->argument('name');
        [$className, $subNamespace, $relativeDir] = $this->parseClassInput($rawName);

        if (! Str::endsWith($className, 'Command')) {
            $className .= 'Command';
        }

        $subPath = $relativeDir !== '' ? $relativeDir . '/' . $className : $className;
        $filePath = $module->getPath("Interface/Console/Commands/{$subPath}.php");

        /** @var string|null $commandOption */
        $commandOption = $this->option('command');
        $defaultSignature = $module->getSlug() . ':' . Str::kebab(Str::before($className, 'Command'));
        $commandSignature = $commandOption !== null && $commandOption !== '' ? $commandOption : $defaultSignature;

        $replacements = [
            'namespace' => rtrim($this->registry->getNamespace(), '\\'),
            'module' => $module->getName(),
            'class' => $className,
            'subNamespace' => $subNamespace !== '' ? '\\' . $subNamespace : '',
            'commandSignature' => $commandSignature,
            'commandDescription' => "Command description for {$className}",
        ];

        $content = $this->replacePlaceholders($this->getStub('command'), $replacements);
        $force = (bool) $this->option('force');

        if ($this->writeFile($filePath, $content, $force)) {
            $this->components->info("Command [{$className}] created successfully at [{$filePath}].");

            return self::SUCCESS;
        }

        return self::FAILURE;
    }
}
