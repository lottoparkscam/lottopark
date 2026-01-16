<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

/**
 * Description of Add_Is_Report_To_Whitelabel
 */
final class Update_Total_Deposit extends \Database_Migration_Graceful
{
    /**
     *
     * @return void
     */
    protected function up_gracefully(): void
    {
        DBUtil::modify_fields('whitelabel_user', [
            'total_deposit_manager' => [
                'constraint' => [9, 2],
                'type' => 'decimal',
                'unsigned' => true,
                'default' => 0,
                'null' => true
            ],
        ]);
    }

    /**
     *
     * @return void
     */
    protected function down_gracefully(): void
    {
        DBUtil::modify_fields('whitelabel_user', [
            'total_deposit_manager' => [
                'constraint' => [9, 2],
                'type' => 'decimal',
                'unsigned' => true,
                'default' => null,
                'null' => true
            ],
        ]);
    }
}
