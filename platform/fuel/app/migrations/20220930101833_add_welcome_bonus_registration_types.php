<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Add_Welcome_Bonus_Registration_Types extends Database_Migration_Graceful
{
    private string $tableName = 'whitelabel_bonus';

    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            $this->tableName,
            [
                'register_website' => [
                    'type' => 'tinyint',
                    'unsigned' => true,
                    'default' => 1,
                    'after' => 'min_total_purchase'
                ],
                'register_api' => [
                    'type' => 'tinyint',
                    'unsigned' => true,
                    'default' => 1,
                    'after' => 'register_website'
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            $this->tableName,
            [
                'register_website',
                'register_api'
            ]
        );
    }
}
