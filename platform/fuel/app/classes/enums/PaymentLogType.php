<?php

namespace Enums;

use MyCLabs\Enum\Enum;

/**
 * @codeCoverageIgnore
 *
 * Class PaymentLogType
 * @Author Sebastian Twaróg <sebastian.twarog@gg.international>
 *
 * @method static static INFO()
 * @method static static SUCCESS()
 * @method static static WARNING()
 * @method static static ERROR()
 */
class PaymentLogType extends Enum
{
    public const INFO = 0;
    public const SUCCESS = 1;
    public const WARNING = 2;
    public const ERROR = 3;
}
