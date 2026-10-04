<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Console\Commands;

use Hatchyu\Modular\Console\GeneratorCommand;
use Illuminate\Support\Str;

class JobMakeCommand extends GeneratorCommand
{
    protected $signature = 'module:make-job
                            {module : The name of the module}
                            {name : The name of the job class (e.g. SyncCustomerToCrmJob or V1/SyncCustomerToCrmJob)}
                            {--sync : Create a synchronous job that does not implement ShouldQueue}
                            {--force : Overwrite the file if it already exists}';

    protected $description = 'Create a new Queue Job inside Infrastructure/Jobs of a module';

    public function handle(): int
    {
        $module = $this->getModule();

        /** @var string $rawName */
        $rawName = $this->argument('name');
        [$className, $subNamespace, $relativeDir] = $this->parseClassInput($rawName);

        if (! Str::endsWith($className, 'Job')) {
            $className .= 'Job';
        }

        $subPath = $relativeDir !== '' ? $relativeDir . '/' . $className : $className;
        $filePath = $module->getPath("Infrastructure/Jobs/{$subPath}.php");

        $replacements = [
            'namespace' => rtrim($this->registry->getNamespace(), '\\'),
            'module' => $module->getName(),
            'class' => $className,
            'subNamespace' => $subNamespace !== '' ? '\\' . $subNamespace : '',
        ];

        $stub = (bool) $this->option('sync') ? 'job.sync' : 'job';
        $content = $this->replacePlaceholders($this->getStub($stub), $replacements);
        $force = (bool) $this->option('force');

        if ($this->writeFile($filePath, $content, $force)) {
            $this->components->info("Job [{$className}] created successfully at [{$filePath}].");

            return self::SUCCESS;
        }

        return self::FAILURE;
    }
}
