<?php

namespace Fuel\Migrations;

use Container;
use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;
use Model_Lcs_Ticket;
use Model_Payment_Method_Currency;
use Model_Whitelabel_Refer_Statistics;
use Model_Whitelabel_User_Ticket_Keno_Data;
use Model_Whitelabel_User_Whitelabel_User_Group;
use Models\CleanerLog;
use Models\Language;
use Models\Raffle;
use Models\WhitelabelLtech;
use Models\WhitelabelRaffleTicket;
use Models\WhitelabelUserAff;
use Models\WhitelabelUserPromoCode;
use Models\Lottery;
use Services\Logs\FileLoggerService;
use Throwable;

final class Add_Missing_Unique_Indexes extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        $logger = Container::get(FileLoggerService::class);

        Helper_Migration::generate_unique_key(Language::get_table_name(), ['code']);
        // New name in model is wordpress_tag
        Helper_Migration::generate_unique_key('wordpress_tags', ['language_id']);

        Helper_Migration::generate_unique_key(Raffle::get_table_name(), ['raffle_rule_id']);
        Helper_Migration::generate_unique_key(Lottery::get_table_name(), ['source_id']);
        Helper_Migration::generate_unique_key(WhitelabelLtech::get_table_name(), ['whitelabel_id']);
        Helper_Migration::generate_unique_key(CleanerLog::get_table_name(), ['whitelabel_transaction_id']);
        Helper_Migration::generate_unique_key(WhitelabelUserPromoCode::get_table_name(), ['whitelabel_transaction_id']);
        Helper_Migration::generate_unique_key(Model_Whitelabel_Refer_Statistics::getTableName(), ['whitelabel_user_id']);

        // Old index that was changed to unique one
        Helper_Migration::generate_unique_key(WhitelabelUserAff::get_table_name(), ['whitelabel_user_id']);
        DBUtil::drop_index(WhitelabelUserAff::get_table_name(), 'whitelabel_user_aff_wu_id_wu_idfx_idx');

        Helper_Migration::generate_unique_key(Model_Whitelabel_User_Ticket_Keno_Data::getTableName(), ['whitelabel_user_ticket_id']);
        Helper_Migration::generate_unique_key(Model_Lcs_Ticket::getTableName(), ['whitelabel_user_ticket_slip_id']);

        Helper_Migration::generate_unique_key(Model_Whitelabel_User_Whitelabel_User_Group::getTableName(), ['whitelabel_user_id', 'whitelabel_user_group_id']);
        try {
            // Before generate_unique_key there were two separate, not-unique keys for: whitelabel_user_id and
            // whitelabel_user_group_id. When we add single unique key contains these both columns, then
            // whitelabel_user_id not-unique key is deleted. We use try because adding this key back in
            // down_gracefully method happens that this key is not deleted again
            DBUtil::create_index(Model_Whitelabel_User_Whitelabel_User_Group::getTableName(), 'whitelabel_user_id', 'whitelabel_user_id');
        } catch (Throwable $e) {
            $logger->error('Cannot create index in whitelabel_user_whitelabel_user_group for whitelabel_user_id column');
        }

        Helper_Migration::generate_unique_key(Model_Payment_Method_Currency::getTableName(), ['whitelabel_payment_method_id', 'currency_id']);

        // Old index that was changed to unique one
        DBUtil::drop_index('wordpress_tags', 'wordpress_tags_language_id_language_idfx_idx');

        Helper_Migration::generate_unique_key(WhitelabelRaffleTicket::get_table_name(), ['whitelabel_transaction_id']);
    }

    protected function down_gracefully(): void
    {
        $logger = Container::get(FileLoggerService::class);

        Helper_Migration::drop_unique_key(Language::get_table_name(), ['code']);
        try {
            // Only with this code and in try catch it is possible to rollback migrations many times
            // Without that setting, it is possible to run only once up and down
            DBUtil::create_index('language', 'code', 'language_code_idx');
        } catch (Throwable $e) {
            $logger->error('Cannot create index in language for code column');
        }


        Helper_Migration::drop_unique_key(WhitelabelLtech::get_table_name(), ['whitelabel_id']);
        try {
            DBUtil::create_index('whitelabel_ltech', 'whitelabel_id', 'whitelabel_ltech_w_id_w_idfx_idx');
        } catch (Throwable $e) {
            $logger->error('Cannot create index in whitelabel_ltech for whitelabel_id column');
        }

        Helper_Migration::drop_unique_key(Model_Whitelabel_Refer_Statistics::getTableName(), ['whitelabel_user_id']);

        Helper_Migration::drop_unique_key(WhitelabelUserAff::get_table_name(), ['whitelabel_user_id']);
        try {
            DBUtil::create_index(WhitelabelUserAff::get_table_name(), 'whitelabel_user_id', 'whitelabel_user_aff_wu_id_wu_idfx_idx');
        } catch (Throwable $e) {
            $logger->error('Cannot create index in whitelabel_user_aff for whitelabel_user_id column');
        }

        Helper_Migration::drop_unique_key(Model_Payment_Method_Currency::getTableName(), ['whitelabel_payment_method_id', 'currency_id']);
        Helper_Migration::drop_unique_key(Lottery::get_table_name(), ['source_id']);
        try {
            DBUtil::create_index(Lottery::get_table_name(), 'source_id', 'lottery_source_id_lottery_source_idfx');
        } catch (Throwable $e) {
            $logger->error('Cannot create index in lottery for source_id column');
        }

        Helper_Migration::drop_unique_key(Model_Whitelabel_User_Whitelabel_User_Group::getTableName(), ['whitelabel_user_id', 'whitelabel_user_group_id']);

        DBUtil::create_index('wordpress_tags', 'language_id', 'wordpress_tags_language_id_language_idfx_idx');
        Helper_Migration::drop_unique_key('wordpress_tags', ['language_id']);

        try {
            DBUtil::create_index(Raffle::get_table_name(), 'raffle_rule_id', 'raffle_raffle_rule_id_idfx_idx');
        } catch (Throwable $e) {
            $logger->error('Cannot create index in raffle for raffle_rule_id column');
        }

        Helper_Migration::drop_unique_key(Raffle::get_table_name(), ['raffle_rule_id']);

        try {
            DBUtil::create_index(CleanerLog::get_table_name(), 'whitelabel_transaction_id', 'cleaner_log_whitelabel_transaction_id_idfx_idx');
        } catch (Throwable $e) {
            $logger->error('Cannot create index in cleaner_log for whitelabel_transaction_id column');
        }
        Helper_Migration::drop_unique_key(CleanerLog::get_table_name(), ['whitelabel_transaction_id']);

        try {
            DBUtil::create_index(WhitelabelUserPromoCode::get_table_name(), 'whitelabel_transaction_id', 'whitelabel_user_promo_code_whitelabel_transaction_id_idfx_idx');
        } catch (Throwable $e) {
            $logger->error('Cannot create index in whitelabel_user_promo_code for whitelabel_transaction_id column');
        }
        Helper_Migration::drop_unique_key(WhitelabelUserPromoCode::get_table_name(), ['whitelabel_transaction_id']);

        try {
            DBUtil::create_index(Model_Whitelabel_User_Ticket_Keno_Data::getTableName(), 'whitelabel_user_ticket_id', 'whitelabel_user_ticket_keno_data_wut_id_idfx_idx');
        } catch (Throwable $e) {
            $logger->error('Cannot create index in whitelabel_user_ticket_keno_data for whitelabel_user_ticket_id column');
        }
        Helper_Migration::drop_unique_key(Model_Whitelabel_User_Ticket_Keno_Data::getTableName(), ['whitelabel_user_ticket_id']);

        try {
            DBUtil::create_index(Model_Lcs_Ticket::getTableName(), 'whitelabel_user_ticket_slip_id', 'lcs_ticket_whitelabel_user_ticket_slip_id_idfx_idx');
        } catch (Throwable $e) {
            $logger->error('Cannot create index in lcs_ticket for whitelabel_user_ticket_slip_id column');
        }
        Helper_Migration::drop_unique_key(Model_Lcs_Ticket::getTableName(), ['whitelabel_user_ticket_slip_id']);

        try {
            DBUtil::create_index(WhitelabelRaffleTicket::get_table_name(), 'whitelabel_transaction_id', 'whitelabel_raffle_ticket_whitelabel_transaction_id_idfx_idx');
        } catch (Throwable $e) {
            $logger->error('Cannot create index in whitelabel_raffle_ticket for whitelabel_raffle_ticket column');
        }
        Helper_Migration::drop_unique_key(WhitelabelRaffleTicket::get_table_name(), ['whitelabel_transaction_id']);
    }
}