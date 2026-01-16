<?php

namespace Helpers\Wordpress;

use Fuel\Core\Input;

class PageHelper
{
    public static function isNewsMainPage(): bool
    {
        $path = parse_url(Input::server('REQUEST_URI'), PHP_URL_PATH);
        $segments = explode('/', $path);
        return key_exists(1, $segments) && $segments[1] === 'news';
    }

    public static function isNotMainPageWithQuery(): bool
    {
        $requestUri = Input::server('REQUEST_URI');
        $path = parse_url($requestUri, PHP_URL_PATH);
        $query = parse_url($requestUri, PHP_URL_QUERY);
        $isMainPage = $path === '/';
        $isQuery = !empty($query);
        return !($isMainPage && $isQuery);
    }

    public static function isPostPage(): bool
    {
        return get_post_type() === 'post';
    }

    public static function isFeedPage(): bool
    {
        return str_contains(Input::server('REQUEST_URI'), 'feed') ||
            !is_null(Input::get('feed'));
    }

    public static function isNotFeedPage(): bool
    {
        return !self::isFeedPage();
    }

    public static function isAnyOrderPage(): bool
    {
        return str_contains(Input::server('REQUEST_URI'), 'order');
    }

    public static function isNotAnyOrderPage(): bool
    {
        return !self::isAnyOrderPage();
    }
}
