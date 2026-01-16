<?php

namespace Fuel\Migrations;

use Fuel\Core\DB;
use Fuel\Core\DBUtil;
use Helper_Migration;

/**
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2020-08-04
 * Time: 13:34:43
 */
final class Fix_Raffle_Structure extends \Database_Migration_Graceful
{
    private function create_raffle_provider(): void
    {
        DBUtil::create_table(
            'raffle_provider',
            [
                'id' => ['type' => 'smallint', 'constraint' => 5, 'unsigned' => true, 'auto_increment' => true],
                'raffle_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'provider' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'min_bets' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'max_bets' => ['type' => 'smallint', 'constraint' => 4, 'unsigned' => true],
                'multiplier' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'closing_time' => ['type' => 'time', 'null' => true, 'default' => null],
                'timezone' => ['type' => 'varchar', 'constraint' => 40, 'null' => true, 'default' => null],
                'offset' => ['type' => 'tinyint', 'constraint' => 4],
                'tax' => ['type' => 'decimal', 'constraint' => [5, 2], 'default' => '0.00'],
                'tax_min' => ['type' => 'decimal', 'constraint' => [8, 2], 'default' => '0.00'],
                'data' => ['type' => 'text', 'null' => true],
            ],
            ['id'],
            true,
            false,
            'utf8mb4_unicode_ci',
            [
                Helper_Migration::generate_foreign_key('raffle_provider', 'raffle_id'),
            ]
        );
    }

    private function apply_raffle_provider_to_whitelabel_raffle(): void
    {
        DB::query('SET FOREIGN_KEY_CHECKS = false;')->execute();

        DBUtil::drop_foreign_key('whitelabel_raffle', 'lottery_provider_id_idfx');
        DBUtil::drop_fields('whitelabel_raffle', ['lottery_provider_id']);
        DBUtil::add_fields('whitelabel_raffle', [
            'raffle_provider_id' => ['type' => 'smallint', 'constraint' => 5, 'unsigned' => true, 'after' => 'raffle_id']
        ]);
        DBUtil::add_foreign_key(
            'whitelabel_raffle',
            Helper_Migration::generate_foreign_key('whitelabel_raffle', 'raffle_provider_id')
        );

        DB::query('SET FOREIGN_KEY_CHECKS = true;')->execute();
    }

    private function fix_raffle_rule_to_raffle_connection(): void
    {
        DBUtil::modify_fields('raffle', [
            'raffle_rule_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'null' => true, 'default' => null],
        ]);
        DBUtil::modify_fields('raffle_rule', [
            'raffle_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'null' => false],
        ]);
    }

