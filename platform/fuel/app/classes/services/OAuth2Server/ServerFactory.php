<?php

declare(strict_types=1);

namespace Services\OAuth2Server;

use OAuth2\Server;

class ServerFactory
{
    /**
     * Proof Key for Code Exchange by OAuth Public Clients.
     *
     * @see https://www.rfc-editor.org/rfc/rfc7636
     */
    private bool $usesPKCE = true;

    /**
     * The lifetime in seconds of the access token.
     *
     * Default value is 1h
     *
     * @see https://www.rfc-editor.org/rfc/rfc6749#section-5
     */
    private int $accessTokenLifetime = 3600;

    /**
     * The lifetime in seconds of the refresh token.
     *
     * Default value is 14 days
     */
    private int $refreshTokenLifetime = 1209600;
    private WhiteLottoStorage $storage;

    public function __construct(WhiteLottoStorage $storage)
    {
        $this->storage = $storage;
    }

    public function create(): Server
    {
        return new Server($this->storage, [
            'enforce_pkce' => $this->usesPKCE,
            'access_lifetime' => $this->accessTokenLifetime,
            'refresh_token_lifetime' => $this->refreshTokenLifetime,
            'allow_public_clients' => false
        ]);
    }
}
