<?php

namespace Classes\Orm\Criteria\Rows;

use Orm\Query;
use Classes\Orm\Model_Orm_Criteria;

class Model_Orm_Criteria_Rows_Limit implements Model_Orm_Criteria
{
    private int $limit;

    public function __construct(int $limit)
    {
        $this->limit = $limit;
    }

    public function apply(Query $query)
    {
        $query->rows_limit($this->limit);
        return $this;
    }
}
