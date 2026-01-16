<?php

namespace Classes\Orm\Criteria;

use Classes\Orm\Model_Orm_Criteria;
use Carbon\Carbon;
use Orm\Query;

class Model_Orm_Criteria_Today implements Model_Orm_Criteria
{
    private string $timezone;

    private string $field;

    public function __construct(string $field, string $timezone = 'UTC')
    {
        $this->timezone = $timezone;
        $this->field = $field;
    }

    public function apply(Query $query)
    {
        $startDateTime = Carbon::now($this->timezone)->startOfDay();
        $endDateTime = Carbon::now($this->timezone)->endOfDay();

        $query->where(
            $this->field,
            '>=',
            $startDateTime
        );

        $query->where(
            $this->field,
            '<=',
            $endDateTime
        );

        return $this;
    }
}
