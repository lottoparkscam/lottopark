<?php

/**
* Use this trait to add last_for_lottery method to model.
* Can be used only by models that belongs to lottery (have lottery_id in fields).
*/
trait Model_Traits_Last_For_Lottery 
{
    /**
     * Get last model for lottery.
     *
     * @param int $lottery_id id of the lottery. NOTE: that it accepts string values as long as they are numeric.
     * @return self|null null possible if lottery doesn't have any models yet.
     * @throws \Throwable on sql errors.
     */
    public static function last_for_lottery(int $lottery_id): ?self
    {
        $models = self::find(
            [
                'where' => [
                    'lottery_id' => $lottery_id
                ],
                'order_by' => [
                    'id' => 'desc'
                ],
                'limit' => 1,
            ]
        );
        return $models[0] ?? null;
    }

    /**
     * Get last model for lottery by draw no.
     *
     * @param int $lottery_id id of the lottery. NOTE: that it accepts string values as long as they are numeric.
     * @return self|null null possible if lottery doesn't have any models yet.
     * @throws \Throwable on sql errors.
     */
    public static function last_for_lottery_by_draw_no(int $lottery_id): ?self
    {
        $models = self::find(
            [
                'where' => [
                    'lottery_id' => $lottery_id
                ],
                'order_by' => [
                    'draw_no' => 'desc',
                    'id' => 'desc'
                ],
                'limit' => 1,
            ]
        );
        return $models[0] ?? null;
    }

    public static function getLotteryDrawByLotteryIdAndTicketDrawDate(int $lotteryId, $drawDate): ?self
    {
        $models = self::find(
            [
                'where' => [
                    'lottery_id' => $lotteryId,
                    'date_local' => $drawDate
                ],
                'order_by' => [
                    'draw_no' => 'desc',
                    'id' => 'desc'
                ],
                'limit' => 1,
            ]
        );
        return $models[0] ?? null;
    }
}
