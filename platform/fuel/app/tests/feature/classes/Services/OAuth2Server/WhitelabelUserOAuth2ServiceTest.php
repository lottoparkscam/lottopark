<?php

namespace Tests\Feature\Services\OAuth2Server;

use Carbon\Carbon;
use Models\Whitelabel;
use Models\WhitelabelOAuthClient;
use OAuth2\OpenID\ResponseType\AuthorizationCode;
use OAuth2\Request;
use OAuth2\Response;
use Repositories\WhitelabelOAuthAuthorizationCodeRepository;
use Repositories\WhitelabelOAuthClientRepository;
use Services\Logs\FileLoggerService;
use Services\OAuth2Server\ServerFactory;
use Services\OAuth2Server\WhitelabelUserOAuth2Service;
use Services\OAuth2Server\WhiteLottoStorage;
use Test_Feature;
use Tests\Fixtures\WhitelabelOAuthAuthorizationCodeFixture;
use Tests\Fixtures\WhitelabelOAuthClientFixture;
use Tests\Fixtures\WhitelabelFixture;
use Tests\Fixtures\WhitelabelUserFixture;
use PHPUnit\Framework\MockObject\MockObject;

class WhitelabelUserOAuth2ServiceTest extends Test_Feature
{
    private const CODE_CHALLENGE_METHOD = 'S256';
    private Whitelabel $whitelabel;
    private FileLoggerService|MockObject $fileLoggerService;
    private WhiteLottoStorage $whiteLottoStorage;
    private WhitelabelFixture $whitelabelFixture;
    private WhitelabelUserFixture $whitelabelUserFixture;
    private WhitelabelOAuthClientFixture $whitelabelOAuthClientFixture;
    private WhitelabelOAuthAuthorizationCodeFixture $whitelabelOAuthAuthorizationCodeFixture;
    private WhitelabelOAuthAuthorizationCodeRepository $whitelabelOAuthAuthorizationCodeRepository;
    private string $codeVerifier;
    private string $codeChallenge;

    public function setUp(): void
    {
        parent::setUp();

        $this->whitelabel = $this->container->get('whitelabel');
        $this->whitelabelFixture = $this->container->get(WhitelabelFixture::class);
        $this->whitelabelUserFixture = $this->container->get(WhitelabelUserFixture::class);
        $this->whitelabelOAuthClientFixture = $this->container->get(WhitelabelOAuthClientFixture::class);
        $this->whitelabelOAuthAuthorizationCodeFixture = $this->container->get(WhitelabelOAuthAuthorizationCodeFixture::class);
        $whitelabelOAuthClientRepository = $this->container->get(WhitelabelOAuthClientRepository::class);
        $this->whitelabelOAuthAuthorizationCodeRepository = $this->container->get(WhitelabelOAuthAuthorizationCodeRepository::class);
        $serverFactory = $this->container->get(ServerFactory::class);
        $this->fileLoggerService = $this->createMock(FileLoggerService::class);
        $this->whiteLottoStorage = $this->container->get(WhiteLottoStorage::class);
        $this->whitelabelUserOAuth2ServiceUnderTest = new WhitelabelUserOAuth2Service(
            $serverFactory->create(),
            $whitelabelOAuthClientRepository,
            $this->fileLoggerService
        );

        $this->codeVerifier = $this->whitelabelOAuthAuthorizationCodeFixture->generateCodeVerifier();
        $this->codeChallenge = $this->whitelabelOAuthAuthorizationCodeFixture->generateCodeChallenge($this->codeVerifier);
    }

    /**
     * @test
     */
    public function handleAuthorizeRequest_requestWithoutOpenIdScope(): void
    {
        // Given
        $clientId = 'client_test';

        $this->whitelabelOAuthClientFixture
            ->withWhitelabel($this->whitelabel)
            ->createOne([
                'client_id' => $clientId
            ]);

        $whitelabelUser = $this->whitelabelUserFixture
            ->with(WhitelabelUserFixture::BASIC)
            ->createOne();

        $request = new Request([
            'client_id' => $clientId,
            'response_type' => 'code',
            'state' => '123',
            'code_challenge' => $this->codeChallenge,
            'code_challenge_method' => self::CODE_CHALLENGE_METHOD,
        ]);

        $this->fileLoggerService
            ->expects($this->once())
            ->method('error')
            ->willReturnCallback(function ($message) use ($clientId) {
                $this->assertSame(sprintf(
                    'Used Open ID connect but authorize request does not include required scope: openid. Client_id: "%s", Whitelabel ID: %s.',
                    $clientId,
                    $this->whitelabel->id
                ), $message);
                return false;
            });

        // When
        $this->whitelabelUserOAuth2ServiceUnderTest->setWhitelabelUser($whitelabelUser);
        $this->whitelabelUserOAuth2ServiceUnderTest->setOpenIdConnect();
        $valid = $this->whitelabelUserOAuth2ServiceUnderTest->validateAuthorizeRequest($request);

        /** @var Response $response */
        $response = $this->whitelabelUserOAuth2ServiceUnderTest->handleAuthorizeRequest($request);
        $actual = $this->whitelabelOAuthAuthorizationCodeRepository->findOneByClientIdAndUserId($clientId, $whitelabelUser->id);

        // Then
        $this->assertTrue($valid);
        $this->assertNotNull($actual);
        $this->assertNull($actual->idToken);
        $this->assertNull($actual->scope);
        $this->assertArrayHasKey('Location', $response->getHttpHeaders());
    }

