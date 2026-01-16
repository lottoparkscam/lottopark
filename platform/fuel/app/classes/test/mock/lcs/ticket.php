<?php

/**
 * Test Mock Lcs Ticket.
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2019-09-20
 * Time: 12:57:15
 */
final class Test_Mock_Lcs_Ticket extends Test_Mock_Mocker
{ // TODO: {Vordis 2019-09-20 13:02:18} very crude - haste make waste
    protected function create(...$args): array
    {
        $overrides = &$args[0];
        $lines = &$args[1];
        return
            $overrides + 
            [
                'uuid' => uniqid(),
                'draw_date' => null,
                'status' => 2,
                'amount' => '32.77',
                'prize' => NULL,
                'currency_code' => 'PLN',
                'ip' => '79.137.81.178',
                'ip_country_code' => 'FR',
                'is_paid_out' => 0,
                'created_at' => '2019-09-20 09:28:19',
                'updated_at' => '2019-09-20 09:28:19',
                'lottery_ticket_lines' => $lines
            ];
    }
}
