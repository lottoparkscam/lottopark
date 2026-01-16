<?php

/**
 * Check if specific Whitelabel's L-TECH account is blocked
 * @author Michal Kowalczyk <michal.kowalczyk at gg.international>
 */
trait Traits_Checks_Block_Ltech
{
    /**
     * @param array $whitelabel
     * @return bool
     */
    private function is_ltech_blocked(array $whitelabel): bool
    {
        $ltech = Model_Whitelabel_Ltech::find_by_whitelabel_id($whitelabel['id']);
        $ltech_helper = new Helpers_Ltech($ltech ? $ltech[0]['id'] : null);

        return $ltech_helper->check_lock_ltech();
    }

    /**
     * @param array $lottery
     * @return bool
     */
    private function is_ltech_blocked_for_this_lottery(array $lottery): bool
    {
        if ((int)$lottery['ltech_lock'] === 1) {
            return true;
        }

        return false;
    }
}
