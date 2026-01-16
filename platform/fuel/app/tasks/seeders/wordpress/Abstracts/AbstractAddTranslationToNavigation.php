<?php

namespace Fuel\Tasks\Seeders\Wordpress\Abstracts;

abstract class AbstractAddTranslationToNavigation extends AbstractWordpressSeeder
{
    protected const MENU = 'primary';
    protected const TYPE = 'navTranslation';

    /** 
     * Available menus: primary, footer
     * Default menu is primary
     */
    protected function parameters(): array
    {
        $params = [
            'TYPE' => self::TYPE,
            'SLUG_FOR_LINK' => static::SLUG_FOR_LINK,
            'WP_DOMAIN_NAME_WITHOUT_PORT' => static::WP_DOMAIN_NAME_WITHOUT_PORT,
            'TITLES_AND_BODIES_PER_LANGUAGE' => static::TITLES_AND_BODIES_PER_LANGUAGE,
            'ENGLISH_TAB_TITLE' => static::ENGLISH_TAB_TITLE,
            'MENU' => static::MENU
        ];

        return $params;
    }
}
