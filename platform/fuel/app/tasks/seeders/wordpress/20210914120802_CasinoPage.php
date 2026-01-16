<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPage;

final class CasinoPage extends AbstractPage
{
    protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark'];
    protected const CUSTOM_TEMPLATE = 'template-casino.php';

    protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => [
            'title' => 'Casino',
            'body' => '',
        ],
    ];
}
