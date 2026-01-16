<?php

/**
 * Helper for resources (strings etc).
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2019-05-22
 * Time: 17:25:41
 */
final class Helpers_Fuel_Resources
{

    /**
     * Get LOCALIZED genders mappings for {@see Model_Whitelabel_User::get_gender_keys()}.
     *
     * @return string[]
     */
    public static function get_genders(): array
    {
        $result = [];
        $result[Model_Whitelabel_User::GENDER_UNSET] = _('Choose your gender');
        $result[Model_Whitelabel_User::GENDER_MALE] = _('male');
        $result[Model_Whitelabel_User::GENDER_FEMALE] = _('female');
        return $result;
    }
}
