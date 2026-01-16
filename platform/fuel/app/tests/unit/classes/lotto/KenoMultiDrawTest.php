<?php

namespace Tests\Unit\Classes\Lotto;

use Carbon\Carbon;
use Lotto_Helper;
use Test_Unit;

final class KenoMultiDrawTest extends Test_Unit
{
    private const MULTI_DRAW_TICKET_COUNT = 5;
    private const DRAW_DATES = [
        'Mon 20:00',
        'Tue 20:00',
        'Wed 20:00',
        'Thu 20:00',
        'Fri 20:00',
        'Sat 20:00',
        'Sun 16:45',
    ];

    private array $lottery = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->lottery = [
            'id' => 46,
            'timezone' => 'Europe/Budapest',
            'draw_dates' => json_encode(self::DRAW_DATES),
            'next_date_local' => null,
        ];
    }

    /**
     * @test
     * @dataProvider dateBeforeCutoffProvider
     */
    public function calculateMultiDrawDatesBeforeCutoffCorrectly(string $lastDrawDate, string $expectedFirstTicketDate, string $expectedLastTicketDate): void
    {
        $lastDrawDate = Carbon::parse($lastDrawDate, $this->lottery['timezone']);

        $firstTicketDate = Lotto_Helper::get_lottery_next_draw($this->lottery, false, $lastDrawDate, 1);
        $lastTicketDate = Lotto_Helper::get_lottery_next_draw($this->lottery, false, $lastDrawDate, self::MULTI_DRAW_TICKET_COUNT);

        $this->assertEquals($expectedFirstTicketDate, $firstTicketDate->format('Y-m-d H:i:s'));
        $this->assertEquals($expectedLastTicketDate, $lastTicketDate->format('Y-m-d H:i:s'));
    }

    public function dateBeforeCutoffProvider()
    {
        return [
            // last draw date | expected first ticket draw date | expected last ticket draw date
            ['2024-07-28 16:45:00', '2024-07-29 20:00:00', '2024-08-02 20:00:00'],
            ['2024-07-29 20:00:00', '2024-07-30 20:00:00', '2024-08-03 20:00:00'],
        ];
    }

    /**
     * @test
     * @dataProvider dateAfterCutoffProvider
     */
    public function calculateMultiDrawDatesAfterCutoffCorrectly(string $lastDrawDate, string $expectedFirstTicketDate, string $expectedLastTicketDate): void
    {
        $lastDrawDate = Carbon::parse($lastDrawDate, $this->lottery['timezone']);

        $firstTicketDate = Lotto_Helper::get_lottery_next_draw($this->lottery, false, $lastDrawDate, 2);
        $lastTicketDate = Lotto_Helper::get_lottery_next_draw($this->lottery, false, $lastDrawDate, self::MULTI_DRAW_TICKET_COUNT + 1);

        $this->assertEquals($expectedFirstTicketDate, $firstTicketDate->format('Y-m-d H:i:s'));
        $this->assertEquals($expectedLastTicketDate, $lastTicketDate->format('Y-m-d H:i:s'));
    }

    public function dateAfterCutoffProvider()
    {
        return [
            // last draw date | expected first ticket draw date | expected last ticket draw date
            ['2024-07-28 16:45:00', '2024-07-30 20:00:00', '2024-08-03 20:00:00'],
            ['2024-07-29 20:00:00', '2024-07-31 20:00:00', '2024-08-04 16:45:00'],
        ];
    }
}
