<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPageLottery;
use Models\Lottery;

final class GreekKenoLottery extends AbstractPageLottery
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark.com'];
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
		'en' => [
			'results' => [
				'title' => 'Greek Keno Results',
				'body' => '',
			],
			'play' => [
				'title' => 'Play Greek Keno',
				'body' => '',
			],
			'lotteries' => [
				'title' => 'Greek Keno Information',
				'body' => '',
			],
		],
	];
	protected const CATEGORY_NAME = 'Greek Keno';
	protected const GAME_NAME_SLUG = Lottery::GREEK_KENO_SLUG;
	protected const IS_PARENT = false;
	protected const DO_NOT_CHECK_IF_SITES_EXIST = true;
}
