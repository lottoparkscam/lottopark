<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPageLottery;
use Models\Lottery;

final class FinnishKenoLottery extends AbstractPageLottery
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark.com'];
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
		'en' => [
			'results' => [
				'title' => 'Finnish Keno Results',
				'body' => '',
			],
			'play' => [
				'title' => 'Play Finnish Keno',
				'body' => '',
			],
			'lotteries' => [
				'title' => 'Finnish Keno Information',
				'body' => '',
			],
		],
	];
	protected const CATEGORY_NAME = 'Finnish Keno';
	protected const GAME_NAME_SLUG = Lottery::FINNISH_KENO_SLUG;
	protected const IS_PARENT = false;
	protected const DO_NOT_CHECK_IF_SITES_EXIST = true;
}
