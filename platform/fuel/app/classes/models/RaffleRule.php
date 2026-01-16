<?php

namespace Models;

use Orm\HasOne;
use Orm\HasMany;
use Orm\BelongsTo;
use Classes\Orm\AbstractOrmModel;

/**
 * @property int $id
 * @property int $raffle_id
 * @property int $currency_id
 * @property float $line_price
 * @property float $fee
 * @property int $max_lines_per_draw
 * @property array $ranges
 *
 * @property float $line_price_with_fee
 * @property bool $has_prize_in_kind
 *
 * @property BelongsTo|Currency $currency
 * @property BelongsTo|Raffle $raffle
 * @property HasOne|RaffleDraw $draw
 * @property HasMany|RaffleRuleTier[] $tiers
 */
class RaffleRule extends AbstractOrmModel
{
    protected static $_table_name = 'raffle_rule';

    protected static $_properties = [
        'id',
        'raffle_id',
        'currency_id',
        'line_price',
        'fee',
        'max_lines_per_draw',
        'ranges',
    ];

    protected static array $_has_many = [
        'tiers' => [
            'key_from' => 'id',
            'model_to' => RaffleRuleTier::class,
            'key_to'   => 'raffle_rule_id',
        ],
    ];

    protected static array $_belongs_to = [
        'raffle'   => [
            'key_from' => 'raffle_id',
            'model_to' => Raffle::class,
            'key_to'   => 'id',
        ],
        'currency' => [
            'key_from' => 'currency_id',
            'model_to' => Currency::class,
            'key_to'   => 'id',
        ],
    ];

    protected static array $_has_one = [
        'draw' => [
            'key_from' => 'id',
            'model_to' => RaffleDraw::class,
            'key_to'   => 'raffle_rule_id',
        ],
    ];

    protected $casts = [
        'id'                         => self::CAST_INT,
        'raffle_id'                  => self::CAST_INT,
        'currency_id'                => self::CAST_INT,
        'line_price'                 => self::CAST_FLOAT,
        'fee'                        => self::CAST_FLOAT,
        'max_lines_per_draw'         => self::CAST_INT,
        'ranges'                     => self::CAST_ARRAY,
    ];

    public function get_line_price_with_fee_attribute(): float
    {
        return $this->line_price + $this->fee;
    }

    public function get_has_prize_in_kind_attribute(): bool
    {
        foreach ($this->tiers as $tier) {
            if (!empty($tier->tier_prize_in_kind)) {
                return true;
            }
        }
        return false;
    }
}
