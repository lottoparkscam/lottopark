<?php

namespace Tests\Fixtures;

use Models\WhitelabelSocialApi;
use Models\WhitelabelUser;
use Models\WhitelabelUserSocial;

class WhitelabelUserSocialFixture extends AbstractFixture
{
    public function getDefaults(): array
    {
        return [
            'social_user_id' => $this->faker->md5(),
            'is_confirmed' => true,
            'activation_hash' => null,
            'last_hash_sent_at' => null,
        ];
    }

    public static function getClass(): string
    {
        return WhitelabelUserSocial::class;
    }

    public function withWhitelabelSocialApiId(WhitelabelSocialApi $whitelabelSocialApi): self
    {
        $this->with(function (WhitelabelUserSocial $whitelabelUserSocial) use ($whitelabelSocialApi) {
            $whitelabelUserSocial->whitelabelSocialApiId = $whitelabelSocialApi->id;
        });

        return $this;
    }

    public function withWhitelabelUser(WhitelabelUser $whitelabelUser): self
    {
        $this->with(function (WhitelabelUserSocial $whitelabelUserSocial) use ($whitelabelUser) {
            $whitelabelUserSocial->whitelabelUserId = $whitelabelUser->id;
        });

        return $this;
    }
}
