<?php

namespace Tests\Unit\Classes\Services;

use Events_Event;
use Test_Unit;

class EventTest extends Test_Unit
{
    /**
     * @test
     * @dataProvider casinoEventProvider
     * @param bool $isCasino
     * @param array $data
     * @param bool $expectedResult
     */
    public function isCasinoEventOff(bool $isCasino, array $data, bool $expectedResult): void
    {
        $result = Events_Event::isCasinoEventOff($data, $isCasino);
        $this->assertEquals($expectedResult, $result);
    }

    public function casinoEventProvider(): array
    {
        return [
            [false, [], false],
            [false, ['onCasinoEvent' => false], false],
            [true, [], true],
            [true, ['onCasinoEvent' => false], true],
            [true, ['onCasinoEvent' => true], false],
        ];
    }
}
