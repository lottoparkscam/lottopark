<?php

namespace Fuel\Migrations;

class Multidraws_Notification_Sent
{
    public function up()
    {
        \DBUtil::add_fields('multi_draw', [
            'is_notification_sent' => ['type' => 'tinyint', 'constraint' => 1, 'null' => false, 'default' => 0],
        ]);

        \DBUtil::create_index('multi_draw', 'is_notification_sent', 'multi_draw_is_notification_sent_idx');
    }

    public function down()
    {
        \DBUtil::drop_fields('multi_draw', 'is_notification_sent');
    }
}