    private function fix_table_actions(): void
    {
        DBUtil::drop_foreign_key('raffle', 'raffle_currency_id_idfx');
        DBUtil::add_foreign_key('raffle', Helper_Migration::generate_foreign_key('raffle', 'currency_id'));
        DBUtil::drop_foreign_key('raffle', 'raffle_raffle_rule_id_idfx');
        DBUtil::add_foreign_key('raffle', Helper_Migration::generate_foreign_key('raffle', 'raffle_rule_id', 'SET NULL'));

        DBUtil::drop_foreign_key('raffle_draw', 'raffle_draw_currency_id_idfx');
        DBUtil::add_foreign_key('raffle_draw', Helper_Migration::generate_foreign_key('raffle_draw', 'currency_id'));
        DBUtil::drop_foreign_key('raffle_draw', 'raffle_draw_raffle_id_idfx');
        DBUtil::add_foreign_key('raffle_draw', Helper_Migration::generate_foreign_key('raffle_draw', 'raffle_id'));
        DBUtil::drop_foreign_key('raffle_draw', 'raffle_draw_raffle_rule_id_idfx');
        DBUtil::add_foreign_key('raffle_draw', Helper_Migration::generate_foreign_key('raffle_draw', 'raffle_rule_id'));

        DBUtil::drop_foreign_key('raffle_log', 'raffle_log_raffle_id_idfx');
        DBUtil::add_foreign_key('raffle_log', Helper_Migration::generate_foreign_key('raffle_log', 'raffle_id'));

        DBUtil::drop_foreign_key('raffle_prize', 'raffle_prize_currency_id_idfx');
        DBUtil::add_foreign_key('raffle_prize', Helper_Migration::generate_foreign_key('raffle_prize', 'currency_id'));
        DBUtil::drop_foreign_key('raffle_prize', 'raffle_prize_raffle_draw_id_idfx');
        DBUtil::add_foreign_key('raffle_prize', Helper_Migration::generate_foreign_key('raffle_prize', 'raffle_draw_id'));
        DBUtil::drop_foreign_key('raffle_prize', 'raffle_prize_raffle_rule_id_idfx');
        DBUtil::add_foreign_key('raffle_prize', Helper_Migration::generate_foreign_key('raffle_prize', 'raffle_rule_id'));
        DBUtil::drop_foreign_key('raffle_prize', 'raffle_prize_raffle_rule_tier_id_idfx');
        DBUtil::add_foreign_key('raffle_prize', Helper_Migration::generate_foreign_key('raffle_prize', 'raffle_rule_tier_id'));

        DBUtil::drop_foreign_key('raffle_rule', 'raffle_rule_currency_id_idfx');
        DBUtil::add_foreign_key('raffle_rule', Helper_Migration::generate_foreign_key('raffle_rule', 'currency_id'));
        DBUtil::drop_foreign_key('raffle_rule', 'raffle_rule_raffle_id_idfx');
        DBUtil::add_foreign_key('raffle_rule', Helper_Migration::generate_foreign_key('raffle_rule', 'raffle_id'));

        DBUtil::drop_foreign_key('raffle_rule_tier', 'raffle_rule_tier_currency_id_idfx');
        DBUtil::add_foreign_key('raffle_rule_tier', Helper_Migration::generate_foreign_key('raffle_rule_tier', 'currency_id'));
        DBUtil::drop_foreign_key('raffle_rule_tier', 'raffle_rule_tier_raffle_rule_id_idfx');
        DBUtil::add_foreign_key('raffle_rule_tier', Helper_Migration::generate_foreign_key('raffle_rule_tier', 'raffle_rule_id'));

        DBUtil::drop_foreign_key('whitelabel_raffle', 'whitelabel_raffle_raffle_id_idfx');
        DBUtil::add_foreign_key('whitelabel_raffle', Helper_Migration::generate_foreign_key('whitelabel_raffle', 'raffle_id'));
        DBUtil::drop_foreign_key('whitelabel_raffle', 'whitelabel_raffle_whitelabel_id_idfx');
        DBUtil::add_foreign_key('whitelabel_raffle', Helper_Migration::generate_foreign_key('whitelabel_raffle', 'whitelabel_id'));

        DBUtil::drop_foreign_key('whitelabel_raffle_ticket', 'whitelabel_raffle_ticket_currency_id_idfx');
        DBUtil::add_foreign_key('whitelabel_raffle_ticket', Helper_Migration::generate_foreign_key('whitelabel_raffle_ticket', 'currency_id'));
        DBUtil::drop_foreign_key('whitelabel_raffle_ticket', 'whitelabel_raffle_ticket_raffle_draw_id_idfx');
        DBUtil::add_foreign_key('whitelabel_raffle_ticket', Helper_Migration::generate_foreign_key('whitelabel_raffle_ticket', 'raffle_draw_id', 'SET NULL'));
        DBUtil::drop_foreign_key('whitelabel_raffle_ticket', 'whitelabel_raffle_ticket_raffle_id_idfx');
        DBUtil::add_foreign_key('whitelabel_raffle_ticket', Helper_Migration::generate_foreign_key('whitelabel_raffle_ticket', 'raffle_id'));
        DBUtil::drop_foreign_key('whitelabel_raffle_ticket', 'whitelabel_raffle_ticket_raffle_rule_id_idfx');
        DBUtil::add_foreign_key('whitelabel_raffle_ticket', Helper_Migration::generate_foreign_key('whitelabel_raffle_ticket', 'raffle_rule_id'));
        DBUtil::drop_foreign_key('whitelabel_raffle_ticket', 'whitelabel_raffle_ticket_whitelabel_id_idfx');
        DBUtil::add_foreign_key('whitelabel_raffle_ticket', Helper_Migration::generate_foreign_key('whitelabel_raffle_ticket', 'whitelabel_id'));
        DBUtil::drop_foreign_key('whitelabel_raffle_ticket', 'whitelabel_raffle_ticket_whitelabel_transaction_id_idfx');
        DBUtil::add_foreign_key('whitelabel_raffle_ticket', Helper_Migration::generate_foreign_key('whitelabel_raffle_ticket', 'whitelabel_transaction_id'));
        DBUtil::drop_foreign_key('whitelabel_raffle_ticket', 'whitelabel_raffle_ticket_whitelabel_user_id_idfx');
        DBUtil::add_foreign_key('whitelabel_raffle_ticket', Helper_Migration::generate_foreign_key('whitelabel_raffle_ticket', 'whitelabel_user_id'));

        DBUtil::drop_foreign_key('whitelabel_raffle_ticket_line', 'whitelabel_raffle_ticket_line_raffle_prize_id_idfx');
        DBUtil::add_foreign_key('whitelabel_raffle_ticket_line', Helper_Migration::generate_foreign_key('whitelabel_raffle_ticket_line', 'raffle_prize_id', 'SET NULL'));
        DBUtil::drop_foreign_key('whitelabel_raffle_ticket_line', 'whitelabel_raffle_ticket_line_whitelabel_id_idfx');
        DBUtil::add_foreign_key('whitelabel_raffle_ticket_line', Helper_Migration::generate_foreign_key('whitelabel_raffle_ticket_line', 'whitelabel_id'));
        DBUtil::drop_foreign_key('whitelabel_raffle_ticket_line', 'whitelabel_raffle_ticket_line_wl_raffle_ticket_id_idfx');
        $key = Helper_Migration::generate_foreign_key('whitelabel_raffle_ticket_line', 'whitelabel_raffle_ticket_id');
        $key['constraint'] = 'whitelabel_raffle_ticket_line_whitelabel_raffle_ticket_id_fk'; // we need to make name shorter
        DBUtil::add_foreign_key('whitelabel_raffle_ticket_line', $key);
    }

