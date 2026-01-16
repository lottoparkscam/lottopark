<?php

namespace Tests\Feature\Classes\Services\OAuth2Server;

use Models\Whitelabel;
use Services\OAuth2Server\WhiteLottoStorage;
use Tests\Fixtures\WhitelabelOAuthAccessTokenFixture;
use Tests\Fixtures\WhitelabelOAuthAuthorizationCodeFixture;
use Tests\Fixtures\WhitelabelOAuthClientFixture;
use Tests\Fixtures\WhitelabelUserFixture;
use Test_Feature;

class WhiteLottoStorageTest extends Test_Feature
{
    private Whitelabel $whitelabel;
    private WhiteLottoStorage $storage;
    private WhitelabelUserFixture $whitelabelUserFixture;
    private WhitelabelOAuthClientFixture $whitelabelOAuthClientFixture;
    private WhitelabelOAuthAuthorizationCodeFixture $whitelabelOAuthAuthorizationCodeFixture;
    private WhitelabelOAuthAccessTokenFixture $whitelabelOAuthAccessTokenFixture;

    public function setUp(): void
    {
        parent::setUp();

        $this->whitelabel = $this->container->get('whitelabel');
        $this->whitelabelUserFixture = $this->container->get(WhitelabelUserFixture::class);
        $this->whitelabelOAuthClientFixture = $this->container->get(WhitelabelOAuthClientFixture::class);
        $this->whitelabelOAuthAuthorizationCodeFixture = $this->container->get(WhitelabelOAuthAuthorizationCodeFixture::class);
        $this->whitelabelOAuthAccessTokenFixture = $this->container->get(WhitelabelOAuthAccessTokenFixture::class);
        $this->storage = new WhiteLottoStorage();
    }

    /**
     * @test
     */
    public function getClientDetails_clientNotExists(): void
    {
        // Given
        $clientId = 'client_test';

        // When
        $actual = $this->storage->getClientDetails($clientId);

        // Then
        $this->assertFalse($actual);
    }

    /**
     * @test
     */
    public function getClientDetails_clientExists(): void
    {
        // Given
        $clientId = 'client_test';

        $client = $this->whitelabelOAuthClientFixture
            ->withWhitelabel($this->whitelabel)
            ->createOne([
                'client_id' => $clientId
            ]);

        // When
        $actual = $this->storage->getClientDetails($clientId);

        // Then
        $this->assertSame($client['client_id'], $actual['client_id']);
        $this->assertSame($client['client_secret'], $actual['client_secret']);
        $this->assertSame($client['redirect_uri'], $actual['redirect_uri']);
        $this->assertSame($client['grant_types'], $actual['grant_types']);
        $this->assertSame($client['scope'], $actual['scope']);
    }

    /**
     * @test
     */
    public function isPublicClient_clientNotExists(): void
    {
        // Given
        $clientId = 'client_test';

        // When
        $actual = $this->storage->isPublicClient($clientId);

        // Then
        $this->assertFalse($actual);
    }

    /**
     * @test
     */
    public function isPublicClient_clientExists_isPublic(): void
    {
        // Given
        $clientId = 'client_test';

        $this->whitelabelOAuthClientFixture
            ->withWhitelabel($this->whitelabel)
            ->createOne([
                'client_id' => $clientId,
                'client_secret' => null
            ]);

        // When
        $actual = $this->storage->isPublicClient($clientId);

        // Then
        $this->assertTrue($actual);
    }

    /**
     * @test
     */
    public function isPublicClient_clientExists_isNotPublic(): void
    {
        // Given
        $clientId = 'client_test';

        $this->whitelabelOAuthClientFixture
            ->withWhitelabel($this->whitelabel)
            ->createOne([
                'client_id' => $clientId,
            ]);

        // When
        $actual = $this->storage->isPublicClient($clientId);

        // Then
        $this->assertFalse($actual);
    }

    /**
     * @test
     */
    public function getAuthorizationCode_codeNotExists(): void
    {
        // Given
        $code = 'test_code';

        // When
        $actual = $this->storage->getAuthorizationCode($code);

        // Then
        $this->assertFalse($actual);
    }

