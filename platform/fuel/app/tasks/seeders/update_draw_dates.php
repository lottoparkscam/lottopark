<?php


namespace Fuel\Tasks\Seeders;

use Carbon\Carbon;
use Models\Lottery;

class Update_Draw_Dates extends Reseeder
{

    const DRAW_DATES = [
        'euromillions' => ["Tue 20:45", "Fri 20:45"],
        'monday-wednesday-lotto-au' => ["Mon 20:45", "Wed 20:45"],
        'lotto-at' => ["Wed 18:47", "Sun 19:17"],
        'lotto-6aus49' => ["Wed 18:25", "Sat 19:25"],
    ];

    /** @param Lottery[] $lotteries */
    private function filterDrawTimes(array &$drawTimes, array $lotteries): void
    {
        foreach ($lotteries as $lottery) {
            $hasConstantTime = !(count($drawTimes[$lottery->slug]) > 1);
            if ($hasConstantTime) {
                continue;
            }
            // otherwise we need to filter based on day of draw
            $nextDateCarbon = Carbon::parse($lottery->next_date_local, $lottery->timezone);
            $drawTimes[$lottery->slug] = $drawTimes[$lottery->slug][$nextDateCarbon->isoWeekday()];
        }
    }

    /** @param Lottery[] $lotteries */
    private function buildDrawTimes(array $lotteries): array
    {
        $drawTimes = [
            'euromillions' => ['20:45'],
            'monday-wednesday-lotto-au' => ['20:45'],
            'lotto-at' => [3 => '18:47', 7 => '19:17'],
            'lotto-6aus49' => [3 => '18:25', 6 => '19:25'],
        ];

        $this->filterDrawTimes($drawTimes, $lotteries);
        return $drawTimes;
    }

    /** @param Lottery[] $lotteries */
    private function buildDrawTimesUTC(array $lotteries): array
    {
        $drawTimesUTC = [
            'euromillions' => ['19:45'],
            'monday-wednesday-lotto-au' => ['9:45'],
            'lotto-at' => [3 => '17:47', 7 => '18:17'],
            'lotto-6aus49' => [3 => '17:25', 6 => '18:25'],
        ];

        $this->filterDrawTimes($drawTimesUTC, $lotteries);
        return $drawTimesUTC;
    }

    private function prepareTicketsUpdateQuery(array &$rows, Lottery $lottery, string $drawDatetimeLocal): void
    {
        $rows['whitelabel_user_tickets'][] = [
            'where' => [
                ["lottery_id", "=", $lottery->id],
                ['draw_date', '>=', $lottery->next_date_local],
            ],
            'set' => [
                'draw_date' => $drawDatetimeLocal,
                'valid_to_draw' => $drawDatetimeLocal,
            ]
        ];
    }

    private function prepareMultidrawFieldUpdate(array &$rows, Lottery $lottery, string $drawDatetimeLocal, string $fieldName): void
    {
        $rows['multi_draw'][] = [
            'where' => [
                ["lottery_id", "=", $lottery->id],
                [$fieldName, '>=', $lottery->next_date_local],
            ],
            'set' => [
                $fieldName => $drawDatetimeLocal,
            ]
        ];
    }

    private function prepareMultidrawUpdateQuery(array &$rows, Lottery $lottery, string $drawDatetimeLocal): void
    {
        $this->prepareMultidrawFieldUpdate($rows, $lottery, $drawDatetimeLocal, 'first_draw');
        $this->prepareMultidrawFieldUpdate($rows, $lottery, $drawDatetimeLocal, 'valid_to_draw');
        $this->prepareMultidrawFieldUpdate($rows, $lottery, $drawDatetimeLocal, 'current_draw');

        $rows['user_draw_notification'][] = [
            'where' => [
                ["lottery_id", "=", $lottery->id],
                ['lottery_draw_date', '>=', $lottery->next_date_local],
            ],
            'set' => [
                'lottery_draw_date' => $drawDatetimeLocal,
            ]
        ];
    }

    protected function rowsStaging(): array
    {
        // NOTE: we need to fetch iso day for lottery with different times per day (they have disabled multidraw) and select proper time
        /** @var Lottery[] $lotteries */
        $lotteries = Lottery::query()
            ->where('slug', 'IN', array_keys(self::DRAW_DATES))
            ->get();

        $drawTimes = $this->buildDrawTimes($lotteries);
        $drawTimesUTC = $this->buildDrawTimesUTC($lotteries);

        $rows = [
            'lottery' => [],
            'whitelabel_user_ticket' => [],
            'multi_draw' => [],
            'user_draw_notification' => [],
        ];
        foreach ($lotteries as $lottery) {
            $lottery->disable_casting();
            $drawTimeLocal = $drawTimes[$lottery->slug][0];
            $drawDatetimeLocal = explode(' ', $lottery->next_date_local)[0] . ' ' . $drawTimeLocal;
            $rows['lottery'][] = [
                'where' => [["slug", "=", $lottery->slug]],
                'set' => [
                    'draw_dates' => json_encode(self::DRAW_DATES[$lottery->slug]),
                    'next_date_local' => $drawDatetimeLocal,
                    'next_date_utc' => explode(' ', $lottery->next_date_utc)[0] . ' ' . $drawTimesUTC[$lottery->slug][0],
                ]
            ];

            $this->prepareTicketsUpdateQuery($rows, $lottery, $drawDatetimeLocal);

            if ($lottery->is_multidraw_enabled) {
                $this->prepareMultidrawUpdateQuery($rows, $lottery, $drawDatetimeLocal);
            }
        }

        return $rows;
    }
}