<?php

namespace Tests\Unit\Helpers;

use Helpers\SlotHelper;
use Test_Unit;

final class SlotHelperTest extends Test_Unit
{
    private const URL = 'lottopark.loc';
    private const GAME_UUID = '03d8487132e607b8d4103103663c52bbf2547d29';

    public function setUp(): void
    {
        parent::setUp();

        $_SERVER['HTTP_HOST'] = self::URL;
        $_SERVER['SITE_URL'] = 'https://' . self::URL;
    }

    /** @test */
    public function getModeSwitchData_IsDemoShouldReturnReal(): void
    {
        $this->setInput('GET', ['mode' => 'demo']);

        $requestUri = '/casino-play/?game_uuid=' . self::GAME_UUID . '&mode=demo';
        $_SERVER['REQUEST_URI'] = $requestUri;
        $actual = SlotHelper::getModeSwitchData();

        $expected = [
            'currentMode' => 'demo',
            'oppositeMode' => 'real',
            'oppositeModeUrl' => 'https://' . self::URL . '/casino-play/?game_uuid=' . self::GAME_UUID
        ];

        $this->assertStringEndsWith('&mode=demo', $requestUri);
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getModeSwitchData_IsRealShouldReturnDemo(): void
    {
        $this->resetInput();

        $requestUri = $this->getRequestUri(false);
        $_SERVER['REQUEST_URI'] = $requestUri;

        $actual = SlotHelper::getModeSwitchData();

        $expected = [
            'currentMode' => 'real',
            'oppositeMode' => 'demo',
            'oppositeModeUrl' => 'https://' . self::URL . '/casino-play/?game_uuid=' . self::GAME_UUID . '&mode=demo'
        ];

        $this->assertStringEndsNotWith('&mode=demo', $requestUri);
        $this->assertSame($expected, $actual);
    }

    private function getRequestUri(bool $isDemo): string
    {
        $requestUri = '/casino-play/?game_uuid=' . self::GAME_UUID;

        if ($isDemo) {
            $requestUri .= '&mode=demo';
        }

        return $requestUri;
    }

    /** @test */
    public function getAllowedGameUuids_domainNotExists(): void
    {
        $domain = 'lottopark.loc';
        $allowedUuids = SlotHelper::getAllowedGameUuids($domain);

        $this->assertTrue($allowedUuids);
    }

    /** @test */
    public function getAllowedGameTypes_domainNotExists(): void
    {
        $domain = 'lottopark.loc';
        $allowedTypes = SlotHelper::getAllowedGameTypes($domain);

        $this->assertTrue($allowedTypes);
    }

    /** @test */
    public function getAllowedUuidsPerType_domainNotExists(): void
    {
        $allowedUuids = SlotHelper::getAllowedUuidsPerType('TEST');
        $this->assertTrue($allowedUuids);
    }
}