    /**
     * @test
     */
    public function validateAuthorizeRequest_userTryingToAuthorizeToAWrongWhitelabel_validationFailed(): void
    {
        // Given
        $clientId1 = 'client_test_1';
        $clientId2 = 'client_test_2';
        $scope = 'openid';

        $this->whitelabelOAuthClientFixture
            ->withWhitelabel($this->whitelabel)
            ->createOne([
                'client_id' => $clientId1
            ]);

        $this->whitelabelOAuthClientFixture
            ->withWhitelabel($this->whitelabelFixture->createOne())
            ->createOne([
                'client_id' => $clientId2
            ]);

        $whitelabelUser = $this->whitelabelUserFixture
            ->with(WhitelabelUserFixture::BASIC)
            ->createOne();

        $request = new Request([
            'client_id' => $clientId2,
            'response_type' => 'code',
            'state' => '123',
            'code_challenge' => $this->codeChallenge,
            'code_challenge_method' => self::CODE_CHALLENGE_METHOD,
            'scope' => $scope
        ]);

        $this->fileLoggerService
            ->expects($this->once())
            ->method('error')
            ->willReturnCallback(function ($message) use ($clientId2) {
                $this->assertSame(sprintf(
                    'Client ID "%s" sent an authorization request to the wrong Whitelabel ID %s.',
                    $clientId2,
                    $this->whitelabel->id
                ), $message);
                return false;
            });

        // When
        $this->whitelabelUserOAuth2ServiceUnderTest->setWhitelabelUser($whitelabelUser);
        $this->whitelabelUserOAuth2ServiceUnderTest->setOpenIdConnect();
        $valid = $this->whitelabelUserOAuth2ServiceUnderTest->validateAuthorizeRequest($request);

        // Then
        $this->assertFalse($valid);
    }

    /**
     * @test
     */
    public function handleAuthorizeRequest_successfulResponse(): void
    {
        // Given
        $clientId = 'client_test';
        $scope = 'openid';

        $this->whitelabelOAuthClientFixture
            ->withWhitelabel($this->whitelabel)
            ->createOne([
                'client_id' => $clientId
            ]);

        $whitelabelUser = $this->whitelabelUserFixture
            ->with(WhitelabelUserFixture::BASIC)
            ->createOne();

        $request = new Request([
            'client_id' => $clientId,
            'response_type' => 'code',
            'state' => '123',
            'code_challenge' => $this->codeChallenge,
            'code_challenge_method' => self::CODE_CHALLENGE_METHOD,
            'scope' => $scope
        ]);

        $this->fileLoggerService
            ->expects($this->never())
            ->method('error');

        // When
        $this->whitelabelUserOAuth2ServiceUnderTest->setWhitelabelUser($whitelabelUser);
        $this->whitelabelUserOAuth2ServiceUnderTest->setOpenIdConnect();
        $valid = $this->whitelabelUserOAuth2ServiceUnderTest->validateAuthorizeRequest($request);

        /** @var Response $response */
        $response = $this->whitelabelUserOAuth2ServiceUnderTest->handleAuthorizeRequest($request);

        $actual = $this->whitelabelOAuthAuthorizationCodeRepository->findOneByClientIdAndUserId($clientId, $whitelabelUser->id);

        // Then
        $this->assertTrue($valid);
        $this->assertNotNull($actual);
        $this->assertNotNull($actual->idToken);
        $this->assertSame($whitelabelUser->id, $actual->userId);
        $this->assertSame($scope, $actual->scope);
        $this->assertTrue(Carbon::now()->lt($actual->expires));
        $this->assertArrayHasKey('Location', $response->getHttpHeaders());
    }

