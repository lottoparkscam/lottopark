<?php

/**
 * @deprecated - use new fixtures instead
 * Test Factory Whitelabel Transaction.
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2019-07-17
 * Time: 14:46:23
 */
final class Test_Factory_Whitelabel_Transaction extends Test_Factory_Base
{
    protected function values(array &$values): array
    {
        return [
            'token' => function () use ($values): int {
                $transaction_token = Lotto_Security::generate_transaction_token($values['whitelabel_id']);
                return $transaction_token;
            },
            'date' => Helpers_Time::now(),
            'status' => 0,
            'type' => 0,
            // TODO: {Vordis 2019-07-17 14:36:25} ATM values below should be provided manually
            // 'whitelabel_id',
            // 'whitelabel_user_id',
            // 'currency_id',
        ];
    }
}
