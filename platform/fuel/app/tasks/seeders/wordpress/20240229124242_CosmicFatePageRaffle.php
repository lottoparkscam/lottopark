<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPageRaffle;
use Models\Raffle;

final class CosmicFatePageRaffle extends AbstractPageRaffle
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['fatelotto'];
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
		'en' => [
			'results-raffle' => [
				'title' => 'Results Cosmic Fate Raffle',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Play Cosmic Fate Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'Information Cosmic Fate Raffle',
				'body' => '',
			],
		],
		'nl' => [
			'results-raffle' => [
				'title' => 'Cosmic Fate Raffle-resultaten',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Speel Cosmic Fate Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'Cosmic Fate Raffle-informatie',
				'body' => '',
			],
		],
		'zh' => [
			'results-raffle' => [
				'title' => 'Cosmic Fate Raffle 彩票结果和开奖数字',
				'body' => '',
			],
			'play-raffle' => [
				'title' => '在线玩 Cosmic Fate Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => '在线 Cosmic Fate Raffle',
				'body' => '',
			],
		],
	];
	protected const IS_PARENT = false;
	protected const GAME_NAME_SLUG = Raffle::COSMIC_FATE_RAFFLE_SLUG;
}
