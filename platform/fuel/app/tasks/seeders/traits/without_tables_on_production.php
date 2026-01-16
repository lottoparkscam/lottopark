<?php

/**
* Inject removal of specified tables from production environment.
* IMPORTANT to work need variable in seeder class: $disabled_tables_on_production e.g.
* /**
* * Tables disabled on production.
* * @var string[]
* *\/
* private $disabled_tables_on_production = [
*   'whitelabel_lottery'
* ];
*/
trait Without_Tables_On_Production
{
    protected function columnsProduction(): array
    {
        $columns = $this->columnsProduction();
        foreach ($this->disabled_tables_on_production as $disabled_table) {
            unset($columns[$disabled_table]); // remove table on production
        }
        return $columns;
    }

    protected function rowsProduction(): array
    {
        $rows = $this->rowsProduction();
        foreach ($this->disabled_tables_on_production as $disabled_table) {
            unset($rows[$disabled_table]); // remove table on production
        }
        return $rows;
    }
}
