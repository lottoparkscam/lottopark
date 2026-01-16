<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPageRaffle;

final class ParentPageRaffle extends AbstractPageRaffle
{
    protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark'];
    protected const IS_PARENT = true;
    protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => [
            'results-raffle' => [
                'title' => 'Raffle results',
                'body' => '',
            ],
            'play-raffle' => [
                'title' => 'Play Raffle',
                'body' => '',
            ],
            'information-raffle' => [
                'title' => 'Raffle information',
                'body' => '',
            ]
        ],
        'pl' => [
            'results-raffle' => [
                'title' => 'Wyniki Raffle',
                'body' => '',
            ],
            'play-raffle' => [
                'title' => 'Zagraj w Raffle',
                'body' => '',
            ],
            'information-raffle' => [
                'title' => 'Informacje o Raffle',
                'body' => '',
            ]
        ]
    ];
}
