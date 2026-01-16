<?php

namespace Fuel\Tasks;

use Container;
use Exception;
use Fuel\Core\Cli;
use Services\PageCacheService;
use Throwable;

class Page_Cache
{
    public function clear(): void
    {
        Cli::write('Starting...');
        try {
            $pageCacheService = Container::get(PageCacheService::class);
            $success = $pageCacheService->clearAllActiveWhitelabels();

            if (!$success) {
                throw new Exception('Error clearing page cache (CloudFlare + Nginx)');
            }
        } catch (Throwable $e) {
            Cli::write('Error clearing page cache (CloudFlare + Nginx). Check error logs on Slack.', 'red');
            return;
        }
        Cli::write('All whitelabel\'s caches (CloudFlare + Nginx) cleared successfully.', 'green');
    }
}
