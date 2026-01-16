<?php

namespace Unit\Classes\Models;

use Models\Whitelabel;
use Models\WhitelabelUserTicket;
use Test_Unit;

class WhitelabelUserTicketTest extends Test_Unit
{
    private WhitelabelUserTicket $whitelabelUserTicket;
    public function setUp(): void
    {
        parent::setUp();
        $this->whitelabelUserTicket = new WhitelabelUserTicket();
        $this->whitelabelUserTicket->whitelabel = new Whitelabel();
    }

    /**
     * @test
     * @dataProvider providerTestCases
     * @param int $inputTicketModel
     * @param bool $expectedResult
     */
    public function isPurchaseAndScanModel(int $inputTicketModel, bool $expectedResult): void
    {
        $this->whitelabelUserTicket->model = $inputTicketModel;
        $result = $this->whitelabelUserTicket->isPurchaseAndScanModel();
        $this->assertEquals($expectedResult, $result);
    }

    public static function providerTestCases(): array
    {
        return [
            [2, true],
            [1, false],
            [0, false],
            [10, false],
        ];
    }

    /**
     * @test
     * @dataProvider providerTestThemeCases
     */
    public function ticketBelongsToDoubleJack(string $inputTheme, bool $expectedResult): void
    {
        $this->whitelabelUserTicket->whitelabel->theme = $inputTheme;
        $result = $this->whitelabelUserTicket->isTicketFromDoubleJack();
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @test
     * @dataProvider providerTestThemeCases
     */
    public function isNotTicketFromDoubleJack(string $inputTheme, bool $expectedResult): void
    {
        $this->whitelabelUserTicket->whitelabel->theme = $inputTheme;
        $result = $this->whitelabelUserTicket->isNotTicketFromDoubleJack();
        $this->assertEquals(!$expectedResult, $result);
    }

    public static function providerTestThemeCases(): array
    {
        return [
            [Whitelabel::LOTTOPARK_THEME, false],
            [Whitelabel::DOUBLEJACK_THEME, true],
            ['DoubleJack', false],
            ['asdadggqwweqess45', false],
        ];
    }
}
