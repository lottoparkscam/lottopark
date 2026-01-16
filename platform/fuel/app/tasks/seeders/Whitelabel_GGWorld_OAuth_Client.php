<?php

namespace Fuel\Tasks\Seeders;

/**
* Whitelabel GGWorld OAuth Client seeder.
*/
final class Whitelabel_GGWorld_OAuth_Client extends Seeder
{
    private string $clientName = 'GG World';
    private string $redirectUri = '/auth/social-login/partner/callback';
    private string $autoLoginUri = '/auth/social-login/partner/auto-login';
    private string $grantTypes = 'authorization_code';
    private string $scope = 'openid balance-transfer';

    protected function columnsStaging(): array
    {
        return [
            'whitelabel_oauth_client' => ['client_id', 'whitelabel_id', 'name', 'domain', 'autologin_uri', 'client_secret', 'redirect_uri', 'scope', 'grant_types']
        ];
    }

    protected function rowsStaging(): array
    {
        $baseUrl = 'https://lottopark.ggworld.work';
        $domain = 'ggworld.lottopark.work';

        $clientId = $this->generateRandomString();
        $clientSecret = $this->generateRandomString();
        $redirectUri = $baseUrl . $this->redirectUri;
        $autoLoginUri = $baseUrl . $this->autoLoginUri;

        return [
            'whitelabel_oauth_client' => [
                [$clientId, 1, $this->clientName, $domain, $autoLoginUri, $clientSecret, $redirectUri, $this->scope, $this->grantTypes],
            ]
        ];
    }

    protected function rowsProduction(): array
    {
        $baseUrl = 'https://ggworld.lottopark.com';
        $domain = 'ggworld.lottopark.com';

        $clientId = $this->generateRandomString();
        $clientSecret = $this->generateRandomString();
        $redirectUri = $baseUrl . $this->redirectUri;
        $autoLoginUri = $baseUrl . $this->autoLoginUri;

        return [
            'whitelabel_oauth_client' => [
                [$clientId, 1, $this->clientName, $domain, $autoLoginUri, $clientSecret, $redirectUri, $this->scope, $this->grantTypes],
            ]
        ];
    }

    private function generateRandomString(): string
    {
        return bin2hex(random_bytes(32));
    }
}
