<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Models\Whitelabel;
use Models\WhitelabelOAuthClient;

final class WhitelabelOAuthClientFixture extends AbstractFixture
{
    public const WHITELABEL = 'whitelabel';

    public function getDefaults(): array
    {
        return [
            'client_id' => $this->generateRandomString(),
            'name' => $this->faker->name(),
            'domain' => $this->faker->domainName(),
            'client_secret' => $this->generateRandomString(),
            'redirect_uri' => $this->faker->url(),
            'autologin_uri' => $this->faker->url(),
            'grant_types' => 'authorization_code',
            'scope' => 'openid',
            'created_at' => $this->faker->dateTime()->format('Y-m-d H:i:s'),
        ];
    }

    public static function getClass(): string
    {
        return WhitelabelOAuthClient::class;
    }

    public function getStates(): array
    {
        return [
            self::WHITELABEL => $this->reference('whitelabel', WhitelabelFixture::class),
        ];
    }

    public function withWhitelabel(Whitelabel $whitelabel): self
    {
        $this->with(function (WhitelabelOAuthClient $oAuthClient, array $attributes = []) use ($whitelabel) {
            $oAuthClient->whitelabelId = $whitelabel->id;
        });
        return $this;
    }

    private function generateRandomString(): string
    {
        return bin2hex(random_bytes(32));
    }
}
