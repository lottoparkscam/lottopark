<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPage;

final class AddRafflePage extends AbstractPage
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark']; 
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
		'en' => [
			'slug' => 'raffle', 
			'title' => 'Raffle', 
			'body' => '', 
		],
	]; 

}
