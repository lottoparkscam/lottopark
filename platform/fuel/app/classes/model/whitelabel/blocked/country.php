<?php

/**
 * Class responsible for interactions with database - table whitelabel_blocked_country.
 */
class Model_Whitelabel_Blocked_Country extends Model_Model
{

    /**
     * Name of the database table binded to this model.
     * @var string
     */
    protected static $_table_name = 'whitelabel_blocked_country';

    /**
     * List of cached columns in this model.
     * @var array
     */
    public static $cache_list = [];

    /**
     * TODO: We can catch those in Model_Model and convert to null (and optionally log).
     * @inheritdoc
     */
    public static function find($config = [], $key = null)
    {
        try {
            return parent::find($config, $key);
        } catch (\Exception $ex) { // TODO: maybe some logging
            return null;
        }
    }

    /**
     * TODO: We can catch those in Model_Model and convert to null (and optionally log).
     * Saves the object to the database by either creating a new record
     * or updating an existing record. Sets the default values if set.
     *
     * @param   bool   $validate  whether to validate the input.
     * @return  array|int|bool  Rows affected and or insert ID, false on failure.
     */
    public function save_safe(bool $validate = true)
    {
        try {
            return parent::save($validate);
        } catch (\Exception $ex) { // TODO: maybe some logging
            return false;
        }
    }

    /**
     * Get all records for specified whitelabel_id and sorted asc by code.
     * @param int $whitelabel_id loosely binded id (castable to int).
     * @return array|null array containing models, or null if none were found or SQL error occurred.
     */
    public static function by_whitelabel_sort_code($whitelabel_id)
    {
        return self::find([
            'where' => ['whitelabel_id' => $whitelabel_id],
            'order_by' => ['code' => 'asc']
        ]);
    }

    /**
     * Get model for specified whitelabel_id and code.
     * @param int $whitelabel_id loosely binded id (castable to int).
     * @param string $code code.
     * @return array|null model, or null if not found or SQL error occurred.
     */
    public static function by_whitelabel_code(int $whitelabel_id, string $code)
    {
        return self::find([
            'where' => [
                'whitelabel_id' => $whitelabel_id,
                'code' => $code
            ],
        ])[0];
    }
}
