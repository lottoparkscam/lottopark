<?php

use Carbon\Carbon;
use Repositories\LotteryLogRepository;

/**
 *
 */
class Lotto_Lotteries_LottoMultiMulti extends Lotto_Lotteries_GGFeed
{
    /**
     * @return void
     * @throws Exception
     */
    public function get_results() : void
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
    public function fetch_draw_data(array $lottery, int $modifier=0) : void
    {
        $url = 'https://www.lotto.pl/multi-multi';

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

        // Jackpot 
        $jackpot = 25;    // Hardcoded value in millions

        // Dates
        $date_str = $this->get_elements_by_classname('p', 'last-results-date');
        if ($date_str->length != 1) {
            throw new Exception("Incorrect date count.");
        }
        $date_str = trim($date_str->item(0)->nodeValue);

        $date_arr = explode(',', $date_str);

        $date =  trim($date_arr[1]);
        $hour = trim(str_replace("godz.", "", $date_arr[2]));

        $date = Carbon::parse("$date $hour", $lottery['timezone']);
        
        // Numbers
        $draw = $this->get_elements_by_query('//div[contains(@class, "multi-balls-order")]/div[contains(@class, "scoreline-item")]');
        if ($draw->length != 20) {
            throw new Exception("Incorrect draw count.");
        } 
        foreach ($draw as $key => $item) {
            $num = intval(trim($item->nodeValue));
            if ($num <= 0) {
                throw new Exception("Number cannot be less than or equal 0.");
            }
            $numbers[] = $num;
        }

        // Bonus
        $draw = $this->get_elements_by_query('//div[contains(@class, "multi-plus-box")]/div[contains(@class, "scoreline-item")]');
        if ($draw->length != 1) {
            throw new Exception("Incorrect bonus draw count.");
        } 
        
        $num = intval(trim($draw->item(0)->nodeValue));
        if ($num <= 0) {
            throw new Exception("Bonus number cannot be less than or equal 0.");
        }
        $bonus_numbers[] = $num;

        $date_utc = $date->clone()->setTimezone(new DateTimeZone('UTC'));
        $this->set_lottery_with_data($lottery, $jackpot, $date, $date_utc, $numbers, $bonus_numbers);
    }

}
