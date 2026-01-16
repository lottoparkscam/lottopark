<?php
class Model_Admin_Modules extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'module';

    /**
     *
     * @access public
     * @return array
     */
    public static function all_modules()
    {
        $res = [];

        $db = DB::select(
            ['module.id', 'module_id'],
            ['module.name', 'module_name']
        )->from('module');
        $res = $db->execute();

        return $res;
    }

    /**
     *
     * @access public
     * @return array
     * @param int $user_id
     */
    public static function accessible_modules($user_id)
    {
        $res = [];

        $db = DB::select(
            ['module.name', 'module_name'],
            ['module.id', 'module_id'],
            ['whitelabel.id', 'whitelabel_id'],
            ['whitelabel.name', 'whitelabel_name']
        )
            ->from('admin_whitelabel_module')->where('admin_user_id', '=', $user_id)
            ->join('module')->on('admin_whitelabel_module.module_id', '=', 'module.id')
            ->join('whitelabel', 'LEFT')->on('admin_whitelabel_module.whitelabel_id', '=', 'whitelabel.id');
        $res = $db->execute();

        return $res;
    }

    /**
     *
     * @access public
     * @return bool
     * @param string $module_name
     * @param int $whitelabel_id
     * @param int $user_id
     */
    public static function check_user_module($module_name, $whitelabel_id, $user_id)
    {
        $res = true;

        $db = DB::select('admin_whitelabel_module.*')
            ->from('admin_whitelabel_module')
            ->where('admin_whitelabel_module.admin_user_id', '=', $user_id)
            ->and_where('admin_whitelabel_module.whitelabel_id', '=', $whitelabel_id)
            ->and_where('module.name', '=', $module_name)
            ->join('module')->on('admin_whitelabel_module.module_id', '=', 'module.id')
            ->join('whitelabel', 'LEFT')->on('admin_whitelabel_module.whitelabel_id', '=', 'whitelabel.id');
      
        $result = $db->execute();
        if (!isset($result[0])) {
            $res = false;
        }

        return $res;
    }

    /**
     *
     * @access public
     * @param int $whitelabel_id
     * @param int $user_id
     * @return bool
     */
    public static function check_admin_whitelabel($whitelabel_id, $user_id)
    {
        $res = true;

        $result = DB::select('*')->from('admin_whitelabel')
        ->where('admin_user_id', '=', $user_id)
        ->and_where('whitelabel_id', '=', $whitelabel_id)
        ->execute();
        if (!isset($result[0])) {
            $res = false;
        }

        return $res;
    }
    
    /**
     *
     * @access public
     * @return array
     * @param int $user_id
     */
    public static function accessible_whitelabels($user_id)
    {
        $res = [];

        $db = DB::select(
            'whitelabel.id',
            'whitelabel.name',
            'whitelabel.type',
            'whitelabel.domain',
            'whitelabel.use_logins_for_users',
            'whitelabel.prefix',
            ['currency.code', 'currency_code'])
            ->from('admin_whitelabel')->where('admin_user_id', '=', $user_id)
            ->join('whitelabel')->on('admin_whitelabel.whitelabel_id', '=', 'whitelabel.id')
            ->join('currency', 'LEFT')->on('whitelabel.manager_site_currency_id', '=', 'currency.id');
        $res = $db->execute();

        return $res;
    }
    
    /**
     *
     * @access public
     * @return bool
     * @param int $whitelabel_id
     * @param int $user_id
     */
    public static function add_whitelabel_admin($whitelabel_id, $user_id)
    {
        $query = DB::insert('admin_whitelabel')
            ->set([
                'admin_user_id' => $user_id,
                'whitelabel_id' => $whitelabel_id
            ])->execute();

        return true;
    }

    /**
     *
     * @access public
     * @return bool
     * @param int $whitelabel_id
     * @param int $module_id
     * @param int $user_id
     */
    public static function add_module_access_to_user($whitelabel_id, $module_id, $user_id)
    {
        $whitelabel = null;
        if ($whitelabel_id != 0) {
            $whitelabel = $whitelabel_id;
        }

        $query = DB::insert('admin_whitelabel_module')
        ->set([
            'admin_user_id' => $user_id,
            'module_id' => $module_id,
            'whitelabel_id' => $whitelabel
        ])->execute();

        return true;
    }

    /**
     *
     * @access public
     * @return bool
     * @param int $whitelabel_id
     * @param int $module_id
     * @param int $user_id
     */
    public static function delete_module_user_access($whitelabel_id, $module_id, $user_id)
    {
        $res = [];
        $whitelabel = null;
        if ($whitelabel_id != 0) {
            $whitelabel = $whitelabel_id;
        }

        $query = DB::delete('admin_whitelabel_module')
            ->where('admin_user_id', '=', $user_id)
            ->and_where('module_id', '=', $module_id)
            ->and_where('whitelabel_id', '=', $whitelabel)
            ->execute();
       
        return true;
    }

    /**
     *
     * @access public
     * @return bool
     * @param int $whitelabel_id
     * @param int $user_id
     */
    public static function delete_admin_whitelabel($whitelabel_id, $user_id)
    {
        $query = DB::delete('admin_whitelabel')
                ->where('admin_user_id', '=', $user_id)
                ->and_where('whitelabel_id', '=', $whitelabel_id)
                ->execute();

        return true;
    }

    /**
     *
     * @access public
     * @return bool
     * @param int $user_id
     */
    public static function delete_admin_accesses($user_id)
    {
        DB::delete('admin_whitelabel_module')
                ->where('admin_user_id', '=', $user_id)
                ->execute();
        DB::delete('admin_whitelabel')
                ->where('admin_user_id', '=', $user_id)
                ->execute();

        return true;
    }
}
