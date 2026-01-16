<?php

declare(strict_types=1);

namespace Services\OAuth2Server;

use OAuth2\Encryption\EncryptionInterface;
use OAuth2\OpenID\ResponseType\IdTokenInterface;
use OAuth2\Storage\ClientInterface;
use RuntimeException;

/**
 * Custom IdToken class that returns user data in the required PartnerProvider format
 *
 * @see https://gginternational.slite.com/app/docs/dlhlL75F3h1vAz
 */
class PartnerIdToken implements IdTokenInterface
{
    public function __construct(
        private ClientInterface $client,
        private array $user,
        private string $encryptionAlgorithm,
        private EncryptionInterface $encryptionUtil
    ) {
    }

    public function getUser(): array
    {
        return $this->user;
    }

    /**
     * @throws RuntimeException
     */
    public function createIdToken($client_id, $userInfo, $nonce = null, $userClaims = null, $access_token = null): string
    {
        $details = $this->client->getClientDetails($client_id);

        if (!$details || empty($details['client_secret'])) {
            throw new RuntimeException('Invalid client.');
        }

        $this->validateUser();

        $secret = $details['client_secret'];

        $payload = [
            'token' => $this->user['token'],
            'email' => $this->user['email'],
            'name' => $this->user['name'] ?? null,
            'is_confirmed' => $this->user['is_confirmed'] ?? false,
        ];

        return $this->encryptionUtil->encode($payload, $secret, $this->encryptionAlgorithm);
    }

    public function getAuthorizeResponse($params, $user_id = null): array
    {
        return [];
    }

    /**
     * @throws RuntimeException
     */
    private function validateUser(): void
    {
        $requiredFields = ['token', 'email'];

        foreach ($requiredFields as $field) {
            if (empty($this->user[$field])) {
                throw new RuntimeException(sprintf(
                    'Missing required user field "%s".',
                    $field
                ));
            }
        }
    }
}
