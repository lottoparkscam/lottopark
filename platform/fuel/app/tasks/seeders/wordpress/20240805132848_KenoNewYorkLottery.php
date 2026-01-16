<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPageLottery;
use Models\Lottery;

final class KenoNewYorkLottery extends AbstractPageLottery
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark.com'];
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
		'en' => [
			'results' => [
				'title' => 'Keno New York Results',
				'body' => '',
			],
			'play' => [
				'title' => 'Play Keno New York',
				'body' => '',
			],
			'lotteries' => [
				'title' => 'Keno New York Information',
				'body' => '',
			],
		],
	];
	protected const CATEGORY_NAME = 'Keno NY';
	protected const GAME_NAME_SLUG = Lottery::KENO_NEW_YORK_SLUG;
	protected const IS_PARENT = false;
	protected const DO_NOT_CHECK_IF_SITES_EXIST = true;
}
