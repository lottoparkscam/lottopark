<?php

namespace Fuel\Tasks\Seeders;

use Models\SocialType;

final class AddFacebookSocialType extends Seeder
{
    protected function columnsStaging(): array
    {
        return [SocialType::table() => ['type']];
    }

    protected function rowsStaging(): array
    {
        return [SocialType::table() => [SocialType::FACEBOOK_TYPE]];
    }
}
