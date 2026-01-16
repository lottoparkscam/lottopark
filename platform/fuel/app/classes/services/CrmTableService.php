<?php

namespace Services;

use Container;
use Fuel\Core\Input;
use Modules\CrmTable\Config;

class CrmTableService
{
    private Config $config;
    private array $dangerousFields;
    private string $whitelabelIdFieldLocation = 'whitelabel_id';
    private array $tableData;

    public function __construct()
    {
        $this->configure();
    }

    public function fetchTableDataByRepository(string $repositoryClass, ?callable $prepareEachItem = null): array
    {
        $this->sanitizeTableColumns();
        $repository = Container::get($repositoryClass);

        /** @see /platform/fuel/app/classes/traits/repositories/CrmTableTrait.php */
        $this->tableData = $repository->getCrmTableData(
            $this->config,
            $this->whitelabelIdFieldLocation,
            $prepareEachItem
        );

        return $this->tableData;
    }

    public function getTableData(): array
    {
        return $this->tableData['results'];
    }

    public function getItemsCountPerTab(): array
    {
        return $this->tableData['itemsCountPerTab'];
    }

    private function configure(): void
    {
        $crmTableConfig = new Config();

        $requestedFields = [
            'whitelabelId',
            'activeTab',
            'itemsPerPage',
            'page',
            'sortBy',
            'order',
            'tableColumns',
            'tabsDatabaseField',
            'tabs',
            'filters',
            'fromDatetime',
            'toDatetime',
            'export',
            'columnNameToFilterByDate',
        ];

        foreach ($requestedFields as $field) {
            $isPage = $field === 'page';
            if ($isPage) {
                $crmTableConfig->offset = (Input::json($field) - 1) * ($crmTableConfig->itemsPerPage ?? 0);
                continue;
            }

            if (key_exists($field, Input::json())) {
                $crmTableConfig->$field = Input::json($field);
            }
        }

        $this->config = $crmTableConfig;
    }

    private function sanitizeTableColumns(): void
    {
        $this->config->tableColumns = array_filter($this->config->tableColumns, function ($columnName) {
            $columnNameChunks = explode('.', $columnName);
            $isRelated = count($columnNameChunks) > 1;
            if (!$isRelated) {
                return !in_array($columnName, $this->dangerousFields);
            }
            $field = array_slice($columnNameChunks, -1, 1);
            return !in_array($field, $this->dangerousFields);
        });
    }

    /**
     * This property will be deleted from results including relations
     * @param array $fields
     */
    public function setDangerousFields(array $fields)
    {
        $this->dangerousFields = $fields;
    }

    /**
     * This should be used when main table does not have direct relation to whitelabel
     * e.g. $location = 'whitelabel_slot_provider.whitelabel_id'
     * It means when we generate table for slot_transaction,
     * we filter whitelabel by field slot_transaction.whitelabel_slot_provider.whitelabel_id
     * Be careful: whitelabel field always needs to be called whitelabel_id
     * The relation should name by convention table_name_id e.g. whitelabel_slot_provider_id
     */
    public function setWhitelabelIdFieldLocation(string $location): void
    {
        $this->whitelabelIdFieldLocation = $location;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }
}
