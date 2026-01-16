<?php

namespace Tests\Unit\Classes\Helpers;

use Helpers\Wordpress\SecurityHelper;
use Models\Whitelabel;
use Test_Unit;

class SecurityTest extends Test_Unit
{
    private const EXAMPLE_SPAIN_IP = '1.178.224.0';

    public function setUp(): void
    {
        parent::setUp();
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REQUEST_URI'] = 'https://lottopark.loc/api';
    }

    /** @test */
    public function shouldBlockSpainForV1_forWhitelabelType2(): void
    {
        $whitelabel = ['type' => Whitelabel::TYPE_V2];
        $shouldBlock = SecurityHelper::shouldBlockSpainForV1($whitelabel);
        $this->assertFalse($shouldBlock);
    }

    /** @test */
    public function shouldBlockSpainForV1_forWhitelabelType1_andCorrectIp(): void
    {
        $whitelabel = ['type' => Whitelabel::TYPE_V1];
        $shouldBlock = SecurityHelper::shouldBlockSpainForV1($whitelabel);
        $this->assertFalse($shouldBlock);
    }

    /** @test */
    public function shouldBlockSpainForV1_forWhitelabelType1_andWrongIp(): void
    {
        $_SERVER['REMOTE_ADDR'] = self::EXAMPLE_SPAIN_IP;
        $whitelabel = ['type' => Whitelabel::TYPE_V1];
        $shouldBlock = SecurityHelper::shouldBlockSpainForV1($whitelabel);
        $this->assertTrue($shouldBlock);
    }

    /** @test */
    public function shouldBlockSpainForV1_isCasino_shouldNotBlock(): void
    {
        $_SERVER['REMOTE_ADDR'] = self::EXAMPLE_SPAIN_IP;
        $_SERVER['REQUEST_URI'] = 'https://api.lottopark.loc/api/slots/slotegrator';
        $whitelabel = ['type' => Whitelabel::TYPE_V1];
        $shouldBlock = SecurityHelper::shouldBlockSpainForV1($whitelabel);
        $this->assertFalse($shouldBlock);
    }
}
