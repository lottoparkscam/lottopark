<?php

namespace Tests\Unit\Helpers;

use Helpers\FullTokenHelper;
use Test_Unit;

final class FullTokenHelperTest extends Test_Unit
{
    /**
     * @test
     * @dataProvider getWhitelabelPrefixProvider
     */
    public function getWhitelabelPrefix(string $expectedResults, string $fullToken): void
    {
        $this->assertSame($expectedResults, FullTokenHelper::getWhitelabelPrefix($fullToken));
    }

    public function getWhitelabelPrefixProvider(): array
    {
        return [
            ['', 'LP'], //Token is too short
            ['LP', 'LPD1234'], //Deposit Token
            ['LP', 'LPT123123'], //Transaction Token
            ['LP', 'LPP13213'], //Purchase Token
            ['LP', 'LPU231233'], //User Token
            ['LP', 'LPW231233'], //Withdrawal Token
            ['FR', 'FRP123123'], //For faireum prefix
            ['FR', 'FRD1234'],
            ['FR', 'FRT123123'],
            ['FR', 'FRP123123'],
            ['FR', 'FRU123123'],
            ['FR', 'FRW123123'],
        ];
    }

    /**
     * @test
     * @dataProvider getTokenProvider
     */
    public function getToken(string $expectedResults, string $fullToken): void
    {
        $this->assertSame($expectedResults, FullTokenHelper::getToken($fullToken));
    }

    public function getTokenProvider(): array
    {
        return [
            ['', 'LP'], //Token is too short
            ['1234', 'LPD1234'], //Deposit Token
            ['123123', 'LPT123123'], //Transaction Token
            ['123123', 'LPP123123'], //Purchase Token
            ['123121231233', 'LPU123121231233'], //User Token
            ['123121231233', 'LPW123121231233'], //Withdrawal Token
            ['123123', 'FRP123123'], //For faireum prefix
            ['1234', 'FRD1234'],
            ['123123', 'FRT123123'],
            ['123123', 'FRP123123'],
            ['123123', 'FRU123123'],
            ['123123', 'FRW123123'],
        ];
    }

    /**
     * @test
     * @dataProvider isIncorrectProvider
     */
    public function isIncorrect(bool $expectedResults, mixed $fullToken): void
    {
        $this->assertSame($expectedResults, FullTokenHelper::isNotValid($fullToken));
    }

    public function isIncorrectProvider(): array
    {
        return [
            [true, 'LP'], //Token is too short
            [true, 'LPP'], //Token is too short
            [true, 'LPPS'], //without numbers
            [false, 'LPD1234'], //Deposit Token
            [false, 'LPT123123'], //Transaction Token
            [false, 'LPP123123'], //Purchase Token
            [false, 'LPU123121231233'], //User Token
            [false, 'LPW123121231233'], //Withdrawal Token
            [false, 'FRP123123'], //For faireum prefix
        ];
    }
}
