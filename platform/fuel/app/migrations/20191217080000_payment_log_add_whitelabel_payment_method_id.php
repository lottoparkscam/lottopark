<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

/**
 * Description of Payment_Log_Add_Whitelabel_Payment_Method_Id
 */
final class Payment_Log_Add_Whitelabel_Payment_Method_Id extends \Database_Migration_Graceful
{
    /**
     *
     * @return void
     */
    protected function up_gracefully(): void
    {
        DBUtil::add_fields('payment_log', [
            'whitelabel_payment_method_id' => [
                'type' => 'int',
                'constraint' => 10,
                'unsigned' => true,
                'null' => true,
                'default' => null,
                'after' => 'payment_method_id'
            ],
        ]);
    }
    
    /**
     *
     * @return void
     */
    protected function down_gracefully(): void
    {
        DBUtil::drop_fields('payment_log', 'whitelabel_payment_method_id');
    }
}
