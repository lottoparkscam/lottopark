<?php

namespace Tests\Unit\Services\OAuth2Server;

use Models\Whitelabel;
use Models\WhitelabelUser;
use OAuth2\GrantType\GrantTypeInterface;
use OAuth2\OpenID\ResponseType\AuthorizationCode;
use OAuth2\OpenID\GrantType\AuthorizationCode as OpenIDGrantTypeAuthorizationCode;
use OAuth2\OpenID\Storage\AuthorizationCodeInterface;
use OAuth2\Request;
use OAuth2\RequestInterface;
use OAuth2\ResponseType\ResponseTypeInterface;
use OAuth2\Server;
use OAuth2\Storage\ClientInterface;
use Repositories\WhitelabelOAuthClientRepository;
use Services\Logs\FileLoggerService;
use Services\OAuth2Server\PartnerIdToken;
use Services\OAuth2Server\WhitelabelUserOAuth2Service;
use Tests\Fixtures\WhitelabelUserFixture;
use PHPUnit\Framework\MockObject\MockObject;
use Test_Unit;

class WhitelabelUserOAuth2ServiceTest extends Test_Unit
{
    private Server|MockObject $server;
    private FileLoggerService|MockObject $fileLoggerService;
    private WhitelabelOAuthClientRepository|MockObject $whitelabelOAuthClientRepository;
    private WhitelabelUserFixture $whitelabelUserFixture;
    private Whitelabel $whitelabel;

    public function setUp(): void
    {
        parent::setUp();

        $this->whitelabel = $this->container->get('whitelabel');
        $this->whitelabelUserFixture = $this->container->get(WhitelabelUserFixture::class);
        $this->whitelabelOAuthClientRepository = $this->createMock(WhitelabelOAuthClientRepository::class);
        $this->server = $this->createMock(Server::class);
        $this->fileLoggerService = $this->createMock(FileLoggerService::class);
        $this->whitelabelUserOAuth2ServiceUnderTest = new WhitelabelUserOAuth2Service(
            $this->server,
            $this->whitelabelOAuthClientRepository,
            $this->fileLoggerService
        );
    }

    /**
     * @test
     */
    public function setOpenIdConnectShouldThrowExceptionWithoutSetWhitelabelUser(): void
    {
        // Expect
        $this->expectExceptionMessage('WhitelabelUser is required with OpenID connect mode');

        // When
        $this->whitelabelUserOAuth2ServiceUnderTest->setOpenIdConnect();
    }

    /**
     * @test
     */
    public function setOpenIdConnectShouldGetValidServerConfiguration(): void
    {
        // Given
        /** @var WhitelabelUser $whitelabelUser */
        $whitelabelUser = $this->whitelabelUserFixture
            ->with(WhitelabelUserFixture::BASIC)
            ->makeOne();

        $this->mockServerGetStorage(2);

        $this->server
            ->expects($this->once())
            ->method('setConfig')
            ->with('use_openid_connect', true);

        // When
        $this->whitelabelUserOAuth2ServiceUnderTest->setWhitelabelUser($whitelabelUser);
        $this->whitelabelUserOAuth2ServiceUnderTest->setOpenIdConnect();
    }

    /**
     * @test
     */
    public function setOpenIdConnectShouldAddPartnerIdTokenAndAuthorizationCodeResponseTypes(): void
    {
        // Given
        /** @var WhitelabelUser $whitelabelUser */
        $whitelabelUser = $this->whitelabelUserFixture
            ->with(WhitelabelUserFixture::BASIC)
            ->makeOne();

        $this->mockServerGetStorage(2);

        $this->server
            ->expects($this->exactly(2))
            ->method('addResponseType')
            ->willReturnCallback(function (ResponseTypeInterface $responseType, $key) use ($whitelabelUser) {
                $this->assertContains($key, ['id_token', 'code']);

                if ($key === 'id_token') {
                    $this->assertInstanceOf(PartnerIdToken::class, $responseType);

                    /** @var PartnerIdToken $responseType */
                    $user = $responseType->getUser();

                    $this->assertSame($whitelabelUser->email, $user['email']);
                    $this->assertSame($whitelabelUser->getPrefixedToken(), $user['token']);
                    $whitelabelUserFullName = trim($whitelabelUser->name . ' ' . $whitelabelUser->surname);
                    $this->assertSame($whitelabelUserFullName, $user['name']);
                    $this->assertSame($whitelabelUser->isConfirmed, $user['is_confirmed']);
                }

                if ($key === 'code') {
                    $this->assertInstanceOf(AuthorizationCode::class, $responseType);
                }
            });

        // When
        $this->whitelabelUserOAuth2ServiceUnderTest->setWhitelabelUser($whitelabelUser);
        $this->whitelabelUserOAuth2ServiceUnderTest->setOpenIdConnect();
    }

