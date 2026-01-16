<?php

namespace Repositories;

use Carbon\Carbon;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Repositories\Orm\AbstractRepository;
use Models\LotteryDelay;

class LotteryDelayRepository extends AbstractRepository
{
    public function __construct(LotteryDelay $model)
    {
        parent::__construct($model);
    }

    public function isNextDrawDelayed(int $lotteryId, Carbon $nextDateLocal): bool
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Where('lottery_id', $lotteryId),
            new Model_Orm_Criteria_Where('date_delay', $nextDateLocal)
        ]);

        return $this->getCount() > 0;
    }

    public function getNextDrawBeforeDelay(int $lotteryId, Carbon $nextDateLocal): ?LotteryDelay
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Where('lottery_id', $lotteryId),
            new Model_Orm_Criteria_Where('date_delay', $nextDateLocal)
        ]);

        return $this->findOne();
    }
}
