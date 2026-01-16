<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractNavigation;
use Models\Lottery;

final class EuromillionsSuperdrawNavButton extends AbstractNavigation
{
    protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark.com'];
    protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => 'EuroMillions Superdraw',
        'pl' => 'EuroMillions Superdraw',
        'az' => 'EuroMillions Superdraw',
        'cs' => 'EuroMillions Superdraw',
        'da' => 'EuroMillions Superdraw',
        'de' => 'EuroMillions Superdraw',
        'et' => 'EuroMillions Superdraw',
        'es' => 'EuroMillions Superdraw',
        'fr' => 'EuroMillions Superdraw',
        'hr' => 'EuroMillions Superdraw',
        'id' => 'EuroMillions Superdraw',
        'it' => 'EuroMillions Superdraw',
        'lv' => 'EuroMillions Superdraw',
        'lt' => 'EuroMillions Superdraw',
        'hu' => 'EuroMillions Superdraw',
        'mk' => 'EuroMillions Superdraw',
        'nl' => 'EuroMillions Superdraw',
        'pt' => 'EuroMillions Superdraw',
        'ro' => 'EuroMillions Superdraw',
        'sq' => 'EuroMillions Superdraw',
        'sk' => 'EuroMillions Superdraw',
        'sl' => 'EuroMillions Superdraw',
        'sr' => 'EuroMillions Superdraw',
        'sv' => 'EuroMillions Superdraw',
        'fil' => 'EuroMillions Superdraw',
        'vi' => 'EuroMillions Superdraw',
        'tr' => 'EuroMillions Superdraw',
        'uk' => 'EuroMillions Superdraw',
        'el' => 'EuroMillions Superdraw',
        'bg' => 'EuroMillions Superdraw',
        'ru' => 'EuroMillions Superdraw',
        'ge' => 'EuroMillions Superdraw',
        'ar' => 'EuroMillions Superdraw',
        'hi' => 'EuroMillions Superdraw',
        'bn' => 'EuroMillions Superdraw',
        'th' => 'EuroMillions Superdraw',
        'ko' => 'EuroMillions Superdraw',
        'zh' => 'EuroMillions Superdraw',
        'fa' => 'EuroMillions Superdraw',
        'fi' => 'EuroMillions Superdraw',
        'ja' => 'EuroMillions Superdraw',
        'he' => 'EuroMillions Superdraw',
    ];
    protected const SLUG_FOR_LINK = 'play/' . Lottery::EUROMILLIONS_SUPERDRAW_SLUG;
}
