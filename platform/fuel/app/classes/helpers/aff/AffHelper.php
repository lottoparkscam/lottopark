<?php

namespace Helpers\Aff;

use Container;
use Helpers\UserHelper;
use Helpers_Aff_Refreader;
use Model_Whitelabel_Aff;
use Model_Whitelabel_User_Aff;

final class AffHelper
{
    /**
     * @deprecated
     * This method was created to allow to create PageCache
     */
    public static function getAff(): Model_Whitelabel_Aff|array|null
    {
        $user = UserHelper::getUser();
        $isUser = !empty($user);

        if ($isUser) {
            return Model_Whitelabel_User_Aff::get_data_for_aff_links($user->to_array());
        }

        $whitelabel = Container::get('whitelabel');

        /** @var $refReader Helpers_Aff_Refreader */
        $refReader = Container::get(Helpers_Aff_Refreader::class);
        if ($refReader->isRefValid()) {
            return Model_Whitelabel_Aff::fetch_aff($whitelabel['id'], $refReader->get_ref_token());
        }

        return null;
    }
}
