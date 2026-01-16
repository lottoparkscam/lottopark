<?php

namespace Modules\CrmTable;

/**
 * @property array{type: string, column: string} $filters
 * @property string[] $tabs
 * @property string[] $tableColumns
 */
class Config
{
    public string $sortBy;
    public string $order = 'DESC';
    public int $offset = 0;
    public int $itemsPerPage = 50;
    public string $columnNameToFilterByDate;
    public string $fromDatetime;
    public string $toDatetime;
    public int $whitelabelId;
    public array $filters = [];
    public bool $isNotSuperadminView = true;
    public string $activeTab;
    public string $tabsDatabaseField;
    public array $tabs;
    public array $tableColumns;
    public int $page;
    public bool $export = false;
}
