<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPageLottery;
use Models\Lottery;

final class EuromillionsSuperdrawLottery extends AbstractPageLottery
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark.com'];
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
		'en' => [
			'results' => [
				'title' => 'EuroMillions Superdraw Results',
				'body' => '',
			],
			'play' => [
				'title' => 'Play EuroMillions Superdraw',
				'body' => '',
			],
			'lotteries' => [
				'title' => 'EuroMillions Superdraw Information',
				'body' => '',
			],
		],
	];
	protected const CATEGORY_NAME = 'EuroMillions Superdraw';
	protected const GAME_NAME_SLUG = Lottery::EUROMILLIONS_SUPERDRAW_SLUG;
	protected const IS_PARENT = false;
	protected const DO_NOT_CHECK_IF_SITES_EXIST = true;
}
