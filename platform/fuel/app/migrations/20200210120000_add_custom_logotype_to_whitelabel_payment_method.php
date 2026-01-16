<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

final class Add_Custom_Logotype_To_Whitelabel_Payment_Method extends \Database_Migration_Graceful
{
    /**
     *
     * @return void
     */
    protected function up_gracefully(): void
    {
        DBUtil::add_fields('whitelabel_payment_method', [
            'custom_logotype' => [
                'type' => 'varchar',
                'constraint' => 2083,
                'null' => true,
                'default' => null,
                'after' => 'show_payment_logotype'
            ],
        ]);
    }

    /**
     *
     * @return void
     */
    protected function down_gracefully(): void
    {
        DBUtil::drop_fields('whitelabel_payment_method', [
            'custom_logotype'
        ]);
    }
}
