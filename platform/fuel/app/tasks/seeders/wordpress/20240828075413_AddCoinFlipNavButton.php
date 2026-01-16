<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractNavigation;

final class AddCoinFlipNavButton extends AbstractNavigation
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark']; 
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => 'CoinFlip',
	];
    protected const FORCE_ENGLISH_NAME = true;
    protected const SLUG_FOR_LINK = 'play/' . AddGgWorldCoinFlipPage::COINFLIP_SLUG;
    protected const MENU = 'primary';
}
