<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

/**
 * Description of Whitelabel_User_Add_Lines_Tickets_Counters
 */
final class Whitelabel_User_Add_Lines_Tickets_Counters extends \Database_Migration_Graceful
{
    /**
     *
     * @return void
     */
    protected function up_gracefully(): void
    {
        DBUtil::add_fields('whitelabel_user', [
            'tickets_sold_quantity' => [
                'type' => 'int',
                'constraint' => 10,
                'unsigned' => true,
                'default' => 0,
                'after' => 'referrer_id'
            ],
            'lines_sold_quantity' => [
                'type' => 'int',
                'constraint' => 10,
                'unsigned' => true,
                'default' => 0,
                'after' => 'tickets_sold_quantity'
            ],
        ]);
    }
    
    /**
     *
     * @return void
     */
    protected function down_gracefully(): void
    {
        DBUtil::drop_fields('whitelabel_user', 'tickets_sold_quantity');
        DBUtil::drop_fields('whitelabel_user', 'lines_sold_quantity');
    }
}
