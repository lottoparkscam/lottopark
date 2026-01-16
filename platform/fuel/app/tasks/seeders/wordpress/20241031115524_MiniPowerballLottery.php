<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPageLottery;
use Models\Lottery;

final class MiniPowerballLottery extends AbstractPageLottery
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark.com'];
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => [
            'results' => [
                'title' => 'Mini Powerball Results',
                'body' => '',
            ],
            'play' => [
                'title' => 'Play Mini Powerball',
                'body' => '',
            ],
            'lotteries' => [
                'title' => 'Mini Powerball Information',
                'body' => '',
            ],
        ],
    ];
    protected const CATEGORY_NAME = 'Mini Powerball';
	protected const GAME_NAME_SLUG = Lottery::MINI_POWERBALL_SLUG;
	protected const IS_PARENT = false;
    protected const DO_NOT_CHECK_IF_SITES_EXIST = true;
}
