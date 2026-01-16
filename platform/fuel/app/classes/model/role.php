<?php

/**
 * @deprecated
 */
class Model_Role extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'admin_user_role';

    /**
     *
     * @return array
     */
    public static function get_all_user_roles(): array
    {
        $query = "SELECT * FROM admin_user_role ORDER BY id";
        $roles = null;
        
        $db = DB::query($query);
        
        $roles = $db->execute()->as_array();        
        
        return $roles;
    }
}
