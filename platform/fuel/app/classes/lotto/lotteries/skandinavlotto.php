<?php

use Carbon\Carbon;
use Repositories\LotteryLogRepository;

class Lotto_Lotteries_SkandinavLotto extends Lotto_Lotteries_GGFeed
{
    /**
     * @return void
     * @throws Exception
     */
    public function get_results(): void
    {
        // Automatic draw
        $this->fetch_draw_data($this->lottery, 0);
        $this->update_lottery();

        // Manual draw
        $this->fetch_draw_data($this->lottery, 1);
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

        $url = "https://bet.szerencsejatek.hu/jatekok/skandinavlotto/sorsolasok";
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
            // Example: 280 millió Ft
            $jackpot = trim($jackpot->item(0)->nodeValue);
            $jackpot = explode(' ', $jackpot);
            if (preg_match('/([0-9]+)/u', $jackpot[0], $m)) {
                $jackpot = intval($m[1]);  // jackpot amount in millions
            } else {
                $jackpot = false;
                $lotteryLogRepository->addWarningLog(
                    $lottery['id'],
                    'Current jackpot not found. Received jackpot does not pass preg_match.'
                );
            }
        }

        // date
        // Example: 2020. 34. hét
        // $date_part1 = $this->get_elements_by_classname('div', 'week');
        $date_part1 = $this->get_elements_by_query('//li[contains(@class, "current")]/div[contains(@class, "week")]');

        if ($date_part1->length != 1) {
            throw new Exception("Incorrect date count.");
        }

        $date_part1 = trim($date_part1->item(0)->nodeValue);

        $date_part1 = explode(' ', $date_part1);

        // Example: augusztus 19. (szerda)
        $date_part2 = $this->get_elements_by_classname('div', 'day');

        if ($date_part2->length != 1) {
            throw new Exception("Incorrect date count.");
        }

        $date_part2 = trim($date_part2->item(0)->nodeValue);
        $date_part2 = explode(' ', $date_part2);
        foreach ($date_part2 as $part_key => $part) {
            if (empty($part)) {
                unset($date_part2[$part_key]);
            }
        }
        $date_part2 = array_values($date_part2);

        $day = trim($date_part2[1]);
        $month = Lotto_Helper::get_hungarian_month_number(trim($date_part2[0])) . ".";
        $year = trim(str_replace('.', '', $date_part1[0]));

        // Format: d.m.Y
        $date = $day . $month . $year;

        $time = null;
        if ($modifier === 1) {
            $time = '20:30';
        } else {
            $time = '20:25';
        }

        $date = Carbon::parse("$date $time", $lottery['timezone']);

        // numbers
        $draw = $this->get_elements_by_query('//div[contains(@class, "clear")]/span[contains(@class, "number")]');
        if ($draw->length != 14) {
            throw new Exception("Incorrect draw count.");
        }
        foreach ($draw as $key => $item) {
            $num = intval(trim($item->nodeValue));
            if ($num <= 0) {
                throw new Exception("Number cannot be less than or equal 0.");
            }
            $numbers[] = $num;
        }

        if ($modifier === 1) {
            $numbers = array_slice($numbers, 7, 7); // Manual
        } else {
            $numbers = array_slice($numbers, 0, 7); // Automatic
        }

        $bonus_numbers = [];

        $date_utc = $date->clone()->setTimezone(new DateTimeZone('UTC'));

        $this->set_lottery_with_data($lottery, $jackpot, $date, $date_utc, $numbers, $bonus_numbers);
    }


}
