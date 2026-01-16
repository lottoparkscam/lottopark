<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPage;

final class CasinoFooterPage extends AbstractPage
{
    protected const TYPE = 'parent';
    protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark'];

    protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => [
            'title' => 'Casino Footer',
            'body' => 'All [whitelabelCasinoDomain] products are operated by [whitelabelCompany]. A company licensed and regulated by the law of Curacao under the Master License Holder Curacao eGaming with license number [whitelabelLicence]. White Lto Limited (CY) (reg.number HE 413497) with a registered office located at Voukourestiou, 25 Neptun House, 1st floor, Flat/Office 11, Zakaki,3045, Limassol, Cyprus, is acting as an Agent on behalf of the license-holding entity White Lotto B.V.',
        ],
    ];
}
