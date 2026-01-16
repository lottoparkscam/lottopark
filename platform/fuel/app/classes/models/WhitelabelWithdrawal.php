<?php

namespace Models;

use Orm\BelongsTo;
use Classes\Orm\AbstractOrmModel;

/**
 * @property int $id
 * @property int $withdrawal_id
 * @property int $whitelabel_id
 * @property bool $show
 * @property bool $show_casino
 *
 * @property BelongsTo|Withdrawal|null $withdrawal
 * @property BelongsTo|Whitelabel|null $whitelabel
 */
class WhitelabelWithdrawal extends AbstractOrmModel
{
    protected static $_table_name = 'whitelabel_withdrawal';

    protected static $_properties = [
        'id',
        'withdrawal_id',
        'whitelabel_id',
        'show',
        'show_casino',
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'withdrawal_id' => self::CAST_INT,
        'whitelabel_id' => self::CAST_INT,
        'show' => self::CAST_BOOL,
        'show_casino' => self::CAST_BOOL,
    ];

    protected static array $_belongs_to = [
        'withdrawal' => [
            'key_from' => 'withdrawal_id',
            'model_to' => Withdrawal::class,
            'key_to' => 'id'
        ],
        'whitelabel' => [
            'key_from' => 'whitelabel_id',
            'model_to' => Whitelabel::class,
            'key_to' => 'id'
        ]
    ];
}
