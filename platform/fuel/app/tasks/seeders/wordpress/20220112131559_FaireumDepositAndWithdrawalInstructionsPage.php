<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPage;

final class FaireumDepositAndWithdrawalInstructionsPage extends AbstractPage
{
    public const SLUG = 'casino-deposit-withdrawal-instructions';
    protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['faireum'];
    protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => [
            'slug' => self::SLUG,
            'title' => 'Deposit & Withdrawal instructions',
            'body' => '',
        ]
    ];
}
