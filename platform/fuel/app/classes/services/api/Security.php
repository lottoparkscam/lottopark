<?php

namespace Services\Api;

use Fuel\Core\Input;
use Lotto_Helper;
use Lotto_Security;
use Models\Whitelabel;
use Model_Whitelabel_API;
use Model_Whitelabel_API_IP;
use Model_Whitelabel_API_Nonce;
use Repositories\WhitelabelRepository;

final class Security
{
    private array $domain;

    private WhitelabelRepository $whitelabelRepository;

    public function __construct(WhitelabelRepository $whitelabelRepository)
    {
        $domain = $_SERVER['HTTP_HOST'];
        $this->domain = explode('.', $domain);
        $this->whitelabelRepository = $whitelabelRepository;
    }

    /**
     * @return bool
     */
    public function isApiRouteNotAccessible(): bool
    {
        return !Lotto_Helper::allow_access("api");
    }

    /**
     * @return bool
     */
    public function checkUrlNotBeginFromApi(): bool
    {
        /** @var array $domain */
        $domain = $this->domain;

        return $domain[0] !== "api";
    }

    /**
     * @return string
     */
    public function getDomainWithoutApiPrefix(): string
    {
        $domain = $this->domain;
        array_shift($domain);
        $domain = implode('.', $domain);
        return $domain;
    }

    /**
     * @param string $domain
     * @return string
     */
    public function prepareDomainWithoutApiPrefix(string $domain): string
    {
        $domain = explode('.', $domain);
        array_shift($domain);
        $domain = implode('.', $domain);
        return $domain;
    }

    public function getWhitelabel(): ?Whitelabel
    {
        $domain = $this->getDomainWithoutApiPrefix();
        return $this->whitelabelRepository->findOneByDomain($domain);
    }

    /**
     * @param Whitelabel $whitelabel
     * @return bool
     */
    public function checkIpNotExist(Whitelabel $whitelabel): bool
    {
        $whitelabelApiIp = Model_Whitelabel_API_IP::find([
            "where" => [
                "whitelabel_id" => $whitelabel->id,
                "ip" => Lotto_Security::get_IP()
            ]
        ]);

        return empty($whitelabelApiIp);
    }

    /**
     * @param string $nonce
     * @param Whitelabel $whitelabel
     * @return bool
     */
    public function checkNonceExist(string $nonce, Whitelabel $whitelabel): bool
    {
        $whitelabelApiNonce = Model_Whitelabel_API_Nonce::find([
            "where" => [
                "whitelabel_id" => $whitelabel->id,
                "nonce" => $nonce
            ]
        ]);

        return !empty($whitelabelApiNonce);
    }

    /**
     * @param string $key
     * @param Whitelabel $whitelabel
     * @return Model_Whitelabel_API[]
     */
    public function getWhitelabelApi(string $key, Whitelabel $whitelabel): array
    {
        return Model_Whitelabel_API::find([
            "where" => [
                "whitelabel_id" => $whitelabel->id,
                "api_key" => $key
            ]
        ]);
    }

    /**
     * @param string $key
     * @param Whitelabel $whitelabel
     * @return bool
     */
    public function isWhitelabelWithApiKeyNotExist(string $key, Whitelabel $whitelabel): bool
    {
        $whitelabelApi = Model_Whitelabel_API::find([
            "where" => [
                "whitelabel_id" => $whitelabel->id,
                "api_key" => $key
            ]
        ]);

        return empty($whitelabelApi);
    }

    /**
     * @param string $nonce
     * @param array $get
     * @param string $apiSecret
     * @param string $uri
     * @return string
     */
    public function getChecksum(string $nonce, array $get, string $apiSecret, string $uri = ""): string
    {
        if ($uri === "") {
            $uri = Input::Uri();
        }

        if ($uri === '/api/error/404') {
            // if fuel not find specific controller it redirects to /api/error/404
            // in this case Uri returns /api/error/404 but signature was created to other uri
            // we cant in this case show not found error because we cannot identify request correctly
            // we need to get original request uri and remove from it get data and response format e.g. .json
            $uri = $_SERVER['REQUEST_URI'];
            $uriGetValues = explode( '?', $uri);
            array_pop( $uriGetValues);
            $uri = implode( '?', $uriGetValues);
            $uriResponseFormat = explode('.', $uri);
            array_pop($uriResponseFormat);
            $uri = implode('.', $uriResponseFormat);
        }

        // convert all values to string
        $get = array_map(function($value) {
            return (string)$value;
        }, $get);

        // remove url from get
        $newGet = [];

        foreach ($get as $key => $value) {
            if (strpos($key, '/api') === false) {
                $newGet[$key] = $value;
            }
        }

        $checksum = hash_hmac(
            "sha512",
            $uri . $nonce . hash("sha256", json_encode($newGet)),
            $apiSecret
        );

        return $checksum;
    }
}