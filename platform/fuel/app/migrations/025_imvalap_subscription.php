<?php

namespace Fuel\Migrations;

class Imvalap_Subscription
{
    public function up()
    {
        \DBUtil::create_table(
            'imvalap_subscription',
            [
                'id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'whitelabel_user_ticket_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
                'imvalap_job_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true]
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'imvalap_subscription_ijid_ijid_idfx',
                    'key' => 'imvalap_job_id',
                    'reference' => [
                        'table' => 'imvalap_job',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
                [
                    'constraint' => 'imvalap_subscription_wutid_wutid_idfx',
                    'key' => 'whitelabel_user_ticket_id',
                    'reference' => [
                        'table' => 'whitelabel_user_ticket',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ]
            ]
        );

        \DBUtil::create_index('imvalap_subscription', 'whitelabel_user_ticket_id', 'imvalap_subscription_wutid_wutid_idfx_idx');
        \DBUtil::create_index('imvalap_subscription', 'imvalap_job_id', 'imvalap_subscription_ijid_ijid_idfx_idx');

    }

    public function down()
    {
        \DBUtil::drop_foreign_key('imvalap_subscription', 'imvalap_subscription_ijid_ijid_idfx');
        \DBUtil::drop_foreign_key('imvalap_subscription', 'imvalap_subscription_wutid_wutid_idfx');

        \DBUtil::drop_table('imvalap_subscription');
    }
}
