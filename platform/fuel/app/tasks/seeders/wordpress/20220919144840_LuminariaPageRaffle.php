<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPageRaffle;

final class LuminariaPageRaffle extends AbstractPageRaffle
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['luminariagames'];
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
		'en' => [
			'results-raffle' => [
				'title' => 'Results Luminaria Raffle',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Play Luminaria Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'Information Luminaria Raffle',
				'body' => '',
			]
		],
	];
	protected const IS_PARENT = false;
	protected const GAME_NAME_SLUG = 'luminaria-raffle';
}
