<?php

namespace Models;

use Orm\HasMany;
use Orm\BelongsTo;
use Fuel\Core\Date;
use Helpers_General;
use Classes\Orm\AbstractOrmModel;
use Modules\Account\Reward\PrizeType;
use Classes\Orm\Criteria\Model_Orm_Criteria_Order;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Classes\Orm\Criteria\With\Model_Orm_Criteria_With_Relation;

/**
 * @property int $id
 * @property int $whitelabel_id
 * @property int $whitelabel_user_id
 * @property int $whitelabel_transaction_id
 * @property int $raffle_id
 * @property int $raffle_rule_id
 * @property int|null $raffle_draw_id
 * @property int $currency_id
 * @property string $uuid
 * @property int $token
 * @property int $status
 * @property int $line_count
 * @property string $ip
 * @property string|null $ip_country_code
 * @property bool $is_paid_out
 * @property Date|null $draw_date
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
 * @property float $amount_manager - in whitelabel currency
 *
 * @property float $bonus_amount
 * @property float $bonus_amount_local
 * @property float $bonus_amount_usd
 * @property float $bonus_amount_payment
 * @property float $bonus_amount_manager - in whitelabel currency
 *
 * @property float $cost_local
 * @property float $cost_usd
 * @property float $cost
 * @property float $cost_manager
 *
 * @property float $margin_value - integer value irl, for example 10 = 10 %
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
 * @property string $prefixed_token
 *
 * @property Date|null $createdAt
 * @property Date|null $updated_at
 *
 * @property BelongsTo|Raffle $raffle
 * @property BelongsTo|RaffleDraw|null $draw
 * @property BelongsTo|Currency $currency
 * @property BelongsTo|RaffleRule $rule
 * @property BelongsTo|WhitelabelTransaction $transaction
 * @property BelongsTo|WhitelabelUser $user
 * @property BelongsTo|Whitelabel $whitelabel
 *
 * @property HasMany|WhitelabelRaffleTicketLine[]|null $lines
 *
 * @property bool $can_be_paid_out - if only cash prizes then yes
 */
class WhitelabelRaffleTicket extends AbstractOrmModel
{
    public const STATUS_WIN = Helpers_General::TICKET_STATUS_WIN;

    protected static $_table_name = 'whitelabel_raffle_ticket';

    protected static $_properties = [
        'id',
        'whitelabel_id',
        'whitelabel_user_id',
        'whitelabel_transaction_id',
        'raffle_id',
        'raffle_rule_id',
        'raffle_draw_id',
        'currency_id',
        'uuid',
        'token',
        'draw_date',
        'status' => ['default' => 0],
        'ip',
        'ip_country_code',
        'is_paid_out' => ['default' => false],
        'line_count' => ['default' => 0],

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
        
        'bonus_cost_local' => ['default' => 0.00],
        'bonus_cost_usd' => ['default' => 0.00],
        'bonus_cost_manager' => ['default' => 0.00],
        'bonus_cost' => ['default' => 0.00],

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

        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'whitelabel_id' => self::CAST_INT,
        'whitelabel_user_id' => self::CAST_INT,
        'whitelabel_transaction_id' => self::CAST_INT,
        'raffle_id' => self::CAST_INT,
        'raffle_draw_id' => self::CAST_INT,
        'raffle_rule_id' => self::CAST_INT,
        'token' => self::CAST_INT,
        'currency_id' => self::CAST_INT,
        'status' => self::CAST_INT,
        'line_count' => self::CAST_INT,

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
        
        'bonus_cost' => self::CAST_FLOAT,
        'bonus_cost_local' => self::CAST_FLOAT,
        'bonus_cost_usd' => self::CAST_FLOAT,
        'bonus_cost_manager' => self::CAST_FLOAT,

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

        'is_paid_out' => self::CAST_BOOL,

        'draw_date' => self::CAST_DATETIME,

        'created_at' => self::CAST_DATETIME,
        'updated_at' => self::CAST_DATETIME,
    ];

    public function isWin(): bool
    {
        return $this->status === self::STATUS_WIN;
    }

    public function isRaffleWithInKindPrize(): bool
    {
        $slug = $this->raffle->slug;
        return $slug === Raffle::FAIREUM_RAFFLE_SLUG || $slug === Raffle::LOTTERYKING_RAFFLE_SLUG || $slug === Raffle::MONKS_RAFFLE_SLUG;
    }

    public function isRaffleWithoutInKindPrize(): bool
    {
        return !$this->isRaffleWithInKindPrize();
    }

    /**
     * @param string $raffle_slug
     *
     * @return array|self[]
     */
    public function get_all_unsynchronized_tickets(string $raffle_slug): array
    {
        return $this->push_criterias([
            new Model_Orm_Criteria_With_Relation('raffle'),
            new Model_Orm_Criteria_With_Relation('lines.raffle_prize.tier.tier_prize_in_kind'),
            new Model_Orm_Criteria_With_Relation('currency'),
            new Model_Orm_Criteria_With_Relation('user.group'),

            new Model_Orm_Criteria_Where('raffle.slug', $raffle_slug),
            new Model_Orm_Criteria_Where('status', Helpers_General::TICKET_STATUS_PENDING),
            new Model_Orm_Criteria_Where('is_paid_out', false),
            new Model_Orm_Criteria_Where('raffle_draw_id', null),
            new Model_Orm_Criteria_Order('created_at', 'asc'),
        ])->get_results();
    }

    public function getByTokenAndUserId(int $token, int $whitelabelUserId = null, array $relations = []): self
    {
        if ($whitelabelUserId) {
            $this->push_criteria(new Model_Orm_Criteria_Where('whitelabel_user_id', $whitelabelUserId));
        }

        $this->push_criteria(new Model_Orm_Criteria_Where('token', $token));
        foreach ($relations as $relation) {
            $this->push_criteria(new Model_Orm_Criteria_With_Relation($relation));
        }

        return $this->get_one();
    }

    public function get_prefixed_token_attribute(): string
    {
        return sprintf('R%d', $this->token);
    }

    public function get_can_be_paid_out_attribute(): bool
    {
        foreach ($this->lines as $line) {
            if ($line->prizeType()->notEquals(PrizeType::CASH())) {
                return false;
            }
        }
        return true;
    }

    public function get_user_tickets_query(int $user_id): self
    {
        return $this->push_criteria(new Model_Orm_Criteria_Where('whitelabel_user_id', $user_id));
    }

    public function isNotFaireumRaffle(): bool
    {
        return $this->raffle->slug !== Raffle::FAIREUM_RAFFLE_SLUG;
    }

    protected static array $_belongs_to = [
        'raffle' => [
            'key_from' => 'raffle_id',
            'model_to' => Raffle::class,
            'key_to' => 'id',
        ],
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
        'currency' => [
            'key_from' => 'currency_id',
            'model_to' => Currency::class,
            'key_to' => 'id',
        ],
        'transaction' => [
            'key_from' => 'whitelabel_transaction_id',
            'model_to' => WhitelabelTransaction::class,
            'key_to' => 'id',
        ],
        'user' => [
            'key_from' => 'whitelabel_user_id',
            'model_to' => WhitelabelUser::class,
            'key_to' => 'id',
        ],
        'whitelabel' => [
            'key_from' => 'whitelabel_id',
            'model_to' => Whitelabel::class,
            'key_to' => 'id',
        ],
    ];

    protected static array $_has_many = [
        'lines' => [
            'key_from' => 'id',
            'model_to' => WhitelabelRaffleTicketLine::class,
            'key_to' => 'whitelabel_raffle_ticket_id',
        ],
    ];

    protected static array $_has_one = [];
}
