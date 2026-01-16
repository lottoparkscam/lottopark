<?php

namespace Repositories\Traits;

use Classes\Orm\Criteria\Model_Orm_Criteria_Expr;
use Exception;
use Fuel\Core\Database_Query_Builder;
use Fuel\Core\Database_Query_Builder_Select;
use Fuel\Core\Database_Result;
use Fuel\Core\Security;
use Helpers\ArrayHelper;
use Helpers\SanitizerHelper;
use Helpers\StringHelper;
use Helpers\TypeHelper;
use Helpers_Crm_General;
use Classes\Orm\AbstractOrmModel;
use Classes\Orm\Criteria\Model_Orm_Criteria_Order;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Classes\Orm\Criteria\With\Model_Orm_Criteria_With_Relation;
use Classes\Orm\Criteria\Rows\Model_Orm_Criteria_Rows_Limit;
use Classes\Orm\Criteria\Rows\Model_Orm_Criteria_Rows_Offset;
use Modules\CrmTable\Config;

trait CrmTableTrait
{
    public static string $searchFilter = 'search';
    public static string $amountFilter = 'amount';
    public static string $dateFilter = 'date';
    public static string $booleanFilter = 'boolean';

    /** @throws Exception */
    public function getCrmTableData(
        Config $config,
        string $whitelabelIdFieldLocation,
        ?callable $prepareEachItem
    ): array {
        $whitelabelId = $config->whitelabelId;
        $activeTab = $config->activeTab;
        $offset = $config->offset;
        $itemsPerPage = $config->itemsPerPage;
        $sortBy = $config->sortBy ?? null;
        $order = $config->order ?? null;
        $tableColumns = $config->tableColumns;
        $tabsDatabaseField = $config->tabsDatabaseField;
        $tabs = $config->tabs;
        $filters = $config->filters;

        if (!empty($sortBy)) {
            $this->pushCriteria(new Model_Orm_Criteria_Order($sortBy, $order));
        }

        foreach ($tableColumns as $columnName) {
            if (!$this->fieldIsRelated($columnName)) {
                continue;
            }

            $columnName = $this->getOnlyRelationsFromField($columnName);
            $this->pushCriteria(new Model_Orm_Criteria_With_Relation($columnName));
        }

        if ($activeTab !== 'all') {
            $this->whereWithNestedRelations($tabsDatabaseField, $activeTab);
        }

        $dontShowAllWhitelabels = $whitelabelId > 0;
        if ($dontShowAllWhitelabels) {
            $this->addWhereForWhitelabel($whitelabelIdFieldLocation, $whitelabelId);
        }

        $this->addFilters($filters);

        $shouldFilterByDate = !empty($config->columnNameToFilterByDate) &&
            !empty($config->fromDatetime) &&
            !empty($config->toDatetime);
        if ($shouldFilterByDate) {
            $this->pushCriteria(new Model_Orm_Criteria_Where(
                $config->columnNameToFilterByDate,
                [$config->fromDatetime, $config->toDatetime],
                'BETWEEN'
            ));
        }

        $currentTabCount = $this->getCountWithoutResetingQuery() ?: 0;

        $this->pushCriterias([
            new Model_Orm_Criteria_Rows_Limit($itemsPerPage),
            new Model_Orm_Criteria_Rows_Offset($offset)
        ]);

        $results = $this->getResults();

        $sanitizedResults = $this->sanitizeResults($results, $tableColumns, $prepareEachItem);

        $itemsCountPerTab = [];
        foreach ($tabs as $tabName) {
            $isActiveTab = $tabName === $activeTab;
            if ($isActiveTab) {
                $itemsCountPerTab[$tabName] = $currentTabCount;
                continue;
            }

            // getResults and getCount reset previous criteria
            if ($dontShowAllWhitelabels) {
                $this->addWhereForWhitelabel($whitelabelIdFieldLocation, $whitelabelId);
            }

            if ($tabName !== 'all') {
                $this->whereWithNestedRelations($tabsDatabaseField, $tabName);
            }

            if ($shouldFilterByDate) {
                $this->pushCriteria(new Model_Orm_Criteria_Where(
                    $config->columnNameToFilterByDate,
                    [$config->fromDatetime, $config->toDatetime],
                    'BETWEEN'
                ));
            }

            $itemsCount = $this->getCount() ?? 0;
            $itemsCountPerTab[$tabName] = $itemsCount;
        }


        return [
            'results' => $sanitizedResults,
            'itemsCountPerTab' => $itemsCountPerTab
        ];
    }

    private function getOnlyRelationsFromField(string $fieldName): string
    {
        $fieldExploded = explode('.', $fieldName);
        array_splice($fieldExploded, -1, 1);
        return implode('.', $fieldExploded);
    }

