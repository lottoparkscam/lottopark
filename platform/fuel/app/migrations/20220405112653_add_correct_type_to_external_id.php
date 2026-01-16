<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Add_correct_type_to_external_id extends Database_Migration_Graceful
{
    private string $tableName = 'whitelabel_user_aff';

    protected function up_gracefully(): void
    {
        DBUtil::modify_fields(
            $this->tableName,
            [
                'external_id' => [
                    'type' => 'varchar',
                    'constraint' => 255,
                    'null' => true,
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::modify_fields(
            $this->tableName,
            [
                'external_id' => [
                    'type' => 'int',
                    'null' => true,
                ],
            ]
        );
    }
}