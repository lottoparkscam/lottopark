<?php

namespace Repositories;

use Exception;
use Models\MiniGameTransaction;
use Repositories\Orm\AbstractRepository;

class MiniGameTransactionRepository extends AbstractRepository
{
    public function __construct(
        MiniGameTransaction $model,
    ) {
        parent::__construct($model);
    }

    public function fetchAllByMiniGameId(int $miniGameId, int $whitelabelUserId): array
    {
        /** @var mixed $query */
        $query = $this->db
            ->selectArray(['type', 'amount', 'user_selected_number', 'system_drawn_number', 'prize', 'mini_game_user_promo_code_id', 'is_bonus_balance_paid', 'created_at'])
            ->from($this->model::get_table_name())
            ->where('mini_game_id', $miniGameId)
            ->where('whitelabel_user_id', $whitelabelUserId)
            ->order_by('id', 'desc')
            ->limit(10)
            ->execute();

        return $query->as_array() ?? [];
    }

    /** @throws Exception */
    public function saveTransaction(MiniGameTransaction $miniGameTransaction): void
    {
        $miniGameTransaction->save();
    }
}
