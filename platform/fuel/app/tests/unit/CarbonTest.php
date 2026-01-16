<?php

namespace Tests\Unit\Classes\Lotto\Lotteries;

use Carbon\Carbon;
use Test_Unit;
use Carbon\Exceptions\InvalidFormatException;

final class CarbonTest extends Test_Unit
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function createFromFormatCatchExceptionTest(): void
    {
        $validFormats = [
            'Y-m-d H:i:s' => '2024-07-10 19:10:00',
            'd/m/Y' => '10/07/2024',
            'm-d-Y' => '07-10-2024',
        ];

        foreach ($validFormats as $format => $date) {
            $carbonDate = Carbon::createFromFormat($format, $date);
            $this->assertInstanceOf(Carbon::class, $carbonDate);
        }

        $invalidDates = [
            'invalid-date',
            '2024-02-30',
            '07/10/2024 25:00',
            ''
        ];

        foreach ($invalidDates as $date) {
            $this->expectException(InvalidFormatException::class);
            Carbon::createFromFormat('Y-m-d H:i:s', $date);
        }
    }
}