    private function getRestRelationsFromField(string $fieldName): string
    {
        $fieldExploded = explode('.', $fieldName);
        $restFields = array_slice($fieldExploded, 1);
        return implode('.', $restFields);
    }

    private function fieldIsRelated(string $fieldName): bool
    {
        $fieldExploded = explode('.', $fieldName);
        return count($fieldExploded) > 1;
    }

    private function whereWithNestedRelations(string $fieldName, string $value): void
    {
        if (!$this->fieldIsRelated($fieldName)) {
            $this->pushCriteria(new Model_Orm_Criteria_Where($fieldName, $value));
            return;
        }

        $relation = $this->getOnlyRelationsFromField($fieldName);
        $conditionsFields = $this->getRestRelationsFromField($fieldName);
        $nestedConditions = $this->prepareNestedConditions($conditionsFields, $value);
        $this->pushCriteria(new Model_Orm_Criteria_With_Relation($relation, $nestedConditions));
    }

    private function prepareNestedConditions(string $fieldName, string $value): array
    {
        if ($this->fieldIsRelated($fieldName)) {
            $chunks = explode('.', $fieldName);
            $relationName = $chunks[0];
            $restFields = $this->getRestRelationsFromField($fieldName);
            return [
                'related' => [
                    $relationName => $this->prepareNestedConditions($restFields, $value)
                ]
            ];
        }

        return [
            'where' => [
                $fieldName => $value
            ]
        ];
    }

    /**
     * @param array $results
     * @param array $columnNames
     * @param callable|null $prepareEachItem
     * @return array
     * @throws Exception
     */
    private function sanitizeResults(array $results, array $columnNames, ?callable $prepareEachItem): array
    {
        $sanitizedResults = [];
        foreach ($results as $result) {
            $resultInArray = $result->to_array();
            $item = [];
            foreach ($columnNames as $columnName) {
                $columnNameExploded = explode('.', $columnName);
                if ($this->fieldIsRelated($columnName)) {
                    /** @var AbstractOrmModel $relatedObject */
                    $relatedObject = $result->{$columnNameExploded[0]};
                    $restColumnName = $this->getRestRelationsFromField($columnName);
                    $item[$columnName] = $this->sanitizeRelation($relatedObject, $restColumnName);
                    continue;
                }
                $item[$columnName] = Security::htmlentities($resultInArray[$columnName]);
            }
            if (isset($prepareEachItem)) {
                $additionalData = $prepareEachItem($result);
                $sanitizedAdditionalData = array_map(function ($item) {
                    return Security::htmlentities($item);
                }, $additionalData);
                $item = array_merge($item, $sanitizedAdditionalData);
            }
            $sanitizedResults[] = $item;
        }

        return $sanitizedResults;
    }

    /**
     * @param AbstractOrmModel $relation
     * @param string $property
     * @return mixed
     * @throws Exception
     */
    private function sanitizeRelation(AbstractOrmModel $relation, string $property)
    {
        if (!$this->fieldIsRelated($property)) {
            $value = $relation->to_array()[$property];

            if (is_object($value)) {
                throw new Exception('Try to get full object. Security error.');
            }

            return $value;
        }

        $properties = explode('.', $property);
        /** @var AbstractOrmModel $relatedObject */
        $relatedObject = $relation->{$properties[0]};
        $restProperties = $this->getRestRelationsFromField($property);
        return $this->sanitizeRelation($relatedObject, $restProperties);
    }

    /**
     * @param array{type: string, column: string} $filters
     * @throws Exception
     */
    private function addFilters(array $filters): void
    {
        foreach ($filters as $filter) {
            $columnName = SanitizerHelper::sanitizeString($filter['column']);
            switch ($filter['type']) {
                case self::$searchFilter:
                    $value = SanitizerHelper::sanitizeString($filter['value']);
                    $this->pushCriteria(new Model_Orm_Criteria_Where($columnName, "%$value%", 'LIKE'));
                    break;
                case self::$amountFilter:
                    if (!empty($filter['start'])) {
                        $start = SanitizerHelper::sanitizeString($filter['start']);
                        $this->pushCriteria(new Model_Orm_Criteria_Where($columnName, $start, '>='));
                    }
                    if (!empty($filter['end'])) {
                        $end = SanitizerHelper::sanitizeString($filter['end']);
                        $this->pushCriteria(new Model_Orm_Criteria_Where($columnName, $end, '<='));
                    }
                    break;
                case self::$dateFilter:
                    // React filter is only a date
                    $startDate = Helpers_Crm_General::prepare_start_date(
                        SanitizerHelper::sanitizeString($filter['startDate'])
                    );
                    $endDate = Helpers_Crm_General::prepare_end_date(
                        SanitizerHelper::sanitizeString($filter['endDate'])
                    );
                    $this->pushCriteria(new Model_Orm_Criteria_Where($columnName, [$startDate, $endDate], 'BETWEEN'));
                    break;
                case self::$booleanFilter:
                    $value = SanitizerHelper::sanitizeString($filter['value']);
                    $castedValue = TypeHelper::cast($value, TypeHelper::BOOLEAN);
                    $this->pushCriteria(new Model_Orm_Criteria_Where($columnName, $castedValue));
                    break;
            }
        }
    }

