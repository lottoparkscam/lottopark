<?php

namespace Classes\Orm\Criteria;

use Classes\Orm\Model_Orm_Criteria;
use Orm\Query;

class Model_Orm_Criteria_Select implements Model_Orm_Criteria
{
    private array $fields;

    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    public function apply(Query $query)
    {
        $query->select($this->fields);
        return $this;
    }

    public function getSingleField(): string
    {
        $lastField = end($this->fields);
        if (is_array($lastField)) {
            return end($lastField);
        }

        return $lastField;
    }
}
