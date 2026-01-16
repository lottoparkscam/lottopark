<?php

namespace Tests\Unit\Lotto\Lotteries\Helper\Closed;

use Carbon\Carbon;
use Lotto_Helper;
use Model_Lottery_Provider;

abstract class ClosedTimeTestParent extends \Test_Unit
{

    protected array $lottery;
    protected array $whitelabel;
    protected Model_Lottery_Provider $lottery_provider;

    public function setUp(): void
    {
        // default case - concrete tests shall adjust accordingly
        $this->lottery = [
            'id' => 1,
            'draw_dates' => json_encode(["Wed 22:59", "Sat 22:59"]),
            'timezone' => 'America/New_York',
            'type' => 'lottery',
        ];
        $this->lottery_provider = Model_Lottery_Provider::forge(
            [
                'closing_time' => '22:59:00',
                'timezone' => 'America/New_York',
                'closing_times' => null,
                'offset' => 0,
            ]
        );
        $this->whitelabel = [
            'id' => 1,
        ];
    }

    protected function prepareFixtures(string $now, string $nextDrawDate): void
    {
        $this->setFakeCarbon($now, $this->lottery_provider['timezone']);
        $this->lottery['next_date_local'] = $nextDrawDate;
    }

    protected function assertLotteryIsClosed(string $now, string $nextDrawDate): void
    {
        $this->prepareFixtures($now, $nextDrawDate);
        $this->assertTrue(Lotto_Helper::is_lottery_closed($this->lottery, null, $this->whitelabel, $this->lottery_provider));
    }

    protected function assertLotteryIsOpen(string $now, string $nextDrawDate): void
    {
        $this->prepareFixtures($now, $nextDrawDate);
        $this->assertFalse(Lotto_Helper::is_lottery_closed($this->lottery, null, $this->whitelabel, $this->lottery_provider));
    }

}
