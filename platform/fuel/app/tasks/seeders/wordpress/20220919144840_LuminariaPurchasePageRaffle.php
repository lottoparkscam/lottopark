<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPageRaffle;

/**
 * @draft DO NOT USE YET. it is only to signal that such page need to be seeded
 */
final class LuminariaPurchasePageRaffle extends AbstractPageRaffle
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['luminariagames'];
	protected const IS_PARENT = false;
	protected const GAME_NAME_SLUG = 'luminaria-raffle';
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
		'en' => [
			'purchase' => [
				'title' => 'Thank you and good luck!',
				'body' => '',
				'parent_slug' => 'luminaria-raffle', // play page of concrete instance of raffle e.g. play-raffle/luminaria-raffle
			],
		],
	];
}
