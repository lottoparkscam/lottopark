<?php

namespace Models;

use Orm\BelongsTo;
use Orm\HasMany;
use Classes\Orm\AbstractOrmModel;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;

/**
 * @property int $id
 * @property string $code
 * @property float $rate
 * @property HasMany|Lottery[] $lotteries
 * @property HasMany|WhitelabelPaymentMethodCurrency[] $whitelabelPaymentMethodCurrencies
 * @property BelongsTo|WhitelabelUserTicket $whitelabelUserTicket
 */
class Currency extends AbstractOrmModel
{
    protected static $_table_name = 'currency';

    protected static $_properties = [
        'id',
        'code',
        'rate'
    ];

    protected $casts = [
        'id'   => self::CAST_INT,
        'code'   => self::CAST_STRING,
        'rate' => self::CAST_FLOAT,
    ];

    public function get_by_code(string $code): static
    {
        return $this->push_criteria(
            new Model_Orm_Criteria_Where('code', $code)
        )->get_one();
    }

    protected array $relations = [
        Lottery::class => self::HAS_MANY,
        WhitelabelPaymentMethodCurrency::class => self::HAS_MANY,
        WhitelabelUserTicket::class => self::BELONGS_TO,
    ];

    protected array $timezones = [];

    // It is very important! Do not remove these variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}

