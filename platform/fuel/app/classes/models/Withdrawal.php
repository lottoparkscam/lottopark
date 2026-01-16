<?php

namespace Models;

use Orm\HasMany;
use Classes\Orm\AbstractOrmModel;

/**
 * @property int $id
 * @property string $name
 *
 * @property HasMany|WhitelabelWithdrawal[]|null $whitelabel_withdrawals
 */
class Withdrawal extends AbstractOrmModel
{
    protected static $_table_name = 'withdrawal';

    protected static $_properties = [
        'id',
        'name'
    ];

    protected $casts = [
        'id' => self::CAST_INT,
    ];

    protected static array $_has_many = [
        'whitelabel_withdrawals' => [
            'key_from' => 'id',
            'model_to' => WhitelabelWithdrawal::class,
            'key_to' => 'withdrawal_id'
        ]
    ];
}
