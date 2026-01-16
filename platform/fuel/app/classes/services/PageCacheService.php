<?php

namespace Services;

use Core\App;
use Fuel\Core\Session;
use Helpers_Time;
use Lotto_Helper;
use Models\Whitelabel;
use Repositories\WhitelabelRepository;
use Wrappers\Decorators\ConfigContract;

class PageCacheService
{
    private const SCRIPT_TYPE_WHITELABEL = 'wl';
    private const SCRIPT_TYPE_ALL = 'all';
    private const SCRIPT_TYPE_REGEX = 'regex';

    private ShellExecutorService $shellExecutorService;
    private CloudflareService $cloudflareService;
    private WhitelabelRepository $whitelabelRepository;
    private ConfigContract $configContract;
    private App $app;

    public function __construct(
        ShellExecutorService $shellExecutorService,
        CloudflareService $cloudflareService,
        WhitelabelRepository $whitelabelRepository,
        ConfigContract $configContract,
        App $app
    ) {
        $this->shellExecutorService = $shellExecutorService;
        $this->cloudflareService = $cloudflareService;
        $this->whitelabelRepository = $whitelabelRepository;
        $this->configContract = $configContract;
        $this->app = $app;
    }

    public function clearWhitelabel(): void
    {
        $this->executeScript(self::SCRIPT_TYPE_WHITELABEL);

        $domain = Lotto_Helper::getWhitelabelDomainFromUrl();
        if (!empty($domain)) {
            $this->cloudflareService->clearCacheByWhitelabel($domain);
        }
    }

    public function clearWhitelabelByLanguage(?string $languageCode = null): void
    {
        if ($languageCode) {
            $regex = "/$languageCode/*";
        } else {
            $regex = '/*';
        }

        $this->executeScript(self::SCRIPT_TYPE_REGEX, $regex);

        $domain = Lotto_Helper::getWhitelabelDomainFromUrl();
        if (!empty($domain)) {
            $this->cloudflareService->clearCacheByWhitelabel($domain);
        }
    }

    public function clearAllActiveWhitelabels(): bool
    {
        $scriptPath = $this->getScriptPath();
        $this->shellExecutorService->execute($scriptPath  . " '" . self::SCRIPT_TYPE_ALL . "'");

        $allWhitelabels = $this->whitelabelRepository->findByIsActive(true);

        $success = false;

        /** @var Whitelabel $whitelabel */
        foreach ($allWhitelabels as $whitelabel) {
            $success = $this->cloudflareService->clearCacheByWhitelabel($whitelabel->domain);

            if (!$success) {
                return false;
            }
        }

        foreach ($this->cloudflareService->getZoneIdsToClear() as $zoneId) {
            $success = $this->cloudflareService->clearCacheByZoneId($zoneId);

            if (!$success) {
                break;
            }
        }

        return $success;
    }

    public function turnOnPageCache(): void
    {
        if ($this->app->isDevelopment()) {
            return;
        }

        // This header tells server to serve cache
        header('page-cache: true');
        header('cache-control: public, max-age=' . Helpers_Time::DAY_IN_SECONDS);

        // Cookies should be served by API
        // If cookie is set, server does not serve page cache
        Session::instance()->set_config('enable_cookie', false);
    }

    private function executeScript(string $type, string $path = null): void
    {
        $domain = Lotto_Helper::getWhitelabelDomainFromUrl();

        if (empty($domain)) {
            return;
        }

        $scriptPath = $this->getScriptPath();
        $command = $scriptPath . " '$type' '$domain'";
        $commandWithWwwPrefix = $scriptPath . " '$type' 'www.$domain'";

        if (!empty($path)) {
            $command .= " '$path'";
            $commandWithWwwPrefix .= " '$path'";
        }

        $this->shellExecutorService->execute($command);
        $this->shellExecutorService->execute($commandWithWwwPrefix);
    }

    private function getScriptPath(): string
    {
        return $this->configContract->get('page_cache.deleteScriptPath');
    }
}
