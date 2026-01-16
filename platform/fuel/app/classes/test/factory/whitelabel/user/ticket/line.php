<?php

/**
 * @deprecated - use new fixtures instead
 * Test Factory Whitelabel User Ticket Line.
 *
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2019-07-17
 * Time: 15:03:22
 */
final class Test_Factory_Whitelabel_User_Ticket_Line extends Test_Factory_Base
{

    private $numbers_per_line;

    private function draw_random_numbers(Model_Lottery_Type $lottery_type, string $count_name = 'ncount', string $range_name = 'nrange'): string
    {
        $numbers_array = [];
        $numbers_count = $this->numbers_per_line ?: $lottery_type->{$count_name};
        for ($i = 0; $i < $numbers_count; $i++) {
            do {
                $number = random_int(1, $lottery_type->{$range_name});
            } while (array_search($number, $numbers_array, true) !== false);
            $numbers_array[] = $number;
        }

        return implode(',', $numbers_array);
    }

    protected function values(array &$values): array
    {
        if (isset($values['numbers_per_line'])) {
            $this->numbers_per_line = $values['numbers_per_line'];
            unset($values['numbers_per_line']);
        }
        $lottery_type = $values['lottery_type'];
        $draw_numbers = function () use ($lottery_type): string {
            return $this->draw_random_numbers($lottery_type);
        };
        $draw_bnumbers = function () use ($lottery_type): string {
            if (empty($lottery_type->bcount)) {
                return ''; // no bonus since count is empty
            }

            return $this->draw_random_numbers($lottery_type, 'bcount', 'brange');
        };
        $line_price = $values['line_price'] ?? self::$reusable_values['line_price'];
        unset($values['line_price'], $values['lottery_type']);

        return [
            // 'whitelabel_user_ticket_id',
            // 'line_price',
            // 'lottery_type',
            'numbers' => $draw_numbers,
            'bnumbers' => $draw_bnumbers,
            'amount_local' => $line_price,
            'amount' => $line_price,
            'amount_usd' => $line_price,
            'status' => 0,
            'payout' => 0,
        ];
    }
}
