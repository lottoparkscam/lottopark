<?php

use Fuel\Core\Cookie;
use Models\WhitelabelPlugin;
use Models\WhitelabelUserAff;
use Modules\Mediacle\UserRegisterEventHandler;
use Repositories\Aff\WhitelabelUserAffRepository;
use Repositories\WhitelabelPluginRepository;
use Repositories\WhitelabelPluginUserRepository;
use Services\AffiliateProService;
use Services\Logs\FileLoggerService;
use Services\Plugin\RegisterService;
use Services\Plugin\SendPostbackTrait;
use Validators\ClickIdValidator;

class Events_User_Register extends Events_Event
{
    use SendPostbackTrait;

    public const DOUBLE_JACK_THEME_NAME = 'doublejack';
    public const MEGA_JACKPOT_THEME_NAME = 'megajackpot';
    public const LOTTO_PARK_THEME_NAME = 'lottopark';
    public const LOV_CASINO_THEME_NAME = 'lovcasino';

    public static function handle(array $data): void
    {
        $data['onCasinoEvent'] = true;
        parent::handle($data);
    }

    protected static function run(array $data): void
    {
        $data['onCasinoEvent'] = true;
        $userToken = $data['plugin_data']['token'] ?? '';
        $whitelabelThemeName = $data['whitelabel_theme'] ?? '';
        if (!IS_CASINO) {
            $fileLoggerService = Container::get(FileLoggerService::class);

            Forms_Wordpress_Pixels_Gtag::trigger_event('sign_up', $data['register_data'], true);

            /** Removed direct Facebook Pixel call; the event is now sent via GTM */
//            $fb_data = [
//                'content_name' => $userToken,
//                'status' => false
//            ];
//            Forms_Wordpress_Pixels_Facebook::trigger_event('CompleteRegistration', $fb_data, true);

            switch ($whitelabelThemeName) {
                case self::LOTTO_PARK_THEME_NAME:
                    try {
                        /** @var WhitelabelUserAffRepository $whitelabelUserAffRepository */
                        $whitelabelUserAffRepository = Container::get(WhitelabelUserAffRepository::class);

                        /** @var RegisterService $registerService */
                        $registerService = Container::get(RegisterService::class);

                        $refToken = mb_strtolower(Cookie::get(Helpers_General::COOKIE_AFF_NAME, ''));

                        // TRAFFIC BAR
                        $postbackSuccessful = $registerService->sendToTrafficBarClickId();
                        if ($postbackSuccessful) {
                            $userClickId = Cookie::get(RegisterService::CLICK_NAME);
                            $whitelabelUserAffRepository->updateClickId($data['whitelabel_id'], $data['user_id'], $userClickId);
                        }

                        switch ($refToken) {
                            case Helpers_General::REF_TAG_MARKETING:
                                if (!empty(Cookie::get(Helpers_General::COOKIE_AFF_TAG_MARKETING_TRANSACTION_ID))) {
                                    $transactionId = Cookie::get(Helpers_General::COOKIE_AFF_TAG_MARKETING_TRANSACTION_ID);
                                    $whitelabelUserAffRepository->updateClickId($data['whitelabel_id'], $data['user_id'], $transactionId);
                                }
                                break;
                            case Helpers_General::REF_DIGITAL_HUB:
                                if (!empty(Cookie::get(Helpers_General::COOKIE_AFF_DIGITAL_HUB_DATA))) {
                                    $digitalHubClickId = Cookie::get(Helpers_General::COOKIE_AFF_DIGITAL_HUB_DATA);
                                    $whitelabelUserAffRepository->updateClickId($data['whitelabel_id'], $data['user_id'], $digitalHubClickId);
                                }
                                break;
                            case Helpers_General::REF_TIBOLARIO:
                                if (!empty(Cookie::get(Helpers_General::COOKIE_AFF_TIBOLARIO))) {
                                    $tibolarioClickId = Cookie::get(Helpers_General::COOKIE_AFF_TIBOLARIO);
                                    $whitelabelUserAffRepository->updateClickId($data['whitelabel_id'], $data['user_id'], $tibolarioClickId);
                                }
                                break;
                            case Helpers_General::REF_LOUDING_ADS:
                                if (!empty(Cookie::get(Helpers_General::COOKIE_AFF_LOUDING_ADS))) {
                                    $loudingAdsClickId = Cookie::get(Helpers_General::COOKIE_AFF_LOUDING_ADS);
                                    $whitelabelUserAffRepository->updateClickId($data['whitelabel_id'], $data['user_id'], $loudingAdsClickId);
                                }
                                break;
                            case Helpers_General::REF_TAGD:
                                if (!empty(Cookie::get(Helpers_General::COOKIE_AFF_TAGD))) {
                                    $tagdClickId = Cookie::get(Helpers_General::COOKIE_AFF_TAGD);
                                    $whitelabelUserAffRepository->updateClickId($data['whitelabel_id'], $data['user_id'], $tagdClickId);
                                }
                                break;
                        }

                        // PRIME ADS
                        if (self::isPrimeadsAffToken(Cookie::get(RegisterService::REF_NAME, ''))) {
                            $userExternalAffId = Cookie::get(RegisterService::EXTERNAL_AFF_ID_NAME);
                            $userClickIdInput = [
                                'clickID' => Cookie::get(RegisterService::CLICK_NAME),
                            ];
                            $clickIdValidator = Container::get(ClickIdValidator::class);
                            $clickIdValidator->setCustomInput($userClickIdInput);
                            $incorrectClickId = !$clickIdValidator->isValid();
                            if ($incorrectClickId) {
                                throw new Exception('Request to primeads with clickId not valid ' . json_encode($clickIdValidator->getErrors()));
                            }

                            $incorrectExternalAffId = !is_numeric($userExternalAffId);
                            if ($incorrectExternalAffId) {
                                throw new Exception('Request to primeads with external aff id not valid ' . json_encode($clickIdValidator->getErrors()));
                            }

                            /** @var WhitelabelUserAff $whitelabelUserAff */
                            $whitelabelUserAff = $whitelabelUserAffRepository->findOneByWhitelabelUserId($data['user_id']);
                            $additionalData = json_encode([
                                'clickId' => $clickIdValidator->getProperty(RegisterService::CLICK_NAME),
                                'affToken' => $whitelabelUserAff->whitelabel_aff->token,
                                'externalAffId' => $userExternalAffId,
                            ]);
                            /** @var WhitelabelPluginUserRepository $whitelabelPluginUserRepository */
                            $whitelabelPluginUserRepository = Container::get(WhitelabelPluginUserRepository::class);
                            $whitelabelPluginRepository = Container::get(WhitelabelPluginRepository::class);
                            /** @var WhitelabelPlugin|Null $whitelabelPlugin */
                            $whitelabelPlugin = $whitelabelPluginRepository->findPluginByNameAndWhitelabelId(WhitelabelPlugin::PRIMEADS_NAME, $data['whitelabel_id']);
                            if (!empty($whitelabelPlugin)) {
                                $isSuccessfullyCreated = $whitelabelPluginUserRepository->addPluginUser($whitelabelPlugin->id, $data['user_id'], $additionalData);
                                if ($isSuccessfullyCreated) {
                                    /** @var RegisterService $registerService */
                                    $registerService = Container::get(RegisterService::class);
                                    $registerService->sendToPrimeadsClickId($data['user_id']);
                                }
                            }
                        }
                    } catch (Throwable $exception) {
                        $fileLoggerService->error(
                            'Message: ' . $exception->getMessage() .
                            'File: ' . $exception->getFile() .
                            'Line: ' . $exception->getLine()
                        );
                    }
                    break;
            }
        } else {
            switch ($whitelabelThemeName) {
                case self::LOTTO_PARK_THEME_NAME:
                    try {
                        $tracking = new AffiliateProService();
                        $tracking->orderId = $userToken;
                        $tracking->orderCurrency = $data['plugin_data']['currency'];
                        $tracking->orderTotal = '';
                        $tracking->productIds = ['register'];
                        $tracking->websiteUrl = 'https://casino.lottopark.com';
                        //place order in affiliate script
                        $tracking->placeOrder('https://partners.lottopark.com/integration/addOrder?');
                    } catch (Throwable $e) {
                        // ignore
                    }
                    break;
                case self::LOV_CASINO_THEME_NAME:
                    try {
                        $tracking = new AffiliateProService();
                        $tracking->orderId = $userToken;
                        $tracking->orderCurrency = $data['plugin_data']['currency'];
                        $tracking->websiteUrl = 'https://casino.lovcasino.com/';
                        //place order in affiliate script
                        $tracking->placeOrder('https://partners.lovcasino.com/integration/addOrder?');
                    } catch (Throwable $e) {
                        // ignore
                    }
                    break;
            }
        }

        // casino and lotteries operations
        try {
            switch ($whitelabelThemeName) {
                case self::DOUBLE_JACK_THEME_NAME:
                case self::MEGA_JACKPOT_THEME_NAME:
                    // mediacle integration
                    Container::get(UserRegisterEventHandler::class)->handle($data);
                    break;
            }
        } catch (Throwable $exception) {
            $fileLoggerService->error(
                'Mediacle register event failed, details: ' . $exception->getMessage()
            );
        }

        parent::run($data);
    }
}
