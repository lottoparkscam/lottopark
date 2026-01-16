<?php

/**
 * @deprecated
 * Class responsible for interactions with database - table whitelabel_aff_medium.
 */
class Model_Whitelabel_Aff_Medium extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_aff_medium';
    
    /**
     *
     * @var array
     */
    public static $cache_list = [];
    
    /**
     * Fetch mediums for specified whitelabel_aff.
     * @param int $whitelabel_aff_id id of the whitelabel_aff.
     * @return array mediums.
     */
    public static function fetch_mediums($whitelabel_aff_id)
    {
        return Model_Whitelabel_Aff_Medium::find([
            "where" => [
                "whitelabel_aff_id" => $whitelabel_aff_id,
            ],
            "order_by" => ["medium" => "ASC"]
        ]);
    }
}
