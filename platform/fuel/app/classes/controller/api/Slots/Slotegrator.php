<?php

use Abstracts\Controllers\Slots\AbstractSlotsController;
use Fuel\Core\Input;
use Fuel\Core\Response;
use Helpers\CaseHelper;
use Models\SlotGame;
use Models\SlotLog;
use Models\SlotOpenGame;
use Models\WhitelabelUser;
use Repositories\CurrencyRepository;
use Repositories\Orm\WhitelabelUserRepository;
use Repositories\SlotGameRepository;
use Repositories\SlotOpenGameRepository;
use Services\Api\Slots\Providers\SlotegratorSecurityService;
use Services\Api\Slots\Providers\SlotegratorService;

class Controller_Api_Slots_Slotegrator extends AbstractSlotsController
{
    protected const PROVIDER_SLUG = 'slotegrator';

    public const INSUFFICIENT_FUNDS_ERROR_CODE = 'INSUFFICIENT_FUNDS';
    public const INTERNAL_ERROR_CODE = 'INTERNAL_ERROR';

    private SlotegratorService $slotegratorService;
    private WhitelabelUserRepository $whitelabelUserRepository;
    private SlotGameRepository $slotGameRepository;
    private ?SlotGame $slotGame;
    private SlotegratorSecurityService $slotegratorSecurityService;
    private SlotOpenGameRepository $slotOpenGameRepository;
    private SlotOpenGame $slotOpenGame;
    private CurrencyRepository $currencyRepository;
    private WhitelabelUser $whitelabelUser;
    private string $previousGameUuid = '';

