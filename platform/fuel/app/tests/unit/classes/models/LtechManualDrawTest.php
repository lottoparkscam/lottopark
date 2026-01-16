<?php

namespace Unit\Classes\Models;

use Carbon\Carbon;
use Factories\LtechManualDrawFactory;
use Models\Lottery;
use Test_Unit;

class LtechManualDrawTest extends Test_Unit
{
    private LtechManualDrawFactory $ltechManualDrawFactory;
    private Lottery $lottery;
    private Carbon $now;

    public function setUp(): void
    {
        parent::setUp();

        $this->now = Carbon::now();
        $this->lottery = new Lottery([
            'id' => 1,
            'slug' => 'powerball',
            'next_date_local' => $this->now,
            'next_date_utc' => $this->now,
            'timezone' => 'Europe/Warsaw',
        ]);
        $this->ltechManualDrawFactory = $this->container->get(LtechManualDrawFactory::class);
    }

    /** @test */
    public function toLtechJson_hasCorrectFormat(): void
    {
        // Given
        $ltechManualDraw = $this->ltechManualDrawFactory->createLtechManualDraw($this->lottery);
        $ltechManualDraw->lottery = $this->lottery;

        // When
        $jsonResponse = $ltechManualDraw->toLtechJson('ownBonusName');
        $arrayResponse = json_decode($jsonResponse, true);
        $theNewestDraw = $arrayResponse[0];
        $currentDraw = $arrayResponse[1];

        // Then
        $this->assertArrayHasKey('type', $theNewestDraw);
        $this->assertSame('powerball', $theNewestDraw['type']);

        $this->assertArrayHasKey('date', $theNewestDraw);
        $this->assertSame($this->now->format('Y-m-d'), $theNewestDraw['date']);

        $this->assertArrayNotHasKey('winners', $theNewestDraw);
        $this->assertArrayNotHasKey('prizes', $theNewestDraw);
        $this->assertArrayNotHasKey('numbers', $theNewestDraw);

        $this->assertArrayHasKey('type', $currentDraw);
        $this->assertSame('powerball', $currentDraw['type']);

        $this->assertArrayHasKey('date', $currentDraw);
        $this->assertSame($this->now->format('Y-m-d'), $currentDraw['date']);

        $matchKeys = [
            'match-0-1',
            'match-1-1',
            'match-2-1',
            'match-3',
            'match-3-1',
            'match-4',
            'match-4-1',
            'match-5',
            'match-5-1',
        ];

        $this->assertSame(10000000.23, $theNewestDraw['jackpot']['total']);

        $this->assertSame([1, 2, 3, 4, 5], $currentDraw['numbers']['main']);
        $this->assertSame([6], $currentDraw['numbers']['ownBonusName']);

        $this->assertArrayHasKey('prizes', $currentDraw);
        $this->assertSame($matchKeys, array_keys($currentDraw['prizes']));

        $this->assertArrayHasKey('winners', $currentDraw);
        $this->assertSame($matchKeys, array_keys($currentDraw['winners']));
    }

    /** @test */
    public function toLtechJson_withoutBonusBall_hasCorrectFormat(): void
    {
        // Given
        $ltechManualDraw = $this->ltechManualDrawFactory->createLtechManualDraw($this->lottery);
        $ltechManualDraw->lottery = $this->lottery;

        // When
        $jsonResponse = $ltechManualDraw->toLtechJson();
        $arrayResponse = json_decode($jsonResponse, true);
        $theNewestDraw = $arrayResponse[0];
        $currentDraw = $arrayResponse[1];

        // Then
        $this->assertArrayHasKey('type', $theNewestDraw);
        $this->assertSame('powerball', $theNewestDraw['type']);

        $this->assertArrayHasKey('date', $theNewestDraw);
        $this->assertSame($this->now->format('Y-m-d'), $theNewestDraw['date']);

        $this->assertSame([1, 2, 3, 4, 5], $currentDraw['numbers']['main']);
        $this->assertCount(1, $currentDraw['numbers']);
    }

    /** @test */
    public function toLtechJson_hasRefundNUmber(): void
    {
        // Given
        $this->lottery->slug = 'la-primitiva';
        $ltechManualDraw = $this->ltechManualDrawFactory->createLtechManualDraw($this->lottery);
        $ltechManualDraw->additionalNumber = 7;
        $ltechManualDraw->lottery = $this->lottery;

        // When
        $jsonResponse = $ltechManualDraw->toLtechJson('ownBonusName');
        $arrayResponse = json_decode($jsonResponse, true);
        $currentDraw = $arrayResponse[1];

        // Then
        $this->assertArrayHasKey('refund', $currentDraw['numbers']);
        $this->assertSame(7, $currentDraw['numbers']['refund']);
    }

    /** @test */
    public function toLtechJson_hasSuperNUmber(): void
    {
        // Given
        $this->lottery->slug = 'lotto-6aus49';
        $ltechManualDraw = $this->ltechManualDrawFactory->createLtechManualDraw($this->lottery);
        $ltechManualDraw->additionalNumber = 8;
        $ltechManualDraw->lottery = $this->lottery;

        // When
        $jsonResponse = $ltechManualDraw->toLtechJson('ownBonusName');
        $arrayResponse = json_decode($jsonResponse, true);
        $currentDraw = $arrayResponse[1];

        // Then
        $this->assertArrayHasKey('super', $currentDraw['numbers']);
        $this->assertSame(8, $currentDraw['numbers']['super']);
    }
}
