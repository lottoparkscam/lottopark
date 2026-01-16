<?php

namespace Services\MiniGame;

use Container;
use Exception;
use Fuel\Core\DB;
use Lotto_View;
use Models\MiniGame;
use Models\MiniGameUserPromoCode;
use Models\Whitelabel;
use Models\WhitelabelUser;
use Orm\RecordNotFound;
use Repositories\MiniGamePromoCodeRepository;
use Repositories\MiniGameRepository;
use Repositories\MiniGameTransactionRepository;
use Repositories\MiniGameUserPromoCodeRepository;
use Repositories\Orm\WhitelabelUserRepository;
use Services\Logs\FileLoggerService;
use Services\MiniGame\Dto\GamePlayResult;
use Services\MiniGame\Dto\MiniGameData;
use Services\MiniGame\Dto\MiniGamePlayData;
use Services\MiniGame\Dto\MiniGameResult;
use Services\MiniGame\ValueObject\MiniGameBalanceTransactionObject;
use Services\MiniGame\ValueObject\MiniGameTransactionObject;
use Services_Currency_Calc;
use Throwable;
use Validators\MiniGamePromoCodeValidator;

abstract class AbstractMiniGameService
{
    protected MiniGameRepository $miniGameRepository;
    protected MiniGameTransactionRepository $miniGameTransactionRepository;
    protected WhitelabelUserRepository $whitelabelUserRepository;
    protected MiniGameUserPromoCodeRepository $miniGameUserPromoCodeRepository;

    protected Whitelabel $whitelabel;

    protected Services_Currency_Calc $currencyCalculatorService;
    protected FileLoggerService $loggerService;
    protected MiniGameNumberDrawService $miniGameNumberDrawService;
    protected MiniGamePromoCodeService $miniGamePromoCodeService;
    protected MiniGameTransactionService $miniGameTransactionService;

    public const GAME_CURRENCY = 'EUR';
    public const GAME_SLUG = 'default';

    public const SYSTEM_ERROR_CODE = 0;
    public const GAME_NOT_FOUND_CODE = -1;
    public const BALANCE_IS_INSUFFICIENT_CODE = -2;
    public const PLAYER_DID_NOT_CHOOSE_NUMBER_CODE = -3;

    public function __construct()
    {
        $this->whitelabel = Container::get('whitelabel');

        $this->miniGameRepository = Container::get(MiniGameRepository::class);
        $this->miniGameTransactionRepository = Container::get(MiniGameTransactionRepository::class);
        $this->whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);
        $this->miniGameUserPromoCodeRepository = Container::get(MiniGameUserPromoCodeRepository::class);

        $this->miniGameNumberDrawService = Container::get(MiniGameNumberDrawService::class);
        $this->currencyCalculatorService = Container::get(Services_Currency_Calc::class);
        $this->loggerService = Container::get(FileLoggerService::class);
        $minigamePromoCodeRepository = Container::get(MiniGamePromoCodeRepository::class);
        $miniGamePromoCodeValidator = Container::get(MiniGamePromoCodeValidator::class);
        $this->miniGamePromoCodeService = new MiniGamePromoCodeService(
            $this->miniGameUserPromoCodeRepository,
            $this->miniGameRepository,
            $this->loggerService,
            $minigamePromoCodeRepository,
            $miniGamePromoCodeValidator
        );
        $this->miniGameTransactionService = new MiniGameTransactionService(
            $this->miniGameTransactionRepository,
            $this->whitelabelUserRepository,
            $this->loggerService,
            $this->whitelabel,
        );