    /**
     * @test
     */
    public function validateAuthorizeRequestFailedWithEmptyRequest(): void
    {
        // Given
        $request = new Request();

        $this->server
            ->expects($this->exactly(1))
            ->method('validateAuthorizeRequest')
            ->willReturn(false);

        $this->fileLoggerService
            ->expects($this->never())
            ->method('error');

        // When
        $result = $this->whitelabelUserOAuth2ServiceUnderTest->validateAuthorizeRequest($request);

        // Then
        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function validateAuthorizeRequestWithSuccessfulWhitelabelValidation(): void
    {
        // Given
        $clientId = 'client_test';
        $request = new Request(['client_id' => $clientId]);

        $this->whitelabelOAuthClientRepository
            ->expects($this->once())
            ->method('findOneByClientId')
            ->with($clientId)
            ->willReturn(['whitelabel_id' => $this->whitelabel->id]);

        $this->server
            ->expects($this->once())
            ->method('validateAuthorizeRequest')
            ->willReturn(true);

        // When
        $result = $this->whitelabelUserOAuth2ServiceUnderTest->validateAuthorizeRequest($request);

        // Then
        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function validateAuthorizeRequestWithFailedWhitelabelValidation(): void
    {
        // Given
        $clientId = 'client_test';
        $request = new Request(['client_id' => $clientId]);

        $this->whitelabelOAuthClientRepository
            ->expects($this->once())
            ->method('findOneByClientId')
            ->with($clientId)
            ->willReturn(['whitelabel_id' => 99]);

        $this->server
            ->expects($this->never())
            ->method('validateAuthorizeRequest');

        $this->fileLoggerService
            ->expects($this->once())
            ->method('error')
            ->willReturnCallback(function ($message) use ($clientId) {
                $this->assertSame(sprintf(
                    'Client ID "%s" sent an authorization request to the wrong Whitelabel ID %s.',
                    $clientId,
                    $this->whitelabel->id
                ), $message);
                return false;
            });

        // When
        $result = $this->whitelabelUserOAuth2ServiceUnderTest->validateAuthorizeRequest($request);

        // Then
        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function handleAuthorizeRequestWithoutWhitelabelUser(): void
    {
        // Given
        $clientId = 'client_test';
        $request = new Request(['client_id' => $clientId]);

        $this->server
            ->expects($this->once())
            ->method('handleAuthorizeRequest')
            ->willReturnCallback(function ($request, $response, $is_authorized, $user_id = null) use ($clientId) {
                $this->assertSame($request->query('client_id'), $clientId);
                $this->assertTrue($is_authorized);
                $this->assertNull($user_id);

                return $response;
            });

        // When
        $this->whitelabelUserOAuth2ServiceUnderTest->handleAuthorizeRequest($request);
    }

    /**
     * @test
     */
    public function handleAuthorizeRequestWithWhitelabelUser(): void
    {
        // Given
        /** @var WhitelabelUser $whitelabelUser */
        $whitelabelUser = $this->whitelabelUserFixture
            ->with(WhitelabelUserFixture::BASIC)
            ->makeOne();

        $clientId = 'client_test';
        $request = new Request(['client_id' => $clientId]);

        $this->server
            ->expects($this->once())
            ->method('handleAuthorizeRequest')
            ->willReturnCallback(function (RequestInterface $request, $response, $is_authorized, $user_id = null) use ($whitelabelUser, $clientId) {
                $this->assertSame($request->query('client_id'), $clientId);
                $this->assertTrue($is_authorized);
                $this->assertSame($whitelabelUser->id, $user_id);

                return $response;
            });

        // When
        $this->whitelabelUserOAuth2ServiceUnderTest->setWhitelabelUser($whitelabelUser);
        $this->whitelabelUserOAuth2ServiceUnderTest->handleAuthorizeRequest($request);
    }

    /**
     * @test
     */
    public function setTokenRequestOpenIdGrantTypeShouldGetValidServerConfiguration(): void
    {
        $this->mockServerGetStorage(1);

        $this->server
            ->expects($this->once())
            ->method('setConfig')
            ->with('use_openid_connect')
            ->willReturnCallback(function ($name, $value) {
                $this->assertTrue($value);
            });

        // When
        $this->whitelabelUserOAuth2ServiceUnderTest->setTokenRequestOpenIdGrantType();
    }

    /**
     * @test
     */
    public function setTokenRequestOpenIdGrantTypeShouldAddAuthorizationCodeGrantType(): void
    {
        $this->mockServerGetStorage(1);

        $this->server
            ->expects($this->exactly(1))
            ->method('addGrantType')
            ->willReturnCallback(function (GrantTypeInterface $grantType, $identifier) {
                $this->assertInstanceOf(OpenIDGrantTypeAuthorizationCode::class, $grantType);
                $this->assertNull($identifier);
            });

        // When
        $this->whitelabelUserOAuth2ServiceUnderTest->setTokenRequestOpenIdGrantType();
    }

    /**
     * @test
     */
    public function validateTokenRequestFailedWithEmptyRequest(): void
    {
        // Given
        $request = new Request();

        $this->fileLoggerService
            ->expects($this->never())
            ->method('error');

        // When
        $result = $this->whitelabelUserOAuth2ServiceUnderTest->validateTokenRequest($request);

        // Then
        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function validateTokenRequestWithFailedWhitelabelValidation(): void
    {
        // Given
        $clientId = 'client_test';
        $request = new Request(request: ['client_id' => $clientId]);

        $this->whitelabelOAuthClientRepository
            ->expects($this->once())
            ->method('findOneByClientId')
            ->with($clientId)
            ->willReturn(['whitelabel_id' => 99]);

        $this->fileLoggerService
            ->expects($this->once())
            ->method('error')
            ->willReturnCallback(function ($message) use ($clientId) {
                $this->assertSame(sprintf(
                    'Client ID "%s" sent a token request to the wrong Whitelabel ID %s.',
                    $clientId,
                    $this->whitelabel->id
                ), $message);
                return false;
            });

        // When
        $result = $this->whitelabelUserOAuth2ServiceUnderTest->validateTokenRequest($request);

        // Then
        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function validateTokenRequestWithSuccessfulWhitelabelValidation(): void
    {
        // Given
        $clientId = 'client_test';
        $request = new Request(request: ['client_id' => $clientId]);

        $this->whitelabelOAuthClientRepository
            ->expects($this->once())
            ->method('findOneByClientId')
            ->with($clientId)
            ->willReturn(['whitelabel_id' => $this->whitelabel->id]);

        // When
        $result = $this->whitelabelUserOAuth2ServiceUnderTest->validateTokenRequest($request);

        // Then
        $this->assertTrue($result);
    }

    private function mockServerGetStorage(int $expectsExactlyCount): void
    {
        $this->server
            ->expects($this->exactly($expectsExactlyCount))
            ->method('getStorage')
            ->willReturnCallback(function (string $name) {
                if ($name === 'client_credentials') {
                    return new class implements ClientInterface {
                        public function getClientDetails($client_id)
                        {
                        }

                        public function getClientScope($client_id)
                        {
                        }

                        public function checkRestrictedGrantType($client_id, $grant_type)
                        {
                        }
                    };
                }

                if ($name === 'authorization_code') {
                    return new class implements AuthorizationCodeInterface {
                        public function getAuthorizationCode($code)
                        {
                        }

                        public function expireAuthorizationCode($code)
                        {
                        }

                        public function setAuthorizationCode($code, $client_id, $user_id, $redirect_uri, $expires, $scope = null, $id_token = null, $code_challenge = null, $code_challenge_method = null)
                        {
                        }
                    };
                }
            });
    }
}
