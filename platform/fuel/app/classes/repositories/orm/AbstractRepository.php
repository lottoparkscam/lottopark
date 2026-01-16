<?php

namespace Repositories\Orm;

use Container;
use Exception;
use Classes\Orm\AbstractOrmModel;
use Classes\Orm\Model_Orm_Criteria;
use Classes\Orm\Criteria\By\Model_Orm_Criteria_By_Id;
use Classes\Orm\Criteria\Model_Orm_Criteria_Order;
use Classes\Orm\Criteria\Model_Orm_Criteria_Select;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Classes\Orm\Criteria\With\Model_Orm_Criteria_With_Relation;
use Classes\Orm\OrmModelInterface;
use Orm\RecordNotFound;
use Repositories\Traits\CrmTableTrait;
use Repositories\Traits\FindByTrait;
use Wrappers\Db;
use Repositories\Traits\DeleteRecordsOlderThanXDaysTrait;

abstract class AbstractRepository
{
    use CrmTableTrait;
    use FindByTrait;
    use DeleteRecordsOlderThanXDaysTrait;

    protected AbstractOrmModel $model;

    protected Db $db;

    private string $singleFieldName;

    public function __construct(AbstractOrmModel $model)
    {
        $this->model = $model;
        $this->db = Container::get(Db::class);
    }

    public function pushCriteria(Model_Orm_Criteria $criteria): self
    {
        $this->rememberSelectCriteria($criteria);
        $this->model->push_criteria($criteria);
        return $this;
    }

    public function resetQuery(): void
    {
        $this->model->reset_query();
    }

    public function pushCriterias(array $criterias): self
    {
        /** @var Model_Orm_Criteria $criteria */
        foreach ($criterias as $criteria) {
            $this->rememberSelectCriteria($criteria);
        }
        $this->model->push_criterias($criterias);
        return $this;
    }

    private function rememberSelectCriteria(Model_Orm_Criteria $criteria): void
    {
        if ($criteria instanceof Model_Orm_Criteria_Select) {
            $this->singleFieldName = $criteria->getSingleField();
        }
    }

    # getters

    /**
     * Main method for fetching results based on pushed criteria.
     *
     * @param int|null $limit
     * @param int $offset
     * @return AbstractOrmModel[]
     */
    public function getResults(int $limit = null, int $offset = 0): array
    {
        return $this->model->get_results($limit, $offset) ?? [];
    }

    /**
     * Returns single array in case you need e.g. array of codes from currency.
     * @param int|null $limit
     * @param int $offset
     * @return array
     */
    public function getResultsForSingleField(int $limit = null, int $offset = 0): array
    {
        $results = $this->model->get_results($limit, $offset);
        $singleFieldName = $this->singleFieldName;

        if (empty($singleFieldName)) {
            return [];
        }

        $values = [];
        foreach ($results as $result) {
            $values[] = $result->$singleFieldName;
        }

        return $values;
    }

    /**
     * @return AbstractOrmModel
     * @throws RecordNotFound
     */
    public function getOne(): AbstractOrmModel
    {
        return $this->model->get_one();
    }

    /**
     * @return AbstractOrmModel|null
     */
    public function findOne(): ?AbstractOrmModel
    {
        return $this->model->find_one();
    }

    /**
     * Returns one result by id with relations if passed any.
     * Pass just relation name or dot nested values.
     *
     * @param int $id
     * @param array $relations
     *
     * @return AbstractOrmModel
     */
    public function getById(int $id, array $relations = []): AbstractOrmModel
    {
        $this->model->push_criteria(new Model_Orm_Criteria_By_Id($id));

        foreach ($relations as $relation) {
            $this->model->push_criteria(new Model_Orm_Criteria_With_Relation($relation));
        }

        return $this->model->get_one();
    }

    /**
     * @param mixed $value
     * @param string $field
     * @return bool
     */
    public function exists($value, string $field = 'id'): bool
    {
        $exists = $this->model->push_criteria(new Model_Orm_Criteria_Where($field, $value))->getCount() !== 0;
        $this->model->reset_query();

        return $exists;
    }

