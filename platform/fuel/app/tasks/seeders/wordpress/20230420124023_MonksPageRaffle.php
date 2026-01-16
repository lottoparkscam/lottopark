<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPageRaffle;

/**
 * !!! PURCHASE PAGE MUST BE ADDED MANUALLY BECAUSE IT BREAKS THE ENTIRE SEEDER !!!
 */
final class MonksPageRaffle extends AbstractPageRaffle
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottomonks'];
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
		'en' => [
			'results-raffle' => [
				'title' => 'Results Monks Raffle',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Play Monks Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'Information Monks Raffle',
				'body' => '',
			],
		],
	];
	protected const IS_PARENT = false;
	protected const GAME_NAME_SLUG = 'monks-raffle';

}
