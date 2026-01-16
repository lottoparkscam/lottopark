<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPage;

final class HowToBuyGgtknPage extends AbstractPage
{
    protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark'];
    protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => [
            'slug' => 'how-to-buy-gg-token',
            'title' => 'What are the reasons to pay with GG Token?',
            'body' => '',
        ]
    ];
}
