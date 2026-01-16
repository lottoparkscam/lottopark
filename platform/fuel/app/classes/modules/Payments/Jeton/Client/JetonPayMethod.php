<?php

namespace Modules\Payments\Jeton\Client;

use MyCLabs\Enum\Enum;

/**
 * @codeCoverageIgnore
 *
 * Jeton supports the followings payment types. If we need any of them in the future,
 * let's re use this snippet.
 *
 * @url https://developer.jeton.com/doc/pay-checkout
 *
 * @method static self CHECKOUT()
 * @method static self DIRECT()
 * @method static self QR()
 * @method static self JETGO()
 */
final class JetonPayMethod extends Enum
{
    public const CHECKOUT = 'CHECKOUT';
    public const DIRECT = 'DIRECT';
    public const QR = 'QR';
    public const JETGO = 'JETGO';
}
