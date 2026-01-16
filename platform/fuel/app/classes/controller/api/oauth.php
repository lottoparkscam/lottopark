<?php

declare(strict_types=1);

use Fuel\Core\Controller_Rest;
use Fuel\Core\Cookie;
use Helpers\UrlHelper;
use OAuth2\Response as OAuthResponse;
use Fuel\Core\Response;
use Models\Whitelabel;
use Models\WhitelabelUser;
use Models\WhitelabelOAuthClient;
use Helpers\UserHelper;
use Repositories\WhitelabelOAuthClientRepository;
use Services\Logs\FileLoggerService;
use Services\OAuth2Server\WhitelabelUserOAuth2Service;
use OAuth2\Request;

class Controller_Oauth extends Controller_Rest
{
    private Request $oAuthRequest;
    private WhitelabelUserOAuth2Service $whitelabelUserOAuth2Service;
    private WhitelabelOAuthClientRepository $whitelabelOAuthClientRepository;
    private Whitelabel $whitelabel;
    private FileLoggerService $fileLoggerService;

    public function before()
    {
        parent::before();

        $this->whitelabel = Container::get('whitelabel');
        $this->oAuthRequest = Request::createFromGlobals();
        $this->whitelabelUserOAuth2Service = Container::get(WhitelabelUserOAuth2Service::class);
        $this->whitelabelOAuthClientRepository = Container::get(WhitelabelOAuthClientRepository::class);
        $this->fileLoggerService = Container::get(FileLoggerService::class);
    }

    public function get_authorize(): void
    {
        /** @var WhitelabelUser $user */
        $user = UserHelper::getUser();
        $domain = $this->whitelabel->domain;

        if (!$user) {
            $queryParameters = $this->oAuthRequest->getAllQueryParameters();

            if (isset($queryParameters['isCasino']) && $queryParameters['isCasino']) {
                $casinoUrl = $this->whitelabelOAuthClientRepository
                    ->getWhitelabelCasinoUrl($this->whitelabel->id)
                    ?? UrlHelper::getCasinoHomeUrl();

                // We don't want to force login on lottopark, we want to redirect to casino.lottopark
                Response::redirect($casinoUrl);
            }

            $isLottoParkWhitelabel = $this->whitelabel->id === 1;
            if (isset($queryParameters['redirectToClientHomePage']) && $isLottoParkWhitelabel) {
                $whitelabelOauthDomain = $this->whitelabelOAuthClientRepository
                    ->getWhitelabelOauthDomain($this->whitelabel->id);

                // We don't want to force login on lottopark, we want to redirect to new.lottopark
                Response::redirect('https://' . $whitelabelOauthDomain);
            }

            // Redirect from the API subdomain to the Whitelabel domain login page
            $whitelabelLoginPage = 'https://' . $domain . '/auth/login/';
            $whitelabelAutoLoginLink = $this->whitelabelOAuthClientRepository
                ->getWhitelabelAutologinLink($this->whitelabel->id);

            if (!empty($whitelabelAutoLoginLink['uri'])) {
                Cookie::set(
                    WhitelabelOAuthClient::AUTOLOGIN_URI_KEY,
                    $whitelabelAutoLoginLink['uri'] . '/',
                    5 * Helpers_Time::MINUTE_IN_SECONDS,
                    '/',
                    '.' . $domain,
                    true,
                    true
                );
            }

            Response::redirect($whitelabelLoginPage);
        }

        Cookie::delete(WhitelabelOAuthClient::AUTOLOGIN_URI_KEY);

        $this->whitelabelUserOAuth2Service->setWhitelabelUser($user);
        $this->whitelabelUserOAuth2Service->setOpenIdConnect();

        if (!$this->whitelabelUserOAuth2Service->validateAuthorizeRequest($this->oAuthRequest)) {
            /** @var OAuthResponse $response */
            $oauthResponse = $this->whitelabelUserOAuth2Service->getResponse();

            $this->sendResponse($oauthResponse);
        } else {
            $response = $this->whitelabelUserOAuth2Service->handleAuthorizeRequest($this->oAuthRequest);
            $location = $response->getHttpHeader('Location');
            $this->fileLoggerService->info("Authorize redirect to: $location. Details: " . json_encode([
                'email' => $user->email
            ]));

            Response::redirect($location);
        }
    }

    public function post_token(): void
    {
        // https://www.rfc-editor.org/rfc/rfc6749#section-4.3.3
        header("Cache-Control: no-store");
        header("Pragma: no-cache");

        $this->whitelabelUserOAuth2Service->setTokenRequestOpenIdGrantType();

        if (!$this->whitelabelUserOAuth2Service->validateTokenRequest($this->oAuthRequest)) {
            $response = $this->whitelabelUserOAuth2Service->getResponse();
        } else {
            $response = $this->whitelabelUserOAuth2Service->handleTokenRequest($this->oAuthRequest);
        }

        $this->sendResponse($response);
    }

    protected function sendResponse(OAuthResponse $oAuthResponse): void
    {
        $contentType = ['Content-type' => 'application/json'];

        $response = new Response(
            $oAuthResponse->getResponseBody(),
            $oAuthResponse->getStatusCode(),
            $contentType
        );

        $response->send(true);
        exit;
    }
}
