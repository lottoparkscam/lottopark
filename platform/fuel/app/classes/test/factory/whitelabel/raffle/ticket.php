<?php

/** @deprecated - use new fixtures instead */
final class Test_Factory_Whitelabel_Raffle_Ticket extends Test_Factory_Base
{
    protected $reusable_columns = [
        'whitelabel_id',
        'whitelabel_user_id',
        'raffle_id',
        'currency_id',
    ];

    protected function values(array &$values): array
    {
        $line_price = $values['line_price'] ?? 5;
        $line_count = $values['line_count'] ?? 1;
        
        unset($values['line_price']);
        unset($values['line_count']);

        return [
            'token' => function () use ($values): int {
                $ticket_token = Lotto_Security::generate_ticket_token($values['whitelabel_id']);
                return $ticket_token;
            },
            'amount' => $line_price * $line_count,
            'draw_date' => null,
            'is_paid_out' => false,
            'status' => 0,
            'ip' => '127.0.0.1',
            'created_at' => time(),
            'updated_at' => time()
        ];
    }
}
