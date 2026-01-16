<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractNavigation;

final class ContactToCasinoPrimaryMenuNavButton extends AbstractNavigation
{
	// without faireum; they don't use this page
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = [
		'lottopark.com',
		'lottohoy.com',
		'lottomat.com',
		'redfoxlotto.com'
	];
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
		'en' => 'Contact',
	];
	protected const SLUG_FOR_LINK = 'contact';
	protected const MENU = 'casino-primary';
}
