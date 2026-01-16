<?php

namespace Models;

use Orm\HasMany;
use Orm\BelongsTo;
use Classes\Orm\AbstractOrmModel;

/**
 * @property int $id
 * @property int $token
 * @property int|null $whitelabel_transaction_id
 * @property int $multi_draw_id
 * @property int $whitelabel_id
 * @property int $whitelabel_user_id
 * @property int $lottery_id
 * @property int $lottery_type_id
 * @property int $currency_id
 * @property int $lottery_provider_id
 * @property int $status
 * @property int $prize_jackpot
 * @property int $prize_quickpick
 * @property int $model
 * @property int $income_type
 * @property int $tier
 * @property int $line_count
 *
 * @property bool $has_ticket_scan
 * @property bool $is_insured
 * @property bool $isLtechInsufficientBalance
 * @property bool $is_synchronized
 * @property bool $paid
 * @property bool $payout
 *
 * @property float $amount_local
 * @property float $amount
 * @property float $amount_usd
 * @property float $amount_payment
 * @property float $amount_manager
 * @property float $prize_payout_percent
 * @property float $prize_local
 * @property float $prize
 * @property float $prize_usd
 * @property float $prize_manager
 * @property float $prize_net_local
 * @property float $prize_net
 * @property float $prize_net_usd
 * @property float $prize_net_manager
 * @property float $cost_local
 * @property float $cost
 * @property float $cost_usd
 * @property float $cost_manager
 * @property float $income_local
 * @property float $income
 * @property float $income_usd
 * @property float $income_manager
 * @property float $income_value
 * @property float $margin_local
 * @property float $margin
 * @property float $margin_usd
 * @property float $margin_manager
 * @property float $margin_value
 * @property float $bonus_amount_local
 * @property float $bonus_amount_payment
 * @property float $bonus_amount_usd
 * @property float $bonus_amount
 * @property float $bonus_amount_manager
 * @property float $bonus_cost_local
 * @property float $bonus_cost
 * @property float $bonus_cost_usd
 * @property float $bonus_cost_manager
 *
 * @property string $ip
 * @property string $valid_to_draw
 * @property string $draw_date
 * @property string $date
 * @property string $date_processed
 *
 * @property BelongsTo|WhitelabelTransaction|null $whitelabelTransaction
 * @property BelongsTo|Lottery|null $lottery
 * @property BelongsTo|LotteryProvider|null $lottery_provider
 * @property BelongsTo|Whitelabel|null $whitelabel
 * @property BelongsTo|WhitelabelUser $whitelabelUser
 * @property HasMany|WhitelabelUserTicketSlip[]|null $whitelabel_user_ticket_slip
 * @property HasMany|WhitelabelUserTicketLine[] $whitelabelUserTicketLines
 * @property HasMany|LottorisqLog[]|null $lottorisq_logs
 * @property BelongsTo|Currency $currency
 */
class WhitelabelUserTicket extends AbstractOrmModel
{
    protected static $_table_name = 'whitelabel_user_ticket';

    protected static $_properties = [
        'id',
        'token',
        'whitelabel_transaction_id',
        'multi_draw_id',
        'whitelabel_id',
        'whitelabel_user_id',
        'lottery_id',
        'lottery_type_id',
        'currency_id',
        'lottery_provider_id',
        'valid_to_draw',
        'draw_date',
        'amount_local',
        'amount',
        'amount_usd',
        'amount_payment',
        'amount_manager',
        'date',
        'date_processed',
        'status',
        'is_synchronized',
        'paid',
        'payout',
        'prize_payout_percent',
        'prize_local',
        'prize',
        'prize_usd',
        'prize_manager',
        'prize_net_local',
        'prize_net',
        'prize_net_usd',
        'prize_net_manager',
        'prize_jackpot',
        'prize_quickpick',
        'model',
        'cost_local',
        'cost',
        'cost_usd',
        'cost_manager',
        'income_local',
        'income',
        'income_usd',
        'income_manager',
        'income_value',
        'income_type',
        'is_insured',
        'is_ltech_insufficient_balance' => ['default' => false],
        'tier',
        'margin_local',
        'margin',
        'margin_usd',
        'margin_manager',
        'margin_value',
        'bonus_amount_local',
        'bonus_amount_payment',
        'bonus_amount_usd',
        'bonus_amount',
        'bonus_amount_manager',
        'bonus_cost_local',
        'bonus_cost',
        'bonus_cost_usd',
        'bonus_cost_manager',
        'has_ticket_scan',
        'ip',
        'line_count'
    ];

