<?php

namespace Fuel\Tasks;

use Model_Admin_User;
use Model_Admin_Modules;
use Model_Whitelabel;
use Fuel\Core\DB;
use Task_Cli;

final class Module_User_Groups extends Task_Cli
{
    public function __construct()
    {
        $this->disableOnProduction();
    }

    public function run(): void
    {
        $id_view = DB::insert('modules')->set(['name' => 'user-groups-view'])->execute();
        $id_edit = DB::insert('modules')->set(['name' => 'user-groups-edit'])->execute();
        $id_delete = DB::insert('modules')->set(['name' => 'user-groups-delete'])->execute();

        $admins = DB::select('id')->from('admin_user')->where('role_id', 1)->or_where('role_id', 3)->execute();
        $whitelabels = Model_Whitelabel::get_all_as_short_list();

        foreach ($admins as $admin) {
            foreach ($whitelabels as $wl) {
                $admin_whitelabel = Model_Admin_Modules::check_admin_whitelabel($wl['id'], $admin['id']);
                if ($admin_whitelabel) {
                    Model_Admin_Modules::add_module_access_to_user($wl['id'], $id_view[0], $admin['id']);
                    Model_Admin_Modules::add_module_access_to_user($wl['id'], $id_edit[0], $admin['id']);
                    Model_Admin_Modules::add_module_access_to_user($wl['id'], $id_delete[0], $admin['id']);
                }
            }
        }
    }
}
