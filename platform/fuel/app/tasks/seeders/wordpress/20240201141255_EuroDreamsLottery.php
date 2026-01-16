<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPageLottery;
use Models\Lottery;

final class EuroDreamsLottery extends AbstractPageLottery
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark.com'];
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
		'en' => [
			'results' => [
				'title' => 'EuroDreams Results',
				'body' => '',
			],
			'play' => [
				'title' => 'Play EuroDreams',
				'body' => '',
			],
			'lotteries' => [
				'title' => 'EuroDreams Information',
				'body' => '',
			],
		],
	];
	protected const CATEGORY_NAME = 'EuroDreams';
	protected const GAME_NAME_SLUG = Lottery::EURODREAMS_SLUG;
	protected const IS_PARENT = false;
	protected const DO_NOT_CHECK_IF_SITES_EXIST = true;
}
