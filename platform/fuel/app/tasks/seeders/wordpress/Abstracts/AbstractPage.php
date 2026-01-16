<?php

namespace Fuel\Tasks\Seeders\Wordpress\Abstracts;

abstract class AbstractPage extends AbstractWordpressSeeder
{
    protected const OVERRIDE_SPECIFIC_DOMAINS = [];
    protected const CUSTOM_TEMPLATE = '';
    protected const TYPE = 'parent';
    protected const SEED_THE_SAME_AS_ENGLISH_FOR_MISSING_LANGUAGES = true;
    protected const PARENT_PAGE_SLUG = '';
    protected const DO_NOT_CHECK_IF_SITES_EXIST = false;

    protected function parameters(): array
    {
        $parameters = [
            'TITLES_AND_BODIES_PER_LANGUAGE' => static::TITLES_AND_BODIES_PER_LANGUAGE,
            'WP_DOMAIN_NAME_WITHOUT_PORT' => static::WP_DOMAIN_NAME_WITHOUT_PORT,
            'TYPE' => self::TYPE,
            'SEED_THE_SAME_AS_ENGLISH_FOR_MISSING_LANGUAGES' => static::SEED_THE_SAME_AS_ENGLISH_FOR_MISSING_LANGUAGES,
            'PARENT_PAGE_SLUG' => static::PARENT_PAGE_SLUG,
        ];

        if (!empty(static::OVERRIDE_SPECIFIC_DOMAINS)) {
            $parameters['OVERRIDE_SPECIFIC_DOMAINS'] = static::OVERRIDE_SPECIFIC_DOMAINS;
        }

        if (!empty(static::CUSTOM_TEMPLATE)) {
            $parameters['CUSTOM_TEMPLATE'] = static::CUSTOM_TEMPLATE;
        }

        if (!empty(static::DO_NOT_CHECK_IF_SITES_EXIST)) {
            $parameters['DO_NOT_CHECK_IF_SITES_EXIST'] = static::DO_NOT_CHECK_IF_SITES_EXIST;
        }

        return $parameters;
    }
}
