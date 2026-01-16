<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractAddTranslationToGame;

final class ParentRaffleAddTranslation extends AbstractAddTranslationToGame
{
    protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark'];
    protected const GAME_TYPE = 'raffle';
    protected const GAME_NAME_SLUG = 'gg-world-raffle';
    protected const PARENT_SLUG = 'gg-world-raffle';
    protected const IS_PARENT = true;
    protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'es' => [
            'results-raffle' => [
                'title' => 'Resultados raffle',
                'body' => '',
            ],
            'play-raffle' => [
                'title' => 'Jugar raffle online hoy',
                'body' => '',
            ],
            'information-raffle' => [
                'title' => 'Información raffle',
                'body' => '',
            ],
            'purchase' => [
                'body' => '',
            ],
        ],
        'de' => [
            'results-raffle' => [
                'title' => 'Raffle Ergebnisse',
                'body' => '',
            ],
            'play-raffle' => [
                'title' => 'Raffle Spielen',
                'body' => '',
            ],
            'information-raffle' => [
                'title' => 'Raffle Informationen',
                'body' => '',
            ],
            'purchase' => [
                'body' => '',
            ],
        ],
    ];
}
