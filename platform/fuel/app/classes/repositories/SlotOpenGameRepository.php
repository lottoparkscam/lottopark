<?php

namespace Repositories;

use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Models\SlotTransaction;
use Repositories\Orm\AbstractRepository;
use Models\SlotOpenGame;
use Wrappers\Db;
use Carbon\Carbon;
use Models\SlotGame;

class SlotOpenGameRepository extends AbstractRepository
{
    public function __construct(SlotOpenGame $model, Db $db)
    {
        parent::__construct($model);
        $this->db = $db;
    }

    public function findOneBySessionIdAndWhitelabelSlotProviderId(
        string $sessionId,
        int $whitelabelSlotProviderId
    ): ?SlotOpenGame {
        $this->pushCriterias([
            new Model_Orm_Criteria_Where('session_id', $sessionId),
            new Model_Orm_Criteria_Where('whitelabel_slot_provider_id', $whitelabelSlotProviderId)
        ]);
        return $this->findOne();
    }

    /** @return SlotTransaction[] */
    public function findSummedUpTransactionsWithSortAndFiltersByWhitelabelUserId(
        int $whitelabelUserId,
        int $offset = 0,
        int $limit = 10,
        array $orderBy = []
    ): array {
        $query = $this->db->selectArray([
            $this->db->expr('MIN(slot_game.name) AS game_name'),
            $this->db->expr('MIN(slot_game.provider) AS game_provider_name'),
            $this->db->expr('MIN(slot_open_game.created_at) AS date'),
            $this->db->expr('MIN(concat(slot_open_game.session_id, "_", slot_open_game.slot_game_id)) AS session_id'),
            $this->db->expr('MIN(currency.code) AS currency_code'),
            $this->db->expr('SUM(
                CASE 
                    WHEN slot_transaction.action = "bet" 
                    THEN COALESCE(slot_transaction.amount, 0) * -1 
                    ELSE COALESCE(slot_transaction.amount, 0) 
                END
            ) AS amount')
        ])
            ->from('slot_open_game')
            ->join('slot_game', 'LEFT')
            ->on('slot_open_game.slot_game_id', '=', 'slot_game.id')
            ->join('slot_transaction', 'LEFT')
            ->on('slot_transaction.slot_open_game_id', '=', 'slot_open_game.id')
            ->join('currency', 'LEFT')
            ->on('slot_open_game.currency_id', '=', 'currency.id')
            ->where('slot_transaction.is_canceled', '=', false)
            ->and_where('slot_transaction.action', 'IN', [
                SlotTransaction::ACTION_BET,
                SlotTransaction::ACTION_WIN
            ])
            ->and_where('slot_open_game.whitelabel_user_id', $whitelabelUserId)
            ->group_by('slot_open_game.session_id', 'slot_open_game.slot_game_id');

        foreach ($orderBy as $column => $order) {
            $query->order_by($column, $order);
        }

        /** @var mixed $result */
        $result = $query->limit($limit)
            ->offset($offset)
            ->execute();

        return $result->as_array();
    }

    public function countOpenGamesByUserIdWithAnyTransaction(int $whitelabelUserId): int
    {
        /** @var mixed $result */
        $result = $this->db->selectArray([
            [$this->db->expr('COUNT(DISTINCT session_id)'), 'open_game_count']
        ])
            ->from('slot_transaction')
            ->join('slot_open_game', 'LEFT')
            ->on('slot_open_game.id', '=', 'slot_transaction.slot_open_game_id')
            ->where('slot_transaction.whitelabel_user_id', $whitelabelUserId)
            ->and_where('slot_transaction.is_canceled', '=', false)
            ->and_where('slot_transaction.action', 'IN', [
                SlotTransaction::ACTION_BET,
                SlotTransaction::ACTION_WIN
            ])
            ->execute();

        return $result->as_array()[0]['open_game_count'];
    }

    public function insert(
        int $whitelabelSlotProviderId,
        int $slotGameId,
        int $whitelabelUserId,
        int $currencyId,
        ?int $sessionId = null // only for lobby games, don't create session id after game change inside lobby
    ): SlotOpenGame {
        $openGame = new SlotOpenGame();
        $openGame->whitelabelSlotProviderId = $whitelabelSlotProviderId;
        $openGame->slotGameId = $slotGameId;
        $openGame->whitelabelUserId = $whitelabelUserId;
        $openGame->currencyId = $currencyId;
        $openGame->sessionId = $sessionId ?? $this->db->expr('UUID_SHORT()');
        $openGame->createdAt = new Carbon($openGame->getTimezoneForField('createdAt'));
        $openGame->save();

        SlotOpenGame::flush_cache();
        $openGame->reload();

        return $openGame;
    }

    public function userHasChangedGameInLobby(
        int $whitelabelUserId,
        int $whitelabelSlotProviderId,
        int $receivedSlotGameId,
        int $sessionId
    ): bool {
        return $this->recordNotExists([
            new Model_Orm_Criteria_Where('whitelabel_user_id', $whitelabelUserId),
            new Model_Orm_Criteria_Where('whitelabel_slot_provider_id', $whitelabelSlotProviderId),
            new Model_Orm_Criteria_Where('slot_game_id', $receivedSlotGameId),
            new Model_Orm_Criteria_Where('session_id', $sessionId),
        ]);
    }

    public function getBySessionIdAndGameIdAndWhitelabelUserId(
        int $whitelabelUserId,
        int $slotGameId,
        int $sessionId
    ): ?SlotOpenGame {
        return $this->pushCriterias([
            new Model_Orm_Criteria_Where('whitelabel_user_id', $whitelabelUserId),
            new Model_Orm_Criteria_Where('slot_game_id', $slotGameId),
            new Model_Orm_Criteria_Where('session_id', $sessionId)
        ])->findOne();
    }

    public function findOneSlotGameBySessionId(int $sessionId, int $whitelabelUserId): ?SlotGame
    {
        /** @var SlotOpenGame $slotOpenGame */
        $slotOpenGame = $this->pushCriterias([
            new Model_Orm_Criteria_Where('whitelabel_user_id', $whitelabelUserId),
            new Model_Orm_Criteria_Where('session_id', $sessionId)
        ])->findOne();

        return $slotOpenGame->slotGame ?? null;
    }
}
