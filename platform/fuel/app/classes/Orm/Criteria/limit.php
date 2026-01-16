<?php

namespace Classes\Orm\Criteria;

use Classes\Orm\Model_Orm_Criteria;
use Orm\Query;

class Model_Orm_Criteria_Limit implements Model_Orm_Criteria
{
    private int $limit;

    public function __construct(int $limit)
    {
        $this->limit = $limit;
    }

    public function apply(Query $query)
    {
        $query->limit($this->limit);
        return $this;
    }
}
