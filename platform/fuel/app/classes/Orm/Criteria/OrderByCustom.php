<?php

namespace Classes\Orm\Criteria;

use Classes\Orm\Model_Orm_Criteria;
use Fuel\Core\DB;
use Orm\Query;

class CriteriaOrderByCustom implements Model_Orm_Criteria
{
    private string $field;
    private array $values;

    public function __construct(string $field, array $values)
    {
        $this->field = $field;
        $this->values = array_reverse($values);
    }

    public function apply(Query $query)
    {
        if (empty($this->values)) {
            return $this;
        }

        $field = $this->field;
        $values = '';
        foreach ($this->values as $value) {
            $values .= "'$value',";
        }
        $values = rtrim($values, ',');

        $query->order_by(
            DB::expr("FIELD($field, $values)"),
            'DESC' // mysql has bugged ASC FIELD()
        );
        return $this;
    }
}
