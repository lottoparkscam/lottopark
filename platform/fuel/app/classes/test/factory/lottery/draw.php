<?php

/**
 * @deprecated - use new fixtures instead
 * Test Factory Lottery Draw.
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2019-07-16
 * Time: 18:21:09
 */
final class Test_Factory_Lottery_Draw extends Test_Factory_Base
{
    // TODO: {Vordis 2019-09-20 10:52:56} great simplification - use lottery to populate data
    protected function values(array &$values): array
    {
        $lottery = $values['lottery'];
        unset($values['lottery']);
        $lottery_type = $values['lottery_type'];
        unset($values['lottery_type']);
        return [
            'lottery_id' => $lottery->id,
            'lottery_type_id' => $lottery_type->id,
            'date_download' => Helpers_Time::now(),
            'date_local' => $lottery->last_date_local,
            'jackpot' => $lottery->last_jackpot_prize,
            'numbers' => $lottery->last_numbers,
            'bnumbers' => $lottery->last_bnumbers,
            'total_prize' => 1111,
            'total_winners' => 33,
            'final_jackpot' => 123123123,
            'has_pending_tickets' => 1,
        ];
    }
}
