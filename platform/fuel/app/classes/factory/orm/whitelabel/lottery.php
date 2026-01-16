<?php

use Models\WhitelabelLottery;
use Classes\Orm\AbstractOrmModel;


/** @deprecated - use new fixtures instead */
class Factory_Orm_Whitelabel_Lottery extends Factory_Orm_Abstract
{
    public function __construct(array $props = [])
    {
        $data = [
            'id'                        => 999,
            'whitelabel_id'             => 999,
            'lottery_id'                => 255,
            'lottery_provider_id'       => 255,
            'tier'                      => 255,
            'min_lines'                 => 255,
            'quick_pick_lines'          => 255,
            'is_enabled'                => true,
            'model'                     => false,
            'is_multidraw_enabled'      => true,
            'is_bonus_balance_in_use'   => false,
            'should_decrease_prepaid'   => true,
            'ltech_lock'                => false,
            'income'                    => 9.99,
            'minimum_expected_income'   => 1.00,
            'volume'                    => 9.99
        ];

        $this->props = array_merge($data, $props);
    }

    /**
     * @return WhitelabelLottery
     * @throws Throwable
     * @deprecated - use new fixtures instead
     */
    public function build(bool $save = true): AbstractOrmModel
    {
        $whitelabel_lottery = new WhitelabelLottery($this->props);

        if ($save) {
            $whitelabel_lottery->save();
        }

        return $whitelabel_lottery;
    }
}
