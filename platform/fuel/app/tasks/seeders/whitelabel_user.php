<?php

namespace Fuel\Tasks\Seeders;

use Lotto_Security;

/**
 * Whitelabel User seeder.
 */
final class Whitelabel_User extends Seeder
{
    const TEST_USER_TOKEN = '607097733';

    protected function columnsStaging(): array
    {
        return [
            'whitelabel_user' => ['token', 'whitelabel_id', 'language_id', 'currency_id', 'is_active', 'is_confirmed', 'email', 'hash', 'salt', 'activation_hash', 'activation_valid', 'resend_hash', 'resend_last', 'lost_hash', 'lost_last', 'name', 'surname', 'balance', 'first_deposit_amount_manager', 'total_deposit_manager', 'total_withdrawal_manager', 'total_purchases_manager', 'total_net_income_manager', 'last_deposit_date', 'last_deposit_amount_manager', 'net_winnings_manager', 'sale_status', 'pnl_manager', 'address_1', 'address_2', 'city', 'country', 'state', 'zip', 'phone_country', 'phone', 'birthdate', 'timezone', 'gender', 'national_id', 'date_register', 'register_ip', 'last_ip', 'register_country', 'last_country', 'last_active', 'system_type', 'browser_type', 'last_update', 'first_purchase', 'is_deleted', 'date_delete']
        ];
    }

    protected function rowsStaging(): array
    {
        return [];
    }

    protected function rowsDevelopment(): array
    {
        return [];
    }

    protected function rowsProduction(): array
    {
        return [];
    }
}
