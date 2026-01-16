<?php

/**
 * @deprecated
 * Class responsible for interactions with database - table whitelabel_aff_content.
 */
class Model_Whitelabel_Aff_Content extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_aff_content';
    
    /**
     *
     * @var array
     */
    public static $cache_list = [];
    
    /**
     * Fetch contents for specified whitelabel_aff.
     * @param int $whitelabel_aff_id id of the whitelabel_aff.
     * @return array mediums.
     */
    public static function fetch_contents(int $whitelabel_aff_id):? array
    {
        return Model_Whitelabel_Aff_Content::find([
            "where" => [
                "whitelabel_aff_id" => $whitelabel_aff_id,
            ],
            "order_by" => ["content" => "ASC"]
        ]);
    }
}
