<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Orm\BelongsTo;

/**
 * The model supports the ability to add different API keys for different whitelabels
 *
 * @property int $id
 * @property int $whitelabelId
 * @property string $apiKey
 * @property string $apiSecret
 * @property string $scanConfirmUrl
 * @property bool $isEnabled
 * @property string $createdAt
 *
 * @property BelongsTo|Whitelabel $whitelabel
 */
class WhitelabelLotteryProviderApi extends AbstractOrmModel
{
    protected static $_table_name = 'whitelabel_lottery_provider_api';

    protected static $_properties = [
        'id',
        'whitelabel_id',
        'api_key',
        'api_secret',
        'is_enabled',
        'scan_confirm_url',
        'created_at',
    ];

    protected $casts = [
        'id'                => self::CAST_INT,
        'whitelabel_id'     => self::CAST_INT,
        'api_key'           => self::CAST_STRING,
        'api_secret'        => self::CAST_STRING,
        'is_enabled'        => self::CAST_BOOL,
        'scan_confirm_url'  => self::CAST_STRING,
        'created_at'        => self::CAST_DATETIME,
    ];

    protected static array $_belongs_to = [
        'whitelabel' => [
            'key_from' => 'whitelabel_id',
            'model_to' => Whitelabel::class,
            'key_to' => 'id'
        ]
    ];
}
