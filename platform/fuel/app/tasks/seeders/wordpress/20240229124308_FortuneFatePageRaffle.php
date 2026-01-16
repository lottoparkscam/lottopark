<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPageRaffle;
use Models\Raffle;

final class FortuneFatePageRaffle extends AbstractPageRaffle
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['fatelotto'];
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
		'en' => [
			'results-raffle' => [
				'title' => 'Results Fortune Fate Raffle',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Play Fortune Fate Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'Information Fortune Fate Raffle',
				'body' => '',
			],
		],
		'nl' => [
			'results-raffle' => [
				'title' => 'Fortune Fate Raffle-resultaten',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Speel Fortune Fate Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'Fortune Fate Raffle-informatie',
				'body' => '',
			],
		],
		'zh' => [
			'results-raffle' => [
				'title' => 'Fortune Fate Raffle 彩票结果和开奖数字',
				'body' => '',
			],
			'play-raffle' => [
				'title' => '在线玩 Fortune Fate Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => '在线 Fortune Fate Raffle',
				'body' => '',
			],
		],
	];
	protected const IS_PARENT = false;
	protected const GAME_NAME_SLUG = Raffle::FORTUNE_FATE_RAFFLE_SLUG;
}
