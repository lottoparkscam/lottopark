<?php

namespace Fuel\Tasks\Seeders\Wordpress\Abstracts;

/**
 * This seeder creates lottery pages with category.
 */

abstract class AbstractPageLottery extends AbstractPage
{
    protected const TYPE = 'lottery';
    protected const IS_PARENT = false;
    /**
     * This function contains lottery sites data
     *
     * ##### Required parameters:
     * @param array TITLES_AND_BODIES_PER_LANGUAGE
     *  - ['language_code' => [
     *      - 'results' => ['slug' => 'slug','title' => 'title','body' => 'body'],
     *      - 'play' => [...],
     *      - 'lotteries' => [...]
     * ]]
     *
     * @param string CATEGORY_SLUG  creates category with this slug
     * @param string CATEGORY_NAME creates category with this name
     * ##### Optional parameters:
     * @param array OVERRIDE_SPECIFIC_DOMAINS = ['domain' => ['language_code' => ['site' => [title/body]]]]
     *
     *  **IMPORTANT: Durning creating a new parent for lottery english slug will be ignored.
     *  It always has to be (and will be) play, lotteries, results**
     *
     * @return array
     */

    protected function parameters(): array
    {
        $parameters['TYPE'] = self::TYPE;
        $parameters['IS_PARENT'] = static::IS_PARENT;
        $parameters['SEED_THE_SAME_AS_ENGLISH_FOR_MISSING_LANGUAGES'] = static::SEED_THE_SAME_AS_ENGLISH_FOR_MISSING_LANGUAGES;
        $parameters['DO_NOT_CHECK_IF_SITES_EXIST'] = static::DO_NOT_CHECK_IF_SITES_EXIST;
        if (!static::IS_PARENT) {
            $parameters['CATEGORY_NAME'] = static::CATEGORY_NAME;
            $parameters['GAME_NAME_SLUG'] = static::GAME_NAME_SLUG;
        }
        return $parameters;
    }
}
