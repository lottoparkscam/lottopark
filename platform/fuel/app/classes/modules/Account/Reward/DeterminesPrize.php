<?php

namespace Modules\Account\Reward;

interface DeterminesPrize
{
    /**
     * We need to know what reward dispatching strategy should be used.
     * If ticket is lost then return PrizeType::CASH() (no processing).
     *
     * @return PrizeType
     */
    public function prizeType(): PrizeType;
}
