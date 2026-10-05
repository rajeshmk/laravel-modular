<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Console\Commands;

use Hatchyu\Modular\Discovery\ModuleRegistry;
use Illuminate\Console\Command;

class ModuleClearCommand extends Command
{
    protected $signature = 'module:clear';

    /**
     * @var array<int, string>
     */
    protected $aliases = ['module:clear-cache'];

    protected $description = 'Remove the compiled module discovery cache';

    public function __construct(
        private readonly ModuleRegistry $registry
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $cachePath = $this->registry->getCachePath();

        if (file_exists($cachePath)) {
            unlink($cachePath);
            $this->components->info("Module cache cleared successfully [{$cachePath}].");
        } else {
            $this->components->info('No module cache file found.');
        }

        $this->registry->flush();

        return self::SUCCESS;
    }
}
