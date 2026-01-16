<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractNavigation;
use Models\Lottery;

final class WeekdayWindfallNavButton extends AbstractNavigation
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark.com']; 
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => 'Weekday Windfall',
        'pl' => 'Weekday Windfall',
        'az' => 'Weekday Windfall',
        'cs' => 'Weekday Windfall',
        'da' => 'Weekday Windfall',
        'de' => 'Weekday Windfall',
        'et' => 'Weekday Windfall',
        'es' => 'Weekday Windfall',
        'fr' => 'Weekday Windfall',
        'hr' => 'Weekday Windfall',
        'id' => 'Weekday Windfall',
        'it' => 'Weekday Windfall',
        'lv' => 'Weekday Windfall',
        'lt' => 'Weekday Windfall',
        'hu' => 'Weekday Windfall',
        'mk' => 'Weekday Windfall',
        'nl' => 'Weekday Windfall',
        'pt' => 'Weekday Windfall',
        'ro' => 'Weekday Windfall',
        'sq' => 'Weekday Windfall',
        'sk' => 'Weekday Windfall',
        'sl' => 'Weekday Windfall',
        'sr' => 'Weekday Windfall',
        'sv' => 'Weekday Windfall',
        'fil' => 'Weekday Windfall',
        'vi' => 'Weekday Windfall',
        'tr' => 'Weekday Windfall',
        'uk' => 'Weekday Windfall',
        'el' => 'Weekday Windfall',
        'bg' => 'Weekday Windfall',
        'ru' => 'Weekday Windfall',
        'ge' => 'Weekday Windfall',
        'ar' => 'Weekday Windfall',
        'hi' => 'Weekday Windfall',
        'bn' => 'Weekday Windfall',
        'th' => 'Weekday Windfall',
        'ko' => 'Weekday Windfall',
        'zh' => 'Weekday Windfall',
        'fa' => 'Weekday Windfall',
        'fi' => 'Weekday Windfall',
        'ja' => 'Weekday Windfall',
        'he' => 'Weekday Windfall',
	]; 
	protected const SLUG_FOR_LINK = 'play/' . Lottery::WEEKDAY_WINDFALL_SLUG; 
}
