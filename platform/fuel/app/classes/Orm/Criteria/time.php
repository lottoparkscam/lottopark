<?php

namespace Classes\Orm\Criteria;

use Classes\Orm\Model_Orm_Criteria;
use Fuel\Core\DB;
use Orm\Query;

/**
 * Class Model_Orm_Criteria_Time
 * Converts DB date to MySql Date format (Y-m-d H:i:s)
 * and allows given $field to be compared against DB value.
 */
class Model_Orm_Criteria_Time extends Model_Orm_Criteria_Date implements Model_Orm_Criteria
{
    public function apply(Query $query)
    {
        $query->where(
            DB::expr("DATE($this->field)"),
            $this->operator,
            $this->date->format('mysql')
        );
        return $this;
    }
}