        $this->loggerService->setSource('api');
    }

    public function fetchMiniGameData(WhitelabelUser $user, string $slug): MiniGameData
    {
        $miniGame = $this->miniGameRepository->findOneBySlug($slug);
        $gameHistory = $this->miniGameTransactionRepository->fetchAllByMiniGameId($miniGame->id, $user->id);
        [$freeSpinCount, $usedFreeSpinCount, $freeSpinValue, $hasUsedAllSpins] = $this->getFreeSpinsByUser($miniGame->id, $user->id);

        $formattedBalance = $this->formatCurrency($user->balance, $user->currency->code);
        $formattedBonusBalance = $this->formatCurrency($user->bonusBalance, $user->currency->code);

        return new MiniGameData(
            $miniGame->name,
            $miniGame->slug,
            $miniGame->isEnabled,
            $miniGame->multiplier,
            $miniGame->availableBets,
            $miniGame->defaultBet,
            $formattedBalance,
            $formattedBonusBalance,
            $gameHistory,
            $freeSpinCount,
            $usedFreeSpinCount,
            $hasUsedAllSpins,
            $freeSpinValue,
        );
    }

    private function getFreeSpinsByUser(int $miniGameId, int $userId): array
    {
        $userPromoCode = $this->getUserPromoCode($miniGameId, $userId);

        if (!$userPromoCode) {
            return [0, 0, 0.0];
        }

        $promoCode = $userPromoCode->miniGamePromoCode;

        return [$promoCode->freeSpinCount, $userPromoCode->usedFreeSpinCount, $promoCode->freeSpinValue, $userPromoCode->hasUsedAllSpins, $userPromoCode->id];
    }

    private function getUserPromoCode(int $miniGameId, int $userId): ?MiniGameUserPromoCode
    {
        try {
            return $this->miniGameUserPromoCodeRepository->getActivePromoCodeByUserAndMiniGameId($userId, $miniGameId);
        } catch (RecordNotFound) {
            return null;
        }
    }

    public function play(WhitelabelUser $user, string $slug, int $selectedNumber, float $betAmountInEur): GamePlayResult
    {
        $miniGame = $this->miniGameRepository->findOneBySlug($slug);
        if (!$miniGame) {
            return new GamePlayResult(false, self::GAME_NOT_FOUND_CODE);
        }

        $gameData = $this->prepareGamePlay($miniGame, $user, $betAmountInEur);
        if (!$gameData->isSelectionValid($selectedNumber) && !$gameData->isFreeSpin()) {
            return new GamePlayResult(false, self::PLAYER_DID_NOT_CHOOSE_NUMBER_CODE);
        }

        if (!$gameData->hasSufficientBalance() && !$gameData->isFreeSpin()) {
            return new GamePlayResult(false, self::BALANCE_IS_INSUFFICIENT_CODE);
        }

        $systemDrawnNumber = $this->getSystemDrawnNumber($miniGame);
        if ($systemDrawnNumber === null) {
            return new GamePlayResult(false, self::SYSTEM_ERROR_CODE);
        }

        $isWin = $this->determineWin($systemDrawnNumber, $selectedNumber);
        $prizeAmountInEur = $this->calculatePrize($gameData->getBetAmount(), $miniGame->multiplier, $isWin);
        $prizeAmountInUserCurrency = $this->getPrizeAmountInUserCurrency($user, $prizeAmountInEur);

        return $this->handleGameTransaction(
            new MiniGameTransactionObject(
                $miniGame,
                $user,
                $gameData->getBetAmount(),
                $prizeAmountInEur,
                $isWin,
                $selectedNumber,
                $systemDrawnNumber,
                $gameData->getPromoCodeUserId(),
                $gameData->getBetAmountInUserCurrency(),
                $prizeAmountInUserCurrency,
                $gameData->isFreeSpin(),
                [],
            )
        );
    }

    protected function getPrizeAmountInUserCurrency(WhitelabelUser $user, float $prizeAmountInEur): float
    {
        return $this->currencyCalculatorService->convert_to_any(
            $prizeAmountInEur,
            self::GAME_CURRENCY,
            $user->currency->code
        );
    }

    protected function prepareGamePlay(MiniGame $miniGame, WhitelabelUser $user, float $betAmountInEur): MiniGamePlayData
    {
        [$freeSpinCount, $usedFreeSpinCount, $freeSpinValue, $hasUsedAllSpins, $userPromoCodeId] = $this->getFreeSpinsByUser($miniGame->id, $user->id);
        $isFreeSpin = $betAmountInEur === 0.0 && $usedFreeSpinCount < $freeSpinCount;

        if ($isFreeSpin) {
            $betAmountInEur = $this->applyFreeSpin($miniGame->id, $user->id, $freeSpinValue);
        }

        return new MiniGamePlayData(
            $miniGame,
            $user,
            $betAmountInEur,
            $this->getBetAmountInUserCurrency($user, $betAmountInEur),
            $userPromoCodeId,
            $isFreeSpin,
        );
    }

    private function getBetAmountInUserCurrency(WhitelabelUser $user, float $betAmountInEur): float
    {
        return $this->currencyCalculatorService->convert_to_any(
            $betAmountInEur,
            self::GAME_CURRENCY,
            $user->currency->code
        );
    }

    private function applyFreeSpin(int $miniGameId, int $userId, float $freeSpinValue): float
    {
        $this->useFreeSpinFromPromoCode($miniGameId, $userId);

        return $freeSpinValue;
    }

    protected function getSystemDrawnNumber(MiniGame $miniGame): ?int
    {
        try {
            return $this->fetchSystemDrawnNumber($miniGame);
        } catch (Throwable $t) {
            $this->loggerService->error('[' . self::GAME_SLUG . ' - Fetch System Number] Error fetching system number: ' . $t->getMessage());
            return null;
        }
    }

    protected function handleGameTransaction(MiniGameTransactionObject $transaction): GamePlayResult
    {
        DB::start_transaction();

        try {
            $isUsedBonusBalance = $this->miniGameTransactionService->updateUserBalance(
                new MiniGameBalanceTransactionObject(
                    $transaction->getUser(),
                    $transaction->getBetAmountInUserCurrency(),
                    $transaction->getPrizeAmountInUserCurrency(),
                    $transaction->isWin(),
                    $transaction->isFreeSpin(),
                )
            );

            if ($isUsedBonusBalance) {
                $transaction->setBonusBalancePaid(true);
            }

            $this->miniGameTransactionService->saveGameTransaction($transaction);

            DB::commit_transaction();
        } catch (Exception $e) {
            DB::rollback_transaction();
            $this->loggerService->error('[' . self::GAME_SLUG . ' - Transaction] Error during transaction: ' . $e->getMessage());
            return new GamePlayResult(false, self::SYSTEM_ERROR_CODE);
        }

        $miniGameResult = new MiniGameResult($transaction, $isUsedBonusBalance);

        return new GamePlayResult(true, null, $miniGameResult);
    }

    private function useFreeSpinFromPromoCode(int $miniGameId, int $userId): void
    {
        try {
            $this->miniGamePromoCodeService->useFreeSpinFromPromoCode($miniGameId, $userId);
        } catch (Throwable $t) {
            $this->loggerService->error('[' . self::GAME_SLUG . ' - Use Free Spin from Promo Code] Error: ' . $t->getMessage());
        }
    }

    /** @throws Exception */
    protected function fetchSystemDrawnNumber(MiniGame $miniGame): int
    {
        return $this->miniGameNumberDrawService->fetchSystemDrawnNumber($miniGame);
    }

    protected function determineWin(int $systemDrawnNumber, int $selectedNumber): bool
    {
        // first, determine the initial outcome with a 50/50 chance.
        // systemDrawnNumber === selectedNumber means initial WIN (50%)
        // otherwise initial LOST (50%).
        $isWin = ($systemDrawnNumber === $selectedNumber);

        // if initially WIN, perform an additional random check (0-99):
        // if the random number is < 10 (which is 10% of the time),
        // convert the WIN to LOST. This results in:
        // - initially: 50% WIN, 50% LOST
        // - after conversion: 45% WIN, 55% LOST (because 10% of 50% is 5%)
        if ($isWin) {
            $random = random_int(0, 99);
            if ($random < 10) {
                // Convert WIN to LOST
                return false;
            }

            return true;
        }

        return false;
    }

    protected function calculatePrize(float $betAmount, float $multiplier, bool $isWin): float
    {
        return $isWin ? bcmul($betAmount, $multiplier, 2) : 0.0;
    }

    protected function formatCurrency(float $amount, string $currencyCode): string
    {
        return Lotto_View::format_currency($amount, $currencyCode, true);
    }
}
