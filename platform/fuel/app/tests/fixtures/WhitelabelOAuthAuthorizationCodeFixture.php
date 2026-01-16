<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Carbon\Carbon;
use Models\WhitelabelOAuthAuthorizationCode;
use Models\WhitelabelOAuthClient;
use Models\WhitelabelUser;

final class WhitelabelOAuthAuthorizationCodeFixture extends AbstractFixture
{
    private const CODE_CHALLENGE_METHOD = 'S256';
    private const AUTHORIZATION_CODE_ALGORITHM = 'sha512';
    private const CODE_CHALLENGE_METHOD_ALGORITHM = 'sha256';

    public function getDefaults(): array
    {
        return [
            'authorization_code' => $this->generateAuthorizationCode(),
            'redirect_uri' => $this->faker->url(),
            'expires' => Carbon::now()->addSeconds(30)->format('Y-m-d H:i:s'),
            'scope' => 'openid',
            'id_token' => null,
        ];
    }

    public static function getClass(): string
    {
        return WhitelabelOAuthAuthorizationCode::class;
    }

    public function withWhitelabelOAuthClient(WhitelabelOAuthClient $client): self
    {
        $this->with(function (WhitelabelOAuthAuthorizationCode $authorizationCode, array $attributes = []) use ($client) {
            $authorizationCode->clientId = $client->clientId;
        });

        return $this;
    }

    public function withWhitelabelUser(WhitelabelUser $whitelabelUser): self
    {
        $this->with(function (WhitelabelOAuthAuthorizationCode $authorizationCode, array $attributes = []) use ($whitelabelUser) {
            $authorizationCode->userId = $whitelabelUser->id;
        });

        return $this;
    }

    public function withCodeChallenge(string $codeVerifier): self
    {
        $this->with(function (WhitelabelOAuthAuthorizationCode $authorizationCode, array $attributes = []) use ($codeVerifier) {
            $authorizationCode->codeChallenge = $this->generateCodeChallenge($codeVerifier);
            $authorizationCode->codeChallengeMethod = self::CODE_CHALLENGE_METHOD;
        });

        return $this;
    }

    public function generateCodeVerifier(): string
    {
        $verifierBytes = random_bytes(80);

        return rtrim(strtr(base64_encode($verifierBytes), '+/', '-_'), '=');
    }

    public function generateCodeChallenge(string $codeVerifier): string
    {
        $challengeBytes = hash(self::CODE_CHALLENGE_METHOD_ALGORITHM, $codeVerifier, true);

        return rtrim(strtr(base64_encode($challengeBytes), '+/', '-_'), '=');
    }

    private function generateAuthorizationCode(): string
    {
        $randomBytes = random_bytes(100);

        return substr(hash(self::AUTHORIZATION_CODE_ALGORITHM, $randomBytes), 0, 40);
    }
}
