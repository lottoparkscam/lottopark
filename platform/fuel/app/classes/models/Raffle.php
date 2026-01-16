<?php

namespace Models;

use DateTime;
use Exception;
use Orm\HasOne;
use Orm\HasMany;
use DateTimeZone;
use Orm\BelongsTo;
use Fuel\Core\Date;
use Classes\Orm\AbstractOrmModel;
use Orm\RecordNotFound;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Classes\Orm\Criteria\With\Model_Orm_Criteria_With_Relation;
use Helper_Raffle;

/**
 * @property int $id
 * @property int $raffle_rule_id
 * @property int $currency_id
 * @property string $name
 * @property string $country
 * @property string $country_iso
 * @property string $slug
 * @property bool $is_enabled
 * @property string $timezone
 * @property float $main_prize
 * @property Date|null $last_draw_date
 * @property Date|null $last_draw_date_utc
 * @property Date|null $next_draw_date
 * @property Date|null $next_draw_date_utc
 * @property float $last_prize_total
 * @property int $draw_lines_count
 * @property int $last_ticket_count
 * @property bool $is_sell_enabled
 * @property bool $is_sell_limitation_enabled
 * @property array $sell_open_dates
 *
 * @property float $prizes_sum
 *
 * @property int $min_bets
 * @property int $max_bets
 * @property int $is_turned_on
 *
 * @property array|DateTime[] $sell_open_dates_objects
 * @property bool $is_sell_temporary_disabled
 *
 * @property HasMany|RaffleLog[] $raffle_logs
 * @property HasMany|RaffleRule[] $rules
 * @property BelongsTo|Currency $currency
 * @property HasOne|WhitelabelRaffle $whitelabel_raffle
 * @property HasOne|RaffleProvider $raffle_provider
 */
class Raffle extends AbstractOrmModel
{
    public const FAIREUM_RAFFLE_SLUG = 'faireum-raffle';
    public const LOTTERYKING_RAFFLE_SLUG = 'lottery-king-raffle';
    public const MONKS_RAFFLE_SLUG = 'monks-raffle';
    public const GG_WORLD_WELCOME_RAFFLE_SLUG = 'gg-world-welcome-raffle';
    public const COSMIC_FATE_RAFFLE_SLUG = 'cosmic-fate-raffle';
    public const FORTUNE_FATE_RAFFLE_SLUG = 'fortune-fate-raffle';
    public const MYSTIC_FATE_RAFFLE_SLUG = 'mystic-fate-raffle';
    public const GG_WORLD_SILVER_RAFFLE_SLUG = 'gg-world-silver-raffle';
    public const GG_WORLD_GOLD_RAFFLE_SLUG = 'gg-world-gold-raffle';
    public const GG_WORLD_PLATINUM_RAFFLE_SLUG = 'gg-world-platinum-raffle';

    protected static $_table_name = 'raffle';

    protected static $_properties = [
        'id',
        'raffle_rule_id',
        'currency_id',
        'name',
        'country',
        'country_iso',
        'slug',
        'is_enabled',
        'timezone',
        'main_prize',
        'last_draw_date',
        'last_draw_date_utc',
        'next_draw_date',
        'next_draw_date_utc',
        'last_prize_total',
        'draw_lines_count',
        'last_ticket_count',
        'is_sell_enabled'            => ['default' => true],
        'is_sell_limitation_enabled' => ['default' => false],
        'sell_open_dates'            => ['default' => []],
    ];

    protected $casts = [
        'id'                 => self::CAST_INT,
        'raffle_rule_id'     => self::CAST_INT,
        'currency_id'        => self::CAST_INT,
        'is_enabled'         => self::CAST_BOOL,
        'main_prize'         => self::CAST_FLOAT,
        'last_draw_date'     => self::CAST_DATETIME,
        'last_draw_date_utc' => self::CAST_DATETIME,
        'next_draw_date'     => self::CAST_DATETIME,
        'next_draw_date_utc' => self::CAST_DATETIME,
        'last_prize_total'   => self::CAST_FLOAT,
        'draw_lines_count'   => self::CAST_INT,
        'last_ticket_count'  => self::CAST_INT,
        'is_sell_enabled'            => self::CAST_BOOL,
        'is_sell_limitation_enabled' => self::CAST_BOOL,
        'sell_open_dates'            => self::CAST_ARRAY
    ];

