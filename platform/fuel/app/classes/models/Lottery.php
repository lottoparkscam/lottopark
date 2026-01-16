<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Carbon\Carbon;
use Helpers_Lottery;
use Orm\BelongsTo;
use Classes\Orm\Criteria\With\Model_Orm_Criteria_With_Relation;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Orm\HasMany;
use Orm\HasOne;

/**
 * @property int $id
 * @property int $sourceId
 * @property string $name
 * @property string $type
 * @property string $shortname
 * @property string $country
 * @property string $countryIso
 * @property string $slug
 * @property bool $isEnabled
 * @property bool $playable - user cannot play lottery, only result and information page is active (for seo)
 * @property bool $isTemporarilyDisabled
 * @property string $timezone
 * @property array $drawDates
 * @property float $currentJackpot
 * @property float $currentJackpotUsd
 * @property float $drawJackpotSet
 * @property int $currencyId
 * @property Carbon $lastDateLocal
 * @property Carbon $nextDateLocal
 * @property Carbon $nextDateUtc
 * @property string $lastNumbers
 * @property string $lastBnumbers
 * @property float $lastTotalPrize
 * @property float $lastTotalWinners
 * @property float $lastJackpotPrize
 * @property Carbon $lastUpdate
 * @property float $price
 * @property bool $estimatedUpdated
 * @property bool $scansEnabled
 * @property string $additionalData
 * @property bool $isMultidrawEnabled
 * @property int $forceCurrencyId
 *
 * @property BelongsTo|Currency $currency
 * @property HasMany|LotteryProvider[]|null $lottery_providers
 * @property HasOne|LotteryType|null $lotteryType
 * @property HasMany|WhitelabelUserTicket[]|null $whitelabel_user_tickets
 * @property HasMany|LotteryLog[] $lottery_logs
 * @property HasMany|WhitelabelLottery[] $whitelabel_lotteries
 * @property HasMany|LotteryDelay[] $lottery_delays
 */
class Lottery extends AbstractOrmModel
{
    public const MEGA_MILLIONS_SLUG = 'mega-millions';
    public const EUROMILLIONS_SLUG = 'euromillions';
    public const SUPERENA_SLUG = 'superenalotto';
    public const LOTTO_6AUS49_SLUG = 'lotto-6aus49';
    public const LOTTO_AUSTRIA_SLUG = 'lotto-at';
    public const QUINA_SLUG = 'quina';
    public const MEGA_SENA_SLUG = 'mega-sena';
    public const POLISH_KENO_SLUG = 'polish-keno';
    public const GREEK_KENO_SLUG = 'greek-keno';
    public const CZECH_KENO_SLUG = 'czech-keno';
    public const SLOVAK_KENO_SLUG = 'slovak-keno';
    public const LATVIAN_KENO_SLUG = 'latvian-keno';
    public const FINNISH_KENO_SLUG = 'finnish-keno';
    public const FRENCH_KENO_SLUG = 'french-keno';
    public const EURODREAMS_SLUG = 'eurodreams';
    public const HUNGARIAN_KENO_SLUG = 'hungarian-keno';
    public const ITALIAN_KENO_SLUG = 'italian-keno';
    public const WEEKDAY_WINDFALL_SLUG = 'weekday-windfall';
    public const BONOLOTO_SLUG = 'bonoloto';
    public const LA_PRIMITIVA_SLUG = 'la-primitiva';
    public const SLOVAK_KENO_10_SLUG = 'slovak-keno-10';
    public const GERMAN_KENO_SLUG = 'german-keno';
    public const UKRAINIAN_KENO_SLUG = 'ukrainian-keno';
    public const BELGIAN_KENO_SLUG = 'belgian-keno';
    public const KENO_NEW_YORK_SLUG = 'keno-ny';
    public const EUROMILLIONS_SUPERDRAW_SLUG = 'euromillions-superdraw';
    public const LOTO_6_49_SLUG = 'loto-6-49';
    public const MINI_POWERBALL_SLUG = 'mini-powerball';
    public const BRAZILIAN_KENO_SLUG = 'brazilian-keno';
    public const SWEDISH_KENO_SLUG = 'swedish-keno';
    public const AUSTRALIAN_KENO_SLUG = 'australian-keno';
    public const DANISH_KENO_SLUG = 'danish-keno';
    public const NORWEGIAN_KENO_SLUG = 'norwegian-keno';
    public const LITHUANIAN_KENO_SLUG = 'lithuanian-keno';
    public const CROATIAN_KENO_SLUG = 'croatian-keno';
    public const BELARUSIAN_KENO_SLUG = 'belarusian-keno';
    public const ESTONIAN_KENO_SLUG = 'estonian-keno';
    public const CANADIAN_KENO_SLUG = 'canadian-keno';
    public const MINI_MEGA_MILLIONS_SLUG = 'mini-mega-millions';
    public const MINI_EUROMILLIONS_SLUG = 'mini-euromillions';
    public const MINI_EUROJACKPOT_SLUG = 'mini-eurojackpot';
    public const MINI_SUPERENALOTTO_SLUG = 'mini-superenalotto';

