<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPageRaffle;
use Models\Raffle;

final class MysticFatePageRaffle extends AbstractPageRaffle
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['fatelotto'];
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
		'en' => [
			'results-raffle' => [
				'title' => 'Results Mystic Fate Raffle',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Play Mystic Fate Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'Information Mystic Fate Raffle',
				'body' => '',
			],
		],
		'nl' => [
			'results-raffle' => [
				'title' => 'Mystic Fate Raffle-resultaten',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Speel Mystic Fate Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'Mystic Fate Raffle-informatie',
				'body' => '',
			],
		],
		'zh' => [
			'results-raffle' => [
				'title' => 'Mystic Fate Raffle 彩票结果和开奖数字',
				'body' => '',
			],
			'play-raffle' => [
				'title' => '在线玩 Mystic Fate Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => '在线 Mystic Fate Raffle',
				'body' => '',
			],
		],
	];
	protected const IS_PARENT = false;
	protected const GAME_NAME_SLUG = Raffle::MYSTIC_FATE_RAFFLE_SLUG;
}
