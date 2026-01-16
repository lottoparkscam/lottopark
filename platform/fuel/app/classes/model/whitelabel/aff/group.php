<?php

use Services\Logs\FileLoggerService;

/**
 * @deprecated
 * Class responsible for interactions with database - table whitelabel_aff_group.
 */
class Model_Whitelabel_Aff_Group extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_aff_group';
    
    /**
     *
     * @var array
     */
    public static $cache_list = [
        "whitelabel_aff_group.wlgroups"
    ];

    /**
     *
     * @param array $groups
     * @return array
     */
    private static function prepare_whitelabel_groups($groups): array
    {
        $ngroups = [];
        foreach ($groups as $group) {
            $ngroups[$group['id']] = $group;
        }
        return $ngroups;
    }
    
    /**
     *
     * @param array $whitelabel
     * @return array
     */
    public static function get_whitelabel_groups($whitelabel): array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $groups = null;
        $expiredTime = Helpers_Whitelabel::get_expired_time();
        $key = self::$cache_list[0];
        if (!empty($whitelabel['id'])) {
            $key .= '.'.$whitelabel['id'];
        }

        $query = "SELECT * 
            FROM whitelabel_aff_group 
            WHERE 1=1";

        if (!empty($whitelabel['id'])) {
            $query .= " AND whitelabel_id = :whitelabel";
        }

        $query .= " ORDER BY name";

        $db = DB::query($query);
        if (!empty($whitelabel['id'])) {
            $db->param(":whitelabel", $whitelabel['id']);
        }

        try {
            try {
                $groups = Lotto_Helper::get_cache($key);
            } catch (\CacheNotFoundException $e) {
                $groups = self::prepare_whitelabel_groups($db->execute()->as_array());
                Lotto_Helper::set_cache($key, $groups, $expiredTime);
            }
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
            $groups = self::prepare_whitelabel_groups($db->execute()->as_array());
        }

        return $groups;
    }
}
