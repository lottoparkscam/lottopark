<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPageLottery;
use Models\Lottery;

final class LoteriaRomanaLottery extends AbstractPageLottery
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark']; 
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
		'en' => [
			'results' => [ 
				'title' => 'Loto 6/49 Results', 
				'body' => '', 
			],
			'play' => [ 
				'title' => 'Play Loto 6/49', 
				'body' => '', 
			],
			'lotteries' => [ 
				'title' => 'Loto 6/49 Information', 
				'body' => '', 
			],
		],
	]; 
	protected const CATEGORY_NAME = 'Loto 6/49';
	protected const GAME_NAME_SLUG = Lottery::LOTO_6_49_SLUG;
	protected const IS_PARENT = false;
	protected const DO_NOT_CHECK_IF_SITES_EXIST = true;
}
