<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractNavigation;
use Models\Raffle;

final class FortuneFateRaffleNavButton extends AbstractNavigation
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['fatelotto'];
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => 'Fortune Fate Raffle',
        'nl' => 'Fortune Fate Raffle',
        'zh' => 'Fortune Fate Raffle',
	];
	protected const SLUG_FOR_LINK = 'play-raffle/' . Raffle::FORTUNE_FATE_RAFFLE_SLUG;
	protected const FORCE_ENGLISH_NAME = true;
}
