<?php

namespace Tests\Unit\Classes\Lotto\Lotteries;

use Carbon\Carbon;
use Helpers_App;
use Lotto_Lotteries_FrenchKeno;
use Lotto_Scraperhtml;
use Test_Unit;

final class FrenchKenoTest extends Test_Unit
{
    private Lotto_Lotteries_FrenchKeno $frenchkeno;
    private Lotto_Scraperhtml $scraper;

    public function setUp(): void
    {
        parent::setUp();
        $this->frenchkeno = new class extends Lotto_Lotteries_FrenchKeno
        {
            protected Carbon $providerNextDrawDate;
            protected array $providerNextDrawDateFragments;

            public function __construct()
            {
                $this->providerNextDrawDate = Carbon::parse('2024-05-23 13:00:00', 'Europe/Paris')->setTimezone('Europe/Paris');
                $this->setProviderNextDrawDateFragments();
            }
        };

        $this->scraper = new class extends Lotto_Scraperhtml
        {
            protected function fetchRawWebsite(string $url)
            {
                $this->rawHTML = file_get_contents(Helpers_App::get_absolute_file_path('platform/fuel/app/tests/data/classes/lotto/lotteries/frenchkeno-results.html'));
                $this->areaOfWorkHTML = $this->rawHTML;
            }
        };
    }

    /** @test */
    public function jackpotTest(): void
    {
        $expected = 2;
        $actual = Lotto_Lotteries_FrenchKeno::LOTTERY_JACKPOT;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function numbersCountTest(): void
    {
        $expected = 20;
        $actual = Lotto_Lotteries_FrenchKeno::LOTTERY_NUMBERS_COUNT;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function numbersRangeTest(): void
    {
        $expected = [1,70];
        $actual = Lotto_Lotteries_FrenchKeno::LOTTERY_NUMBERS_RANGE;
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function providerTimezoneTest(): void
    {
        $expected = 'Europe/Paris';
        $actual = Lotto_Lotteries_FrenchKeno::PROVIDER_TIMEZONE;
        $this->assertSame($expected, $actual);
    }

    public static function removeFrenchAccentCases(): array
    {
        return [
            [Lotto_Lotteries_FrenchKeno::MONTHS[Carbon::FEBRUARY], 'fevrier'],
            [Lotto_Lotteries_FrenchKeno::MONTHS[Carbon::AUGUST], 'aout'],
            [Lotto_Lotteries_FrenchKeno::MONTHS[Carbon::DECEMBER], 'decembre'],
            ['aeiou', 'aeiou'],
        ];
    }

    /**
     * @test
     * @dataProvider removeFrenchAccentCases
     */
    public function removeFrenchAccentTest(string $input, string $expected): void
    {
        $actual = $this->frenchkeno->removeFrenchAccents($input);
        $this->assertSame($expected, $actual);
    }

    public static function nextDrawTimeCases(): array
    {
        return [
            ['13:00', 'Tirage du midi'],
            ['20:00', 'Tirage du soir'],
        ];
    }

    /**
     * @test
     * @dataProvider nextDrawTimeCases
     */
    public function nextDrawTimeTest(string $input, string $expected): void
    {
        $actual = $this->frenchkeno->getNextDrawTime($input);
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function nextDrawTimeExceptionTest(): void
    {
        $this->expectExceptionMessage('french-keno - unable to get next draw time');
        $this->frenchkeno->getNextDrawTime('abc');
    }

    /** @test */
    public function getNumbersPrimaryTest(): void
    {
        $scraper = $this->scraper->build('https://www.fdj.fr/jeux-de-tirage/keno/resultats/jeudi-23-mai-2024', 0, $this->scraper);
        $expected = [2, 6, 9, 10, 14, 15, 16, 20, 21, 29, 31, 32, 36, 49, 52, 54, 55, 59, 63, 69];

        $actual = $this->frenchkeno->get_numbers_primary($scraper);
        $this->assertSame($expected, $actual);
    }
}
