<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPage;

final class Bonus100PercentPage extends AbstractPage
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark'];
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
		'en' => [
			'slug' => '100-welcome-deposit-bonus',
			'title' => '100% Bonus',
			'body' => '',
		],
	];
}
