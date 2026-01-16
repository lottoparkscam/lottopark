<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

/**
 * Description of Whitelabel_User_Add_Second_Purchase_Deposit
 */
final class Whitelabel_User_Add_Second_Purchase_Deposit extends \Database_Migration_Graceful
{
    /**
     *
     * @return void
     */
    protected function up_gracefully(): void
    {
        DBUtil::add_fields('whitelabel_user', [
            'second_purchase' => [
                'type' => 'datetime',
                'null' => true,
                'default' => null,
                'after' => 'first_purchase'
            ],
            'second_deposit' => [
                'type' => 'datetime',
                'null' => true,
                'default' => null,
                'after' => 'first_deposit'
            ],
        ]);
    }
    
    /**
     *
     * @return void
     */
    protected function down_gracefully(): void
    {
        DBUtil::drop_fields('whitelabel_user', 'second_purchase');
        DBUtil::drop_fields('whitelabel_user', 'second_deposit');
    }
}
