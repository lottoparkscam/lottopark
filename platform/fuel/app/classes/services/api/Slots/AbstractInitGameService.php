<?php

namespace Services\Api\Slots;

use Container;
use Helpers\UrlHelper;
use Helpers\UserHelper;
use Models\Whitelabel;
use Models\WhitelabelSlotProvider;
use Repositories\WhitelabelSlotProviderRepository;
use Models\SlotLog;
use Repositories\SlotGameRepository;
use Repositories\SlotOpenGameRepository;
use Models\SlotGame;
use Models\WhitelabelUser;
use Fuel\Core\Input;

abstract class AbstractInitGameService
{
    protected const GAME_MODE_DEMO = 'demo';
    protected const GAME_MODE_REAL = 'real';

    private WhitelabelSlotProviderRepository $whitelabelSlotProviderRepository;
    private SlotGameRepository $slotGameRepository;
    private SlotOpenGameRepository $slotOpenGameRepository;
    private LoggerService $loggerService;
    private int $whitelabelId;
    private int $whitelabelSlotProviderId;
    private LimitService $limitService;
    protected string $action = SlotLog::ACTION_INIT;
    protected bool $isDemo;
    protected ?WhitelabelUser $user;
    protected int $sessionUuid;
    protected string $gameUuid;
    protected ?SlotGame $game;
    protected string $language;
    protected array $response;
    protected bool $isError = false;
    protected ?int $whitelabelUserId;
    protected int $slotGameId;
    protected int $slotProviderId;
    protected array $request;
    public bool $isLimitReached;
    protected string $gameMode;

    public function __construct(
        WhitelabelSlotProviderRepository $whitelabelSlotProviderRepository,
        SlotOpenGameRepository $slotOpenGameRepository,
        LimitService $limitService,
        SlotGameRepository $slotGameRepository
    )
    {
        $this->limitService = $limitService;
        $this->whitelabelSlotProviderRepository = $whitelabelSlotProviderRepository;
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        $this->whitelabelId = $whitelabel->id;
        $this->slotOpenGameRepository = $slotOpenGameRepository;
        $this->slotGameRepository = $slotGameRepository;

        $cannotSetProperties = !$this->setProperties();
        if ($cannotSetProperties) {
            UrlHelper::redirectToHomepage();
        }

        if (!$this->validate()) {
            UrlHelper::redirectToHomepage();
        }

        $userFields = ['id', 'token', 'name', 'email', 'currency_id'];
        $this->user = UserHelper::getUserModel($userFields);

        $shouldRedirectToLoginPage = !$this->isDemo && empty($this->user);
        if ($shouldRedirectToLoginPage) {
            UrlHelper::redirectToLoginPage();
        }

        $this->configure();
    }

    private function setProperties(): bool
    {
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        $this->whitelabelId = $whitelabel->id;
        $this->gameUuid = Input::get('game_uuid', '');
        $this->language = ICL_LANGUAGE_CODE ?? 'en';
        $this->game = $this->slotGameRepository->findOneByUuid($this->gameUuid);

        $cannotFindProvidedGame = empty($this->game);
        if ($cannotFindProvidedGame) {
            return false;
        }

        $this->gameMode = Input::get('mode', self::GAME_MODE_REAL);
        $this->isDemo = !empty($this->gameMode) && $this->gameMode === self::GAME_MODE_DEMO;

        $this->whitelabelSlotProviderId = $this->whitelabelSlotProviderRepository->findIdBySlotProviderIdAndWhitelabelId($this->game->slotProviderId, $this->whitelabelId);

        return true;
    }

    protected function validate(): bool
    {
        $hasInvalidMode = $this->gameMode !== self::GAME_MODE_DEMO && $this->gameMode !== self::GAME_MODE_REAL;
        if (empty($this->gameUuid) || $hasInvalidMode) {
            return false;
        }

        $whitelabelSlotProvider = $this->whitelabelSlotProviderRepository->findOneById($this->whitelabelSlotProviderId);
        $isLimitEnabled = $whitelabelSlotProvider->isLimitEnabled;
        $this->isLimitReached = $isLimitEnabled && $this->limitService->isWhitelabelLimitReached($this->whitelabelId);
        if ($this->isLimitReached) {
            return false;
        }

        $isLobbyAndUserTriesToOpenDemo = $this->game->hasLobby && $this->gameMode === self::GAME_MODE_DEMO;
        if ($isLobbyAndUserTriesToOpenDemo) {
            return false;
        }

        return true;
    }

    protected function configure(): void
    {
        $shouldInsertOpenGame = !$this->isDemo;
        if ($shouldInsertOpenGame) {
            $this->insertOpenGame();
        }

        $this->whitelabelSlotProviderId = $this->whitelabelSlotProviderRepository->findIdBySlotProviderIdAndWhitelabelId($this->game->slotProviderId, $this->whitelabelId);
        if ($this->whitelabelSlotProviderId === 0) {
            UrlHelper::redirectToHomepage();
        }
        $this->slotProviderId = $this->game->slotProviderId;
        $this->slotGameId = $this->game->id;
        if (!$this->isDemo) {
            $this->whitelabelUserId = $this->user->id;
        }
    }

    private function insertOpenGame(): void
    {
        $slotOpenGame = $this->slotOpenGameRepository->insert(
            $this->whitelabelSlotProviderId,
            $this->game->id,
            $this->user->id,
            $this->user->currencyId
        );

        $this->sessionUuid = $slotOpenGame->sessionId;
    }

    /** This function doesn't add logs for demo mode */
    protected function insertInitLog(): void
    {
        $this->request['url'] = UrlHelper::getCurrentUrlWithParams();

        if (!$this->isDemo) {
            $this->loggerService = Container::get(LoggerService::class);
            $this->loggerService->configure(
                $this->whitelabelUserId,
                $this->whitelabelSlotProviderId,
                $this->action,
                $this->request,
                $this->slotGameId
            );

            $this->loggerService->log($this->response, $this->isError);
        }
    }

    abstract public function init(): string;

    abstract public function initDemo(): string;
}
