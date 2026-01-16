<?php

namespace Modules\View\Twig;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use Wrappers\Decorators\ConfigContract;

class TwigEnvironmentFactory
{
    private ConfigContract $config;
    private array $filters;

    public function __construct(ConfigContract $config)
    {
        $this->config = $config;
        $this->addFilters();
    }

    public function create(FilesystemLoader $loader): Environment
    {
        $options = $this->config->get('view.options');
        $environment = new Environment($loader, $options);
        foreach ($this->filters as $filter) {
            $environment->addFilter($filter);
        }
        return $environment;
    }

    private function addFilters(): void
    {
        $this->filters[] = new TwigFilter('snakeToPascalWithSpace', function ($snakeCaseString) {
            return ucwords(str_replace('_', ' ', $snakeCaseString));
        });
    }
}
