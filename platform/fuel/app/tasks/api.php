<?php

namespace Fuel\Tasks;

use Container;
use Fuel\Core\Cli;
use Models\Whitelabel;
use Models\WhitelabelApi;
use Services\Api\Security;
use Repositories\WhitelabelRepository;
use Task_Cli;

final class Api extends Task_Cli
{
    private WhitelabelRepository $whitelabelRepository;

    public function __construct()
    {
        $this->whitelabelRepository = Container::get(WhitelabelRepository::class);
    }

    /**
     * Generate signature to whitelabel
     * @param string $domain
     * @param string $url
     */
    public function generate_signature(string $domain, string $url): void
    {
        $nonce = round(microtime(true) * 1000);

        $_SERVER['HTTP_HOST'] = '127.0.0.1';

        $security = new Security($this->whitelabelRepository);

        $whitelabel = $this->whitelabelRepository->findOneByDomain($domain);

        if (!$whitelabel) {
            echo "Whitelabel not exist";
            return;
        }

        $whitelabel_api = $security->getWhitelabelApi('key', $whitelabel);

        if (!$whitelabel_api) {
            echo "Whitelabel_API not exist";
            return;
        }

        $api_secret = $whitelabel_api[0]['api_secret'];
        $parsed_url = parse_url($url);
        $path = "";
        $query = "";

        if (key_exists('path', $parsed_url)) {
            $path = $parsed_url['path'];
        }

        if (key_exists('query', $parsed_url)) {
            $query = $parsed_url['query'];
        }

        $path = explode('.', $path);
        $path = $path[0];

        parse_str($query, $query_array);

        $signature = $security->getChecksum($nonce, $query_array, $api_secret, $path);

        echo "\r\nSignature: {$signature}\r\nNonce: {$nonce}\r\n";
    }

    /**
     * Create record in whitelabel_api with new key and secret
     * @param string $domain
     * @throws \Exception
     */
    public function create_or_update_credentials(string $domain): void
    {
        $whitelabel = Whitelabel::find('first', [
            'where' => [
                'domain' => $domain
            ]
        ]);

        if (empty($whitelabel)) {
            Cli::write("Whitelabel doesn't exist.");
            return;
        }

        $key = substr(urlencode(base64_encode(md5(time()))), 0, 24);
        $secretHash = md5(time()) . sha1(uniqid());
        $secret = substr(urlencode(base64_encode(hash_hmac('sha256', $secretHash, $key))), 0, 64);

        $whitelabelApi = WhitelabelApi::find('first', [
            'where' => [
                'whitelabel_id' => $whitelabel->id
            ]
        ]);

        if (empty($whitelabelApi)) {
            $whitelabelApi = new WhitelabelApi();
        }

        $whitelabelApi->set([
            'api_key' => $key,
            'api_secret' => $secret,
            'whitelabel' => $whitelabel
        ]);
        $whitelabelApi->save();

        Cli::write('Key and secret have been created');
    }
}
