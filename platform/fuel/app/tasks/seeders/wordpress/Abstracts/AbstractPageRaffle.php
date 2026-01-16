<?php

namespace Fuel\Tasks\Seeders\Wordpress\Abstracts;

abstract class AbstractPageRaffle extends AbstractPage
{
    protected const TYPE = 'raffle';
    protected const IS_PARENT = false;
    /**
     * This function contains raffle sites data
     *
     * ##### Required parameters:
     * @param array TITLES_AND_BODIES_PER_LANGUAGE
     *  - ['language_code' => [
     *      - 'results-raffle' => ['slug' => 'slug','title' => 'title','body' => 'body'],
     *      - 'play-raffle' => [...],
     *      - 'information-raffle' => [...]
     *      - 'purchase' => [...] default title for this site is 'Thank you and good luck!'
     *         you don't have to pass it here
     * ]]
     *
     *  ##### Optional parameters:
     * @param array OVERRIDE_SPECIFIC_DOMAINS = ['domain' => 'language_code' => 'site' => title/body]
     *
     *  **IMPORTANT: Durning creating a new parent for lottery english slug will be ignored.
     *  It always has to be (and will be) results-raffle, play-raffle, information-raffle, purchase**
     * @return array
     */

    protected function parameters(): array
    {
        $parameters['TYPE'] = self::TYPE;
        $parameters['IS_PARENT'] = static::IS_PARENT;
        $parameters['SEED_THE_SAME_AS_ENGLISH_FOR_MISSING_LANGUAGES'] = static::SEED_THE_SAME_AS_ENGLISH_FOR_MISSING_LANGUAGES;
        if (!static::IS_PARENT) {
            $parameters['GAME_NAME_SLUG'] = static::GAME_NAME_SLUG;
        }
        return $parameters;
    }
}
