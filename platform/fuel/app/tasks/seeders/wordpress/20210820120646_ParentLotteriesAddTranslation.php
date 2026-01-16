<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractAddTranslationToGame;

final class ParentLotteriesAddTranslation extends AbstractAddTranslationToGame
{
    protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark'];
    protected const GAME_TYPE = 'lottery';
    protected const GAME_NAME_SLUG = 'gg-world-keno';
    protected const PARENT_SLUG = 'gg-world-keno';
    protected const IS_PARENT = true;
    protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'es' => [
            'results' => [
                'slug' => 'resultados',
                'title' => 'Resultados de la lotería',
                'body' => '',
            ],
            'play' => [
                'slug' => 'jugar',
                'title' => 'Jugar online hoy',
                'body' => '',
            ],
            'lotteries' => [
                'slug' => 'loterias',
                'title' => 'Información',
                'body' => '',
            ]
        ],
        'de' => [
            'results' => [
                'slug' => 'ergebnisse',
                'title' => 'Loterie Ergebnisse',
                'body' => '',
            ],
            'play' => [
                'slug' => 'spielen',
                'title' => 'Loterie Spielen',
                'body' => '',
            ],
            'lotteries' => [
                'slug' => 'lotterie',
                'title' => 'Loterie Informationen',
                'body' => '',
            ]
        ],
    ];
}
