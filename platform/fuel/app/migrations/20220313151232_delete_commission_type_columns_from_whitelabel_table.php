<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Delete_Commission_Type_Columns_From_Whitelabel_Table extends Database_Migration_Graceful
{
    private string $tableName = 'whitelabel';

    protected function up_gracefully(): void
    {
        DBUtil::drop_fields(
            $this->tableName,
            [
                'def_commission_type',
                'def_commission_type_2',
            ],
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::add_fields(
            $this->tableName,
            [
                'def_commission_type' => [
                    'type' => 'tinyint',
                    'constraint' => 3,
                    'unsigned' => true,
                    'null' => true,
                    'default' => null,
                ],
                'def_commission_type_2' => [
                    'type' => 'tinyint',
                    'constraint' => 3,
                    'unsigned' => true,
                    'null' => true,
                    'default' => null,
                ],
            ],
        );
    }
}
