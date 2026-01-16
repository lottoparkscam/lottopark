<?php

namespace Models;

use Carbon\Carbon;
use Classes\Orm\AbstractOrmModel;
use Helpers_Time;
use Orm\BelongsTo;

/**
 * @property int $id
 * @property int $lotteryId
 *
 * Draw was processed, lottery_draw exists and prizes are awarded
 * @property bool $isProcessed
 *
 * Planned next draw date
 * @property Carbon $nextDrawDate
 *
 * The draw which these numbers are
 * @property Carbon $currentDrawDate
 * @property Carbon $currentDrawDateUtc
 *
 * @property array $normalNumbers
 * @property array $bonusNumbers
 *
 * The 'refund' number for lotteries: la-primitiva, bonoloto, el-gordo-la-primitiva
 * And 'super' number for lottery lotto-6aus49
 * @property array $additionalNumber
 *
 * It's a next draw jackpot
 * Full length number - not divided by 1 000 000
 * @property float $nextJackpot
 *
 * Prizes and winners slugs contain keys format: match-{normalNumbersCount}-{bonusNumbersCount}
 * When bonus numbers count does not exist in current tier, there should be only match-{normalNumbersCount}
 * There are many other specific formats for matches that are prepared by Ltech Manual Draw CRM page on frontend
 * @property array $prizes
 * @property array $winners
 *
 * @property int $currencyId
 * @property Carbon $createdAt
 * 
 * @property BelongsTo|Lottery $lottery
 * @property BelongsTo|Currency $currency
 */
class LtechManualDraw extends AbstractOrmModel
{
    public const LOTTERY_SLUGS_WITH_ADDITIONAL_NUMBERS = [
        'la-primitiva',
        'el-gordo-primitiva',
        'bonoloto',
        'lotto-6aus49',
    ] ;

    protected static string $_table_name = 'ltech_manual_draw';

    protected static array $_properties = [
        'id',
        'lottery_id',
        'is_processed' => ['default' => false],
        'next_draw_date',
        'current_draw_date',
        'current_draw_date_utc',
        'normal_numbers',
        'bonus_numbers',
        'additional_number',
        'next_jackpot',
        'prizes',
        'winners',
        'currency_id',
        'created_at',
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'lottery_id' => self::CAST_INT,
        'is_processed' => self::CAST_BOOL,
        'next_draw_date' => self::CAST_CARBON,
        'current_draw_date' => self::CAST_CARBON,
        'current_draw_date_utc' => self::CAST_CARBON,
        'normal_numbers' => self::CAST_ARRAY,
        'bonus_numbers' => self::CAST_ARRAY,
        'additional_number' => self::CAST_INT,
        'next_jackpot' => self::CAST_FLOAT,
        'prizes' => self::CAST_ARRAY,
        'winners' => self::CAST_ARRAY,
        'currency_id' => self::CAST_INT,
        'created_at' => self::CAST_CARBON,
    ];

    protected array $relations = [
        Lottery::class => self::BELONGS_TO,
        Currency::class => self::BELONGS_TO,
    ];

    protected array $timezones = [
        'next_draw_date' => [Lottery::class, 'timezone'],
        'current_draw_date' => [Lottery::class, 'timezone'],
        'current_draw_date_utc' => 'UTC',
        'created_at' => 'UTC'
    ];

    public static function getAdditionalNumberName(string $lotterySlug): string
    {
        return $lotterySlug === 'lotto-6aus49' ? 'super' : 'refund';
    }

    public static function needLotteryAdditionalNumber(string $lotterySlug): bool
    {
        return in_array($lotterySlug, self::LOTTERY_SLUGS_WITH_ADDITIONAL_NUMBERS);
    }

    /**
     * This function is used to created mocked Ltech response
     *
     * Each array is single draw result
     * First item points to new next draw
     * First item CANNOT contain: numbers, prizes or winners key!
     * Date is in lottery timezone
     * Jackpot is full length number with two decimals - it's not divided by 1000000
     * Prizes and winners slugs contain keys format: match-{normalNumbersCount}-{bonusNumbersCount}
     * When bonus numbers count does not exist in current tier, there should be only match-{normalNumbersCount}
     * There are many other specific formats for matches that are prepared by Ltech Manual Draw CRM page on frontend
     */
    public function toLtechJson(string $bonusNumbersName = null): string
    {
        $bonusNumbers = $bonusNumbersName ? [
            $bonusNumbersName => $this->bonusNumbers
        ] : [];

        $additionalNumberName = self::getAdditionalNumberName($this->lottery->slug);
        $refundNumber = self::needLotteryAdditionalNumber($this->lottery->slug) ? [
            $additionalNumberName => $this->additionalNumber,
        ] : [];

        return json_encode([
           [
               'type' => $this->lottery->slug,
               'date' => $this->nextDrawDate->format(Helpers_Time::DATE_FORMAT),
               'jackpot' => [
                   'total' => $this->nextJackpot
               ],
           ],
            [
                'type' => $this->lottery->slug,
                'date' => $this->currentDrawDate->format(Helpers_Time::DATE_FORMAT),
                'numbers' => [
                    'main' => $this->normalNumbers,
                ] + $bonusNumbers + $refundNumber,
                'prizes' => $this->prizes,
                'winners' => $this->winners,
            ],
        ]);
    }

    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}
