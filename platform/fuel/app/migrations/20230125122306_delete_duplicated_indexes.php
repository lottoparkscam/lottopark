<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;
use Throwable;

final class Delete_Duplicated_Indexes extends Database_Migration_Graceful
{
    private array $indexesPerTableToDrop = [
        'imvalap_log' => [
            'imvalap_log_whitelabel_id_type_idx' => ['whitelabel_id', 'type'],
        ],
        'language' => [
            'language_code_idx' => ['code'],
        ],
        'lottery' => [
            'lottery_source_id_lottery_source_idfx_idx' => ['source_id'],
            'lottery_is_enabled_idx' => ['is_enabled'],
        ],
        'lottorisq_log' => [
            'lottorisq_log_type_idx' => ['type'],
        ],
        'payment_log' => [
            'payment_log_type_idx' => ['type'],
        ],
        'slot_game' => [
            'is_deleted_index' => ['is_deleted'],
        ],
        'slot_log' => [
            'is_error_index' => ['is_error'],
        ],
        'slot_transaction' => [
            'type_index' => ['type'],
            'is_canceled_index' => ['is_canceled'],
        ],
        'whitelabel_campaign' => [
            'is_active_index' => ['is_active'],
        ],
        'whitelabel_lottery' => [
            'whitelabel_lottery_w_id_l_id_idmx' => []
        ],
        'whitelabel_ltech' => [
            'whitelabel_ltech_w_id_w_idfx_idx' => []
        ],
        'whitelabel_raffle' => [
            'is_enabled_index' => ['is_enabled'],
        ],
        'whitelabel_raffle_ticket' => [
            'whitelabel_raffle_ticket_whitelabel_transaction_id_idfx_idx' => []
        ],
        'whitelabel_refer_statistics' => [
            'whitelabel_refer_statistics_whitelabel_user_id_idfx' => []
        ],
        'whitelabel_transaction' => [
            'whitelabel_transaction_w_id_w_user_id_status_idmx' => [
                'whitelabel_id',
                'whitelabel_user_id',
                'status',
            ],
            'whitelabel_transaction_w_id_type_idmx' => [
                'whitelabel_id',
                'type',
            ],
        ],
        'whitelabel_user' => [
            'whitelabel_user_w_id_active' => [
                'whitelabel_id',
                'is_deleted',
                'is_active',
            ],
            'whitelabel_user_w_id_date_register_idx' => [
                'whitelabel_id',
                'is_deleted',
                'is_active',
                'date_register'
            ],
        ],
        'whitelabel_user_popup_queue' => [
            'whitelabel_user_popup_queue_whitelabel_id_user_idfx' => ['whitelabel_id'],
        ],
        'whitelabel_user_ticket' => [
            'whitelabel_user_ticket_w_id_paid_id_idmx' => [
                'whitelabel_id',
                'paid',
                'id'
            ]
        ],
    ];

    protected function up_gracefully(): void
    {
        foreach ($this->indexesPerTableToDrop as $tableName => $indexNames) {
            foreach ($indexNames as $indexName => $columns) {
                try {
                    DBUtil::drop_index(
                        $tableName,
                        $indexName
                    );
                } catch (Throwable $exception) {
                    if (!empty($columns)) {
                        throw $exception;
                    }
                }
            }
        }
    }

    protected function down_gracefully(): void
    {
        foreach ($this->indexesPerTableToDrop as $tableName => $indexNames) {
            foreach ($indexNames as $indexName => $columns) {
                // If columns is empty it means there were exact indexes duplicated but one was unique
                // In this case we would like to store it again
                if (empty($columns)) {
                    continue;
                }

                DBUtil::create_index(
                    $tableName,
                    $columns,
                    $indexName,
                    Helper_Migration::INDEX_TYPE_INDEX
                );
            }
        }
    }
}
