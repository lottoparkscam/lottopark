<?php

namespace Fuel\Tasks;

use Container;
use Fuel\Core\Cli;
use Models\Whitelabel;
use OptimizeQueryService;
use WordpressInFuel;

/**
 * Class name starts from lowercase on purpose
 * It is because shell_exec() don't recognize uppercase without special PHP flag
 */
class optimize
{
    private WordpressInFuel $wordpressInFuel;
    private OptimizeQueryService $optimizeQueryService;

    public function __construct()
    {
        $this->wordpressInFuel = Container::get(WordpressInFuel::class);
        $this->optimizeQueryService = Container::get(OptimizeQueryService::class);
    }

    public function refreshPostsIds(string $language = null)
    {
        if (!$this->wordpressInFuel->isWordpressLoadedSuccessfully()) {
            Cli::write('Wordpress not loaded. Check if domain is correct');
            return;
        }

        $languages = $this->getLanguages($language);

        if (empty($languages)) {
            return;
        }

        foreach ($languages as $code => $language) {
            $this->optimizeQueryService->refreshPostsIds($code);
        }

        Cli::write("Every post's id have been refreshed");
    }

    public function refreshSinglePostsIds(int $postId, string $language = null)
    {
        if (!$this->wordpressInFuel->isWordpressLoadedSuccessfully()) {
            Cli::write('Wordpress not loaded. Check if domain is correct');
            return;
        }

        $languages = $this->getLanguages($language);

        if (empty($languages)) {
            return;
        }

        foreach ($languages as $code => $language) {
            $this->optimizeQueryService->refreshSinglePostsIds($postId, $code);
        }

        Cli::write('Every post\'s id have been refreshed');
    }

    public function refreshPermalinks(string $language = null)
    {
        if (!$this->wordpressInFuel->isWordpressLoadedSuccessfully()) {
            Cli::write('Wordpress not loaded. Check if domain is correct');
            return;
        }

        $languages = $this->getLanguages($language);

        if (empty($languages)) {
            return;
        }

        foreach ($languages as $code => $language) {
            $this->optimizeQueryService->refreshPermalinks($code);
        }

        Cli::write("Permalinks refreshed");
    }

    public function refreshSinglePostPermalinks(int $postId, string $language = null)
    {
        if (!$this->wordpressInFuel->isWordpressLoadedSuccessfully()) {
            Cli::write('Wordpress not loaded. Check if domain is correct');
            return;
        }

        $languages = $this->getLanguages($language);

        if (empty($languages)) {
            return;
        }

        foreach ($languages as $code => $language) {
            $this->optimizeQueryService->refreshSinglePostPermalinks($postId, $code);
        }

        Cli::write('Permalinks refreshed');
    }

    public function refreshAllPostsIds()
    {
        Cli::write("Refreshing started");

        $whitelabels = Whitelabel::find('all');

        /** @var Whitelabel $whitelabel */
        foreach ($whitelabels as $whitelabel) {
            shell_exec("WORDPRESS_DOMAIN_IN_CLI='{$whitelabel->domain}' php8.0 {$_ENV['SCHEDULER_OIL_PATH']} r optimize:refreshPostsIds");
        }

        Cli::write("All post's ids refreshed");
    }

    public function refreshAllPermalinks()
    {
        Cli::write("Refreshing started");

        $whitelabels = Whitelabel::find('all');

        /** @var Whitelabel $whitelabel */
        foreach ($whitelabels as $whitelabel) {
            shell_exec("WORDPRESS_DOMAIN_IN_CLI='{$whitelabel->domain}' php8.0 {$_ENV['SCHEDULER_OIL_PATH']} r optimize:refreshPermalinks");
        }

        Cli::write("All permalinks refreshed");
    }

    private function getLanguages(?string $language): ?array
    {
        if ($language) {
            $languages = [$language => []];
        } else {
            $languages = apply_filters('wpml_active_languages', null, array('skip_missing' => 0));
        }

        if (empty($languages)) {
            Cli::write('No language exists');
            return null;
        }

        return $languages;
    }
}
