<?php

namespace Repositories;

use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Repositories\Orm\AbstractRepository;
use Models\WhitelabelReferStatistics;

class WhitelabelReferStatisticsRepository extends AbstractRepository
{
    public function __construct(WhitelabelReferStatistics $model)
    {
        parent::__construct($model);
    }

    public function findOneByWhitelabelAndUser(int $whitelabelId, int $whitelabelUserId, int $token): ?WhitelabelReferStatistics
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId),
            new Model_Orm_Criteria_Where('whitelabel_user_id', $whitelabelUserId),
            new Model_Orm_Criteria_Where('token', $token)
        ]);

        return $this->findOne();
    }
}
