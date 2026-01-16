<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPageLottery;

final class FrenchLottoLotteryPage extends AbstractPageLottery
{
    protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lotto'];
    protected const CATEGORY_NAME = 'French Lotto';
    protected const GAME_NAME_SLUG = 'lotto-fr';
    protected const IS_PARENT = false;
    protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => [
            'results' => [
                'title' => 'French Lotto Results',
                'body' => '',
            ],
            'play' => [
                'title' => 'Play French Lotto',
                'body' => ''
            ],
            'lotteries' => [
                'title' => 'French Lotto Information',
                'body' => '',
            ]
        ],
    ];
}
