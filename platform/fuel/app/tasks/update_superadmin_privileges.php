<?php

namespace Fuel\Tasks;

use Model_Admin_User;
use Models\AdminWhitelabel;
use Models\AdminWhitelabelModule;
use Models\Module;
use Model_Whitelabel;
use Task_Cli;

class Update_Superadmin_Privileges extends Task_Cli
{
    private const WHITELABEL_V2_MODULES = [
        'users-manual-deposit-add',
        'users-balance-edit',
        'users-bonus-balance-manual-deposit-add',
        'users-bonus-balance-edit',
        'users-manual-deposit-casino-add',
        'users-balance-casino-edit',
    ];

    private function update_modules(Model_Admin_User $admin, Model_Whitelabel $whitelabel, array $modules): void
    {
        foreach ($modules as $module) {
            $whitelabel_id = $whitelabel->id;

            $wl_superadmin = $admin->role_id === "3";
            $whitelabel_v1 = $whitelabel->type === "1";
            $module_v2_only = in_array($module->name, self::WHITELABEL_V2_MODULES);

            if ($wl_superadmin && $module_v2_only && $whitelabel_v1) {
                continue;
            }

            $modules_without_whitelabel = [
                'admins-view',
                'admins-edit',
                'admins-delete'
            ];

            if (in_array($module->name, $modules_without_whitelabel)) {
                $whitelabel_id = null;
            }

            $assignment_to_model = AdminWhitelabelModule::find('all', [
                'where' => [
                    'admin_user_id' => $admin->id,
                    'module_id' => $module->id,
                    'whitelabel_id' => $whitelabel_id
                ]
            ]);

            $assignment_to_model_not_exist = !$assignment_to_model || count($assignment_to_model) <= 0;

            if ($assignment_to_model_not_exist) {
                $new_assignment_to_model = new AdminWhitelabelModule();
                $new_assignment_to_model->set([
                    'admin_user_id' => $admin->id,
                    'module_id' => $module->id,
                    'whitelabel_id' => $whitelabel_id
                ]);
                $new_assignment_to_model->save();
            }
        }
    }

    private function update_superadmin_privileges(array $whitelabels, array $modules): void
    {
        $superadmins = Model_Admin_User::find_by('role_id', 1);

        foreach ($superadmins as $superadmin) {
            foreach ($whitelabels as $whitelabel) {
                if (!$this->check_assignment_to_whitelabel_exists($superadmin, $whitelabel)) {
                    $this->assign_admin_to_whitelabel($superadmin, $whitelabel);
                }

                $this->update_modules($superadmin, $whitelabel, $modules);
            }
        }
    }

    private function update_wl_superadmin_privileges(array $whitelabels, array $modules): void
    {
        $wl_superadmins = Model_Admin_User::find_by('role_id', 3);

        foreach ($wl_superadmins as $wl_superadmin) {
            foreach ($whitelabels as $whitelabel) {
                if ($this->check_assignment_to_whitelabel_exists($wl_superadmin, $whitelabel)) {
                    $this->update_modules($wl_superadmin, $whitelabel, $modules);
                }
            }
        }
    }

    private function assign_admin_to_whitelabel(Model_Admin_User $superadmin, Model_Whitelabel $whitelabel): void
    {
        $new_assignment_to_whitelabel = new AdminWhitelabel();
        $new_assignment_to_whitelabel->set([
            'admin_user_id' => $superadmin->id,
            'whitelabel_id' => $whitelabel->id
        ]);
        $new_assignment_to_whitelabel->save();
    }

    private function check_assignment_to_whitelabel_exists(Model_Admin_User $superadmin, Model_Whitelabel $whitelabel): bool
    {
        $assignment_to_whitelabel = AdminWhitelabel::find('all', [
            'where' => [
                'admin_user_id' => $superadmin->id,
                'whitelabel_id' => $whitelabel->id
            ]
        ]);

        $assignment_not_exist = !$assignment_to_whitelabel || count($assignment_to_whitelabel) <= 0;

        if ($assignment_not_exist) {
            return false;
        }

        return true;
    }

    public function default_super(): void
    {
        $whitelabels = Model_Whitelabel::find();
        $modules = Module::find('all');

        $this->update_superadmin_privileges($whitelabels, $modules);
    }

    public function default_wl_super(): void
    {
        $wl_superadmins = Model_Admin_User::find_by('role_id', 3);
        $whitelabels = Model_Whitelabel::find();
        $modules = Module::find('all');

        $this->update_superadmin_privileges($whitelabels, $modules);
        $this->update_wl_superadmin_privileges($whitelabels, $modules);
    }
}
