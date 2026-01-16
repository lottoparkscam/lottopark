<?php

namespace Repositories;

use Carbon\Carbon;
use Classes\Orm\Criteria\Model_Orm_Criteria_Select;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Classes\Orm\Criteria\With\Model_Orm_Criteria_With_Relation;
use Models\MiniGamePromoCode;
use Orm\RecordNotFound;
use Repositories\Orm\AbstractRepository;

class MiniGamePromoCodeRepository extends AbstractRepository
{
    public function __construct(
        MiniGamePromoCode $model,
    ) {
        parent::__construct($model);
    }

    /** @throws RecordNotFound exception */
    public function getActivePromoCodeByCodeAndWhitelabelId(string $code, int $miniGameId, int $whitelabelId): MiniGamePromoCode
    {
        $now = Carbon::now();

        /**@var MiniGamePromoCode $miniGamePromoCode */
        $miniGamePromoCode = $this->pushCriterias([
            new Model_Orm_Criteria_Select(['id', 'free_spin_count', 'free_spin_value', 'usage_limit', 'user_usage_limit']),
            new Model_Orm_Criteria_Where('code', $code),
            new Model_Orm_Criteria_Where('mini_game_id', $miniGameId),
            new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId),
            new Model_Orm_Criteria_Where('is_active', true),
            new Model_Orm_Criteria_Where('date_start', $now->toDateTimeString(), '<='),
            new Model_Orm_Criteria_Where('date_end', $now->toDateTimeString(), '>='),
        ])->getOne();

        return $miniGamePromoCode;
    }

    /**
     *
     * @access public
     * @param int $whitelabelId
     * @return array
     */
    public function getAllPromoCodes(int $whitelabelId): array
    {
        /**@var MiniGamePromoCode $miniGamePromoCode */
        $miniGamePromoCodes = $this->pushCriterias([
            new Model_Orm_Criteria_With_Relation('mini_game'),
            new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId),
        ])->getResults();

        return $miniGamePromoCodes;
    }

    public function isPromoCodeExists(string $code, ?int $ignoreId = null): bool
    {
        $criteria = [
            new Model_Orm_Criteria_Select(['id']),
            new Model_Orm_Criteria_Where('code', $code),
        ];

        if ($ignoreId) {
            $criteria[] = new Model_Orm_Criteria_Where('id', $ignoreId, '!=');
        }

        $miniGamePromoCodes = $this->pushCriterias($criteria)->getResults();

        return !empty($miniGamePromoCodes);
    }
}
