<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;
use Model_Payment_Method_Currency;
use Models\AdminUser;
use Models\PaymentRequestLock;
use Models\RaffleDraw;
use Models\RafflePrize;
use Models\RaffleProvider;
use Models\WhitelabelPaymentMethod;
use Models\WhitelabelRaffleTicket;
use Models\WhitelabelRaffleTicketLine;
use Models\WhitelabelTransaction;
use Models\Lottery;
use Models\WhitelabelUserBalanceLog;

final class Add_Missing_Defaults extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::modify_fields(
            WhitelabelPaymentMethod::get_table_name(),
            [
                'order' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => 0],
            ]
        );

        DBUtil::modify_fields(
            PaymentRequestLock::get_table_name(),
            [
                'requests_count' => ['type' => 'int', 'constraint' => 4, 'unsigned' => true, 'default' => 0],
            ]
        );

        DBUtil::modify_fields(
            RaffleDraw::get_table_name(),
            [
                'lines_won_count' => ['type' => 'int', 'unsigned' => true, 'null' => true, 'default' => 0],
                'is_calculated' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => false]
            ]
        );

        DBUtil::modify_fields(
            RafflePrize::get_table_name(),
            [
                'lines_won_count' => ['type' => 'int', 'unsigned' => true, 'default' => 0],
                'total' => ['type' => 'decimal', 'constraint' => [15,2], 'unsigned' => true, 'default' => 0.0],
                'per_user' => ['type' => 'decimal', 'constraint' => [15,2], 'unsigned' => true, 'default' => 0.0],
            ]
        );

        DBUtil::modify_fields(
            RaffleProvider::get_table_name(),
            [
                'provider' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => 3],
                'multiplier' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => 0],
            ]
        );

        DBUtil::modify_fields(
            WhitelabelRaffleTicket::get_table_name(),
            [
                'status' => ['type' => 'tinyint', 'constraint' => 1, 'default' => false],
                'is_paid_out' => ['type' => 'tinyint', 'constraint' => 1, 'default' => false],
                'prize' => ['type' => 'decimal', 'constraint' => [13,2], 'unsigned' => false, 'default' => 0.0],
                'amount' => ['type' => 'decimal', 'constraint' => [8,2], 'unsigned' => true, 'default' => 0.0],
                'income_type' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => 0],
            ]
        );

        DBUtil::modify_fields(
            WhitelabelRaffleTicketLine::get_table_name(),
            [
                'status' => ['type' => 'tinyint', 'constraint' => 1, 'default' => false],
                'income_type' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => 0],
            ]
        );

        DBUtil::modify_fields(
            WhitelabelTransaction::get_table_name(),
            [
                'amount' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => 0.0],
                'amount_usd' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => 0.0],
                'cost' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => 0.0],
                'cost_usd' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => 0.0],
                'payment_cost' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => 0.0],
                'payment_cost_usd' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => 0.0],
            ]
        );

        DBUtil::modify_fields(
            AdminUser::get_table_name(),
            [
                'timezone' => ['type' => 'varchar', 'constraint' => 40, 'default' => 'UTC'],
            ]
        );

        DBUtil::modify_fields(
            Lottery::get_table_name(),
            [
                'is_enabled' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => false],
            ]
        );

        DBUtil::modify_fields(
            Model_Payment_Method_Currency::getTableName(),
            [
                'whitelabel_payment_method_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'default' => 0],
                'currency_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'null' => true, 'default' => 0],
                'min_purchase' => ['type' => 'decimal', 'constraint' => [15,2], 'null' => true, 'default' => 0.0],
            ]
        );

        DBUtil::modify_fields(
            WhitelabelUserBalanceLog::get_table_name(),
            [
                'balance_change' => ['type' => 'decimal', 'constraint' => [9,2], 'default' => 0.0]
            ]
        );
    }

    protected function down_gracefully(): void
    {
        DBUtil::modify_fields(
            WhitelabelPaymentMethod::get_table_name(),
            [
                'order' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
            ]
        );

        DBUtil::modify_fields(
            PaymentRequestLock::get_table_name(),
            [
                'requests_count' => ['type' => 'int', 'constraint' => 4, 'unsigned' => true],
            ]
        );

        DBUtil::modify_fields(
            RaffleDraw::get_table_name(),
            [
                'lines_won_count' => ['type' => 'int', 'unsigned' => true, 'null' => true],
                'is_calculated' => ['type' => 'tinyint', 'constraint' => 1]
            ]
        );

        DBUtil::modify_fields(
            RafflePrize::get_table_name(),
            [
                'lines_won_count' => ['type' => 'int', 'unsigned' => true],
                'total' => ['type' => 'decimal', 'constraint' => [15,2], 'unsigned' => true],
                'per_user' => ['type' => 'decimal', 'constraint' => [15,2], 'unsigned' => true],
            ]
        );

        DBUtil::modify_fields(
            RaffleProvider::get_table_name(),
            [
                'provider' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'multiplier' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
            ]
        );

        DBUtil::modify_fields(
            WhitelabelRaffleTicket::get_table_name(),
            [
                'status' => ['type' => 'tinyint', 'constraint' => 1],
                'is_paid_out' => ['type' => 'tinyint', 'constraint' => 1],
                'prize' => ['type' => 'decimal', 'constraint' => [13,2], 'unsigned' => false],
                'amount' => ['type' => 'decimal', 'constraint' => [5,2], 'unsigned' => true],
            ]
        );

        DBUtil::modify_fields(
            WhitelabelRaffleTicketLine::get_table_name(),
            [
                'status' => ['type' => 'tinyint', 'constraint' => 1],
            ]
        );

        DBUtil::modify_fields(
            WhitelabelTransaction::get_table_name(),
            [
                'amount' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => null],
                'amount_usd' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => null],
                'cost' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => null],
                'cost_usd' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => null],
                'payment_cost' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => null],
                'payment_cost_usd' => ['type' => 'decimal', 'constraint' => [15, 2], 'null' => true, 'default' => null],
            ]
        );

        DBUtil::modify_fields(
            AdminUser::get_table_name(),
            [
                'timezone' => ['type' => 'varchar', 'constraint' => 40],
            ]
        );

        DBUtil::modify_fields(
            Lottery::get_table_name(),
            [
                'is_enabled' => Helper_Migration::FIELD_TYPE_BOOLEAN,
            ]
        );

        DBUtil::modify_fields(
            Model_Payment_Method_Currency::getTableName(),
            [
                'whitelabel_payment_method_id' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'default' => null],
                'currency_id' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'null' => true, 'default' => null],
                'min_purchase' => ['type' => 'decimal', 'constraint' => [15,2], 'null' => true, 'default' => null],
            ]
        );

        DBUtil::modify_fields(
            WhitelabelUserBalanceLog::get_table_name(),
            [
                'balance_change' => ['type' => 'decimal', 'constraint' => [9,2]]
            ]
        );
    }
}