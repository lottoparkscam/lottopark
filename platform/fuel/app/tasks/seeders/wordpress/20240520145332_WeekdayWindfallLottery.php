<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPageLottery;
use Models\Lottery;

final class WeekdayWindfallLottery extends AbstractPageLottery
    {
    protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark.com'];
    protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => [
            'results' => [
                'title' => 'Weekday Windfall Results',
                'body' => '',
            ],
            'play' => [
                'title' => 'Play Weekday Windfall',
                'body' => '',
            ],
            'lotteries' => [
                'title' => 'Weekday Windfall Information',
                'body' => '',
            ],
        ],
    ];
    protected const CATEGORY_NAME = 'Weekday Windfall';
    protected const GAME_NAME_SLUG = Lottery::WEEKDAY_WINDFALL_SLUG;
    protected const IS_PARENT = false;
    protected const DO_NOT_CHECK_IF_SITES_EXIST = true;
}