    protected function up_gracefully(): void
    {
        $this->create_raffle_provider();
        $this->apply_raffle_provider_to_whitelabel_raffle();
        $this->fix_raffle_rule_to_raffle_connection();
        $this->fix_table_actions();
    }

    /**
     * Revert migration.
     *
     * @return void
     */
    protected function down_gracefully(): void
    {
        // TODO: {Vordis 2020-08-04 17:22:00} revert actions
        echo "down_gracefully is not finished, omitting...";
        return;

        DBUtil::modify_fields('raffle', [
            'raffle_rule_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'null' => false],
        ]);
        DBUtil::modify_fields('raffle_rule', [
            'raffle_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'null' => true, 'default' => null],
        ]);

        DBUtil::drop_foreign_key('whitelabel_raffle', 'whitelabel_raffle_provider_id_raffle_provider_idfx');
        DBUtil::drop_fields('whitelabel_raffle', ['raffle_provider_id']);
        DBUtil::add_fields('whitelabel_raffle', [
            'lottery_provider_id' => ['type' => 'smallint', 'constraint' => 5, 'unsigned' => true]
        ]);
        DBUtil::add_foreign_key('whitelabel_raffle', [
            'constraint' => 'lottery_provider_id_idfx',
            'key'        => 'lottery_provider_id',
            'reference'  => [
                'table'  => 'lottery_provider',
                'column' => 'id'
            ]
        ]);

        DBUtil::drop_table('raffle_provider');
    }
}
