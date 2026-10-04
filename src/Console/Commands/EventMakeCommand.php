<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Console\Commands;

use Hatchyu\Modular\Console\GeneratorCommand;
use Illuminate\Support\Str;

class EventMakeCommand extends GeneratorCommand
{
    protected $signature = 'module:make-event
                            {module : The name of the module}
                            {name : The name of the event class (e.g. OrderPlacedEvent)}';

    protected $description = 'Create a new Domain Event class inside Domain/Events of a module';

    public function handle(): int
    {
        $module = $this->getModule();

        /** @var string $rawName */
        $rawName = $this->argument('name');
        $className = Str::studly($rawName);

        if (! Str::endsWith($className, 'Event')) {
            $className .= 'Event';
        }

        $filePath = $module->getPath("Domain/Events/{$className}.php");

        $replacements = [
            'namespace' => rtrim($this->registry->getNamespace(), '\\'),
            'module' => $module->getName(),
            'class' => $className,
        ];

        $content = $this->replacePlaceholders($this->getStub('event'), $replacements);

        if ($this->writeFile($filePath, $content)) {
            $this->components->info("Domain Event [{$className}] created successfully at [{$filePath}].");

            return self::SUCCESS;
        }

        return self::FAILURE;
    }
}
