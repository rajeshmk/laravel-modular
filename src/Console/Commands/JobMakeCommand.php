<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Console\Commands;

use Hatchyu\Modular\Console\GeneratorCommand;
use Illuminate\Support\Str;

class JobMakeCommand extends GeneratorCommand
{
    protected $signature = 'module:make-job
                            {module : The name of the module}
                            {name : The name of the job class (e.g. SyncCustomerToCrmJob)}
                            {--sync : Create a synchronous job that does not implement ShouldQueue}';

    protected $description = 'Create a new Queue Job inside Infrastructure/Jobs of a module';

    public function handle(): int
    {
        $module = $this->getModule();

        /** @var string $rawName */
        $rawName = $this->argument('name');
        $className = Str::studly($rawName);

        if (! Str::endsWith($className, 'Job')) {
            $className .= 'Job';
        }

        $filePath = $module->getPath("Infrastructure/Jobs/{$className}.php");

        $replacements = [
            'namespace' => rtrim($this->registry->getNamespace(), '\\'),
            'module' => $module->getName(),
            'class' => $className,
        ];

        $stub = (bool) $this->option('sync') ? 'job.sync' : 'job';
        $content = $this->replacePlaceholders($this->getStub($stub), $replacements);

        if ($this->writeFile($filePath, $content)) {
            $this->components->info("Job [{$className}] created successfully at [{$filePath}].");

            return self::SUCCESS;
        }

        return self::FAILURE;
    }
}
