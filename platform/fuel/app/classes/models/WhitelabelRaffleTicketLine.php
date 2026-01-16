<?php

namespace Models;

use Carbon\Carbon;
use Modules\Account\Reward\DeterminesPrize;
use Modules\Account\Reward\PrizeType;
use Orm\BelongsTo;
use Classes\Orm\AbstractOrmModel;
use Classes\Orm\Criteria\With\Model_Orm_Criteria_With_Relation;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Helpers_General;
use Models\RafflePrize;
use Models\Whitelabel;

/**
 * @property int $id
 * @property int $whitelabel_id
 * @property int $whitelabel_raffle_ticket_id
 * @property int|null $raffle_prize_id
 * @property int $number
 * @property int $status
 *
 * @property float $prize
 * @property float $prize_local
 * @property float $prize_usd
 * @property float $prize_manager
 *
 * @property float $amount
 * @property float $amount_local
 * @property float $amount_usd
 * @property float $amount_payment
 * @property float $amount_manager
 *
 * @property float $bonus_amount
 * @property float $bonus_amount_local
 * @property float $bonus_amount_usd
 * @property float $bonus_amount_payment
 * @property float $bonus_amount_manager
 *
 * @property float $cost_local
 * @property float $cost_usd
 * @property float $cost
 * @property float $cost_manager
 *
 * @property float $margin_value
 * @property float $margin_local
 * @property float $margin_usd
 * @property float $margin_manager
 * @property float $margin
 *
 * @property float $income_local
 * @property float $income_usd
 * @property float $income
 * @property float $income_value
 * @property float $income_manager
 * @property bool $income_type
 *
 * @property int $has_item_prize
 *
 * @property Carbon $createdAt
 * @property Carbon $updatedAt
 *
 * @property BelongsTo|WhitelabelRaffleTicket $ticket
 * @property BelongsTo|Whitelabel $whitelabel
 * @property BelongsTo|RafflePrize|null $raffle_prize
 */
final class WhitelabelRaffleTicketLine extends AbstractOrmModel implements DeterminesPrize
{
    protected static $_table_name = 'whitelabel_raffle_ticket_line';

    protected static $_properties = [
        'id',
        'whitelabel_id',
        'whitelabel_raffle_ticket_id',
        'raffle_prize_id',
        'number',
        'status' => ['default' => 0],

        'prize' => ['default' => 0.00],
        'prize_local' => ['default' => 0.00],
        'prize_usd' => ['default' => 0.00],
        'prize_manager' => ['default' => 0.00],

        'amount' => ['default' => 0.00],
        'amount_local' => ['default' => 0.00],
        'amount_usd' => ['default' => 0.00],
        'amount_payment' => ['default' => 0.00],
        'amount_manager' => ['default' => 0.00],

        'bonus_amount' => ['default' => 0.00],
        'bonus_amount_local' => ['default' => 0.00],
        'bonus_amount_usd' => ['default' => 0.00],
        'bonus_amount_payment' => ['default' => 0.00],
        'bonus_amount_manager' => ['default' => 0.00],

        'cost_local' => ['default' => 0.00],
        'cost_usd' => ['default' => 0.00],
        'cost_manager' => ['default' => 0.00],
        'cost' => ['default' => 0.00],

        'margin_value' => ['default' => 0.00],
        'margin_local' => ['default' => 0.00],
        'margin_usd' => ['default' => 0.00],
        'margin' => ['default' => 0.00],
        'margin_manager' => ['default' => 0.00],

        'income_local' => ['default' => 0.00],
        'income_usd' => ['default' => 0.00],
        'income' => ['default' => 0.00],
        'income_value' => ['default' => 0.00],
        'income_manager' => ['default' => 0.00],
        'income_type' => ['default' => 0.00],
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'whitelabel_id' => self::CAST_INT,
        'whitelabel_raffle_ticket_id' => self::CAST_INT,
        'raffle_prize_id' => self::CAST_INT,
        'number' => self::CAST_INT,
        'status' => self::CAST_INT,

        'prize' => self::CAST_FLOAT,
        'prize_local' => self::CAST_FLOAT,
        'prize_usd' => self::CAST_FLOAT,
        'prize_manager' => self::CAST_FLOAT,

        'amount' => self::CAST_FLOAT,
        'amount_local' => self::CAST_FLOAT,
        'amount_usd' => self::CAST_FLOAT,
        'amount_payment' => self::CAST_FLOAT,
        'amount_manager' => self::CAST_FLOAT,

        'bonus_amount' => self::CAST_FLOAT,
        'bonus_amount_local' => self::CAST_FLOAT,
        'bonus_amount_usd' => self::CAST_FLOAT,
        'bonus_amount_payment' => self::CAST_FLOAT,
        'bonus_amount_manager' => self::CAST_FLOAT,

        'cost' => self::CAST_FLOAT,
        'cost_local' => self::CAST_FLOAT,
        'cost_usd' => self::CAST_FLOAT,
        'cost_manager' => self::CAST_FLOAT,

        'margin_value' => self::CAST_FLOAT,
        'margin_local' => self::CAST_FLOAT,
        'margin_usd' => self::CAST_FLOAT,
        'margin_manager' => self::CAST_FLOAT,
        'margin' => self::CAST_FLOAT,

        'income_local' => self::CAST_FLOAT,
        'income_usd' => self::CAST_FLOAT,
        'income' => self::CAST_FLOAT,
        'income_value' => self::CAST_FLOAT,
        'income_type' => self::CAST_INT,
        'income_manager' => self::CAST_FLOAT,
    ];

