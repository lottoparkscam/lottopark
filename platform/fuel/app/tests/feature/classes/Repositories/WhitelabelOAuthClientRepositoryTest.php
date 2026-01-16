<?php

declare(strict_types=1);

namespace Tests\Feature\Classes\Repositories;

use Carbon\Carbon;
use Models\Whitelabel;
use Repositories\WhitelabelOAuthClientRepository;
use Test_Feature;
use Tests\Fixtures\WhitelabelFixture;
use Tests\Fixtures\WhitelabelOAuthClientFixture;

class WhitelabelOAuthClientRepositoryTest extends Test_Feature
{
    private WhitelabelFixture $whitelabelFixture;
    private WhitelabelOAuthClientFixture $whitelabelOAuthClientFixture;
    private WhitelabelOAuthClientRepository $whitelabelOAuthClientRepositoryUnderTest;
    private Whitelabel $whitelabel;

    public function setUp(): void
    {
        parent::setUp();
        $this->whitelabelFixture = $this->container->get(WhitelabelFixture::class);
        $this->whitelabel = $this->whitelabelFixture->createOne();
        $this->whitelabelOAuthClientFixture = $this->container->get(WhitelabelOAuthClientFixture::class);
        $this->whitelabelOAuthClientRepositoryUnderTest = $this->container->get(WhitelabelOAuthClientRepository::class);
    }

    /**
     * @test
     */
    public function findOneByClientId_clientNotExists(): void
    {
        // Given
        $testClientId = 'test_client';

        // When
        $actual = $this->whitelabelOAuthClientRepositoryUnderTest->findOneByClientId($testClientId);

        // Then
        $this->assertNull($actual);
    }

    /**
     * @test
     */
    public function findOneByClientId_clientExists(): void
    {
        // Given
        $testClientId = 'test_client';

        $this->whitelabelOAuthClientFixture
            ->withWhitelabel($this->whitelabel)
            ->createOne([
                'client_id' => $testClientId
            ]);

        // When
        $actual = $this->whitelabelOAuthClientRepositoryUnderTest->findOneByClientId($testClientId);

        $expected = ['whitelabel_id' => (string) $this->whitelabel->id];

        // Then
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function getWhitelabelAutologinLink_clientNotExists(): void
    {
        // When
        $actual = $this->whitelabelOAuthClientRepositoryUnderTest->getWhitelabelAutologinLink($this->whitelabel->id);

        // Then
        $this->assertFalse($actual);
    }

    /**
     * @test
     */
    public function getWhitelabelAutologinLink_clientExists_getLastCreated(): void
    {
        // Given
        $expectedText = 'GG World';
        $expectedUri = 'https://ggworld.lottopark.com/auth/social-login/partner/auto-login';

        // Created yesterday
        $this->whitelabelOAuthClientFixture
            ->withWhitelabel($this->whitelabel)
            ->createOne([
                'name' => 'test_name',
                'autologin_uri' => 'https://test.com/auth/social-login/partner/auto-login',
                'created_at' => Carbon::now()->subDays()->format('Y-m-d H:i:s')
            ]);

        // Created now
        $this->whitelabelOAuthClientFixture
            ->withWhitelabel($this->whitelabel)
            ->createOne([
                'name' => $expectedText,
                'autologin_uri' => $expectedUri,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s')
            ]);

        // When
        $actual = $this->whitelabelOAuthClientRepositoryUnderTest->getWhitelabelAutologinLink($this->whitelabel->id);

        // Then
        $this->assertIsArray($actual);

        $this->assertArrayHasKey('text', $actual);
        $this->assertArrayHasKey('uri', $actual);

        $this->assertSame($expectedText, $actual['text']);
        $this->assertSame($expectedUri, $actual['uri']);
    }
}
