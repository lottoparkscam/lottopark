<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractNavigation;

final class AddRaffleNavButton extends AbstractNavigation
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark']; 
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
		'en' => 'Raffle', 
	]; 
	protected const SLUG_FOR_LINK = 'raffle'; 
	protected const MENU = 'primary'; 

}
