<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Console\Commands;

use Hatchyu\Modular\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MailMakeCommand extends GeneratorCommand
{
    protected $signature = 'module:make-mail
                            {module : The name of the module}
                            {name : The name of the mailable class (e.g. OrderShippedMail or V1/OrderShippedMail)}
                            {--force : Overwrite the file if it already exists}';

    protected $description = 'Create a new Mailable class inside Infrastructure/Mails of a module';

    public function handle(): int
    {
        $module = $this->getModule();

        /** @var string $rawName */
        $rawName = $this->argument('name');
        [$className, $subNamespace, $relativeDir] = $this->parseClassInput($rawName);

        if (! Str::endsWith($className, 'Mail')) {
            $className .= 'Mail';
        }

        $subPath = $relativeDir !== '' ? $relativeDir . '/' . $className : $className;
        $filePath = $module->getPath("Infrastructure/Mails/{$subPath}.php");

        $viewName = $module->getSlug() . '::mail.' . Str::kebab(Str::before($className, 'Mail'));

        $replacements = [
            'namespace' => rtrim($this->registry->getNamespace(), '\\'),
            'module' => $module->getName(),
            'class' => $className,
            'subNamespace' => $subNamespace !== '' ? '\\' . $subNamespace : '',
            'subject' => Str::headline(Str::before($className, 'Mail')),
            'view' => $viewName,
        ];

        $content = $this->replacePlaceholders($this->getStub('mail'), $replacements);
        $force = (bool) $this->option('force');

        if ($this->writeFile($filePath, $content, $force)) {
            $this->components->info("Mailable [{$className}] created successfully at [{$filePath}].");

            return self::SUCCESS;
        }

        return self::FAILURE;
    }
}
