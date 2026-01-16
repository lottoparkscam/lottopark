<?php

namespace Fuel\Tasks\Seeders\Wordpress\Abstracts;

abstract class AbstractAddTranslationToGame extends AbstractPage
{
    protected const TYPE = 'translationGame';
    protected const IS_PARENT = false;

    protected function parameters(): array
    {
        $parameters['TYPE'] = self::TYPE;
        $parameters['GAME_NAME_SLUG'] = static::GAME_NAME_SLUG;
        $parameters['GAME_TYPE'] = static::GAME_TYPE;
        $parameters['IS_PARENT'] = static::IS_PARENT;
        $parameters['SEED_THE_SAME_AS_ENGLISH_FOR_MISSING_LANGUAGES'] = static::SEED_THE_SAME_AS_ENGLISH_FOR_MISSING_LANGUAGES;

        if (!static::IS_PARENT) {
            $parameters['PARENT_SLUG'] = static::PARENT_SLUG;
        }
        if (!static::IS_PARENT && static::GAME_TYPE === 'lottery') {
            $parameters['CATEGORY_NAME'] = static::CATEGORY_NAME;
        }

        return $parameters;
    }
}
