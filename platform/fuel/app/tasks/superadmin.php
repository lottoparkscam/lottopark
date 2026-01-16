<?php

namespace Fuel\Tasks;

use Model_Admin_User;
use Model_Admin_Modules;
use Model_Whitelabel;
use Model_Whitelabel_User;
use Fuel\Core\DB;

final class Superadmin
{
    public function run(): void
    {
        $user = [];

        $user['username'] = 'Superadmin';
        $user['name'] = 'Super';
        $user['surname'] = 'Admin';
        $user['email'] = 'superadmin@test.com';
        $user['password'] = 'superadminpassword';
        $user['timezone'] = 'UTC';
        $user['language_id'] = '1';
        $user['role_id'] = '1';

        $id = \Model_Admin_User::add_user($user);

        \Model_Whitelabel_User::default_admin_user_visible_columns_crm($id);

        $whitelabels = \Model_Whitelabel::get_all_as_short_list();
        $modules = \Model_Admin_Modules::all_modules();

        foreach ($modules as $module) {
            if (strpos($module['module_name'], 'admins') !== false) {
                \Model_Admin_Modules::add_module_access_to_user(0, $module['module_id'], $id);
            }
        }
        foreach ($whitelabels as $whitelabel) {
            \Model_Admin_Modules::add_whitelabel_admin($whitelabel['id'], $id);
            foreach ($modules as $module) {
                if (strpos($module['module_name'], 'admins') === false) {
                    \Model_Admin_Modules::add_module_access_to_user($whitelabel['id'], $module['module_id'], $id);
                }
            }
        }
    }
}
