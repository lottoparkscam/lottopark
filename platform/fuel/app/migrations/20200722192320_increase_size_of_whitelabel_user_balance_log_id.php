<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

/**
* @author Marcin Klimek <marcin.klimek at gg.international>
* Date: 2020-07-22
* Time: 19:25:33
*/
final class Increase_Size_Of_Whitelabel_User_Balance_Log_Id extends \Database_Migration_Graceful
{
    
    public function up_gracefully(): void
    {
        DBUtil::modify_fields('whitelabel_user_balance_log', [
            
            'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
        ]);
    }

    public function down_gracefully(): void
    {
        DBUtil::modify_fields('whitelabel_user_balance_log', [
            'id' => ['type' => 'int', 'constraint' => 3, 'unsigned' => true, 'auto_increment' => true],
        ]);
    }

}