    /**
     * @test
     */
    public function getAuthorizationCode_codeExists(): void
    {
        // Given
        $clientId = 'client_test';
        $code = 'test_code';
        $idToken = 'id_token_test';

        $codeVerifier = $this->whitelabelOAuthAuthorizationCodeFixture->generateCodeVerifier();

        $whitelabelUser = $this->whitelabelUserFixture
            ->with(WhitelabelUserFixture::BASIC)
            ->createOne();

        $whitelabelOAuthClient = $this->whitelabelOAuthClientFixture
            ->withWhitelabel($this->whitelabel)
            ->createOne([
                'client_id' => $clientId
            ]);

        $authorizationCode = $this->whitelabelOAuthAuthorizationCodeFixture
            ->withWhitelabelOAuthClient($whitelabelOAuthClient)
            ->withWhitelabelUser($whitelabelUser)
            ->withCodeChallenge($codeVerifier)
            ->createOne([
                'authorization_code' => $code,
                'id_token' => $idToken
            ]);

        // When
        $actual = $this->storage->getAuthorizationCode($code);

        // Then
        $this->assertSame($authorizationCode['authorization_code'], $actual['authorization_code']);
        $this->assertSame($authorizationCode['client_id'], $actual['client_id']);
        $this->assertSame($authorizationCode['user_id'], $whitelabelUser->id);
        $this->assertSame($authorizationCode['redirect_uri'], $actual['redirect_uri']);
        $this->assertSame($idToken, $actual['id_token']);
        $this->assertSame($authorizationCode['code_challenge'], $actual['code_challenge']);
        $this->assertSame($authorizationCode['code_challenge_method'], $actual['code_challenge_method']);
    }

    /**
     * @test
     */
    public function expireAuthorizationCode_codeNotExists(): void
    {
        // Given
        $code = 'test_code';

        // When
        $actual = $this->storage->expireAuthorizationCode($code);

        // Then
        $this->assertFalse($actual);
    }

    /**
     * @test
     */
    public function expireAuthorizationCode_codeExists(): void
    {
        // Given
        $clientId = 'client_test';
        $code = 'test_code';
        $idToken = 'id_token_test';

        $codeVerifier = $this->whitelabelOAuthAuthorizationCodeFixture->generateCodeVerifier();

        $whitelabelUser = $this->whitelabelUserFixture
            ->with(WhitelabelUserFixture::BASIC)
            ->createOne();

        $whitelabelOAuthClient = $this->whitelabelOAuthClientFixture
            ->withWhitelabel($this->whitelabel)
            ->createOne([
                'client_id' => $clientId
            ]);

        $this->whitelabelOAuthAuthorizationCodeFixture
            ->withWhitelabelOAuthClient($whitelabelOAuthClient)
            ->withWhitelabelUser($whitelabelUser)
            ->withCodeChallenge($codeVerifier)
            ->createOne([
                'authorization_code' => $code,
                'id_token' => $idToken
            ]);

        // When
        $actual = $this->storage->expireAuthorizationCode($code);

        // Then
        $this->assertTrue($actual);
    }

    /**
     * @test
     */
    public function getAccessToken_accessTokenNotExists(): void
    {
        // Given
        $accessToken = 'test_access_token';

        // When
        $actual = $this->storage->getAccessToken($accessToken);

        // Then
        $this->assertFalse($actual);
    }

    /**
     * @test
     */
    public function getAccessToken_accessTokenExists(): void
    {
        // Given
        $clientId = 'client_test';
        $token = 'test_access_token';

        $whitelabelUser = $this->whitelabelUserFixture
            ->with(WhitelabelUserFixture::BASIC)
            ->createOne();

        $whitelabelOAuthClient = $this->whitelabelOAuthClientFixture
            ->withWhitelabel($this->whitelabel)
            ->createOne([
                'client_id' => $clientId
            ]);

        $accessToken = $this->whitelabelOAuthAccessTokenFixture
            ->withWhitelabelOAuthClient($whitelabelOAuthClient)
            ->withWhitelabelUser($whitelabelUser)
            ->createOne([
                'access_token' => $token,
            ]);

        // When
        $actual = $this->storage->getAccessToken($token);

        // Then
        $this->assertSame($accessToken['access_token'], $actual['access_token']);
        $this->assertSame($accessToken['client_id'], $actual['client_id']);
        $this->assertSame($accessToken['user_id'], $whitelabelUser->id);
        $this->assertSame($accessToken['scope'], $actual['scope']);
    }
}
