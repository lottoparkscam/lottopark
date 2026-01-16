<?php

declare(strict_types=1);

namespace Tests\Unit\Services\OAuth2Server;

use OAuth2\Encryption\Jwt;
use OAuth2\Storage\ClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Services\OAuth2Server\PartnerIdToken;
use Test_Unit;

class PartnerIdTokenTest extends Test_Unit
{
    private PartnerIdToken $partnerIdTokenUnderTest;
    private ClientInterface|MockObject $client;
    private Jwt $encryptionUtil;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createMock(ClientInterface::class);
        $this->user = [
            'email' => 'example@test.com',
            'token' => 'LP602395978',
            'name' => 'John',
            'is_confirmed' => true,
        ];

        $this->encryptionUtil = new Jwt();
        $this->partnerIdTokenUnderTest = new PartnerIdToken(
            $this->client,
            $this->user,
            'HS256',
            $this->encryptionUtil
        );
    }

    /**
     * @test
     */
    public function getAuthorizeResponse(): void
    {
        // When
        $actual = $this->partnerIdTokenUnderTest->getAuthorizeResponse([]);

        // Then
        $this->assertSame([], $actual);
    }

    /**
     * @test
     */
    public function getUser(): void
    {
        // When
        $actual = $this->partnerIdTokenUnderTest->getUser();

        // Then
        $this->assertSame($this->user, $actual);
    }

    /**
     * @test
     */
    public function createIdToken_clientExists(): void
    {
        $clientId = 'test_client';
        $userInfo = null;

        $this->client
            ->expects($this->once())
            ->method('getClientDetails')
            ->with($clientId)
            ->willReturn(['client_secret' => 'secret']);

        $expected = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ0b2tlbiI6IkxQNjAyMzk1OTc4IiwiZW1haWwiOiJleGFtcGxlQHRlc3QuY29tIiwibmFtZSI6IkpvaG4iLCJpc19jb25maXJtZWQiOnRydWV9.Zx_Zuz1_M_1dYQ0ovU61wV_4NABENaoqRXbpvZGYp3s';
        $actual = $this->partnerIdTokenUnderTest->createIdToken($clientId, $userInfo);

        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function createIdToken_invalidClient(): void
    {
        // Expect
        $this->expectExceptionMessage('Invalid client.');

        // Given
        $clientId = 'test_client';
        $userInfo = null;

        $this->client
            ->expects($this->once())
            ->method('getClientDetails')
            ->with($clientId)
            ->willReturn([]);

        // When
        $this->partnerIdTokenUnderTest->createIdToken($clientId, $userInfo);
    }

    /**
     * @test
     */
    public function createIdToken_invalidUserData(): void
    {
        // Expect
        $this->expectExceptionMessage('Missing required user field "token".');

        // Given
        $clientId = 'test_client';
        $userInfo = null;
        unset($this->user['token']);

        $partnerIdTokenUnderTest = new PartnerIdToken(
            $this->client,
            $this->user,
            'HS256',
            $this->encryptionUtil
        );

        $this->client
            ->expects($this->once())
            ->method('getClientDetails')
            ->with($clientId)
            ->willReturn(['client_secret' => 'secret']);

        // When
        $partnerIdTokenUnderTest->createIdToken($clientId, $userInfo);
    }

    /**
     * @test
     */
    public function createIdToken_withoutIsConfirmedReturnIdTokenWithDefaultIsConfirmedFalse(): void
    {
        // Given
        $clientId = 'test_client';
        $userInfo = null;
        unset($this->user['is_confirmed']);

        $partnerIdTokenUnderTest = new PartnerIdToken(
            $this->client,
            $this->user,
            'HS256',
            $this->encryptionUtil
        );

        $this->client
            ->expects($this->once())
            ->method('getClientDetails')
            ->with($clientId)
            ->willReturn(['client_secret' => 'secret']);

        // When
        $expected = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ0b2tlbiI6IkxQNjAyMzk1OTc4IiwiZW1haWwiOiJleGFtcGxlQHRlc3QuY29tIiwibmFtZSI6IkpvaG4iLCJpc19jb25maXJtZWQiOmZhbHNlfQ.rsAJKGtN6nuwmerpEBedV9-rGlIQ85o4F5ENLVCU6Lk';
        $actual = $partnerIdTokenUnderTest->createIdToken($clientId, $userInfo);

        $this->assertSame($expected, $actual);
    }
}
