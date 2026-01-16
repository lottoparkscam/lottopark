<?php

namespace Repositories;

use Models\WhitelabelBonus;
use Classes\Orm\Criteria\{
    Model_Orm_Criteria_Where,
    Model_Orm_Criteria_Order
};
use Repositories\Orm\AbstractRepository;

class WhitelabelBonusRepository extends AbstractRepository
{
    public function __construct(WhitelabelBonus $model)
    {
        parent::__construct($model);
    }

    public function findByBonusId(int $whitelabelId, int $bonusId): ?WhitelabelBonus
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId),
            new Model_Orm_Criteria_Where('bonus_id', $bonusId),
        ]);

        return $this->findOne();
    }

    public function findWelcomeBonusRaffleByBonusType(int $whitelabelId, string $bonusType): ?WhitelabelBonus
    {
        $findTypeField =  $bonusType . '_raffle_id';
        $nullField = $bonusType . '_lottery_id';

        $this->pushCriterias([
            new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId),
            new Model_Orm_Criteria_Where('bonus_id', WhitelabelBonus::WELCOME),
            new Model_Orm_Criteria_Where($findTypeField, null, 'IS NOT'),
            new Model_Orm_Criteria_Where($nullField, null, 'IS'),
            new Model_Orm_Criteria_Order('id', SORT_DESC)
        ]);

        return $this->findOne();
    }

    public function insert(int $whitelabelId, int $bonusId, array $bonusValue, $minTotalPurchase = null): void
    {
        $bonus = new WhitelabelBonus();

        $bonus->whitelabelId = $whitelabelId;
        $bonus->bonusId = $bonusId;

        foreach ($bonusValue as $key => $value) {
            $bonus->$key = $value;
        }

        $bonus->minTotalPurchase = $minTotalPurchase;

        $bonus->save();
    }
}
