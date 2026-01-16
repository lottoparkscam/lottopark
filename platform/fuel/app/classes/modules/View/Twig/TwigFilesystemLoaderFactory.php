<?php

namespace Modules\View\Twig;

use Twig\Loader\FilesystemLoader;
use Wrappers\Decorators\ConfigContract;

class TwigFilesystemLoaderFactory
{
    private ConfigContract $config;

    public function __construct(ConfigContract $config)
    {
        $this->config = $config;
    }

    public function create(): FilesystemLoader
    {
        $paths = $this->config->get('view.template_directories');
        return new FilesystemLoader($paths);
    }
}
