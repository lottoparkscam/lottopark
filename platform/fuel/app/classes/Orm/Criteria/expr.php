<?php

namespace Classes\Orm\Criteria;

use Fuel\Core\Database_Expression;
use Orm\Query;
use Classes\Orm\Model_Orm_Criteria;

class Model_Orm_Criteria_Expr implements Model_Orm_Criteria
{
    /** @var Database_Expression */
    private $expression;

    public function __construct(Database_Expression $expression)
    {
        $this->expression = $expression;
    }

    public function apply(Query $query)
    {
        $query->where($this->expression);
        return $this;
    }
}
