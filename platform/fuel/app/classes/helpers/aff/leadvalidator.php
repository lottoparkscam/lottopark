<?php

use Repositories\Aff\WhitelabelUserAffRepository;

/**
 * This class is responsible for validation of being a lead for logged user.
 *
 * @author Marcin
 */
class Helpers_Aff_Leadvalidator
{
   
    /**
     * Check if lead for this user exist, but is outdated.
     * Note this function assume that params are not null.
     * @param array $user reference to user model.
     * @param array $aff  reference to whitelabel_aff model.
     * @return bool true if lead is outdated.
     */
    public function is_lead_outdated($user, $aff)
    {
        // check option in settings.
        switch ($aff['whitelabel_aff']['aff_lead_lifetime']) {
            default:
            case 0:
                // lead is unlimited
                return false;
            case 1: // 1 day
                $max_difference = 1;
                break;
            case 2: // 3 day
                $max_difference = 3;
                break;
            case 3: // 1 week
                $max_difference = 7;
                break;
            case 4: // 2 weeks
                $max_difference = 14;
                break;
            case 5: // 1 month
                $max_difference = 30;
                break;
            case 6: // 3 months
                $max_difference = 90;
                break;
            case 7: // 6 months
                $max_difference = 180;
                break;
            case 8: // 1 year
                $max_difference = 365;
                break;
            case 9: // 2 years
                $max_difference = 730;
                break;
            case 10: // 3 years
                $max_difference = 1095;
                break;
        }

        /** @var WhitelabelUserAffRepository $whitelabelUserAffRepository */
        $whitelabelUserAffRepository = Container::get(WhitelabelUserAffRepository::class);
        $whitelabelUserAff = $whitelabelUserAffRepository->findOneByWhitelabelUserId($user['id']);

        // check if user is a lead (is expired from aff table must be not null and not true)
        if (!empty($whitelabelUserAff) && !$whitelabelUserAff->isExpired) {
            // check if lead is outdated
            $current_date = new DateTime("now");
            $register_date = new DateTime($user['date_register']);
            // calculate interval betweeen registered and now
            $interval = $register_date->diff($current_date);
            // return true if difference (in days) between dates is larger than the one specified in settings.
            return $interval->format('%a') > $max_difference;
        }
        
        // we reached here - user is not a lead.
        return false;
    }
}
