<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractNavigation;

final class MinigamesNavButton extends AbstractNavigation
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark'];
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
		'en' => 'Mini Games',
	];
	protected const FORCE_ENGLISH_NAME = true;
	protected const SLUG_FOR_LINK = 'mini-games';
	protected const MENU = 'primary';
}
