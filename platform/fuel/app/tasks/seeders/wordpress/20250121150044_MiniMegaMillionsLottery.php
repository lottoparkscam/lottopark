<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPageLottery;
use Models\Lottery;

final class MiniMegaMillionsLottery extends AbstractPageLottery
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark.com'];
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => [
            'results' => [
                'title' => 'Mini Mega Millions Results',
                'body' => '',
            ],
            'play' => [
                'title' => 'Play Mini Mega Millions',
                'body' => '',
            ],
            'lotteries' => [
                'title' => 'Mini Mega Millions Information',
                'body' => '',
            ],
        ],
    ];
    protected const CATEGORY_NAME = 'Mini Mega Millions';
	protected const GAME_NAME_SLUG = Lottery::MINI_MEGA_MILLIONS_SLUG;
	protected const IS_PARENT = false;
    protected const DO_NOT_CHECK_IF_SITES_EXIST = true;
}
