<?php

namespace Repositories;

use Carbon\Carbon;
use Models\WhitelabelBonus;
use Models\WhitelabelUserBonus;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Repositories\Orm\AbstractRepository;

class WhitelabelUserBonusRepository extends AbstractRepository
{
    public function __construct(WhitelabelUserBonus $model)
    {
        parent::__construct($model);
    }

    public function insert(int $bonusId, string $type, string $lotteryType, int $userId): WhitelabelUserBonus
    {
        $userBonus = new WhitelabelUserBonus;
        $userBonus->bonusId = $bonusId;
        $userBonus->type = $type;
        $userBonus->lotteryType = $lotteryType;
        $userBonus->whitelabelUserId = $userId;

        $userBonus->save();

        return $userBonus;
    }

    public function findTypeRegisterRaffleByUserId(int $userId): ?WhitelabelUserBonus
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Where('type', WhitelabelUserBonus::TYPE_REGISTER),
            new Model_Orm_Criteria_Where('lottery_type', WhitelabelUserBonus::TYPE_RAFFLE),
            new Model_Orm_Criteria_Where('whitelabel_user_id', $userId),
        ]);

        return $this->findOne();
    }

    public function useByUser(int $userId): void
    {
        $this->db->update($this->model->get_table_name())
            ->set(['used_at' => Carbon::now()])
            ->where('whitelabel_user_id', '=', $userId)
            ->and_where('used_at', 'IS', null)
            ->execute();
    }

    public function isUsedByUser(int $bonusId, int $userId): bool
    {
        return $this->recordExists(
            [
                new Model_Orm_Criteria_Where('bonus_id', $bonusId),
                new Model_Orm_Criteria_Where('whitelabel_user_id', $userId),
                new Model_Orm_Criteria_Where('used_at', null, 'IS NOT'),
            ]
        );
    }
}
