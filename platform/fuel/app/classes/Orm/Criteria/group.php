<?php

namespace Classes\Orm\Criteria;

use Classes\Orm\Model_Orm_Criteria;
use Orm\Query;

class Model_Orm_Criteria_Group implements Model_Orm_Criteria
{
    private array $columns;

    public function __construct(...$columns)
    {
        $this->columns = [...$columns];
    }

    public function apply(Query $query)
    {
        $query->group_by(...$this->columns);
        return $this;
    }
}
