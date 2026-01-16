<?php

namespace Modules\Account\Reward;

use Models\RaffleRuleTierInKindPrize;
use MyCLabs\Enum\Enum;

/**
 * @codeCoverageIgnore
 *
 * @method static PrizeType CASH()
 * @method static PrizeType IN_KIND()
 * @method static PrizeType TICKET()
 */
class PrizeType extends Enum
{
    public const CASH = 'cash';
    public const IN_KIND = 'prize-in-kind';
    public const TICKET = 'ticket';

    public static function createFrom(RaffleRuleTierInKindPrize $prize): self
    {
        return new self($prize->type);
    }

    public function notEquals(PrizeType $type): bool
    {
        return !$this->equals($type);
    }
}
