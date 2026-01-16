<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

final class Delete_Additional_Data_From_Payment_Method_Currency extends Database_Migration_Graceful
{
    private string $tableName = 'payment_method_currency';
    private string $column = 'additional_data';

    protected function up_gracefully(): void
    {
        DBUtil::drop_fields($this->tableName, $this->column);
    }

    protected function down_gracefully(): void
    {
        DBUtil::add_fields(
            $this->tableName,
            [
                $this->column => [
                    'type' => 'varchar',
                    'constraint' => 300,
                    'null' => true,
                    'default' => null,
                    'after' => 'min_purchase'
                ],
            ]
        );
    }
}
