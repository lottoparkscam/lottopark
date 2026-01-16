<?php

namespace Fuel\Migrations;

use Database_Migration_Graceful;
use Fuel\Core\DBUtil;
use Helper_Migration;

final class Fix_Clustered_Indexes extends Database_Migration_Graceful
{
    private array $indexesToFixPerTable = [
        'whitelabel_transaction' => ['whitelabel_transaction_w_id_w_user_id_id_idmx'],
        'whitelabel_user' => ['whitelabel_user_w_id_idx'],
        'whitelabel_user_ticket' => [
            'whitelabel_user_ticket_w_id_wu_id_paid_id_idmx',
            'whitelabel_user_ticket_w_id_wu_id_idmx'
        ],
    ];

    private array $columnsPerIndex = [
        'whitelabel_transaction_w_id_w_user_id_id_idmx' => ['whitelabel_id', 'whitelabel_user_id'],
        'whitelabel_user_w_id_idx' => ['whitelabel_id', 'is_deleted', 'is_active'],
        'whitelabel_user_ticket_w_id_wu_id_paid_id_idmx' => ['whitelabel_id', 'whitelabel_user_id', 'paid'],
        'whitelabel_user_ticket_w_id_wu_id_idmx' => ['whitelabel_id', 'whitelabel_user_id'],
    ];

    protected function up_gracefully(): void
    {
        foreach ($this->indexesToFixPerTable as $tableName => $indexNames) {
            foreach ($indexNames as $indexName) {
                DBUtil::drop_index(
                    $tableName,
                    $indexName
                );
            }
        }

        Helper_Migration::generateIndexKey('whitelabel_transaction', ['whitelabel_id', 'whitelabel_user_id']);
        Helper_Migration::generateIndexKey('whitelabel_user', ['whitelabel_id', 'is_deleted', 'is_active']);
        Helper_Migration::generateIndexKey('whitelabel_user_ticket', [
            'whitelabel_id',
            'whitelabel_user_id',
            'paid',
        ]);
    }

    protected function down_gracefully(): void
    {
        Helper_Migration::dropIndexKey('whitelabel_transaction', ['whitelabel_id', 'whitelabel_user_id']);
        Helper_Migration::dropIndexKey('whitelabel_user', ['whitelabel_id', 'is_deleted', 'is_active']);
        Helper_Migration::dropIndexKey('whitelabel_user_ticket', [
            'whitelabel_id',
            'whitelabel_user_id',
            'paid',
        ]);

        foreach ($this->indexesToFixPerTable as $tableName => $indexNames) {
            foreach ($indexNames as $indexName) {
                $columns = $this->columnsPerIndex[$indexName];
                DBUtil::create_index(
                    $tableName,
                    array_merge($columns, ['id']),
                    $indexName,
                    Helper_Migration::INDEX_TYPE_INDEX
                );
            }
        }
    }
}
