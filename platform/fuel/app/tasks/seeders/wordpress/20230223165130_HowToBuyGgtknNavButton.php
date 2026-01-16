<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractNavigation;

final class HowToBuyGgtknNavButton extends AbstractNavigation
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark']; 
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
		'en' => 'How to buy GG Token', 
	]; 
	protected const SLUG_FOR_LINK = 'how-to-buy-gg-token'; 
	protected const MENU = 'footer'; 

}