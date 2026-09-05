<?php

declare(strict_types=1);

namespace Hatchyu\Modular\Discovery;

use Hatchyu\Modular\Support\Module;
use Illuminate\Contracts\View\Factory as ViewFactory;

final readonly class ViewRegistrar
{
    public function __construct(
        private ViewFactory $view
    ) {}

    public function register(ModuleRegistry $registry): void
    {
        /** @var Module $module */
        foreach ($registry->all() as $module) {
            if ($module->hasViews()) {
                $this->view->addNamespace($module->getSlug(), $module->getViewsPath());
            }
        }
    }
}
