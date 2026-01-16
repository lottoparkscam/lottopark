<?php

namespace Tests\E2E\Classes\Services\Auth;

use Carbon\Carbon;
use Fuel\Core\Session;
use GuzzleHttp\Client;
use Models\WhitelabelUser;
use Tests\Feature\AbstractTests\AbstractUserTest;
use Tests\Traits\ApiTestTrait;
use Psr\Http\Message\ResponseInterface;

class AutoLoginServiceTest extends AbstractUserTest
{
    // we have to disable transactions because API doesn't detect it
    protected $in_transaction = false;

    use ApiTestTrait;

    private const DOMAIN = 'https://lottopark.loc';
    private const END_POINT = '/autologin';
    private const REQUEST_URL = self::DOMAIN . self::END_POINT;
    private const LOGIN_FORM = '/auth/login';
    private Client $client;
    private WhitelabelUser $dummyUser;

    public function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2022-03-27 10:11:12');
        $this->client = $this->getHttpClient();
    }

    /** @test */
    public function loginHasEmptyHashShouldRedirectToHomepage(): void
    {
        $response = $this->getResponse($this->client, $this->get, self::REQUEST_URL, []);
        $this->assertLoginFail($response);
    }

    /** @test */
    public function loginHasEmptyHashWithSlashShouldRedirectToHomepage(): void
    {
        $response = $this->getResponse($this->client, $this->get, self::REQUEST_URL . '/', []);
        $this->assertLoginFail($response);
    }

    /** @test */
    public function loginHasWrongHashShouldRedirectToHomepage(): void
    {
        $someRandomStringPassingValidation = '/mrclqymfmvcvjkldunjeopigndmpxugkbdilajljbcspghjrtvbzpukjukjcunun';
        $urlWithParams = self::REQUEST_URL . $someRandomStringPassingValidation;
        $response = $this->getResponse($this->client, $this->get, $urlWithParams, []);
        $this->assertLoginFail($response);
    }

    /** @test */
    public function loginHasHashShouldLoginAndRedirectToHomepage(): void
    {
        $this->prepareUserVariables('email', false, false);
        $this->dummyUser = new WhitelabelUser($this->whitelabelUser->to_array(), false);
        $this->dummyUser->save();

        $urlWithValidHash = self::REQUEST_URL . '/' . $this->whitelabelUser->loginHash;
        $response = $this->getResponse($this->client, $this->get, $urlWithValidHash, []);

        // refresh user
        $this->dummyUser = $this->whitelabelUserRepository->findOneById($this->dummyUser->id);
        $this->assertLoginSuccess($response);

        $this->dummyUser->delete();
    }
    
    private function assertRedirects(ResponseInterface $response): void
    {
        $this->assertRedirectStatusCode($response, self::DOMAIN, 302);
        $this->assertRedirectCount($response, 1);
        $this->assertResponseStatusCode($response, 200);
        $this->assertRedirectTo($response, self::DOMAIN . '/');
    }

    private function assertLoginFail(ResponseInterface $response): void
    {
        $this->assertRedirects($response);
        $this->assertEmpty(Session::get());
    }

    private function assertLoginSuccess(ResponseInterface $response): void
    {
        $this->assertRedirects($response);
        // Received session is invisible here because we though api we acting as user.
        // Session is saved by server side.
        // We can check if user is logged in by trying to enter some route with auth middleware
        // In this case we use lottopark.loc/auth/login - if redirect appears it means that user is logged in properly
        $response = $this->getResponse($this->client, 'GET', self::DOMAIN . self::LOGIN_FORM, []);
        $this->assertRedirectTo($response, self::DOMAIN . '/');
    }
}
