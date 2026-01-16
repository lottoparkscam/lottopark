<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPageLottery;

final class AddGgWorldCoinFlipPage extends AbstractPageLottery
{
    public const COINFLIP_SLUG = 'gg-world-coinflip';

    protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark.com'];
    protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => [
            'play' => [
                'title' => 'GG World Coin Flip',
                'body' => '',
            ],
        ],
    ];
    protected const GAME_NAME_SLUG = self::COINFLIP_SLUG;
    protected const CATEGORY_NAME = 'Coin Flip Games';
    protected const IS_PARENT = false;
    protected const DO_NOT_CHECK_IF_SITES_EXIST = true;
}
