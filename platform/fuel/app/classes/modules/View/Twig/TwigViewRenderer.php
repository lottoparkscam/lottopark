<?php

namespace Modules\View\Twig;

use Container;
use Core\App;
use Fuel\Core\Asset;
use Helpers\AssetHelper;
use Helpers\UrlHelper;
use Modules\View\ViewRendererContract;
use Throwable;
use Twig\Loader\FilesystemLoader;
use jblond\TwigTrans\Translation;
use Twig\Environment;
use Twig\Markup;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigViewRenderer implements ViewRendererContract
{
    private FilesystemLoader $loader;
    private TwigEnvironmentFactory $environmentFactory;
    private App $app;

    public function __construct(
        FilesystemLoader $loader,
        TwigEnvironmentFactory $environmentFactory,
        App $app,
    ) {
        $this->loader = $loader;
        $this->environmentFactory = $environmentFactory;
        $this->app = $app;
    }

    public function loadTranslationsExtension(Environment $twigConfig): void
    {
        $filter = new TwigFilter(
            'trans',
            function ($context, $string) {
                return Translation::transGetText($string, $context);
            },
            ['needs_context' => true]
        );
        $twigConfig->addFilter($filter);

        $twigConfig->addExtension(new Translation());
    }

    public function addStyleFunction(Environment $twigConfig): void
    {
        $isTestEnv = $this->app->isTest();

        $styleFunction = new TwigFunction(
            'style',
            function ($cssFileName) use ($isTestEnv) {
                if ($isTestEnv) {
                    return '';
                }

                return new Markup(Asset::css("$cssFileName.min.css"), 'UTF-8');
            },
        );
        $twigConfig->addFunction($styleFunction);
    }

    public function addScriptFunction(Environment $twigConfig): void
    {
        $isTestEnv = $this->app->isTest();

        $styleFunction = new TwigFunction(
            'script',
            function ($scriptFileName) use ($isTestEnv) {
                if ($isTestEnv) {
                    return '';
                }

                return new Markup(Asset::js("$scriptFileName.min.js"), 'UTF-8');
            },
        );
        $twigConfig->addFunction($styleFunction);
    }

    public function addThemeStyleFunction(Environment $twig): void
    {
        $styleFunction = new TwigFunction(
            'themeStyle',
            function ($cssFileName) {
                $domain = UrlHelper::addWwwPrefixIfNeeded(Container::get('domain'));
                try {
                    $filePath = AssetHelper::mix("css/$cssFileName", AssetHelper::TYPE_WORDPRESS);
                } catch (Throwable) {
                    $filePath = AssetHelper::mix("css/$cssFileName", AssetHelper::TYPE_WORDPRESS, true);
                }
                $styleHtml = '<link rel="stylesheet" type="text/css" href="https://' . $domain . $filePath . '" />';
                return new Markup($styleHtml, 'UTF-8');
            },
        );
        $twig->addFunction($styleFunction);
    }

    public function render(string $file, array $data = [], array $options = []): string
    {
        $pathInfo = pathinfo($file);
        $dirname = $pathInfo['dirname'];
        $basename = $pathInfo['basename'];

        $this->loader->addPath($dirname);

        $twig = $this->environmentFactory->create($this->loader);
        $this->loadTranslationsExtension($twig);

        $this->addStyleFunction($twig);
        $this->addThemeStyleFunction($twig);
        $this->addScriptFunction($twig);

        return $twig->render($basename, $data);
    }
}
