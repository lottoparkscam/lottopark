<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractNavigation;
use Models\Lottery;

final class MiniPowerballNavButton extends AbstractNavigation
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark.com'];
    protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => 'Mini Powerball',
    ];
    protected const SLUG_FOR_LINK = 'play/' . Lottery::MINI_POWERBALL_SLUG;
    protected const MENU = 'primary';
	protected const SEED_THE_SAME_AS_ENGLISH_FOR_MISSING_LANGUAGES = false;

}
