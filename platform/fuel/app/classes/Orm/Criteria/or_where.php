<?php

namespace Classes\Orm\Criteria;

use Classes\Orm\Model_Orm_Criteria;
use Orm\Query;

class Model_Orm_Criteria_Or_Where implements Model_Orm_Criteria
{
    private string $operator;

    private string $field;

    /** @var mixed */
    private $value;

    public function __construct(string $field, $value, string $operator = '=')
    {
        $this->operator = $operator;
        $this->field = $field;
        $this->value = $value;
    }

    public function apply(Query $query)
    {
        $query->or_where(
            $this->field,
            $this->operator,
            $this->value
        );
        return $this;
    }
}
