<?php

use Models\LotteryTypeData;
use Classes\Orm\AbstractOrmModel;

/** @deprecated - use new fixtures instead */
class Factory_Orm_Lottery_Type_Data extends Factory_Orm_Abstract
{
    public function __construct(array $props = [])
    {
        $data = [
            'id'                => 255,
            'lottery_type_id'   => 255,
            'match_n'           => 255,
            'match_b'           => 255,
            'type'              => 255,
            'is_jackpot'        => 255,
            'additional_data'   => 'additional data',
            'prize'             => 'prize',
            'odds'              => 9.99,
            'estimated'         => 9.99
        ];

        $this->props = array_merge($data, $props);
    }

    /**
     * @return LotteryTypeData
     * @throws Throwable
     * @deprecated - use new fixtures instead
     */
    public function build(bool $save = true): AbstractOrmModel
    {
        $lottery_type_data = new LotteryTypeData($this->props);

        if ($save) {
            $lottery_type_data->save();
        }

        return $lottery_type_data;
    }
}
