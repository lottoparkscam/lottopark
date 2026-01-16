<?php

namespace Repositories;

use Classes\Orm\Criteria\Model_Orm_Criteria_Select;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Repositories\Orm\AbstractRepository;
use Models\SlotSubprovider;

class SlotSubproviderRepository extends AbstractRepository
{
    public function __construct(SlotSubprovider $model)
    {
        parent::__construct($model);
    }

    public function getAllNames(): array
    {
        $this->pushCriteria(new Model_Orm_Criteria_Select(['name']));

        return $this->getResultsForSingleField() ?? [];
    }

    public function getIdsByNames(array $names): array
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Select(['id']),
            new Model_Orm_Criteria_Where('name', $names, 'IN')
        ]);

        return $this->getResultsForSingleField() ?? [];
    }
}
