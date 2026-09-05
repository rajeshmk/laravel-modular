<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Console\Commands;

use Hatchyu\Modular\Discovery\ModuleRegistry;
use Illuminate\Console\Command;

class ModuleCacheCommand extends Command
{
    protected $signature = 'module:cache';

    protected $description = 'Compile and cache the module discovery manifest for production performance';

    public function __construct(
        private readonly ModuleRegistry $registry
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $cachePath = $this->registry->getCachePath();
        $cacheDir = dirname($cachePath);

        if (! is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $modules = $this->registry->toCacheArray();
        $export = var_export($modules, true);
        $content = "<?php\n\ndeclare(strict_types=1);\n\n// Compiled module discovery cache\nreturn {$export};\n";

        file_put_contents($cachePath, $content);

        $count = count($modules);
        $this->components->info("Compiled discovery cache for {$count} module(s) into [{$cachePath}].");

        return self::SUCCESS;
    }
}
