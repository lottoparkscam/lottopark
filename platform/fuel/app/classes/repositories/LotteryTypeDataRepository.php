<?php

namespace Repositories;

use Models\LotteryTypeData;
use Repositories\Orm\AbstractRepository;

/**
 * @method findByLotteryTypeId(int $lotteryTypeId)
 */
class LotteryTypeDataRepository extends AbstractRepository
{
    public function __construct(LotteryTypeData $model)
    {
        parent::__construct($model);
    }
}
