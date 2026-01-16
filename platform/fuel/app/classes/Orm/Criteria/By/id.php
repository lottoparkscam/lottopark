<?php

namespace Classes\Orm\Criteria\By;

use Orm\Query;
use Classes\Orm\Model_Orm_Criteria;

class Model_Orm_Criteria_By_Id implements Model_Orm_Criteria
{
    /** @var mixed $value */
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function apply(Query $query)
    {
        $query->where('id', '=', $this->value);
        return $this;
    }
}
