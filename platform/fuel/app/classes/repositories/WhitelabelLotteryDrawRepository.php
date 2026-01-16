<?php

namespace Repositories;

use Classes\Orm\Criteria\Model_Orm_Criteria_Select;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Models\LotteryDraw;
use Repositories\Orm\AbstractRepository;

class WhitelabelLotteryDrawRepository extends AbstractRepository
{
    public function __construct(LotteryDraw $model)
    {
        parent::__construct($model);
    }

    public function getNumbersByLotteryIdAndDrawDate(int $lotteryId, string $lotteryDrawDate): ?LotteryDraw
    {
       /**@var LotteryDraw $LotteryDraw */
       $LotteryDraw = $this->pushCriterias([
            new Model_Orm_Criteria_Select(['numbers']),
            new Model_Orm_Criteria_Where('lottery_id', $lotteryId),
            new Model_Orm_Criteria_Where('date_local', $lotteryDrawDate),
        ])->getOne();
        return $LotteryDraw;
    }
}