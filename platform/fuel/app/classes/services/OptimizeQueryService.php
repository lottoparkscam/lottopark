<?php

use Fuel\Core\Cache;
use Services\Logs\FileLoggerService;

class OptimizeQueryService
{
    private string $currentLanguageCode;

    private const POST_TYPE_PAGE = 'page';
    private const POST_TYPE_POST = 'post';

    public function getPostIdBySlug(string $slug, string $type, ?string $languageCode = null): ?int
    {
        $languageCode = $languageCode ?? $this->getCurrentLanguageCode();
        $cacheName = $this->getCacheNameForGetPostIdBySlug($languageCode);

        try {
			$cache = Cache::get($cacheName);
        } catch (Throwable $exception) {
            $cache = [];
        }

        if (isset($cache[$type][$slug])) {
            return $cache[$type][$slug];
        }

		global $sitepress;

        /**
         * @see https://developer.wordpress.org/reference/classes/wp_query/
         * - name (string) – use post slug.
         * - pagename (string) – use page slug.
         */
        switch ($type) {
            case self::POST_TYPE_POST:
                $slugParamKey = 'name';
                break;
            default:
                $slugParamKey = 'pagename';
        }

        $queryArgs = [
            $slugParamKey => $slug,
            'post_type' => $type,
            'post_status' => 'publish',
            'suppress_filters' => 0
        ];

        $sitepress->switch_lang($sitepress->get_default_language());
        $theQuery = new WP_Query($queryArgs);
        $sitepress->switch_lang(ICL_LANGUAGE_CODE);

        if (empty($theQuery->posts)) {
            return null;
        }

        $pageId = $theQuery->posts[0]->ID;
        $id = apply_filters('wpml_object_id', $pageId, $type, false, $languageCode);

        if (empty($id)) {
            $id = $pageId;
        }

        $cache[$type][$slug] = $id;

        try {
            Cache::set($cacheName, $cache);
        } catch (Throwable $exception) {
            $this->saveCacheErrorLog($cacheName, $exception->getMessage());
        }

        return $id;
    }

    public function getPermalinkBySlugForDomain(string $slug, string $domain, string $languageCode = 'en', string $type = self::POST_TYPE_PAGE): ?string
    {
        $cacheName = $this->getCacheNameForGetPermalinksBySlug($languageCode, $domain);

        try {
            $cache = Cache::get($cacheName);
        } catch (Throwable $exception) {
            $cache = [];
        }

        return $cache[$type][$slug] ?? null;
    }

	public function getPermalinkBySlug(string $slug, string $type = self::POST_TYPE_PAGE): ?string
	{
		$languageCode = $this->getCurrentLanguageCode();
		$cacheName = $this->getCacheNameForGetPermalinksBySlug($languageCode);

		try {
			$cache = Cache::get($cacheName);
		} catch (Throwable $exception) {
			$cache = [];
		}

		if (isset($cache[$type][$slug])) {
			return $cache[$type][$slug];
		}

		$postId = $this->getPostIdBySlug($slug, $type);

		if (is_null($postId)) {
			return null;
		}

		$permalink = get_permalink($postId);
		$cache[$type][$slug] = $permalink;

		try {
			Cache::set($cacheName, $cache);
		} catch (Throwable $exception) {
			$this->saveCacheErrorLog($cacheName, $exception->getMessage());
		}

		return $permalink;
	}

    public function refreshPostsIds(string $languageCode): void
    {
        $postIds = [];
        $cacheName = $this->getCacheNameForGetPostIdBySlug($languageCode);
        Cache::delete($cacheName);

        $posts = get_posts([
            'post_type' => self::POST_TYPE_PAGE,
            'posts_per_page' => -1,
        ]);
        foreach ($posts as $post) {
            $postId = $post->ID;
            $translatedId = apply_filters('wpml_object_id', $post->ID, self::POST_TYPE_PAGE, false, $languageCode);
            $postIds[self::POST_TYPE_PAGE][$post->post_name] = !empty($translatedId) ? $translatedId : $postId;
        }

        try {
            Cache::set($cacheName, $postIds);
        } catch (Throwable $exception) {
            $this->saveCacheErrorLog($cacheName, $exception->getMessage());
        }
    }

