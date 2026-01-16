<?php

use Models\LotteryType;
use Classes\Orm\AbstractOrmModel;


/** @deprecated - use new fixtures instead */
class Factory_Orm_Lottery_Type extends Factory_Orm_Abstract
{
    public function __construct(array $props = [])
    {
        $data = [
            'id'                    => 255,
            'lottery_id'            => 255,
            'odds'                  => 99.99,
            'ncount'                => 255,
            'bcount'                => 255,
            'nrange'                => 255,
            'brange'                => 255,
            'bextra'                => 255,
            'date_start'            => '2020-10-10',
            'def_insured_tiers'     => 255,
            'additional_data'       => 'a:3:{s:6:"refund";i:1;s:10:"refund_min";i:0;s:10:"refund_max";i:9;}'
        ];

        $this->props = array_merge($data, $props);
    }

    /**
     * @return LotteryType
     * @throws Throwable
     * @deprecated - use new fixtures instead
     */
    public function build(bool $save = true): AbstractOrmModel
    {
        $lottery_type = new LotteryType($this->props);

        if ($save) {
            $lottery_type->save();
        }

        return $lottery_type;
    }
}
