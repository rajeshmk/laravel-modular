<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Console\Commands;

use Hatchyu\Modular\Console\GeneratorCommand;
use Illuminate\Support\Str;

class SeederMakeCommand extends GeneratorCommand
{
    protected $signature = 'module:make-seeder
                            {module : The name of the module}
                            {name : The name of the seeder class (e.g. CustomerSeeder)}';

    protected $description = 'Create a new Seeder class inside Database/Seeders of a module';

    public function handle(): int
    {
        $module = $this->getModule();

        /** @var string $rawName */
        $rawName = $this->argument('name');
        $className = Str::studly($rawName);

        if (! Str::endsWith($className, 'Seeder')) {
            $className .= 'Seeder';
        }

        $seederDir = is_dir($module->getPath('database/seeders')) && ! is_dir($module->getPath('Database/Seeders'))
            ? 'database/seeders'
            : 'Database/Seeders';

        $filePath = $module->getPath("{$seederDir}/{$className}.php");

        $replacements = [
            'namespace' => rtrim($this->registry->getNamespace(), '\\'),
            'module' => $module->getName(),
            'class' => $className,
        ];

        $content = $this->replacePlaceholders($this->getStub('seeder'), $replacements);

        if ($this->writeFile($filePath, $content)) {
            $this->components->info("Seeder [{$className}] created successfully at [{$filePath}].");

            return self::SUCCESS;
        }

        return self::FAILURE;
    }
}
