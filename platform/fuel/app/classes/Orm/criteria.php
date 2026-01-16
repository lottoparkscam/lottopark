<?php

namespace Classes\Orm;

use Orm\Query;

interface Model_Orm_Criteria
{
    /**
     * @param Query $query
     *
     * @return static
     */
    public function apply(Query $query);
}
