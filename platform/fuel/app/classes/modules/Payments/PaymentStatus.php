<?php

namespace Modules\Payments;

use MyCLabs\Enum\Enum;

/**
 * @codeCoverageIgnore
 *
 * @method static self PAID() - when explicit success status
 * @method static self FAILED() - when explicit failed status
 * @method static self PENDING() - when pending status
 * @method static self UNSUPPORTED() - when provider is not supporting method for checking payment status
 * @method static self CORRUPTED() - when transaction was created, but is corrupted. E.g. timeout or unsupported country
 */
class PaymentStatus extends Enum
{
    public const PAID = 'paid';
    public const FAILED = 'failed';
    public const PENDING = 'pending';
    public const UNSUPPORTED = 'unsupported';
    public const CORRUPTED = 'corrupted';
}
