<?php

use Models\WhitelabelUser;
use Classes\Orm\AbstractOrmModel;

/** @deprecated - use new fixtures instead */
class Factory_Orm_Whitelabel_User extends Factory_Orm_Abstract
{
    public function __construct(array $props = [])
    {
        $salt = 'asdqwe';
        $hash = Lotto_Security::generate_hash('asdqwe', $salt);
        $data = [
            'id'                        => 1,
            'login'                     => '',
            'email'                     => 'test@user.loc',
            'balance'                   => 0,
            'bonus_balance'             => 0,
            'currency_id'               => 1,
            'address_1'                  => '',
            'address_2'                  => '',
            'city'                      => '',
            'country'                   => '',
            'date_register'             => '2020-01-12 0:00:00',
            'gender'                    => 1,
            'is_active'                 => 1,
            'is_deleted'                => 0,
            'is_confirmed'              => 1,
            'language_id'               => 1,
            'last_active'               => '2020-01-12 0:00:00',
            'last_ip'                   => '',
            'last_update'               => '2020-01-12 0:00:00',
            'lines_sold_quantity'       => 0,
            'name'                      => '',
            'national_id'               => 1,
            'phone'                     => '123123123',
            'phone_country'             => 'PL',
            'refer_bonus_used'          => 0,
            'register_ip'               => '127.0.0.1',
            'sale_status'               => 0,
            'salt'                      => $salt,
            'sent_welcome_mail'         => 1,
            'state'                     => 1,
            'surname'                   => '',
            'tickets_sold_quantity'     => 0,
            'timezone'                  => 'Europe/Warsaw',
            'token'                     => 1234567,
            'whitelabel_id'             => 1,
            'zip'                       => '62-800',
            'hash'                      => $hash
        ];

        $this->props = array_merge($data, $props);
    }

    /**
     * @return WhitelabelUser
     * @throws Throwable
     * @deprecated - use new fixtures instead
     */
    public function build(bool $save = true): AbstractOrmModel
    {
        $whitelabelUser = new WhitelabelUser($this->props);

        if ($save) {
            $whitelabelUser->save();
        }

        return $whitelabelUser;
    }
}
