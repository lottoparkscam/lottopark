<?php

namespace Services\Plugin;

use Container;
use Exceptions\PrimeadsSecureUrlParameterNotExistsException;
use Fuel\Core\Cookie;
use GuzzleHttp\Client;
use Helpers_App;
use Models\Whitelabel;
use Models\WhitelabelPlugin;
use Repositories\WhitelabelPluginLogRepository;
use Repositories\WhitelabelPluginRepository;
use Repositories\WhitelabelPluginUserRepository;
use Throwable;
use Exception;
use Services\Logs\FileLoggerService;
use Validators\ClickIdValidator;

class RegisterService
{
    use SendPostbackTrait;

    public const REF_NAME = 'ref';
    public const CLICK_NAME = 'clickID';
    public const EXTERNAL_AFF_ID_NAME = 'affiliateID';
    private Client $client;
    private ClickIdValidator $clickIdValidator;
    private FileLoggerService $fileLoggerService;
    private WhitelabelPluginLogRepository $whitelabelPluginLogRepository;
    private WhitelabelPluginRepository $whitelabelPluginRepository;
    private WhitelabelPluginUserRepository $whitelabelPluginUserRepository;

    public function __construct(
        Client $client,
        ClickIDValidator $clickIdValidator,
        FileLoggerService $fileLoggerService,
        WhitelabelPluginLogRepository $whitelabelPluginLogRepository,
        WhitelabelPluginRepository $whitelabelPluginRepository,
        WhitelabelPluginUserRepository $whitelabelPluginUserRepository,
    )
    {
        $this->client = $client;
        $this->clickIdValidator = $clickIdValidator;
        $this->fileLoggerService = $fileLoggerService;
        $this->whitelabelPluginLogRepository = $whitelabelPluginLogRepository;
        $this->whitelabelPluginRepository = $whitelabelPluginRepository;
        $this->whitelabelPluginUserRepository = $whitelabelPluginUserRepository;
    }

    public function sendToTrafficBarClickId(): bool
    {
        if (Helpers_App::is_development_environment()) {
            return false;
        }
        //token is the same as ref
        $ref = Cookie::get(self::REF_NAME, '');
        if ($this->isTrafficBar($ref)) {
            try {
                // you can see how it works here -> platform/fuel/app/classes/helpers/click/clickreader.php on line 25
                $userClickIdInput = [
                    'clickID' => Cookie::get(self::CLICK_NAME)
                ];
                $this->clickIdValidator->setCustomInput($userClickIdInput);
                $incorrectClickId = !$this->clickIdValidator->isValid();
                if ($incorrectClickId) {
                    throw new Exception('Request to trafficBar with clickId not valid ' . json_encode($this->clickIdValidator->getErrors()));
                }
                $validatedClickId = $this->clickIdValidator->getProperty(self::CLICK_NAME);
                return $this->sendPostback("https://api.traffic.bar/campaigns/ipn?access-token=GZ4FjWNA5HlOfKS3zc8UNMgEoaJLWcaC&type=reg&id=$validatedClickId&payout=0", $this->client, 'trafficBar');
            } catch (Throwable $exception) {
                $this->fileLoggerService->error(
                    $exception->getMessage()
                );
            }
        }
        return false;
    }

    public function sendToPrimeadsClickId(int $userId): bool
    {
        try {
            /** @var Whitelabel $whitelabel */
            $whitelabel = Container::get('whitelabel');
            if ($whitelabel->isTheme(Whitelabel::LOTTOPARK_THEME)) {
                $whitelabelPlugin = $this->whitelabelPluginRepository->findPluginByNameAndWhitelabelId(WhitelabelPlugin::PRIMEADS_NAME, $whitelabel->id);
                    if (empty($whitelabelPlugin) || $whitelabelPlugin->isNotEnabled() || Helpers_App::is_development_environment()) {
                    return false;
                }

                //token is the same as ref
                $ref = Cookie::get(self::REF_NAME, '');
                if ($this->shouldSendToPrimeads($ref)) {
                    $clickId = $this->whitelabelPluginUserRepository->getUserClickIdByWhitelabelUserId($userId);
                    $userClickIdExists = !empty($clickId);
                    if ($userClickIdExists) {
                        try {
                            $urlSecureParameter = $whitelabelPlugin->findPrimeadsSecureUrlParameter();
                        } catch (PrimeadsSecureUrlParameterNotExistsException) {
                            $url = $this->generatePrimeadsRegisterUrl($clickId, '');
                            throw new PrimeadsSecureUrlParameterNotExistsException();
                        }
                        $url = $this->generatePrimeadsRegisterUrl($clickId, $urlSecureParameter);
                        return $this->sendPostback($url, $this->client, WhitelabelPlugin::PRIMEADS_NAME, $this->whitelabelPluginLogRepository);
                    }
                }
            }
        } catch (Throwable $exception) {
            $message = json_encode(['message' => "Unsuccessful request to " . WhitelabelPlugin::PRIMEADS_NAME . ", response: {$exception->getMessage()}", 'url' => $url ?? '']);
            try {
                $this->whitelabelPluginLogRepository->addErrorLog(WhitelabelPlugin::PRIMEADS_NAME, $message);
            } catch (Throwable $exception) {
            }
            $this->fileLoggerService->error(
                'Message: ' . $exception->getMessage() .
                'File: ' . $exception->getFile() .
                'Line: ' . $exception->getLine()
            );
        }
        return false;
    }
}
