<?php

namespace Modules\Payments;

use Helpers_General;
use MyCLabs\Enum\Enum;

/**
 * @codeCoverageIgnore
 *
 * @method static static BALANCE()
 * @method static static CREDIT_CARD()
 * @method static static OTHER()
 * @method static static BONUS_BALANCE()
 */
final class PaymentType extends Enum
{
    public const BALANCE = Helpers_General::PAYMENT_TYPE_BALANCE;
    public const CREDIT_CARD = Helpers_General::PAYMENT_TYPE_CC;
    public const OTHER = Helpers_General::PAYMENT_TYPE_OTHER;
    public const BONUS_BALANCE = Helpers_General::PAYMENT_TYPE_BONUS_BALANCE;
}
