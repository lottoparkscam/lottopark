<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;

/** this migration is without Helper_Migration because payment_currency_id didnt want to find auto relation */
final class Add_Foreign_Key_On_Payment_Currency_Id_For_Whitelabel_Cc_Method extends Database_Migration_Graceful
{
    private string $tableName = 'whitelabel_cc_method';
    private string $index = 'payment_currency_id';

    protected function up_gracefully(): void
    {
        $constraint = "{$this->tableName}_{$this->index}_foreign";

        DBUtil::add_foreign_key($this->tableName, [
            'constraint' => $constraint,
            'key' => $this->index,
            'reference' => [
                'table' => 'currency',
                'column' => 'id'
            ],
            'on_update' => 'CASCADE',
            'on_delete' => 'RESTRICT',
        ]);
    }

    protected function down_gracefully(): void
    {
        $constraint = "{$this->tableName}_{$this->index}_foreign";
        DBUtil::drop_foreign_key($this->tableName, $constraint);
    }
}
