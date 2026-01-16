<?php

namespace Classes\Orm\Criteria\Rows;

use Orm\Query;
use Classes\Orm\Model_Orm_Criteria;

class Model_Orm_Criteria_Rows_Offset implements Model_Orm_Criteria
{
    private int $offset;

    public function __construct(int $offset)
    {
        $this->offset = $offset;
    }

    public function apply(Query $query)
    {
        $query->rows_offset($this->offset);
        return $this;
    }
}
