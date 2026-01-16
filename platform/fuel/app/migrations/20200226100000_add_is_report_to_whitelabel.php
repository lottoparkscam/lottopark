<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

/**
 * Description of Add_Is_Report_To_Whitelabel
 */
final class Add_Is_Report_To_Whitelabel extends \Database_Migration_Graceful
{
    /**
     *
     * @return void
     */
    protected function up_gracefully(): void
    {
        DBUtil::add_fields('whitelabel', [
            'is_report' => [
                'type' => 'tinyint',
                'constraint' => 1,
                'unsigned' => true,
                'default' => 1,
                'after' => 'last_active'
            ]
        ]);
    }

    /**
     *
     * @return void
     */
    protected function down_gracefully(): void
    {
        DBUtil::drop_fields('whitelabel', [
            'is_report'
        ]);
    }
}