    protected static array $_belongs_to = [
        'currency' => [
            'key_from' => 'currency_id',
            'model_to' => Currency::class,
            'key_to' => 'id',
        ],
    ];

    protected static array $_has_one = [
        'whitelabel_raffle' => [
            'key_from' => 'id',
            'model_to' => WhitelabelRaffle::class,
            'key_to' => 'raffle_id',
        ],
    ];

    protected static array $_has_many = [
        'rules' => [
            'key_from' => 'id',
            'model_to' => RaffleRule::class,
            'key_to' => 'raffle_id',
        ],
    ];

    protected array $relations = [
        RaffleLog::class => self::HAS_MANY,
        RaffleProvider::class => self::HAS_ONE,
    ];

    /**
     * @throws RecordNotFound
     */
    public function get_by_slug_with_currency_and_rule(string $slug): Raffle
    {
        return $this->push_criterias([
            new Model_Orm_Criteria_With_Relation('rules.currency'),
            new Model_Orm_Criteria_With_Relation('rules.tiers'),
            new Model_Orm_Criteria_With_Relation('rules.tiers.tier_prize_in_kind'),
            new Model_Orm_Criteria_With_Relation('rules.tiers.tier_prize'),
            new Model_Orm_Criteria_With_Relation('currency'),
            new Model_Orm_Criteria_With_Relation('whitelabel_raffle.provider'),
            new Model_Orm_Criteria_Where('slug', $slug),
        ])->get_one();
    }

    public function get_max_bets_attribute(): int
    {
        return $this->whitelabel_raffle->provider->max_bets;
    }

    public function get_min_bets_attribute(): int
    {
        return $this->whitelabel_raffle->provider->min_bets;
    }

    public function get_is_turned_on_attribute(): int
    {
        return $this->is_enabled && $this->whitelabel_raffle->isEnabled && !$this->is_sell_temporary_disabled;
    }

    public function get_prizes_sum_attribute(): float
    {
        $sums = array_map(function (RaffleRuleTier $tier) {
            $winners_count = Helper_Raffle::tier_matches_to_winners($tier->matches);
            return $winners_count * $tier->prize_amount;
        }, $this->getFirstRule()->tiers);

        return array_sum($sums);
    }

    /**
     * @return array|self[]
     */
    public function get_temporary_disabled(): array
    {
        return $this->push_criterias([
            new Model_Orm_Criteria_Where('is_sell_enabled', false),
            new Model_Orm_Criteria_Where('is_sell_limitation_enabled', true),
        ])->get_results();
    }

    /**
     * Maps sell open dates to DateTime and re-order (asc).
     * @return array
     */
    public function get_sell_open_dates_objects_attribute(): array
    {
        $dates = $this->sell_open_dates;

        if (empty($dates)) {
            return [];
        }

        $dates = array_map(function (string $stringDate) {
            $date = new DateTime($stringDate, new DateTimeZone($this->timezone));
            return $date->format('Y-m-d H:i');
        }, $dates);

        asort($dates);

        $dates = array_map(function (string $stringDate) {
            return new DateTime($stringDate, new DateTimeZone($this->timezone));
        }, $dates);

        return array_values($dates);
    }

    public function get_is_sell_temporary_disabled_attribute(): bool
    {
        return $this->is_sell_limitation_enabled && !$this->is_sell_enabled;
    }

    public function enableSellIfNotTemporaryDisabled(): void
    {
        if ($this->is_sell_limitation_enabled) {
            return;
        }
        $this->is_sell_enabled = true;
    }

    /**
     * @return RaffleRule
     * @throws Exception
     * @deprecated - test and refactor after Fixtures release.
     *
     * Temporary work around to fetch first rule.
     * By mistake here was a relation one > one, when it should be > many.
     * In code was assumed usage of one > one relation, so to reduce "hard refactor" without
     * feature tests - this solution was implemented.
     *
     * Array values is required due Fuel maps id's as key value.
     */
    public function getFirstRule(): RaffleRule
    {
        if (empty($this->rules)) {
            throw new Exception('Logical error, Raffle has no related rules');
        }
        return array_values($this->rules)[0];
    }
}
