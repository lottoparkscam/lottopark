<?php

namespace Modules\Payments\Astro;

use MyCLabs\Enum\Enum;

/**
 * @codeCoverageIgnore
 *
 * @method static self PENDING()
 * @method static self APPROVED()
 * @method static self CANCELLED()
 */
class AstroPaymentStatus extends Enum
{
    public const PENDING = 'PENDING';
    public const APPROVED = 'APPROVED';
    public const CANCELLED = 'CANCELLED';
}
