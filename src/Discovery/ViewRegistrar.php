<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Discovery;

use Hatchyu\Modular\Support\Module;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Support\Str;

final readonly class ViewRegistrar
{
    public function __construct(
        private ViewFactory $view
    ) {}

    public function register(ModuleRegistry $registry): void
    {
        /** @var Module $module */
        foreach ($registry->enabled() as $module) {
            if ($module->hasViews()) {
                $path = $module->getViewsPath();
                $slug = $module->getSlug();
                $studly = Str::studly($module->getName());

                $this->view->addNamespace($slug, $path);

                if ($studly !== $slug) {
                    $this->view->addNamespace($studly, $path);
                }
            }
        }
    }
}
