<?php

declare(strict_types=1);

namespace Services\OAuth2Server;

use Container;
use Models\Whitelabel;
use Models\WhitelabelUser;
use OAuth2\Encryption\EncryptionInterface;
use OAuth2\Encryption\Jwt;
use OAuth2\OpenID\GrantType\AuthorizationCode as OpenIDGrantTypeAuthorizationCode;
use OAuth2\OpenID\ResponseType\AuthorizationCode as OpenIDResponseTypeAuthorizationCode;
use OAuth2\OpenID\Storage\AuthorizationCodeInterface;
use OAuth2\RequestInterface;
use OAuth2\ResponseInterface;
use OAuth2\Server;
use OAuth2\Response;
use OAuth2\Storage\ClientInterface;
use Repositories\WhitelabelOAuthClientRepository;
use Services\Logs\FileLoggerService;
use RuntimeException;

class WhitelabelUserOAuth2Service
{
    private int $authorizationCodeLifetime = 60;
    private Whitelabel $whitelabel;
    private ?WhitelabelUser $whitelabelUser = null;
    private Server $server;
    private ResponseInterface $response;
    private string $openIdJwtAlgorithm = 'HS256';
    private EncryptionInterface $openIdJwtEncryptionUtil;
    private FileLoggerService $logger;
    private WhitelabelOAuthClientRepository $whitelabelOAuthClientRepository;

    public function __construct(
        Server $server,
        WhitelabelOAuthClientRepository $whitelabelOAuthClientRepository,
        FileLoggerService $logger
    ) {
        $this->whitelabel = Container::get('whitelabel');
        $this->server = $server;
        $this->response = new Response();
        $this->openIdJwtEncryptionUtil = new Jwt();
        $this->whitelabelOAuthClientRepository = $whitelabelOAuthClientRepository;
        $this->logger = $logger;
    }

    public function setWhitelabelUser(WhitelabelUser $whitelabelUser): void
    {
        $this->whitelabelUser = $whitelabelUser;
    }

    public function setOpenIdConnect(): void
    {
        if ($this->whitelabelUser === null) {
            throw new RuntimeException('WhitelabelUser is required with OpenID connect mode.');
        }

        $this->server->setConfig('use_openid_connect', true);

        /** @var ClientInterface $clientCredentialsStorage */
        $clientCredentialsStorage = $this->server->getStorage('client_credentials');
        /** @var AuthorizationCodeInterface $authorizationCodeStorage */
        $authorizationCodeStorage = $this->server->getStorage('authorization_code');

        $whitelabelUserIdTokenPayload = $this->getWhitelabelUserIdTokenPayload();

        $openIdIdTokenResponseType = new PartnerIdToken(
            $clientCredentialsStorage,
            $whitelabelUserIdTokenPayload,
            $this->openIdJwtAlgorithm,
            $this->openIdJwtEncryptionUtil
        );

        $openIdCodeResponseType = new OpenIDResponseTypeAuthorizationCode($authorizationCodeStorage, [
            'auth_code_lifetime' => $this->authorizationCodeLifetime,
        ]);

        $this->server->addResponseType($openIdIdTokenResponseType, 'id_token');
        $this->server->addResponseType($openIdCodeResponseType, 'code');
    }

    public function validateAuthorizeRequest(RequestInterface $request): bool
    {
        $clientId = $request->query('client_id');

        if ($clientId !== null && !$this->validateWhitelabelClient($clientId)) {
            $this->logger->error(sprintf(
                'Client ID "%s" sent an authorization request to the wrong Whitelabel ID %s.',
                $clientId,
                $this->whitelabel->id
            ));

            return false;
        }

        $this->validateOpenIdAuthorizeRequest($request);

        return $this->server->validateAuthorizeRequest($request, $this->response);
    }

    public function handleAuthorizeRequest(RequestInterface $request): ResponseInterface
    {
        $userId = $this->whitelabelUser?->id;

        return $this->server->handleAuthorizeRequest($request, $this->response, true, $userId);
    }

    public function setTokenRequestOpenIdGrantType(): void
    {
        /** @var AuthorizationCodeInterface $authorizationCodeStorage */
        $authorizationCodeStorage = $this->server->getStorage('authorization_code');

        $this->server->setConfig('use_openid_connect', true);
        $this->server->addGrantType(new OpenIDGrantTypeAuthorizationCode($authorizationCodeStorage));
    }

    public function validateTokenRequest(RequestInterface $request): bool
    {
        $clientId = $request->request('client_id');

        if ($clientId === null) {
            return false;
        }

        if (!$this->validateWhitelabelClient($clientId)) {
            $this->logger->error(sprintf(
                'Client ID "%s" sent a token request to the wrong Whitelabel ID %s.',
                $clientId,
                $this->whitelabel->id
            ));

            return false;
        }

        return true;
    }

    public function handleTokenRequest(RequestInterface $request): ResponseInterface
    {
        return $this->server->handleTokenRequest($request, $this->response);
    }

    /*
     * Whitelabel pre-validation before the main Authorize/Token Request
     * validation to check if the requested client_id belongs to Whitelabel
     */
    public function validateWhitelabelClient(string $clientId): bool
    {
        $whitelabelOAuthClient = $this->whitelabelOAuthClientRepository->findOneByClientId($clientId);

        if (!empty($whitelabelOAuthClient) && (int) $whitelabelOAuthClient['whitelabel_id'] !== $this->whitelabel->id) {
            $this->response->setError(400, 'invalid_client', 'The client credentials are invalid');

            return false;
        }

        return true;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    private function getWhitelabelUserIdTokenPayload(): array
    {
        if ($this->whitelabelUser === null) {
            return [];
        }

        return [
            'email' => $this->whitelabelUser->email,
            'name' => trim($this->whitelabelUser->name . ' ' . $this->whitelabelUser->surname),
            'token' => $this->whitelabelUser->getPrefixedToken(),
            'is_confirmed' => $this->whitelabelUser->isConfirmed
        ];
    }

    private function validateOpenIdAuthorizeRequest(RequestInterface $request): void
    {
        if ($this->server->getConfig('use_openid_connect')) {
            $scopeUtil = $this->server->getScopeUtil();
            $clientId = $request->query('client_id');
            $scope = $request->query('scope');

            if (!$scopeUtil->checkScope('openid', $scope)) {
                $this->logger->error(sprintf(
                    'Used Open ID connect but authorize request does not include required scope: openid. Client_id: "%s", Whitelabel ID: %s.',
                    $clientId,
                    $this->whitelabel->id,
                ));
            }
        }
    }
}