    public function refreshSinglePostsIds(int $postId, string $languageCode): void
    {
        $cacheName = $this->getCacheNameForGetPostIdBySlug($languageCode);
        $postIds = Cache::get($cacheName);

        $postName = get_post_field('post_name', $postId);
        $translatedId = apply_filters('wpml_object_id', $postId, self::POST_TYPE_PAGE, false, $languageCode);
        $postIds[self::POST_TYPE_PAGE][$postName] = !empty($translatedId) ? $translatedId : $postId;

        try {
            Cache::set($cacheName, $postIds);
        } catch (Throwable $throwable) {
            $this->saveCacheErrorLog($cacheName, $throwable->getMessage());
        }
    }

	public function refreshPermalinks(string $languageCode): void
	{
		$permalinks = [];
		$cacheName = $this->getCacheNameForGetPermalinksBySlug($languageCode);
		Cache::delete($cacheName);

        /*
         * get_posts() requires args to be set for post_type=page (default is post).
         * numberposts=-1 for all posts. We may need to consider a specific limit for performance reasons.
         */
		$posts = get_posts('post_type=' . self::POST_TYPE_PAGE . '&numberposts=-1');
		foreach ($posts as $post) {
			$id = apply_filters('wpml_object_id', $post->ID, self::POST_TYPE_PAGE, false, $languageCode);
			if (!empty($id)) {
				$permalinks[self::POST_TYPE_PAGE][$post->post_name] = get_permalink($id);
			} else {
				$permalinks[self::POST_TYPE_PAGE][$post->post_name] = get_permalink($post->ID);
			}
		}

		try {
			Cache::set($cacheName, $permalinks);
		} catch (Throwable $exception) {
			$this->saveCacheErrorLog($cacheName, $exception->getMessage());
		}
	}

    public function refreshSinglePostPermalinks(int $postId, string $languageCode): void
    {
        $cacheName = $this->getCacheNameForGetPermalinksBySlug($languageCode);
        $postPermalinks = Cache::get($cacheName);

        $postName = get_post_field('post_name', $postId);
        $id = apply_filters('wpml_object_id', $postId, self::POST_TYPE_PAGE, false, $languageCode);
        if (!empty($id)) {
            $postPermalinks[self::POST_TYPE_PAGE][$postName] = get_permalink($id);
        } else {
            $postPermalinks[self::POST_TYPE_PAGE][$postName] = get_permalink($postId);
        }

        try {
			Cache::set($cacheName, $postPermalinks);
		} catch (Throwable $throwable) {
			$this->saveCacheErrorLog($cacheName, $throwable->getMessage());
		}
    }

	private function getCacheNameForGetPostIdBySlug(string $languageCode): string
	{
		return $this->getCacheName("get_post_id_by_slug", $languageCode);
	}

    private function getCacheNameForGetPermalinksBySlug(string $languageCode, string $domain = null): string
    {
        return $this->getCacheName("get_permalinks_by_slug", $languageCode, $domain);
    }

	private function getCacheName(string $prefix, string $languageCode, string $domain = null): string
	{
		$domain = $domain ?: $_SERVER['HTTP_HOST'];
		$domain = str_replace('.', '_', $domain);
		return "wordpress_{$prefix}_{$domain}_{$languageCode}";
	}

    private function saveCacheErrorLog(string $cacheName, string $message): void
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $fileLoggerService->error(
            "Saving cache failed. CacheName: $cacheName, Error: $message}"
        );
    }

	private function getCurrentLanguageCode(): string
	{
		if (isset($this->currentLanguageCode)) {
			return $this->currentLanguageCode;
		}

		global $sitepress;
		$this->currentLanguageCode = $sitepress->get_current_language();
		return $this->currentLanguageCode;
	}
}