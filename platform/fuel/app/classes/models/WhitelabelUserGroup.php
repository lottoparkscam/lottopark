<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;

/**
 * @property int $id
 * @property int $whitelabel_id
 * @property string $name
 * @property int $prize_payout_percent
 */
class WhitelabelUserGroup extends AbstractOrmModel
{
    protected static $_table_name = 'whitelabel_user_group';

    protected static $_properties = [
        'id',
        'whitelabel_id',
        'name',
        'prize_payout_percent',
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'whitelabel_id' => self::CAST_INT,
        'prize_payout_percent' => self::CAST_INT,
    ];
}
