<?php

final class Helpers_Quickpick
{

    public static function get_numbers_per_line(int $lottery_id): ?int
    {
        return max(Lotto_Helper::get_numbers_per_line_array($lottery_id)) ?? null;
    }

    public static function get_ticket_multiplier(int $lottery_id): ?int
    {
        return Model_Lottery_Type_Multiplier::min_max_for_lottery($lottery_id)['min'] ?? 1;
    }
}
