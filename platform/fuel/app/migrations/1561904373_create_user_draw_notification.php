<?php

namespace Fuel\Migrations;

class Create_User_Draw_Notification
{
    public function up()
    {
        \DBUtil::create_table(
            'user_draw_notification',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'user_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'lottery_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'lottery_draw_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true],
                'lottery_draw_date' => ['type' => 'date', 'null' => true],
                'is_email_sent' => ['type' => 'tinyint', 'constraint' => 1, 'null' => true, 'default' => 0]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'user_draw_notification_user_id_whitelabel_user_idfx',
                    'key' => 'user_id',
                    'reference' => [
                        'table' => 'whitelabel_user',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'user_draw_notification_lottery_id_lottery_idfx',
                    'key' => 'lottery_id',
                    'reference' => [
                        'table' => 'lottery',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'user_draw_notification_lottery_draw_id_lottery_draw_idfx',
                    'key' => 'lottery_draw_id',
                    'reference' => [
                        'table' => 'lottery_draw',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
            ]
        );

        \DBUtil::create_index('user_draw_notification', 'user_id', 'user_draw_notification_user_id_whitelabel_user_idfx');
        \DBUtil::create_index('user_draw_notification', 'lottery_id', 'user_draw_notification_lottery_id_lottery_idfx');
        \DBUtil::create_index('user_draw_notification', 'lottery_draw_id', 'user_draw_notification_lottery_draw_id_lottery_draw_idfx');
    }

    public function down()
    {
        \DBUtil::drop_foreign_key('user_draw_notification', 'user_draw_notification_user_id_whitelabel_user_idfx');
        \DBUtil::drop_foreign_key('user_draw_notification', 'user_draw_notification_lottery_id_lottery_idfx');
        \DBUtil::drop_foreign_key('user_draw_notification', 'user_draw_notification_lottery_draw_id_lottery_draw_idfx');

        \DBUtil::drop_table('user_draw_notification');
    }
}
