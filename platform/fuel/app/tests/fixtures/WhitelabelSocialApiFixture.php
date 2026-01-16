<?php

namespace Tests\Fixtures;

use Models\SocialType;
use Models\Whitelabel;
use Models\WhitelabelSocialApi;

class WhitelabelSocialApiFixture extends AbstractFixture
{
    public function getDefaults(): array
    {
        return [
            'app_id' => $this->faker->isbn13(), //app_id is a number in string. isbn13 returns a barcode that is the same as the app_id.
            'secret' => $this->faker->md5(),
            'is_enabled' => true,
        ];
    }

    public static function getClass(): string
    {
        return WhitelabelSocialApi::class;
    }

    public function withWhitelabel(Whitelabel $whitelabel): callable
    {
        $this->with(function (WhitelabelSocialApi $whitelabelSocialApi) use ($whitelabel) {
            $whitelabelSocialApi->whitelabelId = $whitelabel->id;
        });

        return $this;
    }

    public function withSocialType(SocialType $socialType): callable
    {
        $this->with(function (WhitelabelSocialApi $whitelabelSocialApi) use ($socialType) {
            $whitelabelSocialApi->socialTypeId = $socialType->id;
        });

        return $this;
    }
}
