<?php

namespace Repositories;

use Classes\Orm\Criteria\By\Model_Orm_Criteria_By_Id;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Repositories\Orm\AbstractRepository;
use Models\WhitelabelMultiDrawOption;

class WhitelabelMultiDrawOptionRepository extends AbstractRepository
{
    public function __construct(WhitelabelMultiDrawOption $model)
    {
        parent::__construct($model);
    }

    public function findOneByIdAndWhitelabelId(int $id, int $whitelabelId): ?WhitelabelMultiDrawOption
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_By_Id($id),
            new Model_Orm_Criteria_Where('whitelabel_id', $whitelabelId)
        ]);

        return $this->findOne();
    }
}
