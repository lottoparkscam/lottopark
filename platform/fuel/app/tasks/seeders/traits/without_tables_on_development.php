<?php

/**
* Inject removal of specified tables from development environment.
* IMPORTANT to work need variable in seeder class: $disabled_tables_on_development e.g.
* /**
* * Tables disabled on development.
* * @var string[]
* *\/
* private $disabled_tables_on_development = [
*   'whitelabel_lottery'
* ];
*/
trait Without_Tables_On_Development
{
    protected function columnsDevelopment(): array
    {
        $columns = $this->columnsDevelopment();
        foreach ($this->disabled_tables_on_development as $disabled_table) {
            unset($columns[$disabled_table]); // remove table on development
        }
        return $columns;
    }

    protected function rowsDevelopment(): array
    {
        $rows = $this->rowsDevelopment();
        foreach ($this->disabled_tables_on_development as $disabled_table) {
            unset($rows[$disabled_table]); // remove table on development
        }
        return $rows;
    }
}
