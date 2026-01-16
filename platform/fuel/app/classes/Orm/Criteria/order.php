<?php

namespace Classes\Orm\Criteria;

use Classes\Orm\Model_Orm_Criteria;
use Orm\Query;

class Model_Orm_Criteria_Order implements Model_Orm_Criteria
{
    /** @var array|string */
    private $field;

    /** @var string */
    private $direction;

    /**
     * Model_Orm_Criteria_Order constructor.
     *
     * @param array|string $field
     * @param string $direction
     */
    public function __construct($field, string $direction = 'asc')
    {
        $this->field = $field;
        $this->direction = $direction;
    }

    public function apply(Query $query)
    {
        $query->order_by(
            $this->field,
            $this->direction
        );
        return $this;
    }
}
