<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;
use Model_Emerchantpay_User_CC;
use Model_Multidraw;
use Model_Payment_Method_Supported_Currency;
use Model_Whitelabel_Aff_Payout;
use Models\LotteryDraw;
use Models\PaymentMethod;
use Models\SlotLog;
use Models\Whitelabel;
use Models\SlotGame;
use Models\WhitelabelAff;
use Models\WhitelabelAffCommission;
use Models\WhitelabelLottery;
use Models\WhitelabelPaymentMethod;
use Models\WhitelabelPaymentMethodCurrency;
use Models\WhitelabelRaffle;
use Models\WhitelabelSlotProvider;
use Models\WhitelabelSlotProviderSubprovider;
use Models\WhitelabelTransaction;
use Models\WhitelabelUser;
use Models\WhitelabelUserAff;
use Models\WhitelabelUserBalanceLog;
use Models\WhitelabelUserTicket;
use Models\WhitelabelUserTicketLine;
use Models\WhitelabelWithdrawal;
use Models\WithdrawalRequest;
use Models\Lottery;

final class Change_Columns_To_Correct_Boolean extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        DBUtil::modify_fields(
            WhitelabelAff::get_table_name(),
            [
                'is_active' => Helper_Migration::FIELD_TYPE_BOOLEAN,
                'is_confirmed' => Helper_Migration::FIELD_TYPE_BOOLEAN,
                'is_accepted' => Helper_Migration::FIELD_TYPE_BOOLEAN,
                'is_deleted' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => false],
                'is_show_name' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => false],
            ]
        );

        DBUtil::modify_fields(
            WhitelabelAffCommission::get_table_name(),
            [
                'is_accepted' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => false]
            ]
        );

        DBUtil::modify_fields(
            WhitelabelUserAff::get_table_name(),
            [
                'is_deleted' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => false],
                'is_accepted' => Helper_Migration::FIELD_TYPE_BOOLEAN,
                'is_expired' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => false],
            ]
        );

        DBUtil::modify_fields(
            SlotGame::get_table_name(),
            [
                'is_deleted' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => false],
                'has_demo' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => false],
                'has_lobby' => Helper_Migration::FIELD_TYPE_BOOLEAN,
                'has_freespins' => Helper_Migration::FIELD_TYPE_BOOLEAN,
                'is_mobile' => Helper_Migration::FIELD_TYPE_BOOLEAN,
                'freespin_valid_until_full_day' => Helper_Migration::FIELD_TYPE_BOOLEAN,
            ]
        );

        DBUtil::modify_fields(
            Whitelabel::get_table_name(),
            [
                'user_registration_through_ref_only' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => false],
                'aff_auto_accept' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => true],
                'aff_lead_auto_accept' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => true],
                'aff_hide_ticket_and_payment_cost' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => false],
                'aff_hide_amount' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => false],
                'aff_hide_income' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => false],
                'aff_enable_sign_ups' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => false],
                'aff_auto_create_on_register' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => false],
                'use_register_company' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['null' => true, 'default' => false],
                'is_report' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => true],
                'use_logins_for_users' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => false],
                'can_user_register_via_site' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => true],
                'can_user_login_via_site' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => true],
                'assert_unique_emails_for_users' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => true],
            ]
        );

        DBUtil::modify_fields(
            WhitelabelLottery::get_table_name(),
            [
                'is_enabled' => Helper_Migration::FIELD_TYPE_BOOLEAN,
                'is_multidraw_enabled' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => false],
            ]
        );

        DBUtil::modify_fields(
            WhitelabelRaffle::get_table_name(),
            [
                'is_margin_calculation_enabled' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => true],
            ]
        );

        DBUtil::modify_fields(
            WhitelabelPaymentMethod::get_table_name(),
            [
                'show' => Helper_Migration::FIELD_TYPE_BOOLEAN,
                'show_payment_logotype' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => true],
            ]
        );

        DBUtil::modify_fields(
            WhitelabelPaymentMethodCurrency::get_table_name(),
            [
                'is_zero_decimal' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['null' => true, 'default' => false],
            ]
        );

        DBUtil::modify_fields(
            WhitelabelSlotProvider::get_table_name(),
            [
                'is_limit_enabled' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => false],
            ]
        );

        DBUtil::modify_fields(
            WhitelabelSlotProviderSubprovider::get_table_name(),
            [
                'is_enabled' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => true],
            ]
        );

        DBUtil::modify_fields(
            WhitelabelTransaction::get_table_name(),
            [
                'is_casino' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => false],
            ]
        );

        DBUtil::modify_fields(
            WhitelabelUser::get_table_name(),
            [
                'is_active' => Helper_Migration::FIELD_TYPE_BOOLEAN,
                'is_confirmed' => Helper_Migration::FIELD_TYPE_BOOLEAN,
                'is_deleted' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => false],
                'refer_bonus_used' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => false],
                'sent_welcome_mail' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => false],
            ]
        );

        DBUtil::modify_fields(
            WhitelabelUserBalanceLog::get_table_name(),
            [
                'is_bonus' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => false],
            ]
        );

        DBUtil::modify_fields(
            Model_Multidraw::getTableName(),
            [
                'is_finished' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => false],
                'is_cancelled' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => false],
            ]
        );

        DBUtil::modify_fields(
            WhitelabelUserTicketLine::get_table_name(),
            [
                'payout' => Helper_Migration::FIELD_TYPE_BOOLEAN,
            ]
        );

        DBUtil::modify_fields(
            WhitelabelWithdrawal::get_table_name(),
            [
                'show' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => true],
                'show_casino' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => true],
            ]
        );


        DBUtil::modify_fields(
            WithdrawalRequest::get_table_name(),
            [
                'is_casino' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => false],
            ]
        );

        DBUtil::modify_fields(
            Model_Emerchantpay_User_CC::getTableName(),
            [
                'is_deleted' => Helper_Migration::FIELD_TYPE_BOOLEAN,
                'is_lastused' => Helper_Migration::FIELD_TYPE_BOOLEAN,
            ]
        );

        DBUtil::modify_fields(
        // model haven't existed yet
            'mail_templates',
            [
                'is_partial' => Helper_Migration::FIELD_TYPE_BOOLEAN,
            ]
        );

        DBUtil::modify_fields(
            Model_Payment_Method_Supported_Currency::getTableName(),
            [
                'is_zero_decimal' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['null' => true, 'default' => false],
            ]
        );

        DBUtil::modify_fields(
            Model_Whitelabel_Aff_Payout::getTableName(),
            [
                'is_paidout' => Helper_Migration::FIELD_TYPE_BOOLEAN,
            ]
        );

        DBUtil::modify_fields(
            Lottery::get_table_name(),
            [
                'is_enabled' => Helper_Migration::FIELD_TYPE_BOOLEAN,
                'draw_jackpot_set' => Helper_Migration::FIELD_TYPE_BOOLEAN,
                'estimated_updated' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => true],
                'scans_enabled' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => false],
                'is_multidraw_enabled' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => false],
            ]
        );

        DBUtil::modify_fields(
            PaymentMethod::get_table_name(),
            [
                'is_enabled_for_casino' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => true],
            ]
        );

        DBUtil::modify_fields(
            SlotLog::get_table_name(),
            [
                'is_error' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => false],
            ]
        );

        DBUtil::modify_fields(
            WhitelabelUserTicket::get_table_name(),
            [
                'paid' => Helper_Migration::FIELD_TYPE_BOOLEAN,
                'payout' => Helper_Migration::FIELD_TYPE_BOOLEAN,
                'is_insured' => Helper_Migration::FIELD_TYPE_BOOLEAN,
                'has_ticket_scan' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => false],
                'is_synchronized' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => false],
            ]
        );

        DBUtil::modify_fields(
            LotteryDraw::get_table_name(),
            [
                'has_pending_tickets' => Helper_Migration::FIELD_TYPE_BOOLEAN + ['default' => true],
            ]
        );
    }


    protected function down_gracefully(): void
    {
        DBUtil::modify_fields(
            WhitelabelAff::get_table_name(),
            [
                'is_active' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'is_confirmed' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'is_accepted' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'is_deleted' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => false],
                'is_show_name' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => false],
            ]
        );

        DBUtil::modify_fields(
            WhitelabelAffCommission::get_table_name(),
            [
                'is_accepted' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => false],
            ]
        );

        DBUtil::modify_fields(
            WhitelabelUserAff::get_table_name(),
            [
                'is_deleted' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => false],
                'is_accepted' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true],
                'is_expired' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => false],
            ]
        );

        DBUtil::modify_fields(
            SlotGame::get_table_name(),
            [
                'is_deleted' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => false],
                'has_demo' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => false],
                'has_lobby' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true],
                'has_freespins' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true],
                'is_mobile' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true],
                'freespin_valid_until_full_day' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true],
            ]
        );

        DBUtil::modify_fields(
            Whitelabel::get_table_name(),
            [
                'user_registration_through_ref_only' =>  ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => false],
                'aff_auto_accept' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => true],
                'aff_lead_auto_accept' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => true],
                'aff_hide_ticket_and_payment_cost' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => false],
                'aff_hide_amount' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => false],
                'aff_hide_income' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => false],
                'aff_enable_sign_ups' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => false],
                'aff_auto_create_on_register' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => false],
                'use_register_company' => ['type' => 'tinyint', 'unsigned' => true, 'null' => true, 'default' => false],
                'is_report' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => true],
                'use_logins_for_users' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => false],
                'can_user_register_via_site' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => true],
                'can_user_login_via_site' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => true],
                'assert_unique_emails_for_users' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => true],
            ]
        );

        DBUtil::modify_fields(
            WhitelabelLottery::get_table_name(),
            [
                'is_enabled' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true],
                'is_multidraw_enabled' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => false],
            ]
        );

        DBUtil::modify_fields(
            WhitelabelRaffle::get_table_name(),
            [
                'is_margin_calculation_enabled' =>  ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => true],
            ]
        );

        DBUtil::modify_fields(
            WhitelabelPaymentMethod::get_table_name(),
            [
                'show' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'show_payment_logotype' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => true],
            ]
        );

        DBUtil::modify_fields(
            WhitelabelPaymentMethodCurrency::get_table_name(),
            [
                'is_zero_decimal' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'null' => true, 'default' => false],
            ]
        );

        DBUtil::modify_fields(
            WhitelabelSlotProvider::get_table_name(),
            [
                'is_limit_enabled' => ['type' => 'tinyint', 'default' => false],
            ]
        );

        DBUtil::modify_fields(
            WhitelabelSlotProviderSubprovider::get_table_name(),
            [
                'is_enabled' => ['type' => 'tinyint', 'default' => true],
            ]
        );

        DBUtil::modify_fields(
            WhitelabelTransaction::get_table_name(),
            [
                'is_casino' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => false],
            ]
        );

        DBUtil::modify_fields(
            WhitelabelUser::get_table_name(),
            [
                'is_active' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'is_confirmed' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'is_deleted' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'refer_bonus_used' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true],
                'sent_welcome_mail' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => false],
            ]
        );

        DBUtil::modify_fields(
            WhitelabelUserBalanceLog::get_table_name(),
            [
                'is_bonus' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => false],
            ]
        );

        DBUtil::modify_fields(
            Model_Multidraw::getTableName(),
            [
                'is_finished' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => false],
                'is_cancelled' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => false],
            ]
        );

        DBUtil::modify_fields(
            WhitelabelUserTicketLine::get_table_name(),
            [
                'payout' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
            ]
        );

        DBUtil::modify_fields(
            WhitelabelWithdrawal::get_table_name(),
            [
                'show' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => true],
                'show_casino' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => true],
            ]
        );

        DBUtil::modify_fields(
            WithdrawalRequest::get_table_name(),
            [
                'is_casino' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => false],
            ]
        );

        DBUtil::modify_fields(
            Model_Emerchantpay_User_CC::getTableName(),
            [
                'is_deleted' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'is_lastused' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
            ]
        );

        DBUtil::modify_fields(
        // model haven't existed yet
            'mail_templates',
            [
                'is_partial' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true],
            ]
        );

        DBUtil::modify_fields(
            Model_Payment_Method_Supported_Currency::getTableName(),
            [
                'is_zero_decimal' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'null' => true, 'default' => 0],
            ]
        );

        DBUtil::modify_fields(
            Model_Whitelabel_Aff_Payout::getTableName(),
            [
                'is_paidout' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true],
            ]
        );

        DBUtil::modify_fields(
            Lottery::get_table_name(),
            [
                'is_enabled' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true],
                'draw_jackpot_set' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'estimated_updated' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => true],
                'scans_enabled' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => false],
                'is_multidraw_enabled' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => false],
            ]
        );

        DBUtil::modify_fields(
            PaymentMethod::get_table_name(),
            [
                'is_enabled_for_casino' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => true],
            ]
        );

        DBUtil::modify_fields(
            SlotLog::get_table_name(),
            [
                'is_error' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true, 'default' => false],
            ]
        );

        DBUtil::modify_fields(
            WhitelabelUserTicket::get_table_name(),
            [
                'paid' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'payout' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
                'is_insured' => ['type' => 'tinyint', 'constraint' => 1, 'unsigned' => true],
                'has_ticket_scan' => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => false],
                'is_synchronized' => ['type' => 'int', 'constraint' => 1, 'unsigned' => true, 'default' => false],
            ]
        );

        DBUtil::modify_fields(
            LotteryDraw::get_table_name(),
            [
                'has_pending_tickets' => ['type' => 'int', 'constraint' => 1, 'unsigned' => true, 'default' => true],
            ]
        );
    }
}
