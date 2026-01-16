<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPageLottery;
use Models\Lottery;

final class UkrainianKenoLottery extends AbstractPageLottery
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark.com'];
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
		'en' => [
			'results' => [
				'title' => 'Ukrainian Keno Results',
				'body' => '',
			],
			'play' => [
				'title' => 'Play Ukrainian Keno',
				'body' => '',
			],
			'lotteries' => [
				'title' => 'Ukrainian Keno Information',
				'body' => '',
			],
		],
	];
	protected const CATEGORY_NAME = 'Ukrainian Keno';
	protected const GAME_NAME_SLUG = Lottery::UKRAINIAN_KENO_SLUG;
	protected const IS_PARENT = false;
	protected const DO_NOT_CHECK_IF_SITES_EXIST = true;
}
