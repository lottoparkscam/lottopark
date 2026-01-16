<?php

use Carbon\Carbon;
use Fuel\Core\DB;

/**
 * @deprecated - use new fixtures instead
 * Test Factory Lottery.
 *
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2019-07-06
 * Time: 16:48:13
 */
final class Test_Factory_Lottery extends Test_Factory_Base
{
    /**
     * Variable for calculation of source_id.
     *
     * @var int
     */
    protected $source_id;

    /**
     * True if source id was passed by user.
     *
     * @var bool
     */
    private $is_source_id_passed;

    protected function before(int $count, array $values): void
    {
        $this->is_source_id_passed = isset($values['source_id']);
        DB::query('SET FOREIGN_KEY_CHECKS=0;')
            ->execute();
    }

    protected function after(int $count, array $values): void
    {
        DB::query('SET FOREIGN_KEY_CHECKS=1;')
            ->execute();
        // auto create sources for lotteries, if they weren't passed by user.
        if ($this->is_source_id_passed) {
            return;
        }

        foreach (self::$result['lottery'] as $lottery) {
            $this->with(Test_Factory_Lottery_Source::class, [
                'id' => $lottery['source_id']
            ]);
        }
    }

    private function next_source_id(): int
    {
        return parent::next('lottery_source');
    }

    protected function values(array &$values): array
    {
        $sort_dates_ascending = function ($date_base, $date_compared) {
            return Carbon::createFromTimeString($date_base) > Carbon::createFromTimeString($date_compared);
        };
        $random_draw_dates = array_fill(0, random_int(2, 21), null);
        foreach ($random_draw_dates as $date_key => $date) {
            $random_draw_dates[$date_key] = Helpers_Time::ISO_WEEK_DAYS[random_int(1, 7)] . " " . Test_Factory_Base::random_time('H:i');
        }
        usort($random_draw_dates, $sort_dates_ascending);

        return [
            'source_id' => function (): int {
                return $this->next_source_id();
            },
            'name' => parent::random_string(40),
            // 'shortname' => parent::random_string(10),
            'country' => parent::random_string(15),
            // 'country_iso' => parent::random_string_uppercase(2, 'alpha'),
            'slug' => parent::random_string(40),
            'is_enabled' => true,
            'timezone' => parent::random_timezone(),
            'draw_dates' => json_encode($random_draw_dates),
            // 'current_jackpot' => parent::random_decimal(999999999999, 99999999),
            // 'current_jackpot_usd' => parent::random_decimal(999999999999, 99999999), // at this point of time doesn't care about integrity between jackpot and usd_jackpot
            'draw_jackpot_set' => '0',
            'currency_id' => Helpers_Currency::USD_ID,
            'last_total_winners' => 0,
            'last_total_prize' => 0,
            'last_jackpot_prize' => 0,
            'last_update' => 0,
            'price' => 0,
            'estimated_updated' => 0,
        ];
    }
}
