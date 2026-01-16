<?php

namespace Fuel\Migrations;

use Fuel\Core\DBUtil;

/**
 * Zambia integration migration.
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2019-07-04
 * Time: 12:19:18
 */
final class Zambia_Integration extends \Database_Migration_Graceful
{

    /**
     * Run migration.
     *
     * @return void
     */
    protected function up_gracefully(): void
    {
        // add new field to draw - hour_local (temporary optional, but in future should be mandatory)
        DBUtil::add_fields(
            'lottery_draw',
            [
                'hour_local' => [
                    'type' => 'time',
                    'null' => true,
                    'default' => null,
                    'after' => 'date_local'
                ],
            ]
        );

        // add last_draw_hour_local to lottery table (nullable)
        DBUtil::add_fields(
            'lottery',
            [
                'last_draw_hour_local' => [
                    'type' => 'time',
                    'null' => true,
                    'default' => null,
                    'after' => 'draw_hour_local'
                ],
            ]
        );

        // make jackpot field in lottery match LCS
        DBUtil::modify_fields(
            'lottery',
            [
                'last_jackpot_prize' => [
                    'type' => 'decimal',
                    'unsigned' => true,
                    'constraint' => [15, 2],
                ]
            ]
        );

        // make jackpot field in draw match LCS
        DBUtil::modify_fields(
            'lottery_draw',
            [
                'final_jackpot' => [
                    'type' => 'decimal',
                    'unsigned' => true,
                    'constraint' => [15, 2],
                ]
            ]
        );

        // add lcs_ticket table
        DBUtil::create_table(
            'lcs_ticket',
            [
                'id' => [
                    'type' => 'int',
                    'constraint' => 10,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'whitelabel_user_ticket_slip_id' => [
                    'type' => 'int',
                    'constraint' => 10,
                    'unsigned' => true,
                ],
                'uuid' => [
                    'type' => 'char',
                    'constraint' => 36,
                ],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                [
                    'constraint' => 'lcs_ticket_wutsid_wutsid_idfx',
                    'key' => 'whitelabel_user_ticket_slip_id',
                    'reference' => [
                        'table' => 'whitelabel_user_ticket_slip',
                        'column' => 'id'
                    ],
                    'on_update' => 'NO ACTION',
                    'on_delete' => 'CASCADE'
                ],
            ]
        );

        // add boolean is_synchronized column to ticket table. true if ticket is synchronized with provider (sent to and properly received by provider)
        DBUtil::add_fields(
            'whitelabel_user_ticket',
            [
                'is_synchronized' => [
                    'type' => 'int',
                    'constraint' => 1,
                    'unsigned' => true,
                    'default' => 0,
                    'after' => 'status',
                ],
            ]
        );
        DBUtil::create_index( // index for is_synchronized field, since it's usage will be quite high
            'whitelabel_user_ticket',
            ['is_synchronized' => 'ASC'],
            'whitelabel_user_ticket_is_synchronized_idx'
        );

        // add has_pending_tickets default true flag to lottery_draw
        DBUtil::add_fields(
            'lottery_draw',
            [
                'has_pending_tickets' => [
                    'type' => 'int',
                    'constraint' => 1,
                    'unsigned' => true,
                    'default' => 1,
                    'after' => 'final_jackpot',
                ],
            ]
        );
        DBUtil::create_index( // index for has_pending_tickets field, since it's usage will be quite high
            'lottery_draw',
            'has_pending_tickets',
            'lottery_draw_has_pending_tickets_idx'
        );
    }

    /**
     * Revert migration.
     *
     * @return void
     */
    protected function down_gracefully(): void
    {
        DBUtil::drop_fields('lottery_draw', 'hour_local');
        DBUtil::drop_fields('lottery', 'last_draw_hour_local');

        DBUtil::modify_fields(
            'lottery',
            [
                'last_jackpot_prize' => [
                    'type' => 'decimal',
                    'unsigned' => true,
                    'constraint' => [12, 2],
                ]
            ]
        );
        DBUtil::modify_fields(
            'lottery_draw',
            [
                'final_jackpot' => [
                    'type' => 'decimal',
                    'unsigned' => true,
                    'constraint' => [12, 2],
                ]
            ]
        );

        DBUtil::drop_foreign_key('lcs_ticket', 'lcs_ticket_wutsid_wutsid_idfx');
        DBUtil::drop_table('lcs_ticket');

        DBUtil::drop_index('whitelabel_user_ticket', 'whitelabel_user_ticket_is_synchronized_idx');
        DBUtil::drop_fields('whitelabel_user_ticket', 'is_synchronized');
        DBUtil::drop_index('lottery_draw', 'lottery_draw_has_pending_tickets_idx');
        DBUtil::drop_fields('lottery_draw', 'has_pending_tickets');
    }
}
