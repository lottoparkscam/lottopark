<?php

namespace Repositories\Aff;

use Classes\Orm\Criteria\Model_Orm_Criteria_Select;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Classes\Orm\Criteria\Rows\Model_Orm_Criteria_Rows_Limit;
use Repositories\Orm\AbstractRepository;
use Models\WhitelabelAffClick;

class WhitelabelAffClickRepository extends AbstractRepository
{
    public function __construct(WhitelabelAffClick $model)
    {
        parent::__construct($model);
    }

    public function takeLastClickIdByWhitelabelAffId($whitelabelAffId): int
    {
        return $this->model->push_criterias([
            new Model_Orm_Criteria_Select(['id']),
            new Model_Orm_Criteria_Where('whitelabel_aff_id', $whitelabelAffId, '='),
            new Model_Orm_Criteria_Rows_Limit(1),
        ])
            ->order_by('id')
            ->get_one()->id;
    }
}
