<?php

namespace Classes\Orm\Criteria;

use Classes\Orm\Model_Orm_Criteria;
use Orm\Query;

class Model_Orm_Criteria_Having implements Model_Orm_Criteria
{
    /** @var string */
    private $operator;

    /** @var string */
    private $field;

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
        $query->having(
            $this->field,
            $this->operator,
            $this->value
        );
        return $this;
    }
}
