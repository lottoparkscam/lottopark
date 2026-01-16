<?php

use Fuel\Core\Cache;
use Fuel\Core\Crypt;
use Fuel\Core\Session;
use Orm\Query;
use Helpers\Docker;
use Models\Whitelabel;
use Repositories\Orm\WhitelabelUserRepository;
use Services\Api\Reply;
use Models\WhitelabelApi;
use Models\WhitelabelUser;
use Services\Api\Security;
use Models\WhitelabelApiIp;
use Repositories\WhitelabelRepository;

abstract class Test_E2e_Controller_Api extends Test_Base
{
    const DOMAIN = 'lottopark.loc';
    const KEY = 'key';
    const SECRET = 'secret';

    private Reply $reply;
    private Security $security;
    protected Whitelabel $whitelabel;
    private WhitelabelApi $whitelabelApi;
    private WhitelabelApiIp $whitelabelApiIp;
    private WhitelabelUserRepository $whitelabelUserRepository;
    private WhitelabelRepository $whitelabelRepository;
    protected WhitelabelUser $whitelabelUser;
    protected array $responseHeaders;
    protected string $ip;

    public function setUp(): void
    {
        // It is necessary if you would like to use @runInSeparateProcess
        Cache::_init();
        Query::caching(false);

        $this->whitelabelApi = new WhitelabelApi([
            'whitelabel_id' => 1,
            'api_key' => 'key',
            'api_secret' => 'secret'
        ]);
        $this->whitelabelApi->save();

        $ip = Docker::getCurrentIp();
        $this->ip = $ip;

        $this->whitelabelApiIp = new WhitelabelApiIp([
            'whitelabel_id' => 1,
            'ip' => $ip
        ]);
        $this->whitelabelApiIp->save();

        $_SERVER['HTTP_HOST'] = $ip;

        /** @var WhitelabelRepository $whitelabelRepository */
        $whitelabelRepository = Container::get(WhitelabelRepository::class);

        $this->reply = new Reply();
        $this->security = new Security($whitelabelRepository);

        $modelWhitelabelApi = Model_Whitelabel_API::find([
            'where' => [
                'whitelabel_id' => 1,
            ]
        ]);

        if (empty($modelWhitelabelApi)) {
            $modelWhitelabelApi = new Model_Whitelabel_API();
            $modelWhitelabelApi->set([
                'whitelabel_id' => 1,
                'api_key' => 'key',
                'api_secret' => 'secret'
            ]);
            $modelWhitelabelApi->save();
        } else {
            $modelWhitelabelApi = $modelWhitelabelApi[0];
        }

        $modelWhitelabelApiIp = Model_Whitelabel_API_IP::find([
            'where' => [
                'whitelabel_id' => 1,
                'ip' => '127.0.0.1'
            ]
        ]);

        if (empty($modelWhitelabelApiIp)) {
            $modelWhitelabelApiIp = new Model_Whitelabel_API_IP();
            $modelWhitelabelApiIp->set([
                'whitelabel_id' => 1,
                'ip' => '127.0.0.1'
            ]);
            $modelWhitelabelApiIp->save();
        }

        $this->whitelabel = Whitelabel::find($modelWhitelabelApi['whitelabel_id']);

        $whitelabelUser = WhitelabelUser::find('first', [
            'where' => [
                'email' => 'test@user.loc'
            ]
        ]);

        if (empty($whitelabelUser)) {
            $factory_whitelabel_user = new Factory_Orm_Whitelabel_User();
            $whitelabelUser = $factory_whitelabel_user->build();
            $whitelabelUser->set('is_deleted', false);
            $whitelabelUser->save();
        }

        $this->whitelabelUser = $whitelabelUser;

        $this->whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);
        $this->whitelabelRepository = Container::get(WhitelabelRepository::class);

