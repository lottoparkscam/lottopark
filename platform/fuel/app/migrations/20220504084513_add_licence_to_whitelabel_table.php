<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Add_Licence_To_Whitelabel_Table extends Database_Migration_Graceful
{
    private const TABLE_NAME = 'whitelabel';
    private const COLUMN_NAME = 'licence';
    private const AFTER_COLUMN = 'company_details';

    protected function up_gracefully(): void
    {
        DBUtil::add_fields(
            self::TABLE_NAME,
            [
                self::COLUMN_NAME => [
                    'type' => 'varchar',
                    // should be changed on prod, I've added this as default because the majority of wls are v1
                    'default' => '8048/JAZ',
                    'after' => self::AFTER_COLUMN,
                    'constraint' => 10
                ],
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::drop_fields(
            self::TABLE_NAME,
            [
                self::COLUMN_NAME,
            ]
        );
    }
}
