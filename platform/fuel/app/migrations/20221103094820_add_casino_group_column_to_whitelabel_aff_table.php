<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Models\WhitelabelAff;
use Models\WhitelabelAffCasinoGroup;

final class Add_Casino_Group_Column_To_Whitelabel_Aff_Table extends Database_Migration_Graceful
{
    private string $tableName = 'whitelabel_aff';
    private string $index = 'whitelabel_aff_casino_group_id';
    private string $foreignKey = 'wl_aff_cg_wl_aff_cg_id_foreign';

    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            $this->tableName,
            [
                $this->index => [
                    'type' => 'int',
                    'constraint' => 10,
                    'unsigned' => true,
                    'null' => true,
                    'default' => null,
                    'after' => 'whitelabel_aff_group_id',
                ],
            ]
        );

        DBUtil::add_foreign_key(
            $this->tableName,
            [
                'constraint' => $this->foreignKey,
                'key' => $this->index,
                'reference' => [
                    'table' => WhitelabelAffCasinoGroup::get_table_name(),
                    'column' => 'id'
                ],
                'on_update' => 'NO ACTION',
                'on_delete' => 'SET NULL'
            ],
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_foreign_key($this->tableName, $this->foreignKey);

        DBUtil::drop_fields(
            WhitelabelAff::get_table_name(), [$this->index]
        );
    }
}