    /**
     * @test
     */
    public function handleTokenRequest_userHasNoAuthorizationCode_errorResponse(): void
    {
        // Given
        $clientId = 'client_test';
        $clientSecret = 'secret_test';
        $scope = 'openid';

        /** @var WhitelabelOAuthClient $whitelabelOAuthClient */
        $whitelabelOAuthClient = $this->whitelabelOAuthClientFixture
            ->withWhitelabel($this->whitelabel)
            ->createOne([
                'client_id' => $clientId,
                'client_secret' => $clientSecret
            ]);

        $whitelabelUser = $this->whitelabelUserFixture
            ->with(WhitelabelUserFixture::BASIC)
            ->createOne();

        $request = new Request(request: [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => 'authorization_code',
            'code' => 'test_code',
            'scope' => $scope,
            'redirect_uri' => $whitelabelOAuthClient->redirectUri,
            'code_verifier' => $this->codeVerifier
        ], server: ['REQUEST_METHOD' => 'POST']);

        $this->fileLoggerService
            ->expects($this->never())
            ->method('error');

        // When
        $this->whitelabelUserOAuth2ServiceUnderTest->setTokenRequestOpenIdGrantType();
        $valid = $this->whitelabelUserOAuth2ServiceUnderTest->validateTokenRequest($request);

        /** @var Response $response */
        $response = $this->whitelabelUserOAuth2ServiceUnderTest->handleTokenRequest($request);

        $authorizationCode = $this->whitelabelOAuthAuthorizationCodeRepository->findOneByClientIdAndUserId($clientId, $whitelabelUser->id);

        // Then
        $this->assertTrue($valid);
        $this->assertNull($authorizationCode);
        $this->assertSame('invalid_grant', $response->getParameter('error'));
        $this->assertSame("Authorization code doesn't exist or is invalid for the client", $response->getParameter('error_description'));
    }

    /**
     * @test
     */
    public function handleTokenRequest_successfulResponse(): void
    {
        // Given
        $clientId = 'client_test';
        $clientSecret = 'secret_test';
        $idToken = 'id_token_test';
        $scope = 'openid';

        /** @var WhitelabelOAuthClient $whitelabelOAuthClient */
        $whitelabelOAuthClient = $this->whitelabelOAuthClientFixture
            ->withWhitelabel($this->whitelabel)
            ->createOne([
                'client_id' => $clientId,
                'client_secret' => $clientSecret
            ]);

        $whitelabelUser = $this->whitelabelUserFixture
            ->with(WhitelabelUserFixture::BASIC)
            ->createOne();

        $responseType = new AuthorizationCode($this->whiteLottoStorage);

        $authorizationCode = $responseType->createAuthorizationCode(
            $clientId,
            $whitelabelUser->id,
            $whitelabelOAuthClient->redirectUri,
            $scope,
            $idToken,
            $this->codeChallenge,
            self::CODE_CHALLENGE_METHOD
        );

        $request = new Request(request: [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => 'authorization_code',
            'code' => $authorizationCode,
            'scope' => $scope,
            'redirect_uri' => $whitelabelOAuthClient->redirectUri,
            'code_verifier' => $this->codeVerifier
        ], server: ['REQUEST_METHOD' => 'POST']);

        $this->fileLoggerService
            ->expects($this->never())
            ->method('error');

        // When
        $this->whitelabelUserOAuth2ServiceUnderTest->setTokenRequestOpenIdGrantType();
        $valid = $this->whitelabelUserOAuth2ServiceUnderTest->validateTokenRequest($request);

        /** @var Response $response */
        $response = $this->whitelabelUserOAuth2ServiceUnderTest->handleTokenRequest($request);

        $usedAuthorizationCode = $this->whitelabelOAuthAuthorizationCodeRepository->findOneByClientIdAndUserId($clientId, $whitelabelUser->id);

        // Then
        $this->assertTrue($valid);
        $this->assertNull($usedAuthorizationCode);
        $this->assertNotNull($response->getParameter('access_token'));
        $this->assertSame(3600, $response->getParameter('expires_in'));
        $this->assertSame($scope, $response->getParameter('scope'));
        $this->assertSame($idToken, $response->getParameter('id_token'));
    }

    /**
     * @test
     */
    public function validateTokenRequest_userTryingToAuthorizeToAWrongWhitelabel_validationFailed(): void
    {
        // Given
        $clientId1 = 'client_test_1';
        $clientId2 = 'client_test_2';
        $client2Secret = 'secret_test_2';
        $scope = 'openid';

        $this->whitelabelOAuthClientFixture
            ->withWhitelabel($this->whitelabel)
            ->createOne([
                'client_id' => $clientId1
            ]);

        /** @var WhitelabelOAuthClient $client2 */
        $client2 = $this->whitelabelOAuthClientFixture
            ->withWhitelabel($this->whitelabelFixture->createOne())
            ->createOne([
                'client_id' => $clientId2
            ]);

        $request = new Request(request: [
            'client_id' => $clientId2,
            'client_secret' => $client2Secret,
            'grant_type' => 'authorization_code',
            'code' => 'test_code',
            'scope' => $scope,
            'redirect_uri' => $client2->redirectUri,
            'code_verifier' => $this->codeVerifier
        ], server: ['REQUEST_METHOD' => 'POST']);

        $this->fileLoggerService
            ->expects($this->once())
            ->method('error')
            ->willReturnCallback(function ($message) use ($clientId2) {
                $this->assertSame(sprintf(
                    'Client ID "%s" sent a token request to the wrong Whitelabel ID %s.',
                    $clientId2,
                    $this->whitelabel->id
                ), $message);
                return false;
            });

        // When
        $this->whitelabelUserOAuth2ServiceUnderTest->setTokenRequestOpenIdGrantType();
        $valid = $this->whitelabelUserOAuth2ServiceUnderTest->validateTokenRequest($request);

        // Then
        $this->assertFalse($valid);
    }
}
