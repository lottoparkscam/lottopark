<?php

namespace Fuel\Migrations;

/**
 * Class Update_Ticket_Slips_Tickets_Scans
 * @package Fuel\Migrations
 */
class Update_Ticket_Slips_Tickets_Scans
{
    public function up()
    {
        // whitelabel_user_ticket
        \DBUtil::add_fields('whitelabel_user_ticket', [
            'has_ticket_scan' => [
                'type' => 'tinyint',
                'constraint' => 3,
                'unsigned' => true,
                'default' => 0,
                'after' => 'bonus_cost_manager'
            ],
        ]);

        // whitelabel_user_ticket_slip
        \DBUtil::add_fields('whitelabel_user_ticket_slip', [
            'ticket_scan_url' => [
                'type' => 'varchar',
                'constraint' => 2083,
                'null' => true,
                'default' => null,
                'after' => 'whitelabel_user_ticket_id'
            ],
        ]);

        // lottery
        \DBUtil::add_fields('lottery', [
            'scans_enabled' => [
                'type' => 'tinyint',
                'constraint' => 3,
                'unsigned' => true,
                'default' => 0,
                'after' => 'estimated_updated'
            ],
        ]);

        // lottery_provider
        \DBUtil::add_fields('lottery_provider', [
            'scan_cost' => [
                'type' => 'decimal',
                'constraint' => [4, 2],
                'unsigned' => true,
                'null' => true,
                'default' => 0.00,
                'after' => 'fee'
            ],
            'scan_fee' => [
                'type' => 'decimal',
                'constraint' => [4, 2],
                'unsigned' => true,
                'null' => true,
                'default' => 0.00,
                'after' => 'scan_cost'
            ]
        ]);
    }

    public function down()
    {
        // whitelabel_user_ticket
        \DBUtil::drop_fields('whitelabel_user_ticket', 'has_ticket_scan');

        // whitelabel_user_ticket_slip
        \DBUtil::drop_fields('whitelabel_user_ticket_slip', 'ticket_scan_url');

        // lottery
        \DBUtil::drop_fields('lottery', 'scans_enabled');

        // lottery_provider
        \DBUtil::drop_fields('lottery_provider', 'scan_cost');
        \DBUtil::drop_fields('lottery_provider', 'scan_fee');
    }
}
