<?php

use Abstracts\Controllers\Internal\AbstractPublicController;
use Fuel\Core\Input;
use Fuel\Core\Response;
use Helpers\SanitizerHelper;
use Helpers\UserHelper;
use Models\WhitelabelUser;
use Services\Logs\FileLoggerService;
use Services\MiniGame\AbstractMiniGameService;
use Services\MiniGame\Factory\MiniGameServiceFactory;
use Services\MiniGame\MiniGamePromoCodeService;

class Controller_Api_Internal_MiniGames extends AbstractPublicController
{
    protected FileLoggerService $loggerService;
    protected MiniGameServiceFactory $miniGameServiceFactory;
    protected MiniGamePromoCodeService $miniGamePromoCodeService;

    public function before(): void
    {
        parent::before();

        putenv('LC_ALL=en_GB.utf8');
        setlocale(LC_ALL, 'en_GB.utf8');

        $this->miniGameServiceFactory = Container::get(MiniGameServiceFactory::class);
        $this->miniGamePromoCodeService = Container::get(MiniGamePromoCodeService::class);
        $this->loggerService = Container::get(FileLoggerService::class);

        $this->loggerService->setSource('api');
    }

    protected function getUserOrFail(): ?WhitelabelUser
    {
        $user = UserHelper::getUser();
        if (!$user) {
            $this->returnUnauthorized();
        }

        return $user;
    }

    protected function returnUnauthorized(): Response
    {
        return $this->returnResponse([
            'error' => 'Unauthorized'
        ], 403);
    }

    protected function handleException(Throwable $e, string $context): Response
    {
        $this->loggerService->error("[MiniGames - $context] Error message: " . $e->getMessage());
        return $this->returnResponse(['errorCode' => AbstractMiniGameService::SYSTEM_ERROR_CODE], 500);
    }

    public function get_index(): Response
    {
        $user = $this->getUserOrFail();
        if (!$user) {
            return $this->returnUnauthorized();
        }

        $miniGameSlug = SanitizerHelper::sanitizeSlug(Input::get('slug') ?? '');

        return $this->processFetchMiniGame($user, $miniGameSlug);
    }

    private function processFetchMiniGame(WhitelabelUser $user, string $miniGameSlug): Response
    {
        try {
            $miniGameService = $this->miniGameServiceFactory->getServiceBySlug($miniGameSlug);
            $miniGameData = $miniGameService->fetchMiniGameData($user, $miniGameSlug);

            if (!$miniGameData->isEnabled()) {
                return $this->returnResponse(['errorCode' => AbstractMiniGameService::GAME_NOT_FOUND_CODE], 404);
            }

            return $this->returnResponse($miniGameData->toArray());
        } catch (Throwable $e) {
            return $this->handleException($e, 'fetch');
        }
    }

    public function post_play(): Response
    {
        $user = $this->getUserOrFail();
        if (!$user) {
            return $this->returnUnauthorized();
        }

        $miniGameSlug = SanitizerHelper::sanitizeSlug(Input::post('slug'));
        $userSelectedNumber = (int)Input::post('userSelectedNumber');
        $userSelectedAmount = (float)SanitizerHelper::sanitizeString(Input::post('userSelectedAmountInEur'));

        try {
            $miniGameService = $this->miniGameServiceFactory->getServiceBySlug($miniGameSlug);
            $gamePlayResult = $miniGameService->play($user, $miniGameSlug, $userSelectedNumber, $userSelectedAmount);

            if ($gamePlayResult->isSuccess()) {
                return $this->returnResponse($gamePlayResult->getResult()->toArray());
            }

            return $this->returnResponse(['errorCode' => $gamePlayResult->getErrorCode()]);
        } catch (Exception $e) {
            return $this->handleException($e, 'play');
        }
    }

    public function post_applyPromoCode(): Response
    {
        $user = $this->getUserOrFail();
        if (!$user) {
            return $this->returnUnauthorized();
        }

        $miniGameSlug = SanitizerHelper::sanitizeSlug(Input::post('slug'));
        $promoCode = SanitizerHelper::sanitizeSlug(Input::post('promoCode'));

        return $this->processApplyPromoCode($user, $miniGameSlug, $promoCode);
    }

    private function processApplyPromoCode(WhitelabelUser $user, string $miniGameSlug, string $promoCode): Response
    {
        try {
            $gameApplyPromoCodeResult = $this->miniGamePromoCodeService->apply($user, $miniGameSlug, $promoCode);
            return $this->returnResponse($gameApplyPromoCodeResult->toArray());
        } catch (Exception $e) {
            return $this->handleException($e, 'applyPromoCode');
        }
    }
}
