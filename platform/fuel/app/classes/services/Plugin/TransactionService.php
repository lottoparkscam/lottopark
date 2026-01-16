<?php

namespace Services\Plugin;

use Container;
use Exception;
use Exceptions\PrimeadsSecureUrlParameterNotExistsException;
use GuzzleHttp\Client;
use Helpers_App;
use Helpers_General;
use Models\Whitelabel;
use Models\WhitelabelPlugin;
use Models\WhitelabelUser;
use Repositories\Aff\WhitelabelAffCommissionRepository;
use Repositories\Aff\WhitelabelAffRepository;
use Repositories\WhitelabelPluginLogRepository;
use Repositories\WhitelabelPluginRepository;
use Repositories\WhitelabelPluginUserRepository;
use Services\Logs\FileLoggerService;
use Throwable;

class TransactionService
{
    use SendPostbackTrait;

    private WhitelabelAffCommissionRepository $whitelabelAffCommissionRepository;
    private Client $client;
    private FileLoggerService $fileLoggerService;
    private WhitelabelPluginUserRepository $whitelabelPluginUserRepository;
    private WhitelabelPluginLogRepository $whitelabelPluginLogRepository;
    private WhitelabelPluginRepository $whitelabelPluginRepository;
    private WhitelabelAffRepository $whitelabelAffRepository;

    public function __construct(
        Client $client,
        WhitelabelAffCommissionRepository $whitelabelAffCommissionRepository,
        FileLoggerService $fileLoggerService,
        WhitelabelPluginUserRepository $whitelabelPluginUserRepository,
        WhitelabelPluginLogRepository $whitelabelPluginLogRepository,
        WhitelabelPluginRepository $whitelabelPluginRepository,
        WhitelabelAffRepository $whitelabelAffRepository,
    )
    {
        $this->whitelabelAffCommissionRepository = $whitelabelAffCommissionRepository;
        $this->client = $client;
        $this->fileLoggerService = $fileLoggerService;
        $this->whitelabelPluginUserRepository = $whitelabelPluginUserRepository;
        $this->whitelabelPluginLogRepository = $whitelabelPluginLogRepository;
        $this->whitelabelPluginRepository = $whitelabelPluginRepository;
        $this->whitelabelAffRepository = $whitelabelAffRepository;
    }

    public function getExternalId(WhitelabelUser $user, string $userAffToken): string|false
    {
        if (Helpers_App::is_development_environment()) {
            return false;
        }

        $referrerAffId = $user['whitelabel_user_aff']['whitelabel_aff_id'];
        if (empty($referrerAffId)) {
            return false;
        }

        $referrerAffUser = $this->whitelabelAffRepository->findAffiliateById($referrerAffId);
        if ($referrerAffUser['token'] !== strtolower($userAffToken)) {
            return false;
        }

        $clickId = $user['whitelabel_user_aff']['external_id'];
        if (empty($clickId)) {
            return false;
        }

        return $clickId;
    }

    public function sendToTrafficBarClickIdAndCommission(WhitelabelUser $user): bool
    {
        try {
            if (Helpers_App::is_development_environment()) {
                return false;
            }

            $userAff = $user->whitelabel_user_aff;
            if (!empty($userAff) && $this->isTrafficBar($userAff->whitelabel_aff->token)) {
                $commission = $this->whitelabelAffCommissionRepository->getLastUserTransactionCommission($user);
                $userClickId = $userAff->externalId;
                return $this->sendPostback("https://api.traffic.bar/campaigns/ipn?access-token=GZ4FjWNA5HlOfKS3zc8UNMgEoaJLWcaC&type=sale&id=$userClickId&payout=$commission", $this->client, 'trafficBar');
            }
        } catch (Throwable $exception) {
            $this->fileLoggerService->error(
                $exception->getMessage()
            );
        }
        return false;
    }

    public function sendToTagMarketingUserFirstDepositConversion(WhitelabelUser $user): bool
    {
        try {
            $clickId = $this->getExternalId($user, Helpers_General::REF_TAG_MARKETING);
            if (empty($clickId)) {
                return false;
            }

            return $this->sendPostback("https://www.gd4udj.com/?nid=2441&transaction_id=$clickId", $this->client, WhitelabelPlugin::TAG_MARKETING_NAME);
        } catch (Throwable $exception) {
            $this->fileLoggerService->error(
                $exception->getMessage()
            );
        }
        return false;
    }

