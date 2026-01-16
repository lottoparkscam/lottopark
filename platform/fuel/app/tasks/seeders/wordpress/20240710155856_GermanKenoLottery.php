<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPageLottery;
use Models\Lottery;

final class GermanKenoLottery extends AbstractPageLottery
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark.com'];
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
		'en' => [
			'results' => [
				'title' => 'German Keno Results',
				'body' => '',
			],
			'play' => [
				'title' => 'Play German Keno',
				'body' => '',
			],
			'lotteries' => [
				'title' => 'German Keno Information',
				'body' => '',
			],
		],
	];
	protected const CATEGORY_NAME = 'German Keno';
	protected const GAME_NAME_SLUG = Lottery::GERMAN_KENO_SLUG;
	protected const IS_PARENT = false;
	protected const DO_NOT_CHECK_IF_SITES_EXIST = true;
}