    /** This function handle multiple criterias */
    public function recordExists(array $criterias): bool
    {
        $exists = $this->model->push_criterias($criterias)->getIntCount() !== 0;
        $this->model->reset_query();

        return $exists;
    }

    public function recordNotExists(array $criterias): bool
    {
        return $this->recordExists($criterias) === false;
    }

    # miscellaneous

    /**
     * @param string $sortField
     * @param string $sortOrder
     * @return AbstractOrmModel|OrmModelInterface|AbstractRepository
     */
    public function orderBy(string $sortField, string $sortOrder = 'desc')
    {
        return $this->model->push_criteria(new Model_Orm_Criteria_Order($sortField, $sortOrder));
    }

    /**
     * @param AbstractOrmModel $model
     * @param bool $flush
     * @return bool
     * @throws Exception
     */
    public function save(AbstractOrmModel $model, bool $flush = true): bool
    {
        return $model->save();
    }

    /**
     * @param AbstractOrmModel[] $models
     * @throws Exception
     */
    public function saveMany(array $models): void
    {
        foreach ($models as $model) {
            $model->save();
        }
    }

    /**
     * @param int $modelId
     * @param string|array $fieldName
     * @param float $addValue
     * @param array $additionalValues
     * @param array $additionalWhereConditions
     * @throws Exception
     */
    public function updateFloatField(
        int $modelId,
        string|array $fieldName,
        float $addValue,
        array $additionalValues = [],
        array $additionalWhereConditions = []
    ): void {
        if (empty($addValue)) {
            throw new Exception("Cannot update {$fieldName} if newValue is 0");
        }

        $setFields = [];
        $tableName = $this->model::get_table_name();
        $sign = $addValue > 0 ? '+' : '-';
        $addValue = abs($addValue);

        if (is_array($fieldName)) {
            foreach ($fieldName as $field) {
                $setFields[$field] = $this->db->expr("COALESCE({$field}, 0) {$sign} {$addValue}");
            }
        } else {
            $setFields[$fieldName] = $this->db->expr("COALESCE({$fieldName}, 0) {$sign} {$addValue}");
        }

        if (!empty($setFields)) {
            $query = $this->db->update($tableName)
                ->set(array_merge($setFields, $additionalValues));

            $query->where('id', '=', $modelId);

            foreach ($additionalWhereConditions as $condition) {
                $query->where(...$condition);
            }

            $query->execute();
        }
    }

    public function clearCache(): void
    {
        $this->model::flush_cache();
    }

    /**
     * Count the result of a query
     *
     * @param   bool  $column   False for random selected column or specific column, only works for main model currently
     * @param   bool  $distinct True if DISTINCT has to be aded to the query
     *
     * @return  int|bool   number of rows OR false
     */
    public function getCount($column = null, $distinct = true)
    {
        return $this->model->getCount($column, $distinct, true);
    }

    final public function getIntCount(array $criterias): int
    {
        return $this->model->push_criterias($criterias)->getIntCount();
    }

    /**
     * Count the result of a query and leave criterias
     *
     * @param   bool  $column   False for random selected column or specific column, only works for main model currently
     * @param   bool  $distinct True if DISTINCT has to be aded to the query
     *
     * @return  int|bool   number of rows OR false
     */
    public function getCountWithoutResetingQuery(bool $column = null, bool $distinct = true)
    {
        return $this->model->getCount($column, $distinct);
    }

    /**
     * Eager loads relation and prevents additional queries.
     * For example: loading relation whitelabelLottery->lottery will ensure there is only one query done upfront.
     *
     * Uses with relation to execute join.
     * @param string $relation - relation name, e.g. 'lottery'
     */
    public function withRelation(string $relation): static
    {
        $this->pushCriteria(new Model_Orm_Criteria_With_Relation($relation));

        return $this;
    }

    /**
     * Eager loads relations and prevents additional queries.
     * For example: loading relation whitelabelLottery->lottery will ensure there is only one query done upfront.
     *
     * Uses with relation to execute join.
     * @param string[] $relations - relation name, e.g. 'lottery'
     */
    public function withRelations(array $relations = []): static
    {
        foreach ($relations as $relation) {
            $this->withRelation($relation);
        }

        return $this;
    }
}
