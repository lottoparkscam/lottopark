<?php

/**
 * @deprecated - use new fixtures instead
 * Test Factory Whitelabel User Ticket.
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2019-07-16
 * Time: 16:33:41
 */
final class Test_Factory_Whitelabel_User_Ticket extends Test_Factory_Base
{
    protected $reusable_columns = [
        'whitelabel_id',
        'whitelabel_user_id',
        'lottery_id',
        'lottery_type_id',
        'currency_id',
    ];

    protected function values(array &$values): array
    {
        $line_price = $values['line_price'] ?? 5;
        $ticket_multiplier = $values['ticket_multiplier'] ?? 1;
        unset($values['ticket_multiplier'], $values['line_price']);

        return [
            'token' => function () use ($values): int {
                $ticket_token = Lotto_Security::generate_ticket_token($values['whitelabel_id']);
                return $ticket_token;
            },
            // 'line_price',
            // 'line_count',
            'amount_local' => $line_price * $values['line_count'] * $ticket_multiplier, // TODO: {Vordis 2019-07-17 14:44:14} data integrity
            'amount' => $line_price * $values['line_count'] * $ticket_multiplier,
            'amount_usd' => $line_price * $values['line_count'] * $ticket_multiplier,
            'amount_payment' => $line_price * $values['line_count'] * $ticket_multiplier,
            'amount_manager' => $line_price * $values['line_count'] * $ticket_multiplier,
            'draw_date' => $values['valid_to_draw'] ?? null,
            'date' => Helpers_Time::now(),
            'date_processed' => null,
            'paid' => true,
            'payout' => false,
            'model' => 0,
            'cost_local' => 0,
            'cost_usd' => 0,
            'cost' => 0,
            'cost_manager' => 0,
            'income_local' => 0,
            'income_usd' => 0,
            'income' => 0,
            'income_value' => 0,
            'income_manager' => 0,
            'income_type' => 0,
            'is_insured' => 0,
            'tier' => 0,
            'status' => 0,
            'margin_value' => 0,
            'margin_local' => 0,
            'margin_usd' => 0,
            'margin' => 0,
            'margin_manager' => 0,
            'ip' => '127.0.0.1',
        ];
    }
}
