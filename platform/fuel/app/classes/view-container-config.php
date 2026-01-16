<?php

use DI\Container as Container;
use Modules\View\Twig\TwigFilesystemLoaderFactory;
use Twig\Loader\FilesystemLoader;

return [
    /**************************************************************************/
    /* TWIG                                                                   */
    /**************************************************************************/

    FilesystemLoader::class => fn (Container $c) => $c->get(TwigFilesystemLoaderFactory::class)->create()
];