    protected static array $_belongs_to = [
        'ticket' => [
            'key_from' => 'whitelabel_raffle_ticket_id',
            'model_to' => WhitelabelRaffleTicket::class,
            'key_to' => 'id',
        ],
        'raffle_prize' => [
            'key_from' => 'raffle_prize_id',
            'model_to' => RafflePrize::class,
            'key_to' => 'id',
        ],
    ];

    protected static array $_has_many = [];
    protected static array $_has_one = [];

    protected array $relations = [
        Whitelabel::class => self::BELONGS_TO,
    ];

    /**
     * @param int $rule_id
     *
     * @return static[]
     */
    public function get_pending_by_rule(int $rule_id): array
    {
        return $this->push_criterias([
            new Model_Orm_Criteria_Where('status', Helpers_General::TICKET_STATUS_PENDING),
            new Model_Orm_Criteria_With_Relation('ticket'),
            new Model_Orm_Criteria_Where('ticket.raffle_rule_id', $rule_id),
        ])->get_results();
    }

    public function get_has_item_prize_attribute(): bool
    {
        return !empty($this->raffle_prize->tier->lottery_rule_tier_in_kind_prize_id);
    }

    /**
     * @param int $raffle_id
     * @return integer
     */
    public function get_all_unsynchronized_lines_count(int $raffle_id): int
    {
        return $this->push_criterias([
            new Model_Orm_Criteria_With_Relation('ticket.raffle'),

            new Model_Orm_Criteria_Where('ticket.raffle.id', $raffle_id),
            new Model_Orm_Criteria_Where('ticket.status', Helpers_General::TICKET_STATUS_PENDING),
            new Model_Orm_Criteria_Where('ticket.is_paid_out', false),
            new Model_Orm_Criteria_Where('ticket.raffle_draw_id', null),

            new Model_Orm_Criteria_Where('status', Helpers_General::TICKET_STATUS_PENDING),
        ])->getCount();
    }

    public function prizeType(): PrizeType
    {
        if (false === empty($this->raffle_prize->tier->tier_prize_in_kind)) {
            return PrizeType::createFrom($this->raffle_prize->tier->tier_prize_in_kind);
        }

        return PrizeType::CASH();
    }
}
