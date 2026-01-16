<?php

use Fuel\Core\DB;

final class Model_Whitelabel_User_Group extends Model_Model
{
    /**
     * @var string
     */
    protected static $_table_name = 'whitelabel_user_group';
    
    /**
     *
     * @param int $whitelabel_id
     * @return array
     */
    public static function get_all_groups_for_whitelabel(int $whitelabel_id)
    {
        $res = [];

        $query = DB::select('*')->from(self::$_table_name);

        if ($whitelabel_id > 0) {
            $query->where('whitelabel_id', '=', $whitelabel_id);
        }

        $res = $query->execute()->as_array();

        return $res;
    }

    /**
     *
     * @param int $id
     * @param array $updated_group
     * @return bool
     */
    public static function update_user_group(int $id, array $updated_group)
    {
        $res = 0;

        $query = DB::update('whitelabel_user_group')->set($updated_group)->where('id', '=', $id);

        $res = $query->execute();

        if ((int)$res === 0) {
            return false;
        }
        return true;
    }

    /**
     *
     * @param int $id
     * @return bool
     */
    public static function delete_group(int $id)
    {
        $db = DB::delete(self::$_table_name)
            ->where('id', '=', $id)
            ->execute();

        return true;
    }

    /**
     *
     * @param int $user_id
     * @return array
     */
    public static function get_for_user(int $user_id)
    {
        $groups = [];

        $query = DB::select('whitelabel_user_group.id', 'whitelabel_user_group.name')
        ->from('whitelabel_user_whitelabel_user_group')
        ->join('whitelabel_user_group')->on('whitelabel_user_whitelabel_user_group.whitelabel_user_group_id', '=', 'whitelabel_user_group.id')
        ->where('whitelabel_user_whitelabel_user_group.whitelabel_user_id', '=', $user_id);

        $groups = $query->execute()->as_array();

        return $groups;
    }

    /**
     *
     * @param int $group_id
     * @return array
     */
    public static function check_users_for_group(int $group_id)
    {
        $has_members = false;

        $result = DB::select('*')
        ->from('whitelabel_user_whitelabel_user_group')
        ->where('whitelabel_user_group_id', '=', $group_id)
        ->execute()
        ->as_array();

        if (count($result) > 0) {
            $has_members = true;
        }

        return $has_members;
    }

    /**
     *
     * @param int $id
     * @return array
     */
    public static function get_groups_keys_for_user(int $id)
    {
        $group_keys = [];

        $groups = self::get_for_user($id);

        foreach ($groups as $group) {
            array_push($group_keys, $group['id']);
        }

        return $group_keys;
    }

    /**
     *
     * @param array $users_ids
     * @return bool
     */
    public static function delete_all_for_users($users_ids)
    {
        $db = DB::delete('whitelabel_user_whitelabel_user_group')
            ->where('whitelabel_user_id', 'IN', $users_ids)
            ->execute();

        return true;
    }

    /**
     *
     * @param array $users
     * @param array $groups
     * @return bool
     */
    public static function add_users_to_groups($users, $groups)
    {
        $values = [];
        foreach ($users as $user) {
            foreach ($groups as $group) {
                $user_group = Model_Whitelabel_User_Whitelabel_User_Group::find_by(['whitelabel_user_id' => $user, 'whitelabel_user_group_id' => $group]);
                if (empty($user_group)) {
                    $row = [$user, $group];
                    array_push($values, $row);
                }
            }
        }

        try {
            if (count($values) > 0) {
                DB::insert('whitelabel_user_whitelabel_user_group')
                ->columns(['whitelabel_user_id', 'whitelabel_user_group_id'])
                ->values($values)
                ->execute();
            }
        } catch (\Throwable $e) {
            return false;
        }
        return true;
    }

    /**
     *
     * @param int $whitelabel_id
     * @return array
     */
    public static function get_all_groups_keys_for_whitelabel(int $whitelabel_id)
    {
        $group_keys = [];

        $groups = self::get_all_groups_for_whitelabel($whitelabel_id);

        foreach ($groups as $group) {
            array_push($group_keys, $group['id']);
        }

        return $group_keys;
    }

    /**
     *
     * @param string $whitelabel_id
     * @return array
     * @throws \Exception on database error.
     */
    public static function for_whitelabel($whitelabel_id) : array
    {
        $result = [];

        $query = "SELECT 
                wug.id,
                wug.prize_payout_percent
            FROM whitelabel_user_group wug
            WHERE wug.whitelabel_id = :whitelabel_id";

        $db = DB::query($query);
        
        if (!empty($whitelabel_id)) {
            $db->param(":whitelabel_id", $whitelabel_id);
        }
        
        $result = $db->execute()->as_array();

        return $result;
    }

    /**
     *
     * @param string $whitelabel_id
     * @return array
     */
    public static function payout_percents_for_whitelabel($whitelabel_id) : array
    {
        $result = [];
        $user_groups = self::for_whitelabel($whitelabel_id);

        foreach ($user_groups as $group) {
            $result[$group['id']] = $group['prize_payout_percent'];
        }

        return $result;
    }

    /**
     *
     * @param int $whitelabel_id
     * @return array
     */
    public static function get_all_selectable_by_whitelabel(int $whitelabel_id): array
    {
        $selectable_groups = [];
        
        $query = DB::select('*')
        ->from(self::$_table_name)
        ->where('is_selectable_by_user', '=', 1);
        $query->and_where('whitelabel_id', '=', $whitelabel_id);

        $selectable_groups = $query->execute()->as_array();

        return $selectable_groups;
    }

    /**
     *
     * @param int $whitelabel_id
     * @return array
     */
    public static function get_default_not_selectable_for_whitelabel(int $whitelabel_id): array
    {
        $group = [];
        
        $query = DB::select('whitelabel_user_group.*')
        ->from('whitelabel_user_group')
        ->join('whitelabel')
        ->on('whitelabel.default_whitelabel_user_group_id', '=', 'whitelabel_user_group.id')
        ->where('whitelabel_user_group.is_selectable_by_user', '=', 0)
        ->and_where('whitelabel_user_group.whitelabel_id', '=', $whitelabel_id);

        $result = $query->execute()->as_array();
        
        if (isset($result[0])) {
            $group = $result[0];
        }

        return $group;
    }
}
