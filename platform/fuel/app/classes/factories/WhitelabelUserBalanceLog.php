<?php

namespace Factories;

use Exception;
use Factory_Orm_Abstract;
use Classes\Orm\AbstractOrmModel;

/** @deprecated - use new fixtures instead */
class WhitelabelUserBalanceLog extends Factory_Orm_Abstract
{
    public function __construct(array $props = [])
    {
        $data = [
            'id'                                    => 256,
            'whitelabel_user_id'                    => 256,
            'level'                                 => 5,
            'created_at'                            => '2020-01-12 0:00:00',
            'session_datetime'                      => '2020-01-12 0:00:00',
            'message'                               => 'Example message',
            'balance_change_currency_code'          => 'USD',
            'balance_change_import_currency_code'   => 'EUR',
            'is_bonus'                              => 0,
            'balance_change'                        => 99.99,
            'balance_change_import'                 => 0.00,
            'balance_change_before_conversion'      => 1.99,
            'balance_change_before_conversion_currency_code' => 'PLN'
        ];

        $this->props = array_merge($data, $props);
    }

    /**
     * @param bool $save
     * @return AbstractOrmModel
     * @throws Exception
     * @deprecated - use new fixtures instead
     */
    public function build(bool $save = true): AbstractOrmModel
    {
        $whitelabelUserBalanceLog = new \Models\WhitelabelUserBalanceLog($this->props);

        if ($save) {
            $whitelabelUserBalanceLog->save();
        }

        return $whitelabelUserBalanceLog;
    }
}
