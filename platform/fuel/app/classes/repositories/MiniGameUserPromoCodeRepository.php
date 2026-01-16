<?php

namespace Repositories;

use Carbon\Carbon;
use Classes\Orm\Criteria\Model_Orm_Criteria_Select;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Classes\Orm\Criteria\With\Model_Orm_Criteria_With_Relation;
use Models\MiniGameUserPromoCode;
use Orm\RecordNotFound;
use Repositories\Orm\AbstractRepository;

class MiniGameUserPromoCodeRepository extends AbstractRepository
{
    public function __construct(
        MiniGameUserPromoCode $model,
    ) {
        parent::__construct($model);
    }

    /** @throws RecordNotFound exception */
    public function getActivePromoCodeByUserAndMiniGameId(int $userId, int $miniGameId): MiniGameUserPromoCode
    {
        $now = Carbon::now();

         /**@var MiniGameUserPromoCode $miniGameUserPromoCode */
        $miniGameUserPromoCode = $this->pushCriterias([
            new Model_Orm_Criteria_With_Relation('mini_game_promo_code'),
            new Model_Orm_Criteria_Select(['used_free_spin_count', 't1.free_spin_count', 'has_used_all_spins']),
            new Model_Orm_Criteria_Where('whitelabel_user_id', $userId),
            new Model_Orm_Criteria_Where('has_used_all_spins', false),
            new Model_Orm_Criteria_Where('mini_game_promo_code.mini_game_id', $miniGameId),
            new Model_Orm_Criteria_Where('mini_game_promo_code.date_start', $now->toDateTimeString(), '<='),
            new Model_Orm_Criteria_Where('mini_game_promo_code.date_end', $now->toDateTimeString(), '>='),
        ])->getOne();

        return $miniGameUserPromoCode;
    }

    public function countTotalPromoCodeUsage(int $promoCodeId): int
    {
        return $this->pushCriteria(
            new Model_Orm_Criteria_Where('mini_game_promo_code_id', $promoCodeId)
        )->getCount();
    }

    public function countUserPromoCodeUsage(int $userId, int $promoCodeId): int
    {
        return $this->pushCriterias([
            new Model_Orm_Criteria_Where('whitelabel_user_id', $userId),
            new Model_Orm_Criteria_Where('mini_game_promo_code_id', $promoCodeId)
        ])->getCount();
    }

    public function getUsersByPromoCodeId(int $promoCodeId): array
    {
        $result = $this->pushCriterias([
            new Model_Orm_Criteria_Select(['t1.code', 't2.token', 't2.name', 't2.surname']),
            new Model_Orm_Criteria_With_Relation('mini_game_promo_code'),
            new Model_Orm_Criteria_With_Relation('whitelabel_user'),
            new Model_Orm_Criteria_Where('mini_game_promo_code_id', $promoCodeId),
        ])
        ->getResults();

        return $result;
    }
}
