<?php

namespace Fuel\Tasks\Seeders\Wordpress\Abstracts;

abstract class AbstractMenu extends AbstractWordpressSeeder
{
    protected const TYPE = 'menu';
    protected const SEED_THE_SAME_AS_ENGLISH_FOR_MISSING_LANGUAGES = true;

    protected function parameters(): array
    {
        $params = [
            'TYPE' => self::TYPE,
            'POSITION' => static::POSITION,
            'MENU_SLUG' => static::MENU_SLUG,
            'WP_DOMAIN_NAME_WITHOUT_PORT' => static::WP_DOMAIN_NAME_WITHOUT_PORT,
            'LANGUAGES' => static::LANGUAGES,
            'SEED_THE_SAME_AS_ENGLISH_FOR_MISSING_LANGUAGES' => static::SEED_THE_SAME_AS_ENGLISH_FOR_MISSING_LANGUAGES
        ];

        return $params;
    }
}
