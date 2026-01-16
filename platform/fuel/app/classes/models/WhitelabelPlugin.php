<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Exceptions\PrimeadsSecureUrlParameterNotExistsException;
use Orm\BelongsTo;
use Orm\HasMany;

/**
 *
 * @property int $id
 * @property int $whitelabel_id
 * @property string $plugin
 * @property bool $is_enabled
 * @property array $options
 *
 * @property BelongsTo|Whitelabel $whitelabel
 * @property HasMany|WhitelabelPluginUser $whitelabelPluginUser
 * @property HasMany|WhitelabelPluginLog $whitelabelPluginLog
 */
class WhitelabelPlugin extends AbstractOrmModel
{
    public const MAUTIC_API_NAME = 'mautic-api';

    public const PRIMEADS_NAME = 'primeads';
    public const TAG_MARKETING_NAME = 'tag-marketing';
    public const DIGITAL_HUB_NAME = 'digital-hub';
    public const TIBOLARIO_NAME = 'tibolario';
    public const LOUDING_ADS_NAME = 'louding-ads';
    public const TAGD_NAME = 'tagd';

    protected static string $_table_name = 'whitelabel_plugin';

    protected static array $_properties = [
        'id',
        'whitelabel_id',
        'plugin',
        'is_enabled',
        'options',
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'whitelabel_id' => self::CAST_INT,
        'is_enabled' => self::CAST_BOOL,
        'options' => self::CAST_ARRAY,
    ];

    protected array $relations = [
        Whitelabel::class => self::BELONGS_TO,
        WhitelabelPluginUser::class => self::HAS_MANY,
        WhitelabelPluginLog::class => self::HAS_MANY,
    ];

    protected array $timezones = [];

    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];

    public function isEnabled(): bool
    {
        return $this->is_enabled;
    }

    public function isNotEnabled(): bool
    {
        return !$this->isEnabled();
    }

    /**
     * @throws PrimeadsSecureUrlParameterNotExistsException
     */
    public function findPrimeadsSecureUrlParameter(): string
    {
        return $this->options['secureUrlParameter'] ?? throw new PrimeadsSecureUrlParameterNotExistsException();
    }
}
