<?php

use Fuel\Core\Cli;
use Fuel\Core\DBUtil;
use Fuel\Tasks\Migrate;

require_once(APPPATH . '/../core/tasks/migrate.php');

final class Helper_Migration
{
    public const FIELD_TYPE_BOOLEAN = ['type' => 'boolean'];
    public const INDEX_TYPE_UNIQUE = 'unique';
    public const INDEX_TYPE_INDEX = 'index';

    /** Short the constraint if is longer than 64 chars (mysql doesn't allow that) */
    private static function shortenConstraintIfLengthIsWrong(string $constraint): string
    {
        $hasConstraintAllowedLength = strlen($constraint) <= 64;

        if ($hasConstraintAllowedLength) {
            return $constraint;
        }

        $shortenValues = [
            'whitelabel' => 'wl',
            'currency' => 'curr',
            'calculation' => 'calc',
            'enabled' => 'on',
            'disabled' => 'off',
            'margin' => 'marg',
            'whitelabel_slot_provider' => 'wlsp'
        ];

        return strtr($constraint, $shortenValues);
    }

    public static function generate_foreign_key(string $table, string $fieldName, string $onDeleteAction = 'CASCADE', string $onUpdateAction = "RESTRICT", string $destinationTable = null): array
    {
        $destinationTable = $destinationTable ?: substr($fieldName, 0, strrpos($fieldName, '_'));
        $constraint = "{$table}_{$fieldName}_foreign";
        $constraint = self::shortenConstraintIfLengthIsWrong($constraint);

        return [
            'constraint' => $constraint,
            'key' => "$fieldName",
            'reference' => [
                'table' => $destinationTable,
                'column' => 'id'
            ],
            'on_update' => $onUpdateAction,
            'on_delete' => $onDeleteAction,
        ];
    }

    /** Use this function when you want to create not unique key */
    public static function generateIndexKey(
        string $tableName,
        array $indexColumns,
        string $indexType = self::INDEX_TYPE_INDEX
    ): void
    {
        $name = self::generateKeyName($indexColumns, $indexType);
        $name = self::shortenConstraintIfLengthIsWrong($name);
        
        DBUtil::create_index(
            $tableName,
            $indexColumns,
            $name,
            $indexType
        );
    }

    /** Fuel don't have method for update index. We have to generate new one and then delete old */
    public static function updateIndex(
        string $tableName,
        array $oldIndexColumns,
        array $newIndexColumns,
        string $indexType = self::INDEX_TYPE_INDEX
    ): void {
        self::generateIndexKey($tableName, $newIndexColumns, $indexType);
        self::dropIndexKey($tableName, $oldIndexColumns, $indexType);
    }

    public static function generate_unique_key(string $table_name, array $index_columns): void
    {
        $name = self::generateKeyName($index_columns);

        DBUtil::create_index(
            $table_name,
            $index_columns,
            $name,
            self::INDEX_TYPE_UNIQUE
        );
    }

    public static function dropIndexKey(
        string $tableName,
        array $indexColumns,
        string $indexType = self::INDEX_TYPE_INDEX
    ): void
    {
        $name = self::generateKeyName($indexColumns, $indexType);
        $name = self::shortenConstraintIfLengthIsWrong($name);

        DBUtil::drop_index(
            $tableName,
            $name
        );
    }

    public static function drop_unique_key(string $table_name, array $index_columns): void
    {
        $name = self::generateKeyName($index_columns);

        DBUtil::drop_index(
            $table_name,
            $name
        );
    }

    private static function generateKeyName(array $indexColumns, string $indexType = 'unique'): string
    {
        return implode('_', $indexColumns) . '_' . $indexType;
    }

    public static function migrate(bool $catchup = true): void
    {
        if ($catchup) {
            Cli::set_option('catchup', true);
        }
        (new Migrate())->run();
    }
}
