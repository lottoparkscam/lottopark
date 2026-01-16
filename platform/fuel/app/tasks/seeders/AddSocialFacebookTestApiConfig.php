<?php

namespace Fuel\Tasks\Seeders;

use Models\WhitelabelSocialApi;

final class AddSocialFacebookTestApiConfig extends Seeder
{
    protected function columnsStaging(): array
    {
        return [WhitelabelSocialApi::table() => ['whitelabel_id', 'app_id', 'secret', 'social_type_id', 'is_enabled']];
    }

    protected function rowsStaging(): array
    {
        return [WhitelabelSocialApi::table() => [1, '827910645162206', '5464387ee51534bb1d7d4a7b3d19e05b', 1, false]];
    }

    protected function columnsDevelopment(): array
    {
        return [WhitelabelSocialApi::table() => ['whitelabel_id', 'app_id', 'secret', 'social_type_id', 'is_enabled']];
    }

    protected function rowsDevelopment(): array
    {
        return [WhitelabelSocialApi::table() => [1, '827910645162206', '5464387ee51534bb1d7d4a7b3d19e05b', 1, true]];
    }

    protected function columnsProduction(): array
    {
        return [];
    }

    protected function rowsProduction(): array
    {
        return [];
    }
}