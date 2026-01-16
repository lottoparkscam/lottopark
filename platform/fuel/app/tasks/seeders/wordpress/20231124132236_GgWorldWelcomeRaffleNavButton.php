<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractNavigation;
use Models\Raffle;

final class GgWorldWelcomeRaffleNavButton extends AbstractNavigation
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark.com'];
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => 'Raffle',
		'pl' => 'Raffle',
        'az' => 'Raffle',
        'cs' => 'Raffle',
        'da' => 'Raffle',
        'de' => 'Raffle',
        'et' => 'Raffle',
        'es' => 'Raffle',
        'fr' => 'Raffle',
        'hr' => 'Raffle',
        'id' => 'Raffle',
        'it' => 'Raffle',
        'lv' => 'Raffle',
        'lt' => 'Raffle',
        'hu' => 'Raffle',
        'mk' => 'Raffle',
        'nl' => 'Raffle',
        'pt' => 'Raffle',
        'ro' => 'Raffle',
        'sq' => 'Raffle',
        'sk' => 'Raffle',
        'sl' => 'Raffle',
        'sr' => 'Raffle',
        'sv' => 'Raffle',
        'fil' => 'Raffle',
        'vi' => 'Raffle',
        'tr' => 'Raffle',
        'uk' => 'Raffle',
        'el' => 'Raffle',
        'bg' => 'Raffle',
        'ru' => 'Raffle',
        'ge' => 'Raffle',
        'ar' => 'Raffle',
        'hi' => 'Raffle',
        'bn' => 'Raffle',
        'th' => 'Raffle',
        'ko' => 'Raffle',
        'zh' => 'Raffle',
	];
	protected const SLUG_FOR_LINK = 'play-raffle/' . Raffle::GG_WORLD_WELCOME_RAFFLE_SLUG;
	protected const FORCE_ENGLISH_NAME = true;
}
