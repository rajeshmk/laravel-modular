<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Console\Commands;

use Hatchyu\Modular\Console\GeneratorCommand;
use Illuminate\Support\Str;

class ControllerMakeCommand extends GeneratorCommand
{
    protected $signature = 'module:make-controller
                            {module : The name of the module}
                            {name : The name of the controller class (e.g. OrderController or V2/OrderController)}
                            {--api : Create an API controller in Interface/Controllers/Api/V1/}
                            {--admin : Create an Admin controller in Interface/Controllers/Admin/}
                            {--force : Overwrite the file if it already exists}';

    protected $description = 'Create a new Controller class inside Interface/Controllers of a module';

    public function handle(): int
    {
        $module = $this->getModule();

        /** @var string $rawName */
        $rawName = $this->argument('name');
        [$className, $subNamespace, $relativeDir] = $this->parseClassInput($rawName);

        if (! Str::endsWith($className, 'Controller')) {
            $className .= 'Controller';
        }

        $isApi = (bool) $this->option('api');
        $isAdmin = (bool) $this->option('admin');

        $stubName = 'controller';
        $baseDir = 'Interface/Controllers';

        if ($isApi) {
            $stubName = 'controller.api';
            $baseDir = 'Interface/Controllers/Api/V1';
        } elseif ($isAdmin) {
            $stubName = 'controller.admin';
            $baseDir = 'Interface/Controllers/Admin';
        }

        $subPath = $relativeDir !== '' ? $baseDir . '/' . $relativeDir . '/' . $className : $baseDir . '/' . $className;
        $filePath = $module->getPath("{$subPath}.php");

        $replacements = [
            'namespace' => rtrim($this->registry->getNamespace(), '\\'),
            'module' => $module->getName(),
            'class' => $className,
            'subNamespace' => $subNamespace !== '' ? '\\' . $subNamespace : '',
        ];

        $content = $this->replacePlaceholders($this->getStub($stubName), $replacements);
        $force = (bool) $this->option('force');

        if ($this->writeFile($filePath, $content, $force)) {
            $this->components->info("Controller [{$className}] created successfully at [{$filePath}].");

            return self::SUCCESS;
        }

        return self::FAILURE;
    }
}
