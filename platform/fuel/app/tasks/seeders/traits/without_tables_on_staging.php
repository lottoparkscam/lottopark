<?php

/**
* Inject removal of specified tables from staging environment.
* IMPORTANT to work need variable in seeder class: $disabled_tables_on_staging e.g.
* /**
* * Tables disabled on staging.
* * @var string[]
* *\/
* private $disabled_tables_on_staging = [
*   'whitelabel_lottery'
* ];
*/
trait Without_Tables_On_Staging
{
    protected function columnsStaging(): array
    {
        $columns = $this->columnsStaging();
        foreach ($this->disabled_tables_on_staging as $disabled_table) {
            unset($columns[$disabled_table]); // remove table on staging
        }
        return $columns;
    }

    protected function rowsStaging(): array
    {
        $rows = $this->rowsStaging();
        foreach ($this->disabled_tables_on_staging as $disabled_table) {
            unset($rows[$disabled_table]); // remove table on staging
        }
        return $rows;
    }
}
