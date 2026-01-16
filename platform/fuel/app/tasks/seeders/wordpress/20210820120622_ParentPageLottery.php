<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPageLottery;

final class ParentPageLottery extends AbstractPageLottery
{
    protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark'];
    protected const IS_PARENT = true;
    protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => [
            'results' => [
                'title' => 'Lottery results',
                'body' => '',
            ],
            'play' => [
                'title' => 'Play lottery',
                'body' => '',
            ],
            'lotteries' => [
                'title' => 'Lottery information',
                'body' => '',
            ]
        ],
        'pl' => [
            'results' => [
                'slug' => 'wyniki',
                'title' => 'Wyniki loterii',
                'body' => '',
            ],
            'play' => [
                'slug' => 'graj',
                'title' => 'Graj',
                'body' => '',
            ],
            'lotteries' => [
                'slug' => 'loterie',
                'title' => 'Informacje o loteriach',
                'body' => '',
            ]
        ]
    ];
}
