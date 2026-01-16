<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractAddTranslationToGame;

final class GGWorldRaffleAddTranslation extends AbstractAddTranslationToGame
{
    protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark'];
    protected const IS_PARENT = false;
    protected const GAME_TYPE = 'raffle';
    protected const GAME_NAME_SLUG = 'gg-world-raffle';
    protected const PARENT_SLUG = 'gg-world-raffle';
    protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'es' => [
            'results-raffle' => [
                'title' => 'GG World raffle Resultados',
                'body' => '',
            ],
            'play-raffle' => [
                'title' => 'Jugar GG World raffle',
                'body' => '',
            ],
            'information-raffle' => [
                'title' => 'GG World raffle Información',
                'body' => '',
            ],
            'purchase' => [
                'body' => '',
            ],
        ],
        'de' => [
            'results-raffle' => [
                'title' => 'GG World raffle Ergebnisse',
                'body' => '',
            ],
            'play-raffle' => [
                'title' => 'GG World raffle Spielen',
                'body' => '',
            ],
            'information-raffle' => [
                'title' => 'GG World raffle Informationen',
                'body' => '',
            ],
            'purchase' => [
                'body' => '',
            ],
        ],
    ];
}
