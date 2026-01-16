<?php

use Fuel\Core\Cache;
use Fuel\Core\Input;
use Fuel\Core\Request;
use Helpers\SanitizerHelper;
use Repositories\WhitelabelSlotGameOrderRepository;
use Repositories\AdminWhitelabelRepository;
use Repositories\WhitelabelRepository;

class Controller_Crm_Modules_Settings_Casino_GamesOrder extends Controller_Crm
{
    protected AdminWhitelabelRepository $adminWhitelabelRepository;
    protected WhitelabelRepository $whitelabelRepository;
    private WhitelabelSlotGameOrderRepository $whitelabelSlotGameOrderRepository;

    private $user;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->whitelabelSlotGameOrderRepository = Container::get(WhitelabelSlotGameOrderRepository::class);
        $this->whitelabelRepository = Container::get(WhitelabelRepository::class);
        $this->adminWhitelabelRepository = Container::get(AdminWhitelabelRepository::class);

        $this->user = $this->current_user();
        if (empty($this->user)) {
            $error = Request::forge('index/404')->execute();
            echo $error;
            exit;
        }
    }

    public function get_enabled_games_for_whitelabel(): array
    {
        return [
            'code' => 200,
            'enabledSlotGames' => $this->slotGameRepository->findEnabledGamesByWhitelabelId($this->getWhitelabelId())
        ];
    }

    /**
     * We don't check here if games are available, user will get info on front.
     * If game was temporary unavailable during games synchronization, it would remove it.
     */
    public function get_games_order(): array
    {
        $category = SanitizerHelper::sanitizeString(Input::get('category') ?? '');

        try {
            $gamesOrder = $this->whitelabelSlotGameOrderRepository->findCurrentGameOrderByWhitelabelId($this->getWhitelabelId(), $category) ?? [];
            return [
                'code' => 200,
                'orderWithGameData' => $gamesOrder['orderWithGameData'] ?? []
            ];
        } catch (Throwable $e) {
            $this->fileLoggerService->error($e->getMessage());
            return [
                'code' => 500,
            ];
        }
    }

    public function post_update_games_order(): array
    {
        $orderJson = Input::json('newGamesOrder');
        $category = Input::json('category');

        try {
            $isNotUpdated = !$this->whitelabelSlotGameOrderRepository->updateOrderJson(
                $this->getWhitelabelId(true),
                $orderJson,
                $category
            );
            if ($isNotUpdated) {
                throw new Exception('Cannot update slot games order.');
            }

            return [
                'code' => 200,
            ];
        } catch (Throwable $e) {
            $this->fileLoggerService->error($e->getMessage());
            return [
                'code' => 500,
            ];
        }
    }

    public function post_delete_game_from_order(): array
    {
        $orderJson = Input::json('newGamesOrder');
        $category = Input::json('category');

        try {
            $isNotUpdated = !$this->whitelabelSlotGameOrderRepository->deleteGameFromJson(
                $this->getWhitelabelId(true),
                $orderJson,
                $category,
            );
            if ($isNotUpdated) {
                throw new Exception('Cannot update slot games order.');
            }

            return [
                'code' => 200,
            ];
        } catch (Throwable $e) {
            $this->fileLoggerService->error($e->getMessage());
            return [
                'code' => 500,
            ];
        }
    }

    // ?whitelabelId=${whitelabelId} GET from js script
    // { whitelabelId: whitelabelId } POST from js script
    private function getWhitelabelId(bool $fromPostMethod = false): int
    {
        return $fromPostMethod ? (int)Input::json('whitelabelId') : (int)Input::get('whitelabelId');
    }

    private function getWhitelabelThemeByWhitelabelId(int $whitelabelId): string
    {
        return $this->whitelabelRepository->findOneById($whitelabelId)->theme ?: '';
    }

    public function get_clean_cache(): void
    {
        $section = $this->getWhitelabelThemeByWhitelabelId($this->getWhitelabelId()) . '.slots.games';
        Cache::delete_all($section);
    }
}
