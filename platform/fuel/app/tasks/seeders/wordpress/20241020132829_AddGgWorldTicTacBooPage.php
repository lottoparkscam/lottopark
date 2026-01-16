<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPageLottery;

final class AddGgWorldTicTacBooPage extends AbstractPageLottery
{
    public const GAME_SLUG = 'gg-world-tic-tac-boo';

    protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark.com'];
    protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => [
            'play' => [
                'title' => 'GG World Tic Tac Boo',
                'body' => '',
            ],
        ],
    ];
    protected const GAME_NAME_SLUG = self::GAME_SLUG;
    protected const CATEGORY_NAME = 'Mini Games';
    protected const IS_PARENT = false;
    protected const DO_NOT_CHECK_IF_SITES_EXIST = true;
}
