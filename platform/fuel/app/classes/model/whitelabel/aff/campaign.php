<?php

/**
 * @deprecated
 * Class responsible for interactions with database - table whitelabel_aff_campaign.
 */
class Model_Whitelabel_Aff_Campaign extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_aff_campaign';
    
    /**
     *
     * @var array
     */
    public static $cache_list = [];

    /**
     * Fetch campaigns for specified whitelabel_aff.
     * @param int $whitelabel_aff_id id of the whitelabel_aff.
     * @return array campaigns.
     */
    public static function fetch_campaigns($whitelabel_aff_id)
    {
        return self::find([
            "where" => [
                "whitelabel_aff_id" => $whitelabel_aff_id,
            ],
            "order_by" => ["campaign" => "ASC"]
        ]);
    }
}
