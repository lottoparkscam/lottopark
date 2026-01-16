<?php


class Tests_E2e_Controller_Api_Lottery extends Test_E2e_Controller_Api
{
    const EXPECTED_KEYS = [
        "name",
        "country",
        "country_iso",
        "timezone",
        "draw_days",
        "draw_date_utc",
        "currency",
        "last_date_local",
        "last_numbers",
        "last_bnumbers",
        "last_total_prize",
        "last_total_winners",
    ];

    const EXCLUDE_KEYS_DURING_VALUE_CHECK = [
        "draw_date_local",
        "real_draw_date_local",
        "real_draw_date_utc",
        "jackpot",
        "name",
        "draw_date_utc"
    ];

    public function test_get_lotteries()
    {
        $method = "GET";
        $endpoint = "/api/lotteries?order_by=id&order=ASC";

        $response = $this->get_response_with_security_check($method, $endpoint);

        // check amount of lotteries
        $lotteries = Model_Whitelabel::get_lotteries_by_order_for_whitelabel(
            (int)$this->whitelabel['id'],
            "id",
            "ASC"
        );

        if (empty($lotteries)) {
            $this->markTestSkipped('Lack of lotteries in database');
        }

        $lotteries_from_response = $response['data'];

        $this->assertCount(count($lotteries), $lotteries_from_response);

        // check if random lottery has correct keys

        $random_key = array_rand($lotteries_from_response);
        $random_key_single_lottery = array_rand($lotteries_from_response);

        $single_lottery = $lotteries_from_response[$random_key_single_lottery];
        $lottery_from_db = null;

        foreach ($lotteries as $lottery) {
            if ($lottery['slug'] === $single_lottery['name']) {
                $lottery_from_db = $lottery;
            }
        }

        foreach (self::EXPECTED_KEYS as $key) {
            $this->assertArrayHasKey($key, $lotteries_from_response[$random_key]);

            if (in_array($key, self::EXCLUDE_KEYS_DURING_VALUE_CHECK)) {
                continue;
            }

            // check data of specific lottery
            $this->assertSame($lottery_from_db[$key], $single_lottery[$key]);
        }
    }

    public function test_get_lotteries_with_limit()
    {
        $method = "GET";
        $endpoint = "/api/lotteries?order_by=id&order=ASC&limit=5";

        $response = $this->get_response_with_security_check($method, $endpoint);

        // check amount of lotteries
        $this->assertLessThanOrEqual(5, count($response['data']));
    }

    public function test_get_lottery()
    {
        $lotteries = Model_Whitelabel::get_lotteries_by_order_for_whitelabel(
            (int)$this->whitelabel['id'],
            "id",
            "ASC"
        );

        if (empty($lotteries)) {
            $this->markTestSkipped('Lack of lotteries in database');
        }

        $rand_key = array_rand($lotteries);
        $single_lottery = $lotteries[$rand_key];
        $name = $single_lottery['slug'];

        $method = "GET";
        $endpoint = "/api/lottery?name={$name}";

        $response = $this->get_response_with_security_check($method, $endpoint);
        $lottery_from_response = $response['data'];

        foreach (self::EXPECTED_KEYS as $key) {
            $this->assertArrayHasKey($key, $lottery_from_response);

            if (in_array($key, self::EXCLUDE_KEYS_DURING_VALUE_CHECK)) {
                continue;
            }

            // check data of specific lottery
            $this->assertSame($lottery_from_response[$key], $single_lottery[$key]);
        }
    }
}