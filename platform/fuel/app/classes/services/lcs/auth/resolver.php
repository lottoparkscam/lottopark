<?php

use Fuel\Core\Config;

/**
 * This class only prepares the most commonly used "credentials"
 * to establish LCS connection. It's generate simple dto
 * with fields (to be transformed to proper headers).
 */
class Services_Lcs_Auth_Resolver
{
    use Services_Api_Signature;

    public function issue(string $url, string $message = ''): Services_Lcs_Auth_Credentials
    {
        Config::load('lottery_central_server', true);
        $key = Config::get('lottery_central_server.sale_point.key');
        $nonce = $this->generate_nonce();
        $secret = Config::get('lottery_central_server.sale_point.secret');
        return new Services_Lcs_Auth_Credentials(
            $key,
            $secret,
            $this->build_signature($secret, $nonce, $url, $message),
            $nonce
        );
    }

    private function generate_nonce(): int
    {
        return microtime(true)*10000;
    }
}
