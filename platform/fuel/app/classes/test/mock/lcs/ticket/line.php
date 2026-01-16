<?php

/**
 * Test Mock Lcs Ticket Line.
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2019-09-20
 * Time: 14:16:01
 */
final class Test_Mock_Lcs_Ticket_Line extends Test_Mock_Mocker
{ // TODO: {Vordis 2019-09-20 13:02:18} very crude - haste make waste
    protected function create(...$args): array
    {
        $overrides = &$args[0];
        return
            $overrides +
            [
                'status' => 2,
                'created_at' => '2019-09-20 09:28:20',
                'updated_at' => '2019-09-20 09:28:20',
                'numbers' =>
                [
                    0 =>
                    [
                        0 => 1,
                        1 => 2,
                        2 => 3,
                        3 => 4,
                        4 => 5,
                        5 => 6,
                    ]
                ],
                // 'lottery_prize' =>
                // [
                //     'per_user' => '418.00',
                //     'currency_code' => 'PLN',
                //     'lottery_rule_tier' =>
                //     [
                //         'slug' => 'match-6',
                //     ],
                // ],
            ];
    }
}
