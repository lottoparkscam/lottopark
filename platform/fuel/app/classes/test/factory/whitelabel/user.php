<?php

/** @deprecated - use new fixtures instead */
final class Test_Factory_Whitelabel_User extends Test_Factory_Base
{
    protected function values(array &$values): array
    {
        return [
            'token' => Lotto_Security::generate_user_token($values['whitelabel_id']),
            'whitelabel_id' => $values['whitelabel_id'],
            'language_id' => 1,
            'currency_id' => 2,
            'is_active' => 1,
            'is_confirmed' => 0,
            'email' =>  $this->random_string() . '@test.loc',
            'salt' =>  $newsalt = Lotto_Security::generate_salt(),
            'hash' => Lotto_Security::generate_hash(
                \Fuel\Core\Str::random(10),
                $newsalt
            ),
            'name' => '',
            'surname' => '',
            'address_1' => '',
            'address_2' => '',
            'city' => '',
            'country' => '',
            'state' => '',
            'zip' => '',
            'phone_country' => '',
            'gender' => Model_Whitelabel_User::GENDER_UNSET,
            'national_id' => '',
            'birthdate' => null,
            'phone' => '',
            'timezone' => '',
            'date_register' => Helpers_Time::now(),
            'balance' => 0,
            'register_ip' => '127.0.0.1',
            'last_ip' => '127.0.0.1',
            'last_active' => Helpers_Time::now(),
            'last_update' => Helpers_Time::now(),
            'last_country' => null,
            'register_country' => null,
            'referrer_id' => null,
            'connected_aff_id' => null,
            'login' => null,
            'prize_payout_whitelabel_user_group_id' => null
        ];
    }
}
