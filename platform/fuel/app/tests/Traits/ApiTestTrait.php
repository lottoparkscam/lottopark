<?php

namespace Tests\Traits;

use Container;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Fuel\Core\Cli;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Services\HttpService;

trait ApiTestTrait
{
    protected string $post = 'POST';
    protected string $get = 'GET';
    protected string $patch = 'PATCH';

    protected function getHttpClient(): Client
    {
        /** @var HttpService $httpService */
        $httpService = Container::get(HttpService::class);

        $onRedirect = function (
            RequestInterface $request,
            ResponseInterface $response,
            UriInterface $uri
        ) {
            $boldBlue = "\e[1m \e[34m";
            Cli::write($boldBlue . 'Redirecting! ' . $request->getUri() . ' to ' . $uri . "\n",);
        };

        return $httpService->getClient([
            RequestOptions::COOKIES => true,
            RequestOptions::VERIFY => false,
            RequestOptions::ALLOW_REDIRECTS => [
                'max'             => 10,        // allow at most 10 redirects.
                'strict'          => true,      // use "strict" RFC compliant redirects.
                'referer'         => true,      // add a Referer header
                'protocols'       => ['https'], // only allow https URLs
                'on_redirect'     => $onRedirect,
                'track_redirects' => true
            ]
        ]);
    }

    protected function getResponse(Client $client, string $method, string $url, array $params): ResponseInterface
    {
        return $client->request($method, $url, $params);
    }

    protected function assertResponseStatusCode(ResponseInterface $response, int $expectedStatusCode): void
    {
        $this->assertSame(
            $expectedStatusCode,
            $response->getStatusCode(),
            'Received response code is not as expected.'
        );
    }

    protected function assertRedirectCount(ResponseInterface $response, int $expectedCount): void
    {
        $this->assertCount($expectedCount, $response->getHeader('X-Guzzle-Redirect-History'));
    }

    protected function assertRedirects(ResponseInterface $response, array $expectedRedirects): void
    {
        $this->assertSame($expectedRedirects, $response->getHeader('X-Guzzle-Redirect-History'));
    }

    protected function assertRedirectStatusCode(ResponseInterface $response, string $url, int $expectedCode): void
    {
        $redirects = $response->getHeader('X-Guzzle-Redirect-History');
        $numberOfRedirect = array_search($url, $redirects);
        if (!$numberOfRedirect) {
            $this->assertFalse(false, "Redirect to provided $url has not happened!");
        }

        $this->assertSame(
            $expectedCode,
            (int) $response->getHeader('X-Guzzle-Redirect-Status-History')[$numberOfRedirect],
            'Received response code for provided redirect is not as expected.'
        );
    }

    protected function assertRedirectTo(ResponseInterface $response, string $expectedUrl): void
    {
        $this->assertSame(
            $response->getHeaderLine('X-Guzzle-Redirect-History'),
            $expectedUrl,
            'Redirect has not appeared.'
        );
    }
}
