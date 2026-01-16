<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractAddTranslationToGame;

final class KenoLotteryAddTranslation extends AbstractAddTranslationToGame
{
    protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark'];
    protected const GAME_TYPE = 'lottery';
    protected const GAME_NAME_SLUG = 'gg-world-keno';
    protected const CATEGORY_NAME = 'GG World Keno';
    protected const PARENT_SLUG = 'gg-world-keno';
    protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'es' => [
            'results' => [
                'title' => 'GG World Keno Resultados',
                'body' => '',
            ],
            'play' => [
                'title' => 'Jugar GG World Keno',
                'body' => '',
            ],
            'lotteries' => [
                'title' => 'GG World Keno Información',
                'body' => '',
            ]
        ],
        'de' => [
            'results' => [
                'title' => 'GG World Keno Ergebnisse',
                'body' => '',
            ],
            'play' => [
                'title' => 'GG World Keno Spielen',
                'body' => '',
            ],
            'lotteries' => [
                'title' => 'GG World Keno Informationen',
                'body' => '',
            ]
        ],
    ];
}