    public function sendToDigitalHubUserFirstDepositConversion(WhitelabelUser $user): bool
    {
        try {
            $clickId = $this->getExternalId($user, Helpers_General::REF_DIGITAL_HUB);
            if (empty($clickId)) {
                return false;
            }

            return $this->sendPostback("https://offers-digitalhub.affise.com/postback?clickid={$clickId}", $this->client, WhitelabelPlugin::DIGITAL_HUB_NAME);
        } catch (Throwable $exception) {
            $this->fileLoggerService->error(
                $exception->getMessage()
            );
        }
        return false;
    }

    public function sendToTibolarioFirstDepositConversion(WhitelabelUser $user): bool
    {
        try {
            $clickId = $this->getExternalId($user, Helpers_General::REF_TIBOLARIO);
            if (empty($clickId)) {
                return false;
            }

            return $this->sendPostback("https://tibolario.com/t/fb/24OkHkOJ8EPl9fQ5SosfxU?code={$clickId}", $this->client, WhitelabelPlugin::TIBOLARIO_NAME);
        } catch (Throwable $exception) {
            $this->fileLoggerService->error(
                $exception->getMessage()
            );
        }
        return false;
    }

    public function sendToLoudingAdsFirstDepositConversion(WhitelabelUser $user): bool
    {
        try {
            $clickId = $this->getExternalId($user, Helpers_General::REF_LOUDING_ADS);
            if (empty($clickId)) {
                return false;
            }

            return $this->sendPostback("https://www.ladbh6fg.com/?nid=2457&transaction_id={$clickId}", $this->client, WhitelabelPlugin::LOUDING_ADS_NAME);
        } catch (Throwable $exception) {
            $this->fileLoggerService->error(
                $exception->getMessage()
            );
        }
        return false;
    }

    public function sendToTagdFirstDepositConversion(WhitelabelUser $user): bool
    {
        try {
            $clickId = $this->getExternalId($user, Helpers_General::REF_TAGD);
            if (empty($clickId)) {
                return false;
            }

            return $this->sendPostback("https://www.tderr5hjtrk.com/?nid=2966&transaction_id={$clickId}", $this->client, WhitelabelPlugin::TAGD_NAME);
        } catch (Throwable $exception) {
            $this->fileLoggerService->error(
                $exception->getMessage()
            );
        }
        return false;
    }

    /** To send postback from local environment you must set aff commission % */
    public function sendCommissionToPrimeadsByUser(WhitelabelUser $user): bool
    {
        try {
            /** @var Whitelabel $whitelabel */
            $whitelabel = Container::get('whitelabel');
            if ($whitelabel->isTheme(Whitelabel::LOTTOPARK_THEME)) {
                $whitelabelPlugin = $this->whitelabelPluginRepository->findPluginByNameAndWhitelabelId(WhitelabelPlugin::PRIMEADS_NAME, $whitelabel->id);
                if (empty($whitelabelPlugin) || $whitelabelPlugin->isNotEnabled() || Helpers_App::is_development_environment()) {
                    return false;
                }

                $affToken = $this->whitelabelPluginUserRepository->getUserAffTokenByWhitelabelUserId($user->id);
                if (!empty($affToken) && $this->shouldSendToPrimeads($affToken)) {
                    $userClickId = $this->whitelabelPluginUserRepository->getUserClickIdByWhitelabelUserId($user->id);
                    $userClickIdExists = !empty($userClickId);
                    if ($userClickIdExists) {
                        $commission = $this->whitelabelAffCommissionRepository->getLastUserTransactionCommission($user);
                        try {
                            $urlSecureParameter = $whitelabelPlugin->findPrimeadsSecureUrlParameter();
                        } catch (PrimeadsSecureUrlParameterNotExistsException) {
                            $url = $this->generatePrimeadsTransactionUrl($userClickId, $commission, '');
                            throw new PrimeadsSecureUrlParameterNotExistsException();
                        }
                        $url = $this->generatePrimeadsTransactionUrl($userClickId, $commission, $urlSecureParameter);
                        return $this->sendPostback($url, $this->client, WhitelabelPlugin::PRIMEADS_NAME, $this->whitelabelPluginLogRepository);
                    }
                }
            }
        } catch (Throwable $exception) {
            $message = json_encode(['message' => "Unsuccessful request to " . WhitelabelPlugin::PRIMEADS_NAME . ", response: {$exception->getMessage()}", 'url' => $url ?? '']);
            $this->whitelabelPluginLogRepository->addErrorLog(WhitelabelPlugin::PRIMEADS_NAME, $message);
            $this->fileLoggerService->error(
                'Message: ' . $exception->getMessage() .
                'File: ' . $exception->getFile() .
                'Line: ' . $exception->getLine()
            );
        }
        return false;
    }
}
