<?php

namespace Classes\Orm\Criteria\With;

use Orm\Query;
use Classes\Orm\Model_Orm_Criteria;

/**
 * Class Model_Orm_Criteria_With_Relation
 * Adds eager loading to relation.
 *
 * Attention!
 * If You want to use nested relations with conditions then
 * each condition must be in sub array, for example:
 *
 * relation1.relation2
 * should receive $conditions:
 * [
 *  'relation1' => ['where' => cdn, 'order_by' => cdn],
 *  'relation12 => ['where' => cdn, 'order_by' => cdn],
 * ]
 *
 * If no nested relations then pass just a regular syntax (like Fuel doc says):
 * ['where' => cdn, 'order_by' => cdn]
 */
class Model_Orm_Criteria_With_Relation implements Model_Orm_Criteria
{
    /** @var string */
    private $related;

    /** @var array */
    private $conditions;

    public function __construct(string $related, array $conditions = [])
    {
        $this->related = $related;
        $this->conditions = $conditions;
    }

    public function apply(Query $query)
    {
        $chunks = explode('.', $this->related);
        $chain = '';
        foreach ($chunks as $related) {
            $chain .= empty($chain) ? $related : '.' . $related;
            $conditions = [];
            if (count($chunks) === 1) {
                $conditions = $this->conditions;
            } elseif (isset($this->conditions[$related])) {
                $conditions = $this->conditions[$related];
            }
            $query->related($chain, $conditions);
        }
        return $this;
    }
}
