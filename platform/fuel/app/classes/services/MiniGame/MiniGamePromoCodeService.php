<?php

namespace Services\MiniGame;

use Carbon\Carbon;
use Exception;
use Fuel\Core\Input;
use Models\MiniGamePromoCode;
use Models\MiniGameUserPromoCode;
use Models\WhitelabelUser;
use Orm\RecordNotFound;
use Repositories\MiniGamePromoCodeRepository;
use Repositories\MiniGameRepository;
use Repositories\MiniGameUserPromoCodeRepository;
use Services\Logs\FileLoggerService;
use Services\MiniGame\Dto\GameApplyPromoCodeResult;
use Validators\MiniGamePromoCodeValidator;

class MiniGamePromoCodeService
{
    private MiniGameUserPromoCodeRepository $miniGameUserPromoCodeRepository;
    private MiniGamePromoCodeRepository $miniGamePromoCodeRepository;
    private MiniGameRepository $miniGameRepository;
    private FileLoggerService $loggerService;
    private MiniGamePromoCodeValidator $miniGamePromoCodeValidator;
    protected array $errors = [];

    /** @var array<string, string> */
    public const INPUT_NAMES = [
        'code' => 'input.code',
        'miniGameId' => 'input.mini_game_id',
        'freeSpinCount' => 'input.free_spin_count',
        'freeSpinValue' => 'input.free_spin_value',
        'usageLimit' => 'input.usage_limit',
        'userUsageLimit' => 'input.user_usage_limit',
        'dateStart' => 'input.date_start',
        'dateEnd' => 'input.date_end',
        'isActive' => 'input.is_active'
    ];

