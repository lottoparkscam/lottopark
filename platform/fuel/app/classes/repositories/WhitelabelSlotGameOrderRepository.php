<?php

namespace Repositories;

use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Repositories\Orm\AbstractRepository;
use Models\WhitelabelSlotGameOrder;

/** 
 * @method WhitelabelSlotGameOrder findOneByWhitelabelId(int $whitelabelId) 
 */
class WhitelabelSlotGameOrderRepository extends AbstractRepository
{
    private SlotGameRepository $slotGameRepository;

    public function __construct(WhitelabelSlotGameOrder $model, SlotGameRepository $slotGameRepository)
    {
        parent::__construct($model);
        $this->slotGameRepository = $slotGameRepository;
    }

    public function findCurrentGameOrderByWhitelabelId(int $whitelabelId, string $category): array
    {
        $this->pushCriteria(
            new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId)
        );

        /** @var WhitelabelSlotGameOrder $currentOrder */
        $currentOrder = $this->findOne();
        $orderJson = $currentOrder->orderJson[$category] ?? [];
        if (empty($orderJson)) {
            return [];
        }
        
        $orderWithGameData = [];

        $gameIds = array_column($orderJson, 'gameId');
        $games = $this->slotGameRepository->findByIds($gameIds);

        foreach ($orderJson as $orderedGame) {
            $gameId = (int) $orderedGame['gameId'];
            $gameOrder = (int) $orderedGame['gameOrder'];
            $game = $games[$gameId] ?? [];
            if (empty($game)) {
                continue;
            }

            $orderWithGameData[] = [
                'gameOrder' => (int) $gameOrder,
                'game' => $game
            ];
        }

        return [
            'orderWithGameData' => $orderWithGameData
        ] ?? [];
    }

    public function deleteGameFromJson(int $whitelabelId, array $orderJson, string $category): bool
    {
        $whitelabelSlotGameOrder = $this->findOneByWhitelabelId($whitelabelId) ?? [];

        $newOrder = [];
        foreach ($orderJson as $orderedGame) {
            $gameId = (int) $orderedGame['game']['id'];
            $gameOrder = $orderedGame['gameOrder'];
            $newOrder[$category][] = [
                'gameOrder' => (int) $gameOrder,
                'gameId' => (int) $gameId
            ];
        }

        $whitelabelSlotGameOrder->orderJson = $newOrder;
        return $whitelabelSlotGameOrder->save();
    }

    public function updateOrderJson(int $whitelabelId, array $orderJson, string $category): bool
    {
        $recordNotExists = $this->recordNotExists([
            new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId)
        ]);

        $databaseOrderFormat = [];
        foreach ($orderJson as $game) {
            $databaseOrderFormat[$category][] = [
                'gameId' => (int) $game['game']['id'],
                'gameOrder' => (int) $game['gameOrder']
            ];
        }

        if ($recordNotExists) {
            $whitelabelSlotGameOrder = new WhitelabelSlotGameOrder();
            $whitelabelSlotGameOrder->whitelabelId = $whitelabelId;
            $whitelabelSlotGameOrder->orderJson = $databaseOrderFormat;
            return $whitelabelSlotGameOrder->save();
        }

        $whitelabelSlotGameOrder = $this->findOneByWhitelabelId($whitelabelId);
        $whitelabelSlotGameOrder->orderJson = $databaseOrderFormat;
        return $whitelabelSlotGameOrder->save();
    }
}
