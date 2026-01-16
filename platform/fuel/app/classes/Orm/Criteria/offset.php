<?php

namespace Classes\Orm\Criteria;

use Classes\Orm\Model_Orm_Criteria;
use Orm\Query;

class Model_Orm_Criteria_Offset implements Model_Orm_Criteria
{
    private int $offset;

    public function __construct(int $offset)
    {
        $this->offset = $offset;
    }

    public function apply(Query $query)
    {
        $query->offset($this->offset);
        return $this;
    }
}
