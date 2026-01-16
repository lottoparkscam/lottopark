<?php

namespace Repositories;

use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Models\WhitelabelAffClick;
use Repositories\Orm\AbstractRepository;

class WhitelabelAffClickRepository extends AbstractRepository
{
    public function __construct(WhitelabelAffClick $model)
    {
        parent::__construct($model);
    }

    public function getClickCountByAffiliateId(int $affiliateId, ?string $startDate = null, ?string $endDate = null): int
    {
        $criteria = [
            new Model_Orm_Criteria_Where('whitelabel_aff_id', $affiliateId),
        ];

        if ($startDate) {
            $criteria[] = new Model_Orm_Criteria_Where('date', $startDate, '>=');
        }

        if ($endDate) {
            $criteria[] = new Model_Orm_Criteria_Where('date', $endDate, '<=');
        }

        $this->pushCriterias($criteria);

        return $this->getCount();
    }
}
