<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

/**
 * Description of Whitelabel_User_Add_First_Deposit
 */
final class Whitelabel_User_Add_First_Deposit extends \Database_Migration_Graceful
{
    /**
     *
     * @return void
     */
    protected function up_gracefully(): void
    {
        DBUtil::add_fields('whitelabel_user', [
            'first_deposit' => [
                'type' => 'datetime',
                'null' => true,
                'default' => null,
                'after' => 'first_purchase'
            ],
        ]);
    }
    
    /**
     *
     * @return void
     */
    protected function down_gracefully(): void
    {
        DBUtil::drop_fields('whitelabel_user', 'first_deposit');
    }
}
