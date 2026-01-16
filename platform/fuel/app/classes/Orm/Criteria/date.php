<?php

namespace Classes\Orm\Criteria;

use Fuel\Core\Date;
use Fuel\Core\DB;
use Orm\Query;
use Classes\Orm\Model_Orm_Criteria;

/**
 * Class Model_Orm_Criteria_Date
 * Converts DB date to MySql Date format (Y-m-d)
 * and allows given $field to be compared against DB value.
 */
class Model_Orm_Criteria_Date implements Model_Orm_Criteria
{
    /** @var Date */
    protected $date;

    /** @var string */
    protected $operator;

    /** @var string */
    protected $field;

    public function __construct(Date $date, string $field, string $operator = '=')
    {
        $this->date = $date;
        $this->operator = $operator;
        $this->field = $field;
    }

    public function apply(Query $query)
    {
        $query->where(
            DB::expr("DATE($this->field)"),
            $this->operator,
            $this->date->format('mysql_date')
        );
        return $this;
    }
}
