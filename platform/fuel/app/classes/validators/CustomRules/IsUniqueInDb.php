<?php

namespace Validators\CustomRules;

use Fuel\Core\DB;
use Helpers\WhitelabelHelper;

class IsUniqueInDb
{
    /** 
     * Eg. options:
     * - column => whitelabel_user.login (required)
     * - checkWhitelabelId => true (default)
     * 
     * Notice: This function uses htmlspecialchars for sanitization of $value
     * 
     * @testPath platform/fuel/app/tests/feature/validators/rules/IsUniqueInDbTest.php
     */
    public static function _validation_isUniqueInDb(string $value, array $options): bool
    {
        $value = htmlspecialchars($value);
        list($table, $field) = explode('.', $options['column']);
        $field = strtolower($field);

        $result = DB::select(DB::expr("count($field) as 'count'"))
            ->where($field, '=', $value);

        $checkWhitelabelId = !isset($options['checkWhitelabelId']) ? true : $options['checkWhitelabelId'];
        if ($checkWhitelabelId) {
            $result = $result->where('whitelabel_id', '=', WhitelabelHelper::getId());
        }

        $result = $result->from($table)->execute();
        $count = (int) $result[0]['count'];

        return $count === 0;
    }
}