    protected static string $_table_name = 'lottery';

    protected static array $_properties = [
        'id',
        'source_id',
        'name',
        'type' => ['default' => 'lottery'],
        'shortname',
        'country',
        'country_iso',
        'slug',
        'is_enabled' => ['default' => false],
        'playable' => ['default' => true],
        'is_temporarily_disabled' => ['default' => false],
        'timezone',
        'draw_dates',
        'current_jackpot',
        'current_jackpot_usd',
        'draw_jackpot_set',
        'currency_id',
        'last_date_local',
        'next_date_local',
        'next_date_utc',
        'last_numbers',
        'last_bnumbers',
        'last_total_prize',
        'last_total_winners',
        'last_jackpot_prize',
        'last_update',
        'price',
        'estimated_updated' => ['default' => true],
        'scans_enabled' => ['default' => false],
        'additional_data',
        'is_multidraw_enabled' => ['default' => false],
        'force_currency_id'
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'source_id' => self::CAST_INT,
        'name' => self::CAST_STRING,
        'type' => self::CAST_STRING,
        'shortname' => self::CAST_STRING,
        'country' => self::CAST_STRING,
        'country_iso' => self::CAST_STRING,
        'slug' => self::CAST_STRING,
        'is_enabled' => self::CAST_BOOL,
        'playable' => self::CAST_BOOL,
        'is_temporarily_disabled' => self::CAST_BOOL,
        'timezone' => self::CAST_STRING,
        'draw_dates' => self::CAST_ARRAY,
        'current_jackpot' => self::CAST_FLOAT,
        'current_jackpot_usd' => self::CAST_FLOAT,
        'draw_jackpot_set' => self::CAST_BOOL,
        'currency_id' => self::CAST_INT,
        'last_date_local' => self::CAST_CARBON,
        'next_date_local' => self::CAST_CARBON,
        'next_date_utc' => self::CAST_CARBON,
        'last_numbers' => self::CAST_STRING,
        'last_bnumbers' => self::CAST_STRING,
        'last_total_prize' => self::CAST_FLOAT,
        'last_total_winners' => self::CAST_FLOAT,
        'last_jackpot_prize' => self::CAST_FLOAT,
        'last_update' => self::CAST_CARBON,
        'price' => self::CAST_FLOAT,
        'estimated_updated' => self::CAST_BOOL,
        'scans_enabled' => self::CAST_BOOL,
        'additional_data' => self::CAST_STRING,
        'is_multidraw_enabled' => self::CAST_BOOL,
        'force_currency_id' => self::CAST_INT
    ];

    protected array $relations = [
        Currency::class => self::BELONGS_TO,
        LotteryLog::class => self::HAS_MANY,
        WhitelabelLottery::class => self::HAS_MANY,
        WhitelabelUserTicket::class => self::HAS_MANY,
        LotteryProvider::class => self::HAS_MANY,
        LotteryType::class => self::HAS_ONE,
        LotteryDelay::class => self::HAS_MANY,
    ];

    protected array $timezones = [
        'last_date_local' => ['timezone'],
        'next_date_local' => ['timezone'],
        'next_date_utc' => 'UTC',
        'last_update' => 'UTC',
    ];

    // It is very important! Do not remove these variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];

    public function get_by_slug(string $slug, array $relations = []): self
    {
        $this->push_criteria(new Model_Orm_Criteria_Where('slug', $slug));
        foreach ($relations as $relation) {
            $this->push_criteria(new Model_Orm_Criteria_With_Relation($relation));
        }
        return $this->get_one();
    }

    public function isKeno(): bool
    {
        return $this->type === Helpers_Lottery::TYPE_KENO;
    }

    public function isNotKeno(): bool
    {
        return !$this->isKeno();
    }

    public static function hasManyDrawsPerDay(array $drawDates): bool
    {
        $onlyWeekDays = array_map(fn($drawDate) => substr($drawDate, 0, 3), $drawDates);
        $uniqueWeekDays = array_unique($onlyWeekDays);
        return count($onlyWeekDays) !== count($uniqueWeekDays);
    }
}
