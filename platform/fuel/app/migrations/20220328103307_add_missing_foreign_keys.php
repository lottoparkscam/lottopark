<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;
use Model_Whitelabel_Country_Currency;
use Models\Currency;
use Models\PaymentLog;
use Models\WhitelabelAffCommission;
use Models\WhitelabelPaymentMethod;
use Models\WhitelabelTransaction;
use Models\Whitelabel;

final class Add_Missing_Foreign_Keys extends Database_Migration_Graceful
{
    protected function up_gracefully(): void
    {
        /** [in, to, field_name] */
        $keysToAdd = [
            [WhitelabelPaymentMethod::get_table_name(), Currency::get_table_name(), 'payment_currency_id'],
            [WhitelabelAffCommission::get_table_name(), Currency::get_table_name(), 'payment_currency_id'],
            [WhitelabelTransaction::get_table_name(), Currency::get_table_name(), 'payment_currency_id'],
            [Whitelabel::get_table_name(), Currency::get_table_name(), 'manager_site_currency_id'],
            [PaymentLog::get_table_name(), WhitelabelPaymentMethod::get_table_name(), 'whitelabel_payment_method_id'],
        ];

        foreach ($keysToAdd as $data) {
            [$inTableName, $toTableName, $fieldName] = $data;

            $foreignKey = Helper_Migration::generate_foreign_key(
                $inTableName,
                $fieldName,
                'CASCADE',
                'RESTRICT',
                $toTableName
            );
            DBUtil::add_foreign_key($inTableName, $foreignKey);
        }

        DBUtil::drop_foreign_key(Model_Whitelabel_Country_Currency::getTableName(), 'whitelabel_country_currency_wdc_id_c_idfx_idx');
    }

    protected function down_gracefully(): void
    {
        $keysToRemove = [
            [WhitelabelPaymentMethod::get_table_name(), Currency::get_table_name(), 'payment_currency_id'],
            [WhitelabelAffCommission::get_table_name(), Currency::get_table_name(), 'payment_currency_id'],
            [WhitelabelTransaction::get_table_name(), Currency::get_table_name(), 'payment_currency_id'],
            [Whitelabel::get_table_name(), Currency::get_table_name(), 'manager_site_currency_id'],
            [PaymentLog::get_table_name(), WhitelabelPaymentMethod::get_table_name(), 'whitelabel_payment_method_id'],
        ];

        foreach ($keysToRemove as $data) {
            [$inTableName, $toTableName, $fieldName] = $data;

            $foreignKey = Helper_Migration::generate_foreign_key(
                $inTableName,
                $fieldName,
                'CASCADE',
                'RESTRICT',
                $toTableName
            );
            DBUtil::drop_foreign_key($inTableName, $foreignKey['constraint']);
        }

        DBUtil::add_foreign_key(Model_Whitelabel_Country_Currency::getTableName(), [
            'constraint' => 'whitelabel_country_currency_wdc_id_c_idfx_idx',
            'key' => 'whitelabel_default_currency_id',
            'reference' => [
                'table' => 'whitelabel_default_currency',
                'column' => 'id'
            ],
            'on_update' => 'NO ACTION',
            'on_delete' => 'CASCADE'
        ]);
    }
}