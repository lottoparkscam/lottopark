<?php

namespace Services;

use DI\DependencyException;
use DI\NotFoundException;
use Fuel\Core\Cache;
use Helpers\SocialMediaConnect\LastStepsHelper;
use Helpers\UrlHelper;
use Throwable;
use Services\Logs\FileLoggerService;
use Container;

class RedirectService
{
    public function redirectToHomePage(): void
    {
        UrlHelper::redirectToHomepage();
    }

    public function redirectToSignUpPage(): void
    {
        UrlHelper::redirectToSignUpPage();
    }

    public function redirectToLoginPage(): void
    {
        UrlHelper::redirectToLoginPage();
    }

    /**
     * Last steps page cant work without socialType.
     * Forwarding to last steps page with correct social type will start social connection.
     * Social types you can find in database table social_type.
     */
    public function redirectToLastSteps(string $socialType): void
    {
        LastStepsHelper::redirectToLastSteps($socialType);
    }

    public function getWordpressRedirects(): array
    {
        $languages = apply_filters('wpml_active_languages', null, array('skip_missing' => 0));

        if (empty($languages)) {
            return [];
        }

        $allRedirects = [];
        foreach ($languages as $code => $language) {
            $cacheName = $this->getCacheName($code);

            try {
                $allRedirects[$code] = Cache::get($cacheName);
                continue;
            } catch (Throwable $exception) {}

            $redirectsFromThisLanguage = $this->generateWordpressRedirects($code);
            $allRedirects[$code] = $redirectsFromThisLanguage;

            try {
                Cache::set($cacheName, $redirectsFromThisLanguage);
            } catch (Throwable $exception) {
                $this->saveCacheErrorLog($cacheName, $exception->getMessage());
            }
        }

        return $allRedirects;
    }