        parent::setUp();
    }

    /**
     * @param string $method
     * @param string $endpoint
     * @param array $guzzleOptions
     * @return mixed
     */
    protected function get_response_with_security_check(
        string $method,
        string $endpoint,
        array $guzzleOptions = []
    ) {
        $response = $this->getResponse($method, $endpoint, [], $guzzleOptions);

        $expectedResponse = $this->reply->buildResponseError(
            Reply::UNAUTHORIZED,
            ["Nonce, signature or key is not present"]
        );

        $this->assertEquals($expectedResponse['response'], $response['body']);

        $headers = [
            'X-Whitelotto-Key' => 'wrongkey',
            'X-Whitelotto-Nonce' => '123',
            'X-Whitelotto-Signature' => 'signature',
        ];

        $this->getResponse($method, $endpoint, $headers, $guzzleOptions);
        $response = $this->getResponse($method, $endpoint, $headers, $guzzleOptions);

        $expectedResponse = $this->reply->buildResponseError(
            Reply::UNAUTHORIZED,
            ["Bad API key"]
        );

        $this->assertEquals($expectedResponse['response'], $response['body']);

        $this->clear_nonce_log('123');
        $this->clear_nonce_log('123');

        $nonce = round(microtime(true) * 1000);

        $headers['X-Whitelotto-Nonce'] = $nonce;

        $response = $this->getResponse($method, $endpoint, $headers, $guzzleOptions);

        $expectedResponse = $this->reply->buildResponseError(
            Reply::UNAUTHORIZED,
            ["Bad API key"]
        );

        $this->assertEquals($expectedResponse['response'], $response['body']);

        $headers['X-Whitelotto-Key'] = self::KEY;

        $this->clear_nonce_log($nonce);
        $nonce = round(microtime(true) * 1000);

        $headers['X-Whitelotto-Nonce'] = $nonce;

        $response = $this->getResponse($method, $endpoint, $headers, $guzzleOptions);

        $expectedResponse = $this->reply->buildResponseError(
            Reply::UNAUTHORIZED,
            ["Bad signature"]
        );

        $this->assertEquals($expectedResponse['response'], $response['body']);

        $this->clear_nonce_log($nonce);
        $nonce = round(microtime(true) * 1000);

        $headers['X-Whitelotto-Nonce'] = $nonce;

        $query_array = [];
        $path = "";
        $parsedUrl = parse_url($endpoint);

        if (key_exists('query', $parsedUrl)) {
            $query = $parsedUrl['query'];
            $properties = explode('&', $query);

            $query_array = [];
            foreach ($properties as $property) {
                parse_str($property, $row);
                $query_array = array_merge($query_array, $row);
            }
        }

        if (key_exists('path', $parsedUrl)) {
            $path = $parsedUrl['path'];
        }

        $signature = $this->security->getChecksum($nonce, $query_array, self::SECRET, $path);
        $headers['X-Whitelotto-Signature'] = $signature;

        $response = $this->getResponse($method, $endpoint, $headers, $guzzleOptions);

        $this->clear_nonce_log($nonce);

        return $response['body'];
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->whitelabelApi->delete();
        $this->whitelabelApiIp->delete();
    }

    protected function getResponse(
        string $method,
        string $endpoint,
        array $headers = [],
        array $guzzleOptions = []
    ): array {
        $domain = $this->whitelabel->domain;
        $client = new GuzzleHttp\Client([
            'verify' => false,
            'http_errors' => false,
            'headers' => [
                'Host' => $domain,
                'Origin' => 'https://' . $domain,
            ]
        ]);

        $domain = self::DOMAIN;

        $parsedUrl = parse_url($endpoint);
        $path = "";
        $query = "";

        if (key_exists('path', $parsedUrl)) {
            $path = $parsedUrl['path'];
        }

        if (key_exists('query', $parsedUrl)) {
            $query = $parsedUrl['query'];
        }

        $url = "https://api.{$domain}{$path}.json?{$query}";

        $headersArray = [
            'headers' => $headers
        ];

        $requestOptions = $guzzleOptions + $headersArray;

        $res = $client->request($method, $url, $requestOptions);

        $this->responseHeaders = $res->getHeaders();
        $body = $res->getBody()->getContents();
        $bodyArray = json_decode($body, true);

        return [
            'body' => $bodyArray,
            'status' => $res->getStatusCode()
        ];
    }

    private function clear_nonce_log(int $nonce): void
    {
        $nonce = Model_Whitelabel_API_Nonce::find([
            'where' => [
                'nonce' => $nonce
            ]
        ]);

        $nonceExist = $nonce !== null && count($nonce) !== 0;

        if ($nonceExist) {
            $nonce[0]->delete();
        }
    }

    public function prepareAndGetLoggedUserHeaders(array $additionalSessionData = []): array
    {
        $whitelabel = $this->whitelabelRepository->findOneByTheme('LottoPark');
        $domainWithoutDots = str_replace('.', '', $whitelabel->domain);
        $userAgent = 'Example user agent';
        $_SERVER['HTTP_USER_AGENT'] = $userAgent;
        Session::set('user.email', $this->whitelabelUser->email);
        Session::set('user.token', $this->whitelabelUser->token);
        Session::set('user.hash', $this->whitelabelUser->hash);
        Session::set('user.remember', 1);

        foreach ($additionalSessionData as $key => $value) {
            Session::set($key, $value);
        }

        // session is the same for each test, so closed session could not be closed again
        try {
            Session::close();
        } catch (Throwable $throwable) {}

        $sessionId = urlencode(Crypt::encode(Session::key()));
        return [
            'Cookie' => "{$domainWithoutDots}_lottorsesid={$sessionId}",
            'User-agent' => $userAgent
        ];
    }
}
