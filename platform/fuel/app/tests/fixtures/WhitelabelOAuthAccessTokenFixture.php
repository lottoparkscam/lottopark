<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Carbon\Carbon;
use Models\WhitelabelOAuthAccessToken;
use Models\WhitelabelOAuthClient;
use Models\WhitelabelUser;

final class WhitelabelOAuthAccessTokenFixture extends AbstractFixture
{
    private const ACCESS_TOKEN_ALGORITHM = 'sha512';

    public function getDefaults(): array
    {
        return [
            'access_token' => $this->generateAccessToken(),
            'redirect_uri' => $this->faker->url(),
            'expires' => Carbon::now()->addSeconds(3600)->format('Y-m-d H:i:s'),
            'scope' => 'openid',
        ];
    }

    public static function getClass(): string
    {
        return WhitelabelOAuthAccessToken::class;
    }

    public function withWhitelabelOAuthClient(WhitelabelOAuthClient $client): self
    {
        $this->with(function (WhitelabelOAuthAccessToken $accessToken, array $attributes = []) use ($client) {
            $accessToken->clientId = $client->clientId;
        });

        return $this;
    }

    public function withWhitelabelUser(WhitelabelUser $whitelabelUser): self
    {
        $this->with(function (WhitelabelOAuthAccessToken $accessToken, array $attributes = []) use ($whitelabelUser) {
            $accessToken->userId = $whitelabelUser->id;
        });

        return $this;
    }

    private function generateAccessToken(): string
    {
        $randomData = random_bytes(20);

        if ($randomData !== false && strlen($randomData) === 20) {
            return bin2hex($randomData);
        }

        $randomData = mt_rand() . mt_rand() . mt_rand() . mt_rand() . microtime(true) . uniqid((string) mt_rand(), true);

        return substr(hash(self::ACCESS_TOKEN_ALGORITHM, $randomData), 0, 40);
    }
}