    /**
     * @param array{type: string, column: string} $filters
     * @throws Exception
     */
    public function addFiltersToQueryBuilderOnNonaggregatedData(array $filters, Database_Query_Builder $query): void
    {
        $this->addFiltersToQueryBuilder($filters, $query);
    }

    /**
     * @param array{type: string, column: string} $filters
     * @throws Exception
     */
    public function addFiltersToQueryBuilderOnAggregatedData(array $filters, Database_Query_Builder $query): void
    {
        $this->addFiltersToQueryBuilder($filters, $query, true);
    }

    /**
     * @param array{type: string, column: string} $filters
     * @param bool $dataIsAggregated
     * @throws Exception
     */
    private function addFiltersToQueryBuilder(
        array $filters,
        Database_Query_Builder $query,
        bool $dataIsAggregated = false
    ): void {
        foreach ($filters as $filter) {
            $columnName = SanitizerHelper::sanitizeString($filter['column']);
            switch ($filter['type']) {
                case self::$searchFilter:
                    $value = SanitizerHelper::sanitizeString($filter['value']);
                    $condition = [$columnName, 'LIKE', "%$value%"];
                    $this->addCondition($dataIsAggregated, $condition, $query);
                    break;
                case self::$amountFilter:
                    if (!empty($filter['start'])) {
                        $start = SanitizerHelper::sanitizeString($filter['start']);
                        $condition = [$columnName, '>=', $start];
                        $this->addCondition($dataIsAggregated, $condition, $query);
                    }
                    if (!empty($filter['end'])) {
                        $end = SanitizerHelper::sanitizeString($filter['end']);
                        $condition = [$columnName, '<=', $end];
                        $this->addCondition($dataIsAggregated, $condition, $query);
                    }
                    break;
                case self::$dateFilter:
                    // React filter is only a date
                    $startDate = Helpers_Crm_General::prepare_start_date(
                        SanitizerHelper::sanitizeString($filter['startDate'])
                    );
                    $endDate = Helpers_Crm_General::prepare_end_date(
                        SanitizerHelper::sanitizeString($filter['endDate'])
                    );
                    $condition = [$columnName, 'BETWEEN', [$startDate, $endDate]];
                    $this->addCondition($dataIsAggregated, $condition, $query);
                    break;
                case self::$booleanFilter:
                    $value = SanitizerHelper::sanitizeString($filter['value']);
                    $castedValue = TypeHelper::cast($value, TypeHelper::BOOLEAN);
                    $condition = [$columnName, '=', $castedValue];
                    $this->addCondition($dataIsAggregated, $condition, $query);
                    break;
            }
        }
    }

    private function addCondition(bool $dataIsAggregated, array $condition, Database_Query_Builder_Select $query): void
    {
        if ($dataIsAggregated) {
            $query->and_having(...$condition);
        } else {
            $query->and_where(...$condition);
        }
    }

    private function addWhereForWhitelabel(string $whitelabelIdFieldLocation, int $whitelabelId): void
    {
        if ($this->fieldIsRelated($whitelabelIdFieldLocation)) {
            $onlyRelation = StringHelper::removeLastChunkBySeparator($whitelabelIdFieldLocation, '.');
            $chunks = explode('.', $onlyRelation);
            $tableToFindId = end($chunks);
            /** @var Database_Result */
            $queryResponse = $this->db->select('id')
                ->from($tableToFindId)
                ->where('whitelabel_id', $whitelabelId)
                ->execute();
            $rows = $queryResponse->as_array();
            $ids = ArrayHelper::createSingleArrayFromValue($rows, 'id');
            $this->pushCriteria(new Model_Orm_Criteria_With_Relation($onlyRelation));
            $whitelabelIdFieldLocation = $onlyRelation . '_id';

            if (empty($ids)) {
                $this->pushCriteria(new Model_Orm_Criteria_Expr($this->db->expr('1 = 2')));
            } else {
                $this->pushCriteria(new Model_Orm_Criteria_Where($whitelabelIdFieldLocation, $ids, 'IN'));
            }
        } else {
            $this->pushCriteria(new Model_Orm_Criteria_Where($whitelabelIdFieldLocation, $whitelabelId));
        }
    }
}
