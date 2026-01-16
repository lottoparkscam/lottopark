<?php

use Carbon\Carbon;
use Repositories\LotteryLogRepository;

class Lotto_Lotteries_OtosLotto extends Lotto_Lotteries_GGFeed
{
    protected string $lottery_slug = 'otoslotto';

    /**
     * @return void
     * @throws Exception
     */
    public function get_results(): void
    {
        $this->fetch_draw_data($this->lottery);
        $this->update_lottery();
    }


    /**
     * @param array $lottery
     * @param int   $modifier
     *
     * @return void
     * @throws Exception
     */
    public function fetch_draw_data(array $lottery, int $modifier = 0): void
    {
        /** @var LotteryLogRepository $lotteryLogRepository */
        $lotteryLogRepository = Container::get(LotteryLogRepository::class);

        $url = "https://bet.szerencsejatek.hu/jatekok/otoslotto/sorsolasok";
        $this->init_html_feed($url);

        if (!$this->xpath) {
            $this->sendDelayedErrorLog(
                slug: $this->lottery_slug,
                error: "Failed to initialize XPath.",
                errorMessage: "HTML might not have loaded correctly from: $url",
                delayInHours: 6
            );

            return;
        }

        // jackpot
        $jackpot = $this->get_elements_by_query('//div[contains(@class, "prediction")]/h3');

        if ($jackpot->length != 1) {
            $jackpot = false;
            $lotteryLogRepository->addWarningLog(
                $lottery['id'],
                'Current jackpot not found. Received jackpot length is != 1.'
            );
        } else {
            // Example: 1,2 milliárd Ft
            $jackpot = trim($jackpot->item(0)->nodeValue);
            if (preg_match('/([0-9.,]+(?=\s+milliárd))/ui', $jackpot, $m)) {
                $jackpot_str = str_replace(',', '.', $m[1]);
                $jackpot = intval(floatval($jackpot_str) * 1000);  // jackpot amount in millions
            } elseif (preg_match('/([0-9.,]+(?=\s+millió))/ui', $jackpot, $m)) {
                $jackpot_str = str_replace(',', '.', $m[1]);
                $jackpot = intval(floatval($jackpot_str));  // jackpot amount in millions
            } else {
                $jackpot = false;
                $lotteryLogRepository->addWarningLog(
                    $lottery['id'],
                    'Current jackpot not found. Received jackpot does not pass preg_match.'
                );
            }
        }

        // date
        $next_date_local = new DateTime($lottery['next_date_local']);
        $year = $next_date_local->format('Y');

        // Example: augusztus 22. (szombat)
        $date_part2 = $this->get_elements_by_classname('div', 'day');

        if ($date_part2->length != 1) {
            throw new Exception("Incorrect date count.");
        }

        $date_part2 = trim($date_part2->item(0)->nodeValue);
        $date_part2 = explode(' ', $date_part2);

        $date_part2 = array_values(array_filter($date_part2, function ($value) {
            return !empty($value);
        }));

        $day = trim($date_part2[1]);
        $month = Lotto_Helper::get_hungarian_month_number(trim($date_part2[0])) . ".";

        $date = Carbon::parse("$day$month$year", $lottery['timezone']);
        $date->setTimeFromTimeString($this->get_draw_hour_from_draw_dates($date->shortEnglishDayOfWeek));

        // numbers
        $draw = $this->get_elements_by_query('//div[contains(@class, "clear")]/span[contains(@class, "number")]');
        if ($draw->length != 5) {
            throw new Exception("Incorrect draw count.");
        }
        foreach ($draw as $key => $item) {
            $num = intval(trim($item->nodeValue));
            if ($num <= 0) {
                throw new Exception("Number cannot be less than or equal 0.");
            }
            $numbers[] = $num;
        }

        $bonus_numbers = [];

        $date_utc = false;
        if ($date !== false){
            $date_utc = $date->clone()->setTimezone(new DateTimeZone('UTC'));
        }

        $this->set_lottery_with_data($lottery, $jackpot, $date, $date_utc, $numbers, $bonus_numbers);
    }

}
