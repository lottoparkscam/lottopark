<?php

namespace Fuel\Tasks\Seeders\Wordpress\Abstracts;

abstract class AbstractPost extends AbstractWordpressSeeder
{
    protected const TYPE = 'post';
    protected const OVERRIDE_SPECIFIC_DOMAINS = [];

    protected function parameters(): array
    {
        if (static::OVERRIDE_SPECIFIC_DOMAINS !== null) {
            $parameters['OVERRIDE_SPECIFIC_DOMAINS'] = static::OVERRIDE_SPECIFIC_DOMAINS;
        }
        $postParameters = [
            'TYPE' => self::TYPE,
            'WP_DOMAIN_NAME_WITHOUT_PORT' => static::WP_DOMAIN_NAME_WITHOUT_PORT,
            'TITLES_AND_BODIES_PER_LANGUAGE' => static::TITLES_AND_BODIES_PER_LANGUAGE,
            'POST_CATEGORY_SLUG' => static::POST_CATEGORY_SLUG
        ];
        $parameters = array_merge($parameters, $postParameters);
        return $parameters;
    }
}
