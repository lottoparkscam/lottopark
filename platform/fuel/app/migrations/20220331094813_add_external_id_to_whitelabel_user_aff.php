<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Add_external_id_To_Whitelabel_User_Aff extends Database_Migration_Graceful
{
    private string $tableName = 'whitelabel_user_aff';

    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            $this->tableName,
            [
                'external_id' => [
                    'type' => 'int',
                    'null' => true,
                    'after' => 'whitelabel_aff_content_id'
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            $this->tableName,
            [
                'external_id',
            ]
        );
    }
}