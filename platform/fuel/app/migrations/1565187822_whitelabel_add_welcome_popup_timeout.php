<?php

namespace Fuel\Migrations;

/**
 * Description of Whitelabel_User_Ticket_Add_Bonus_Cost
 *
 */
class Whitelabel_Add_Welcome_Popup_Timeout
{
    public function up()
    {
        \DBUtil::add_fields('whitelabel', [
            'welcome_popup_timeout' => [
                'type' => 'smallint',
                'constraint' => 5,
                'default' => 30,
                'after' => 'max_payout'
            ],
        ]);
    }
    
    public function down()
    {
        \DBUtil::drop_fields('whitelabel', 'welcome_popup_timeout');
    }
}
