<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Console\Commands;

use Hatchyu\Modular\Console\GeneratorCommand;
use Illuminate\Support\Str;

class ControllerMakeCommand extends GeneratorCommand
{
    protected $signature = 'module:make-controller
                            {module : The name of the module}
                            {name : The name of the controller class}
                            {--api : Create an API controller in Controllers/Api/V1/}
                            {--admin : Create an Admin controller in Controllers/Admin/}';

    protected $description = 'Create a new Controller class inside a module';

    public function handle(): int
    {
        $module = $this->getModule();

        /** @var string $rawName */
        $rawName = $this->argument('name');
        $className = Str::studly($rawName);

        if (! Str::endsWith($className, 'Controller')) {
            $className .= 'Controller';
        }

        $isApi = (bool) $this->option('api');
        $isAdmin = (bool) $this->option('admin');

        $stubName = 'controller';
        $subDir = 'Controllers';

        if ($isApi) {
            $stubName = 'controller.api';
            $subDir = 'Controllers/Api/V1';
        } elseif ($isAdmin) {
            $stubName = 'controller.admin';
            $subDir = 'Controllers/Admin';
        }

        $filePath = $module->getPath("{$subDir}/{$className}.php");

        $replacements = [
            'namespace' => rtrim($this->registry->getNamespace(), '\\'),
            'module' => $module->getName(),
            'class' => $className,
        ];

        $content = $this->replacePlaceholders($this->getStub($stubName), $replacements);

        if ($this->writeFile($filePath, $content)) {
            $this->components->info("Controller [{$className}] created successfully at [{$filePath}].");

            return self::SUCCESS;
        }

        return self::FAILURE;
    }
}
