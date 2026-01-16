<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPage;

final class LastStepsPage extends AbstractPage
{
    protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark'];

    protected const IS_PARENT = false;
    protected const PARENT_PAGE_SLUG = 'signup';

	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => [
            'slug' => 'last-steps',
            'title' => 'Last steps of the registration',
            'body' => '',
        ],
    ];
}
