<?php

class WordpressInFuel
{
    private array $domain_chunks = [];
    private array $route_chunks = [];
    private static string $domainFromCli = '';
    private static bool $isDomainLoadedSuccessfully = false;

    private ?int $blog_id = null;

    private const ALLOWED_CLI_COMMANDS_DURING_MAINTENANCE_MODE = [
        'migrate',
        'migrate:current',
        'migrate:up',
        'migrate:down',
        'install',
        'optimize:refreshAllPostsIds',
        'optimize:refreshAllPermalinks',
        'optimize:refreshPostsIds',
        'optimize:refreshPermalinks',
        'page_cache:clear'
    ];

    public function __construct()
    {
        if (empty($_SERVER['HTTP_HOST'])) {
            return;
        }

        if (empty($_SERVER['REQUEST_URI'])) {
            return;
        }

        if ($this->isDotInHost()) {
            $this->setNotDottedHost();
            header('Location: ' . 'https://' . $_SERVER['HTTP_HOST'], true, 301);
            exit();
        }

        $this->domain_chunks = explode('.', $_SERVER['HTTP_HOST']);
        $this->route_chunks = explode('/', $_SERVER['REQUEST_URI']);
    }

    public function setNotDottedHost(): void
    {
        $_SERVER['HTTP_HOST'] = trim($_SERVER['HTTP_HOST'], '.');
    }

    public function isDotInHost(): bool
    {
        return str_ends_with($_SERVER['HTTP_HOST'], '.');
    }

    // no Wordpress here
    public function runBootstrap(): void
    {
        if (!defined('IS_CASINO')) {
            define('IS_CASINO', $this->isCasino());
        }

        if (file_exists(realpath(APPPATH . '/.maintenance'))) {
            if ($this->isApi()) {
                http_response_code(503);
                exit();
            }

            if ($this->isCli()) {
                if (!empty($_SERVER['argv']) && !empty($_SERVER['argv'][2])) {
                    $cliArgumentAfterOilRefine = $_SERVER['argv'][2];
                    if (!in_array($cliArgumentAfterOilRefine, self::ALLOWED_CLI_COMMANDS_DURING_MAINTENANCE_MODE)) {
                        echo "Maintenance mode, only these commands are allowed:\n- "
                            . implode("\n- ", self::ALLOWED_CLI_COMMANDS_DURING_MAINTENANCE_MODE) . "\n";
                        exit(1);
                    }
                }
            }
        }

        if ($this->isCli() && $this->domainExistInCli()) {
            $this->setDomain();
            $this->setFakeIp();
            $this->defineConstants();
            $this->loadWordpress();
            return;
        }

        if ($this->isEmpire() && $this->isController('task') && $this->isAction("checkpages")) {
            $this->defineConstants();
            $this->loadWordpress();
        }
    }

    // early Wordpress here
    public function runSunrise(): void
    {
        if (!defined('IS_CASINO')) {
            define('IS_CASINO', $this->isCasino());
        }

        if ($this->isCli() && $this->domainExistInCli()) {
            $this->findBlog();
            $this->addFilter();
            return;
        }

        if ($this->isEmpire() && $this->isController('task') && $this->isAction("checkpages")) {
            $this->findBlog();
            $this->addFilter();
        }

        $isCasino = $this->isCasino();
        if ($isCasino) {
            $this->findBlog();
            $this->addFilter();
        }
    }

    private function defineConstants(): void
    {
        define("WORDPRESS_INSIDE_FUEL", true);
    }

    private function loadWordpress(): void
    {
        require_once(APPPATH . '../../../wordpress/wp-load.php');
    }

    private function addFilter(): void
    {
        if ($this->blog_id === null) {
            return;
        }
        $blog = WP_Site::get_instance($this->blog_id);
        if ($blog !== false) {
            add_filter('pre_get_site_by_path', fn () => $blog);
        }
    }

    private function findBlog(): void
    {
        switch (true) {
            case $this->isCli():
                $domain = self::$domainFromCli;
                break;
            case $this->isCasino():
                $host = $_SERVER['HTTP_HOST'];
                $isLottohoy = strpos($host, 'lottohoy') !== false;
                if ($isLottohoy) {
                    try {
                        $casinoPrefix = Helpers\UrlHelper::getCasinoPrefixForWhitelabel('lottohoy.com');
                    } catch (Throwable $exception) {
                        $casinoPrefix = 'casino';
                    }
                    $domain = str_replace($casinoPrefix, 'www', $host);
                } else {
                    try {
                        $currentCasinoPrefix = Helpers\UrlHelper::getCurrentCasinoPrefix();
                    } catch (Throwable $exception) {
                        $currentCasinoPrefix = 'casino';
                    }

                    // +1 because we want to delete dot after prefix too
                    $prefixLength = strlen($currentCasinoPrefix) + 1;
                    $domain = substr($host, $prefixLength);
                }
                break;
            default:
                $domain = $this->getParam(3);
        }

        if (empty($domain)) {
            return;
        }

        $this->domain = $domain;

        $site = get_site_by_path($domain, '/');
        self::$isDomainLoadedSuccessfully = !empty($site);
        define("WORDPRESS_INSIDE_FUEL_DOMAIN", $domain);

        if ($site === false) {
            return;
        }

        $this->blog_id = $site->blog_id;
    }

    private function isEmpire(): bool
    {
        $domain = $this->domain_chunks;
        return count($domain) >= 3 && $domain[0] === 'empire';
    }

    private function isApi(): bool
    {
        $domain = $this->domain_chunks;
        return count($domain) >= 3 && $domain[0] === 'api';
    }

    private function isCli(): bool
    {
        if (php_sapi_name() === 'cli') {
            return true;
        }

        return false;
    }

    private function isController($search): bool
    {
        return $this->isParam($search, 1);
    }

    private function isAction($search): bool
    {
        return $this->isParam($search, 2);
    }

    private function getParam($index): ?string
    {
        return $this->route_chunks[$index] ?? null;
    }

    private function isParam($search, $index): bool
    {
        $route = $this->route_chunks;
        if (count($route) >= 2 && $route[$index] == $search) {
            return true;
        }
        return false;
    }

    private function domainExistInCli(): bool
    {
        return !empty(getenv('WORDPRESS_DOMAIN_IN_CLI'));
    }

    private function setDomain(): void
    {
        $domain = getenv('WORDPRESS_DOMAIN_IN_CLI');

        $_SERVER['HTTP_HOST'] = $domain;
        $_SERVER['REQUEST_URI'] = "/";

        self::$domainFromCli = $domain;
    }

    private function setFakeIp(): void
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    }

    public function isWordpressLoadedSuccessfully(): bool
    {
        return self::$isDomainLoadedSuccessfully;
    }

    public function getCliDomain(): string
    {
        return self::$domainFromCli;
    }

    private function isCasino(): bool
    {
        try {
            return Helpers\UrlHelper::isCasino();
        } catch (Throwable $exception) {
            return false;
        }
    }
}