    /**
     * @param string $language
     * @return array
     * @throws DependencyException
     * @throws NotFoundException
     *
     * See add_filter('query_vars'... in Platform.php and method Platform.php:add_query_vars
     * It could be related to add own query parameters
     */
    private function generateWordpressRedirects(string $language): array
    {
        $rewritesConditions = [
            'account' => [
                [
                    'regex' => '^{$pageName}/tickets/playagain/([0-9]+)/?',
                    'query' => 'index.php?pagename={$pageName}&section=tickets&action=playagain&id=$matches[1]'
                ],
                [
                    'regex' => '^{$pageName}/transactions/details/([0-9]+)/?',
                    'query' => 'index.php?pagename={$pageName}&section=transactions&action=details&id=$matches[1]'
                ],
                [
                    'regex' => '^{$pageName}/withdrawal/details/([0-9]+)/?',
                    'query' => 'index.php?pagename={$pageName}&section=withdrawal&action=details&id=$matches[1]'
                ],
                [
                    'regex' => '^{$pageName}/tickets/awaiting/?',
                    'query' => 'index.php?pagename={$pageName}&section=tickets&action=awaiting'
                ],
                [
                    'regex' => '^{$pageName}/tickets/details/multidraw/([0-9]+)/?',
                    'query' => 'index.php?pagename={$pageName}&section=tickets&action=details_multidraw&id=$matches[1]'
                ],
                [
                    'regex' => '^{$pageName}/tickets/details/([0-9]+)/?',
                    'query' => 'index.php?pagename={$pageName}&section=tickets&action=details&id=$matches[1]'
                ],
                [
                    'regex' => '^{$pageName}/tickets/raffle/([0-9]+)/?',
                    'query' => 'index.php?pagename={$pageName}&section=raffle&action=details&id=$matches[1]'
                ],
                [
                    'regex' => '^{$pageName}/tickets/quickpick/([0-9]+)/?',
                    'query' => 'index.php?pagename={$pageName}&section=tickets&action=quickpick&id=$matches[1]'
                ],
                [
                    'regex' => '^{$pageName}/payments/remove/([0-9]+)/?',
                    'query' => 'index.php?pagename={$pageName}&section=payments&action=remove&id=$matches[1]'
                ],
                [
                    'regex' => '^{$pageName}/slip/([0-9]+)/?',
                    'query' => 'index.php?pagename={$pageName}&section=slip&id=$matches[1]'
                ],
                [
                    'regex' => '^{$pageName}/([a-z]+)/?',
                    'query' => 'index.php?pagename={$pageName}&section=$matches[1]'
                ]
            ],
            'activation' => [
                [
                    'regex' => '^{$pageName}/([0-9]+)/([a-z0-9]+)/?',
                    'query' => 'index.php?pagename={$pageName}&id=$matches[1]&hash=$matches[2]'
                ]
            ],
            'auth/lostpassword' => [
                [
                    'regex' => '^auth/{$pageName}/([0-9]+)/([a-z0-9-]+)/?',
                    'query' => 'index.php?pagename=auth/{$pageName}&action=lostpassword&id=$matches[1]&hash=$matches[2]'
                ]
            ],
            'order' => [
                [
                    'regex' => '^{$pageName}/undo/?',
                    'query' => 'index.php?pagename={$pageName}&action=undo'
                ],
                [
                    'regex' => '^{$pageName}/quickpick/([a-z0-9-]+)/([0-9]+)/?',
                    'query' => 'index.php?pagename={$pageName}&action=quickpick&lottery=$matches[1]&amount=$matches[2]'
                ],
                [
                    'regex' => '^{$pageName}/remove/([0-9]+)/?',
                    'query' => 'index.php?pagename={$pageName}&remove=$matches[1]'
                ],
                [
                    'regex' => '^{$pageName}/clear/?',
                    'query' => 'index.php?pagename={$pageName}&clear=1'
                ],
                [
                    'regex' => '^{$pageName}/confirm/([a-z]+)/([0-9]+)/?',
                    'query' => 'index.php?pagename={$pageName}&action=confirm&section=$matches[1]&id=$matches[2]'
                ],
                [
                    'regex' => '^{$pageName}/entropay/([0-9-]+)/?',
                    'query' => 'index.php?pagename={$pageName}&action=entropay&amount=$matches[1]'
                ]
            ],
            'deposit' => [
                [
                    'regex' => '^{$pageName}/entropay/([0-9-]+)/?',
                    'query' => 'index.php?pagename={$pageName}&action=entropay&amount=$matches[1]'
                ]
            ],
            'index' => [
                [
                    'regex' => '^gresend/?',
                    'query' => 'index.php?page_id={$pageId}&action=gresend'
                ],
                [
                    'regex' => '^resend/([0-9]+)/([a-z0-9]+)/?',
                    'query' => 'index.php?page_id={$pageId}&action=resend&id=$matches[1]&hash=$matches[2]'
                ]
            ]
        ];

        $rewrites = [];
        foreach ($rewritesConditions as $slug => $rules) {
            if ($slug === 'index') {
                $page = get_post(apply_filters('wpml_object_id', get_option('page_on_front'), 'page', false, $language));
            } else {
                $page = get_post(apply_filters('wpml_object_id', lotto_platform_get_post_id_by_slug($slug), 'page', false, $language));
            }

			if (empty($page)) {
				continue;
			}

            foreach ($rules as $rule) {
                $regex = str_replace('{$pageName}', $page->post_name, $rule['regex']);
                $regex = str_replace('{$pageId}', $page->ID, $regex);
                $query = str_replace('{$pageName}', $page->post_name, $rule['query']);
                $query = str_replace('{$pageId}', $page->ID, $query);
                $rewrites[$slug][] = [
                    'regex' => $regex,
                    'query' => $query
                ];
            }
        }

        return $rewrites;
    }

    private function getCacheName(string $language): string
    {
        $domain = $_SERVER['HTTP_HOST'];
        $domain = str_replace('.', '_', $domain);
        return "wordpress_redirects_{$domain}_{$language}";
    }
    private function saveCacheErrorLog(string $cacheName, string $message): void
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $fileLoggerService->error(
            "Saving cache failed. CacheName: $cacheName, Error: $message}"
        );
    }

}