    public function before()
    {
        parent::before();
        $this->slotegratorService = Container::get(SlotegratorService::class);
        $this->whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);
        $this->slotGameRepository = Container::get(SlotGameRepository::class);
        $this->slotegratorSecurityService = Container::get(SlotegratorSecurityService::class);
        $this->slotOpenGameRepository = Container::get(SlotOpenGameRepository::class);
        $this->currencyRepository = Container::get(CurrencyRepository::class);
    }

    protected function checkRequest(): bool
    {
        parent::checkRequest();

        $hasErrors = !empty($this->errors);
        if ($hasErrors) {
            return false;
        }

        $expectedHeadersKeys = [
            'X-Merchant-Id',
            'X-Timestamp',
            'X-Nonce',
            'X-Sign'
        ];

        $headers = Input::headers();
        foreach ($expectedHeadersKeys as $header) {
            $headerNotExists = !key_exists($header, $headers);
            if ($headerNotExists) {
                $this->errors[] = "$header header does not exist in this request";
                return false;
            }
        }

        $XMerchantId = $headers['X-Merchant-Id'];
        $XTimestamp = $headers['X-Timestamp'];
        $XNonce = $headers['X-Nonce'];
        $XSign = $headers['X-Sign'];

        $slotProvider = $this->whitelabelSlotProvider->slotProvider;
        $apiCredentials = $slotProvider->apiCredentials;
        $whitelabelTheme = CaseHelper::kebabToSnake($this->whitelabelFromUrl->theme);
        $merchantId = $apiCredentials["{$whitelabelTheme}_merchant_id"] ?? null;
        if ($merchantId !== $XMerchantId) {
            $this->errors[] = 'Invalid merchant_id';
            return false;
        }

        $expectedHeaders = $this->slotegratorSecurityService->prepareAccessHeaders(
            $whitelabelTheme,
            Input::post(),
            $XTimestamp,
            $XNonce
        );
        $expectedSign = $expectedHeaders['X-Sign'];
        if ($XSign !== $expectedSign) {
            $this->errors[] = 'Invalid sign';
            return false;
        }

        // We won't process request sent earlier than 30 seconds ago
        $requestExpireTimeInSeconds = 30;
        $requestExpired = time() - $XTimestamp > $requestExpireTimeInSeconds;
        if ($requestExpired) {
            $this->errors[] = 'Request expired';
            return false;
        }

        $action = $this->requestData['action'] ?? '';
        if (empty($action)) {
            $this->errors[] = "Action key cannot be empty";
            return false;
        }

        $methodNotExists = !method_exists(SlotegratorService::class, $action);
        if ($methodNotExists) {
            $errorMessage = 'We do not support this endpoint yet';
            $this->errors[] = $errorMessage;
            return false;
        }

        $basicRequiredDataKeys = [
            'action',
            'currency',
            'player_id',
            'session_id'
        ];

        $requiredDataKeys = [
            SlotLog::ACTION_BALANCE => [
                ...$basicRequiredDataKeys,
            ],
            SlotLog::ACTION_BET => [
                'amount',
                'game_uuid',
                'transaction_id',
                'type',
                ...$basicRequiredDataKeys,
            ],
            SlotLog::ACTION_WIN => [
                'amount',
                'game_uuid',
                'transaction_id',
                'type',
                ...$basicRequiredDataKeys,
            ],
            SlotLog::ACTION_REFUND => [
                'amount',
                'game_uuid',
                'transaction_id',
                'bet_transaction_id',
                ...$basicRequiredDataKeys,
            ],
            SlotLog::ACTION_ROLLBACK => [
                'game_uuid',
                'transaction_id',
                'rollback_transactions' => [
                    'action',
                    'amount',
                    'transaction_id',
                ],
                'type',
                ...$basicRequiredDataKeys,
            ]
        ];

        $requestDoNotContainRequiredKeys = !$this->checkRequiredFieldsInRequest($requiredDataKeys[$action]);
        if ($requestDoNotContainRequiredKeys) {
            return false;
        }

        $userToken = $this->requestData['player_id'];

        $whitelabelUser = $this->whitelabelUserRepository->findByTokenAndWhitelabelId($userToken, $this->whitelabelFromUrl->id);
        if (empty($whitelabelUser)) {
            $this->errors[] = 'User with given player_id does not exist';
            return false;
        }

        $this->whitelabelUser = $whitelabelUser;

        $cannotFindProvidedGame = !$this->setSlotGame();
        if ($cannotFindProvidedGame) {
            return false;
        }

        $sessionId = $this->requestData['session_id'];
        $cannotFindReceivedSession = !$this->setSlotOpenGame();
        if ($cannotFindReceivedSession) {
            $this->errors[] = "Game with session_id $sessionId does not exist";
            return false;
        }

        return true;
    }

    public function setSlotGame(): bool
    {
        // check if uuid exists in request
        $userChangedGame = key_exists('game_uuid', $this->requestData) && $this->requestData['game_uuid'] !== $this->previousGameUuid;
        if ($userChangedGame) {
            $gameUuid = $this->requestData['game_uuid'];
            $this->slotGame = $this->slotGameRepository->findOneByUuidAndSlotProviderId(
                $gameUuid,
                $this->whitelabelSlotProvider->slotProviderId
            );

            if (empty($this->slotGame)) {
                $this->errors[] = "Game with uuid $gameUuid does not exist";
                return false;
            }

            return true;
        }

        $sessionId = $this->requestData['session_id'];
        $this->slotGame = $this->slotOpenGameRepository->findOneSlotGameBySessionId(
            $sessionId,
            $this->whitelabelUser->id
        );

        // its optional param, used only for lobby games
        if (!empty($this->slotGame)) {
            $this->previousGameUuid = $this->slotGame->uuid;
        }

        return true;
    }

    public function setSlotOpenGame(): bool
    {
        $sessionId = $this->requestData['session_id'];

        $receivedDifferentGameUuid = key_exists('game_uuid', $this->requestData) && $this->requestData['game_uuid'] !== $this->previousGameUuid;
        $userChangedGame = $receivedDifferentGameUuid && $this->slotGame->hasLobby && $this->slotOpenGameRepository->userHasChangedGameInLobby(
            $this->whitelabelUser->id,
            $this->whitelabelSlotProvider->id,
            $this->slotGame->id,
            $sessionId
        );

        $slotOpenGame = $this->slotOpenGameRepository->findOneBySessionIdAndWhitelabelSlotProviderId(
            $sessionId,
            $this->whitelabelSlotProvider->id
        );

        if (empty($slotOpenGame)) {
            return false;
        }

        if (!$userChangedGame) {
            $this->slotOpenGame = $slotOpenGame;
            return true;
        }

        $currency = $this->requestData['currency'];

        $this->slotOpenGame = $this->slotOpenGameRepository->insert(
            $this->whitelabelSlotProvider->id,
            $this->slotGame->id,
            $this->whitelabelUser->id,
            $this->currencyRepository->findOneByCode($currency)->id,
            $slotOpenGame->sessionId
        );

        if (empty($this->slotOpenGame)) {
            return false;
        }

        return true;
    }

    /**
     * Slotegrator want us to return HTTP code 200 even if error occurred.
     */
    public function post_index(): Response
    {
        $wrongRequest = !$this->checkRequest();
        if ($wrongRequest) {
            $errorResponse = $this->slotegratorService->createErrorResponse($this->getFirstErrorMessage());
            return $this->returnResponse($errorResponse);
        }

        $action = $this->requestData['action'];
        $userToken = $this->requestData['player_id'];

        $whitelabelUser = $this->whitelabelUserRepository->findByTokenAndWhitelabelId($userToken, $this->whitelabelFromUrl->id);
        if (empty($whitelabelUser)) {
            $errorResponse = $this->slotegratorService->createErrorResponse('User with given player_id does not exist');
            return $this->returnResponse($errorResponse);
        }

        $slotGameId = empty($this->slotGame) ? null : $this->slotGame->id;
        $this->logger->configure(
            $whitelabelUser->id,
            $this->whitelabelSlotProvider->id,
            $action,
            $this->requestData,
            $slotGameId
        );

        $this->slotegratorService->configure(
            $whitelabelUser,
            $this->whitelabelSlotProvider,
            $this->logger,
            $this->slotGame ?? null,
            $this->slotOpenGame,
            $this->requestData
        );

        $responseData = $this->slotegratorService->$action();
        return $this->returnResponse($responseData);
    }
}
