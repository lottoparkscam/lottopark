<?php

namespace Fuel\Tasks\Seeders\Wordpress\Abstracts;

abstract class AbstractAddTranslationToSinglePage extends AbstractPage
{
    protected const TYPE = 'translationSingle';
    protected const IS_PARENT = false;

    protected function parameters(): array
    {
        $parameters['TYPE'] = self::TYPE;
        $parameters['PAGE_TYPE'] = static::PAGE_TYPE;
        $parameters['SEED_THE_SAME_AS_ENGLISH_FOR_MISSING_LANGUAGES'] = static::SEED_THE_SAME_AS_ENGLISH_FOR_MISSING_LANGUAGES;

        if (!static::IS_PARENT) {
            $parameters['PARENT_SLUG'] = static::PARENT_SLUG;
        }
        return $parameters;
    }
}
