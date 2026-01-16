<?php

namespace Repositories\Traits;

use Classes\Orm\AbstractOrmModel;
use Helpers\CaseHelper;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;

trait FindByTrait
{
    /** @return mixed */
    public function __call(string $name, $args)
    {
        $isFindBy = substr($name, 0, 6) === 'findBy';
        if ($isFindBy) {
            $columnName = CaseHelper::pascalToSnake(substr($name, 6));
            return $this->findBy($columnName, ...$args);
        }

        $isFindOneBy = substr($name, 0, 9) === 'findOneBy';
        if ($isFindOneBy) {
            $columnName = CaseHelper::pascalToSnake(substr($name, 9));
            return $this->findOneBy($columnName, ...$args);
        }

        $className = __CLASS__;
        trigger_error("Call to undefined method $className::$name()", E_USER_ERROR);
    }

    /** @return static[] */
    public function findBy(string $column, $value, string $operator = '='): array
    {
        $this->pushCriteria(new Model_Orm_Criteria_Where($column, $value, $operator));
        return $this->getResults();
    }

    public function findOneBy(string $column, mixed $value, string $operator = '='): ?AbstractOrmModel
    {
        $this->pushCriteria(new Model_Orm_Criteria_Where($column, $value, $operator));
        return $this->findOne();
    }
}