    protected $casts = [
        'id'                    => self::CAST_INT,
        'token'                 => self::CAST_INT,
        'whitelabel_transaction_id' => self::CAST_INT,
        'multi_draw_id'         => self::CAST_INT,
        'whitelabel_id'         => self::CAST_INT,
        'whitelabel_user_id'    => self::CAST_INT,
        'lottery_id'            => self::CAST_INT,
        'lottery_type_id'       => self::CAST_INT,
        'currency_id'           => self::CAST_INT,
        'lottery_provider_id'   => self::CAST_INT,
        'status'                => self::CAST_INT,
        'prize_jackpot'         => self::CAST_INT,
        'prize_quickpick'       => self::CAST_INT,
        'model'                 => self::CAST_INT,
        'income_type'           => self::CAST_INT,
        'tier'                  => self::CAST_INT,
        'line_count'            => self::CAST_INT,

        'is_synchronized'       => self::CAST_BOOL,
        'is_insured'            => self::CAST_BOOL,
        'is_ltech_insufficient_balance' => self::CAST_BOOL,
        'has_ticket_scan'       => self::CAST_BOOL,
        'paid'                  => self::CAST_BOOL,
        'payout'                => self::CAST_BOOL,

        'amount_local'          => self::CAST_FLOAT,
        'amount'                => self::CAST_FLOAT,
        'amount_usd'            => self::CAST_FLOAT,
        'amount_payment'        => self::CAST_FLOAT,
        'amount_manager'        => self::CAST_FLOAT,
        'prize_payout_percent'  => self::CAST_FLOAT,
        'prize_local'           => self::CAST_FLOAT,
        'prize'                 => self::CAST_FLOAT,
        'prize_usd'             => self::CAST_FLOAT,
        'prize_manager'         => self::CAST_FLOAT,
        'prize_net_local'       => self::CAST_FLOAT,
        'prize_net'             => self::CAST_FLOAT,
        'prize_net_usd'         => self::CAST_FLOAT,
        'prize_net_manager'     => self::CAST_FLOAT,
        'cost_local'            => self::CAST_FLOAT,
        'cost'                  => self::CAST_FLOAT,
        'cost_usd'              => self::CAST_FLOAT,
        'cost_manager'          => self::CAST_FLOAT,
        'income_local'          => self::CAST_FLOAT,
        'income'                => self::CAST_FLOAT,
        'income_usd'            => self::CAST_FLOAT,
        'income_manager'        => self::CAST_FLOAT,
        'income_value'          => self::CAST_FLOAT,
        'margin_local'          => self::CAST_FLOAT,
        'margin'                => self::CAST_FLOAT,
        'margin_usd'            => self::CAST_FLOAT,
        'margin_value'          => self::CAST_FLOAT,
        'bonus_amount_local'    => self::CAST_FLOAT,
        'bonus_amount_payment'  => self::CAST_FLOAT,
        'bonus_amount_usd'      => self::CAST_FLOAT,
        'bonus_amount'          => self::CAST_FLOAT,
        'bonus_amount_manager'  => self::CAST_FLOAT,
        'bonus_cost_local'      => self::CAST_FLOAT,
        'bonus_cost'            => self::CAST_FLOAT,
        'bonus_cost_usd'        => self::CAST_FLOAT,
        'bonus_cost_manager'    => self::CAST_FLOAT,
    ];

    protected array $relations = [
        WhitelabelTransaction::class => self::BELONGS_TO,
        WhitelabelUser::class => self::BELONGS_TO,
    ];

    protected static array $_belongs_to = [
        'whitelabel' => [
            'key_from' => 'whitelabel_id',
            'model_to' => Whitelabel::class,
            'key_to' => 'id'
        ],
        'lottery' => [
            'key_from' => 'lottery_id',
            'model_to' => Lottery::class,
            'key_to' => 'id'
        ],
        'lottery_provider' => [
            'key_from' => 'lottery_provider_id',
            'model_to' => LotteryProvider::class,
            'key_to' => 'id'
        ],
        'currency' => [
            'key_from' => 'currency_id',
            'model_to' => Currency::class,
            'key_to' => 'id'
        ]
    ];

    protected static array $_has_many = [
        'whitelabel_user_ticket_lines' => [
            'key_from' => 'id',
            'model_to' => WhitelabelUserTicketLine::class,
            'key_to' => 'whitelabel_user_ticket_id'
        ],
        'lottorisq_logs' => [
            'key_from' => 'id',
            'model_to' => LottorisqLog::class,
            'key_to' => 'whitelabel_user_ticket_id'
        ],
        'whitelabel_user_ticket_slip' => [
            'key_from' => 'id',
            'model_to' => WhitelabelUserTicketSlip::class,
            'key_to' => 'whitelabel_user_ticket_id'
        ]
    ];

    protected static array $_has_one = [];

    public function isPurchaseAndScanModel(): bool
    {
        return $this->model === 2;
    }

    public function isTicketFromDoubleJack(): bool
    {
        return $this->whitelabel->isTheme(Whitelabel::DOUBLEJACK_THEME);
    }

    public function isNotTicketFromDoubleJack(): bool
    {
        return !$this->isTicketFromDoubleJack();
    }

}
