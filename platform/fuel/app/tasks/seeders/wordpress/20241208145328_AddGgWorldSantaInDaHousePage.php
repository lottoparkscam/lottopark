<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPageLottery;
use Models\MiniGame;

final class AddGgWorldSantaInDaHousePage extends AbstractPageLottery
{
    protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark.com'];
    protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => [
            'play' => [
                'title' => 'GG World Santa In Da House',
                'body' => '',
            ],
        ],
    ];
    protected const GAME_NAME_SLUG = MiniGame::GG_WORLD_SANTA_IN_DA_HOUSE_SLUG;
    protected const CATEGORY_NAME = 'Mini Games';
    protected const IS_PARENT = false;
    protected const DO_NOT_CHECK_IF_SITES_EXIST = true;
}
