<?php

namespace Modules\Payments\Jeton\Client;

use MyCLabs\Enum\Enum;

/**
 * @codeCoverageIgnore
 *
 * @method static self PAY()
 * @method static self PAYOUT()
 */
final class JetonTransactionType extends Enum
{
    public const PAY  = 'PAY';
    public const PAYOUT = 'PAYOUT';
}
