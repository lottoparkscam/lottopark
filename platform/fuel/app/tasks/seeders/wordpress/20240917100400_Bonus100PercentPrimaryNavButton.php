<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractNavigation;

final class Bonus100PercentPrimaryNavButton extends AbstractNavigation
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark'];
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
		'en' => '100% Bonus',
	];
	protected const SLUG_FOR_LINK = '100-welcome-deposit-bonus';
	protected const MENU = 'primary';
}