    public function __construct(
        MiniGameUserPromoCodeRepository $miniGameUserPromoCodeRepository,
        MiniGameRepository $miniGameRepository,
        FileLoggerService $loggerService,
        MiniGamePromoCodeRepository $miniGamePromoCodeRepository,
        MiniGamePromoCodeValidator $miniGamePromoCodeValidator
    )
    {
        $this->miniGameUserPromoCodeRepository = $miniGameUserPromoCodeRepository;
        $this->miniGameRepository = $miniGameRepository;
        $this->loggerService = $loggerService;
        $this->miniGamePromoCodeRepository = $miniGamePromoCodeRepository;
        $this->miniGamePromoCodeValidator = $miniGamePromoCodeValidator;

        $this->loggerService->setSource('api');
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function useFreeSpinFromPromoCode(int $miniGameId, int $userId): void
    {
        try {
            $this->processFreeSpinUsage($miniGameId, $userId);
        } catch (Exception $e) {
            $this->loggerService->error('[Use Free Spin from Promo Code] Error: ' . $e->getMessage());
        }
    }

    /** @throws Exception */
    private function processFreeSpinUsage(int $miniGameId, int $userId): void
    {
        $userPromoCode = $this->miniGameUserPromoCodeRepository->getActivePromoCodeByUserAndMiniGameId($userId, $miniGameId);
        if (!$userPromoCode) {
            return;
        }

        $userPromoCode->usedFreeSpinCount++;

        $hasUsedAllSpins = $userPromoCode->usedFreeSpinCount >= $userPromoCode->miniGamePromoCode->freeSpinCount;
        if ($hasUsedAllSpins) {
            $userPromoCode->hasUsedAllSpins = true;
        }

        $this->miniGameUserPromoCodeRepository->save($userPromoCode);
    }

    /** @throws Exception */
    public function apply(WhitelabelUser $user, string $miniGameSlug, string $promoCode): GameApplyPromoCodeResult
    {
        $miniGame = $this->miniGameRepository->findOneBySlug($miniGameSlug);
        if (!$miniGame) {
            return new GameApplyPromoCodeResult(false, _('Mini game not found'));
        }

        try {
            $promoCode = $this->miniGamePromoCodeRepository->getActivePromoCodeByCodeAndWhitelabelId($promoCode, $miniGame->id, $user->whitelabel_id);
        } catch (RecordNotFound) {
            return new GameApplyPromoCodeResult(false, _('The promo code does not exist or is inactive'));
        }

        if ($this->isPromoCodeUsageExceeded($promoCode->id, $promoCode->usageLimit)) {
            return new GameApplyPromoCodeResult(false, _('The promo code does not exist or is inactive'));
        }

        if ($this->isUserUsageLimitExceeded($user->id, $promoCode->id, $promoCode->userUsageLimit)) {
            return new GameApplyPromoCodeResult(false, _('The provided promo code has already been used'));
        }

        if ($this->hasActivePromoCodeWithUnusedSpins($user->id, $miniGame->id)) {
            return new GameApplyPromoCodeResult(false, _('You already have an active promo code'));
        }

        $this->createUserPromoCode($user->id, $miniGame->id, $promoCode->id);

        return new GameApplyPromoCodeResult(true, _('The promo code has been successfully applied!'));
    }

    private function isPromoCodeUsageExceeded(int $promoCodeId, int $usageLimit): bool
    {
        $totalUsageCount = $this->miniGameUserPromoCodeRepository->countTotalPromoCodeUsage($promoCodeId);

        return $totalUsageCount >= $usageLimit;
    }

    private function isUserUsageLimitExceeded(int $userId, int $promoCodeId, int $userUsageLimit): bool
    {
        $usageCount = $this->miniGameUserPromoCodeRepository->countUserPromoCodeUsage($userId, $promoCodeId);

        return $usageCount >= $userUsageLimit;
    }

    private function hasActivePromoCodeWithUnusedSpins(int $userId, int $miniGameId): bool
    {
        try {
            $userPromoCode = $this->miniGameUserPromoCodeRepository->getActivePromoCodeByUserAndMiniGameId($userId, $miniGameId);
            return $userPromoCode && !$userPromoCode->hasUsedAllSpins;
        } catch (RecordNotFound $e) {
            return false;
        }
    }

    /** @throws Exception */
    private function createUserPromoCode(int $userId, int $miniGameId, int $promoCodeId): void
    {
        $this->miniGameUserPromoCodeRepository->save(
            new MiniGameUserPromoCode([
                'mini_game_promo_code_id' => $promoCodeId,
                'mini_game_id' => $miniGameId,
                'whitelabel_user_id' => $userId,
                'created_at' => Carbon::now()
            ])
        );
    }

    private function getValuesFromInput(): array
    {
        $promoCode = Input::post(self::INPUT_NAMES['code']);
        $miniGameId = (int)Input::post(self::INPUT_NAMES['miniGameId']);
        $freeSpinCount = (int)Input::post(self::INPUT_NAMES['freeSpinCount']);
        $freeSpinValue = (float)Input::post(self::INPUT_NAMES['freeSpinValue']);
        $usageLimit = (int)Input::post(self::INPUT_NAMES['usageLimit']);
        $userUsageLimit = (int)Input::post(self::INPUT_NAMES['userUsageLimit']);
        $dateStart = Input::post(self::INPUT_NAMES['dateStart']);
        $dateEnd = Input::post(self::INPUT_NAMES['dateEnd']);
        $isActive = (bool)Input::post(self::INPUT_NAMES['isActive']);
        $dateStartFormatted = Carbon::parse($dateStart)->format('Y-m-d H:i:s');
        $dateEndFormatted = Carbon::parse($dateEnd)->format('Y-m-d H:i:s');

        return [
            'code' => $promoCode,
            'mini_game_id' => $miniGameId,
            'free_spin_count' => $freeSpinCount,
            'free_spin_value' => $freeSpinValue,
            'usage_limit' => $usageLimit,
            'user_usage_limit' => $userUsageLimit,
            'date_start' => $dateStartFormatted,
            'date_end' => $dateEndFormatted,
            'is_active' => $isActive,
        ];
    }

    public function updateMiniGamePromoCode(int $promoCodeId): bool
    {
        $data = $this->getValuesFromInput();

        if (!$this->validateCreateForm($data, $promoCodeId)) {
            return false;
        }

        $data['date_end'] = str_replace('00:00:00', '23:59:59', $data['date_end']);
        $promoCode = $this->miniGamePromoCodeRepository->findOneBy('id', $promoCodeId);
        $promoCode->set($data);
        $promoCode->save();

        return true;
    }

    public function createMiniGamePromoCode(int $whitelabelId): bool
    {
        $data = $this->getValuesFromInput();
        if (!$this->validateCreateForm($data)) {
            return false;
        }
        $data['whitelabel_id'] = $whitelabelId;
        $data['created_at'] = Carbon::now()->format('Y-m-d H:i:s');

        $promoCode = new MiniGamePromoCode($data);
        $promoCode->save();

        return true;
    }

    private function validateCreateForm(array $data, ?int $promoCodeId = null): bool
    {
        $isRequestInvalid = !$this->miniGamePromoCodeValidator->isValid();
        if ($isRequestInvalid) {
            $this->errors = $this->miniGamePromoCodeValidator->getErrors();
            return false;
        }

        if (!$this->isPromoCodeUnique($data['code'], $promoCodeId)) {
            $this->errors = [_('The code for this mini game already exists.')];
            return false;
        }

        $dateStart = strtotime($data['date_start']);
        $dateEnd   = strtotime($data['date_end']);

        if (!$dateStart || !$dateEnd) {
            $this->errors = [_('Invalid date format. Please use mm/dd/yyyy.')];
            return false;
        }

        if ($dateEnd < $dateStart) {
            $this->errors = [_('The end date cannot be earlier than the start date.')];
            return false;
        }

        return true;
    }

    private function isPromoCodeUnique(string $code, ?int $ignoreId = null): bool
    {
        return !$this->miniGamePromoCodeRepository->isPromoCodeExists($code, $ignoreId);
    }
}
