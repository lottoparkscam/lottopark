<?php

namespace Repositories;

use Classes\Orm\Criteria\Model_Orm_Criteria_Select;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Helpers_Time;
use Models\Lottery;
use Models\LtechManualDraw;
use Repositories\Orm\AbstractRepository;

class LtechManualDrawRepository extends AbstractRepository
{
    public function __construct(LtechManualDraw $model)
    {
        parent::__construct($model);
    }

    public function findForNextDraw(Lottery $lottery): ?LtechManualDraw
    {
        if (empty($lottery->nextDateLocal)) {
            return null;
        }

        $this->pushCriterias([
            new Model_Orm_Criteria_Where('is_processed', false),
            new Model_Orm_Criteria_Where('lottery_id', $lottery->id),
            new Model_Orm_Criteria_Where(
                'current_draw_date',
                $lottery->nextDateLocal->format(Helpers_Time::DATETIME_FORMAT)
            ),
        ]);

        return $this->findOne();
    }

    public function getPendingLotteryIds(): array
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Select(['lottery_id']),
            new Model_Orm_Criteria_Where('is_processed', false),
        ]);

        return $this->getResultsForSingleField();
    }
}
