<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractNavigation;

final class AddKenoNavButton extends AbstractNavigation
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark']; 
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
		'en' => 'Keno', 
	]; 
	protected const SLUG_FOR_LINK = 'keno'; 
	protected const MENU = 'primary'; 

}
