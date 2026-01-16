<?php

namespace Models;

use Orm\HasMany;
use Orm\BelongsTo;
use Classes\Orm\AbstractOrmModel;
use Classes\Orm\Criteria\With\Model_Orm_Criteria_With_Relation;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Classes\Orm\Criteria\Model_Orm_Criteria_Order;
use Helpers_General;

/**
 * @property int $id
 * @property int $raffle_draw_id
 * @property int $raffle_rule_id
 * @property int $raffle_rule_tier_id
 * @property int $currency_id
 * @property int $lines_won_count
 * @property float $total
 * @property float $per_user
 *
 * @property bool $prize_amount
 *
 * @property BelongsTo|RaffleDraw $draw
 * @property BelongsTo|RaffleRule $rule
 * @property BelongsTo|RaffleRuleTier $tier
 * @property BelongsTo|Currency $currency
 * @property HasMany|WhitelabelRaffleTicketLine[] $lines
 */
class RafflePrize extends AbstractOrmModel
{
    protected static $_table_name = 'raffle_prize';

    protected static $_properties = [
        'id',
        'raffle_draw_id',
        'raffle_rule_id',
        'raffle_rule_tier_id',
        'currency_id',
        'lines_won_count' => ['default' => 0],
        'total' => ['default' => 0],
        'per_user' => ['default' => 0]
    ];

    protected $casts = [
        'id'                  => self::CAST_INT,
        'raffle_draw_id'      => self::CAST_INT,
        'raffle_rule_id'      => self::CAST_INT,
        'raffle_rule_tier_id' => self::CAST_INT,
        'currency_id'         => self::CAST_INT,
        'lines_won_count'     => self::CAST_INT,
        'total'               => self::CAST_FLOAT,
        'per_user'            => self::CAST_FLOAT
    ];

    protected static array $_belongs_to = [
        'draw' => [
            'key_from' => 'raffle_draw_id',
            'model_to' => RaffleDraw::class,
            'key_to' => 'id',
        ],
        'rule' => [
            'key_from' => 'raffle_rule_id',
            'model_to' => RaffleRule::class,
            'key_to' => 'id',
        ],
        'tier' => [
            'key_from' => 'raffle_rule_tier_id',
            'model_to' => RaffleRuleTier::class,
            'key_to' => 'id',
        ],
        'currency' => [
            'key_from' => 'currency_id',
            'model_to' => Currency::class,
            'key_to' => 'id',
        ],
    ];

    protected static array $_has_many = [
        'lines' => [
            'key_from' => 'id',
            'model_to' => WhitelabelRaffleTicketLine::class,
            'key_to' => 'raffle_prize_id',
        ],
    ];

    public function get_prize_by_tier_slug(int $draw_id, string $tier_slug, int $raffle_rule_id): self
    {
        return $this->push_criterias([
            new Model_Orm_Criteria_With_Relation('tier.tier_prize_in_kind'),
            new Model_Orm_Criteria_Where('tier.slug', $tier_slug),
            new Model_Orm_Criteria_Where('raffle_rule_id', $raffle_rule_id),
            new Model_Orm_Criteria_Where('raffle_draw_id', $draw_id)
        ])->get_one();
    }

    public function limit_to_tier_main_prize(): self
    {
        return $this->push_criteria(new Model_Orm_Criteria_Where('tier.is_main_prize', true));
    }

    /**
     * Calculates prize value depending is it prize in kind or regular prize in cash.
     *
     * @return float
     */
    public function get_prize_amount_attribute(): float
    {
        return !empty($this->tier->tier_prize_in_kind) ? $this->tier->tier_prize_in_kind->per_user : $this->per_user;
    }

    /**
     * @param int $draw_id
     *
     * @return array|self[]
     */
    public function get_prizes_by_draw(int $draw_id): array
    {
        return $this->push_criterias([
            new Model_Orm_Criteria_With_Relation('tier'),
            new Model_Orm_Criteria_With_Relation('lines'),
            new Model_Orm_Criteria_With_Relation('currency'),

            new Model_Orm_Criteria_Where('lines.status', Helpers_General::TICKET_STATUS_WIN),
            new Model_Orm_Criteria_Where('raffle_draw_id', $draw_id),
            new Model_Orm_Criteria_Order('per_user', 'desc')
        ])->get_results();
    }
}
