<?php

namespace Models;

use Modules\Account\Reward\PrizeType;
use Orm\HasOne;
use Orm\BelongsTo;
use Webmozart\Assert\Assert;
use InvalidArgumentException;
use Classes\Orm\AbstractOrmModel;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Classes\Orm\Criteria\With\Model_Orm_Criteria_With_Relation;

/**
 * @property int $id
 * @property int $raffle_rule_id
 * @property int $currency_id
 * @property string $slug
 * @property array $matches
 * @property float|null $odds
 * @property float $prize
 * @property float $prize_fund_percent
 * @property bool $is_main_prize
 * @property int $prize_type
 * @property int|null $lottery_rule_tier_in_kind_prize_id
 *
 * @property float $prize_amount
 * @property int $winners_count
 *
 * @property BelongsTo|RaffleRule $rule
 * @property BelongsTo|Currency $currency
 * @property BelongsTo|RaffleRuleTierInKindPrize $tier_prize_in_kind
 * @property HasOne|RafflePrize|null $tier_prize
 */
class RaffleRuleTier extends AbstractOrmModel
{
    protected static $_table_name = 'raffle_rule_tier';

    protected static $_properties = [
        'id',
        'raffle_rule_id',
        'currency_id',
        'slug',
        'matches',
        'prize_type',
        'lottery_rule_tier_in_kind_prize_id',
        'prize_fund_percent',
        'odds',
        'prize',
        'is_main_prize'
    ];

    protected static array $_belongs_to = [
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
        'tier_prize_in_kind' => [
            'key_from' => 'lottery_rule_tier_in_kind_prize_id',
            'model_to' => RaffleRuleTierInKindPrize::class,
            'key_to' => 'id',
        ],
    ];

    protected static array $_has_one = [
        'tier_prize' => [
            'key_from' => 'id',
            'model_to' => RafflePrize::class,
            'key_to' => 'raffle_rule_tier_id',
        ],
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'raffle_rule_id' => self::CAST_INT,
        'currency_id' => self::CAST_INT,
        'lottery_rule_tier_in_kind_prize_id' => self::CAST_INT,
        'matches' => self::CAST_ARRAY,
        'odds' => self::CAST_ARRAY,
        'prize' => self::CAST_FLOAT,
        'prize_fund_percent' => self::CAST_FLOAT,
        'is_main_prize' => self::CAST_BOOL,
        'prize_type' => self::CAST_INT,
    ];

    protected function create_base_query(): void
    {
        parent::create_base_query();
        $this->push_criterias([
            new Model_Orm_Criteria_With_Relation('rule.draw'),
        ]);
    }

    /**
     * @param string $slug
     *
     * @return self
     */
    public function get_by_slug(string $slug): self
    {
        return $this->push_criteria(new Model_Orm_Criteria_Where('slug', $slug))->get_one();
    }

    /**
     * Calculates prize value depending is it prize in kind or regular prize in cash.
     *
     * @return float
     */
    public function get_prize_amount_attribute(): float
    {
        return !empty($this->tier_prize_in_kind) ? $this->tier_prize_in_kind->per_user : $this->prize;
    }

    /**
     * Finds expected winners count for tier.
     * Atm only raffle-closed format supported.
     *
     * @return int
     * @throws InvalidArgumentException
     */
    public function get_winners_count_attribute(): int
    {
        $error_message = 'Unsupported matches format.';

        Assert::notEmpty($this->matches, 'Matches not provided.');
        Assert::count($this->matches, 1, 'Matches must be in one range only (raffle)');

        if (is_numeric($this->matches[0])) {
            return $this->matches[0];
        }

        [$from, $to] = $this->matches[0];
        Assert::numeric($from, $error_message);
        Assert::numeric($to, $error_message);
        return ($to - $from) + 1;
    }

    public function isPrizeInKind(): bool
    {
        return $this->tier_prize_in_kind !== null;
    }

    public function isPrizeInTickets(): bool
    {
        return $this->isPrizeInKind() && $this->tier_prize_in_kind->type === PrizeType::TICKET;
    }
}
