<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPageRaffle;

final class FaireumPageRaffle extends AbstractPageRaffle
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark'];
	protected const IS_PARENT = false;
	protected const GAME_NAME_SLUG = 'faireum-raffle';
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
		'en' => [
			'results-raffle' => [
				'title' => 'Results Faireum Raffle',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Play Faireum Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'Information Faireum Raffle',
				'body' => '',
			]
		],
		'pl' => [
            'results-raffle' => [
                'title' => 'Wyniki Faireum Raffle',
                'body' => '',
            ],
            'play-raffle' => [
                'title' => 'Graj w Faireum Raffle',
                'body' => '',
            ],
            'information-raffle' => [
                'title' => 'Informacje o Faireum Raffle',
                'body' => '',
            ],
        ],
	];
}
