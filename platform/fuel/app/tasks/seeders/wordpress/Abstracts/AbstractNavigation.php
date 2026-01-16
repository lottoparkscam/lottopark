<?php

namespace Fuel\Tasks\Seeders\Wordpress\Abstracts;

abstract class AbstractNavigation extends AbstractWordpressSeeder
{
    protected const IS_NOT_WORDPRESS_PAGE = false;
    protected const MENU = 'primary';
    protected const TYPE = 'nav';
    protected const SEED_THE_SAME_AS_ENGLISH_FOR_MISSING_LANGUAGES = true;
    protected const FORCE_ENGLISH_NAME = false;
    protected const DIRECT_LINK = '';
    protected const SLUG_FOR_LINK = '';

    /**
     * Available menus: primary, footer
     * Default menu is primary
     */
    protected function parameters(): array
    {
        $params = [
            'IS_NOT_WORDPRESS_PAGE' => static::IS_NOT_WORDPRESS_PAGE,
            'DIRECT_LINK' => static::DIRECT_LINK,
            'TYPE' => self::TYPE,
            'SLUG_FOR_LINK' => static::SLUG_FOR_LINK,
            'WP_DOMAIN_NAME_WITHOUT_PORT' => static::WP_DOMAIN_NAME_WITHOUT_PORT,
            'TITLES_AND_BODIES_PER_LANGUAGE' => static::TITLES_AND_BODIES_PER_LANGUAGE,
            'MENU' => static::MENU,
            'SEED_THE_SAME_AS_ENGLISH_FOR_MISSING_LANGUAGES' => static::SEED_THE_SAME_AS_ENGLISH_FOR_MISSING_LANGUAGES,
            'FORCE_ENGLISH_NAME' => static::FORCE_ENGLISH_NAME
        ];

        return $params;
    }
}
