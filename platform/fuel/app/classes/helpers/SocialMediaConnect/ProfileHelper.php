<?php

namespace Helpers\SocialMediaConnect;

use Fuel\Core\Session;
use Hybridauth\User\Profile;

class ProfileHelper
{
    public const PROFILE_KEY = 'socialProfile';

    public static function setSocialProfileToSession(Profile $userProfile): void
    {
        Session::set(self::PROFILE_KEY, $userProfile);
    }

    public static function getSocialProfileFromSession(): ?Profile
    {
       return Session::get(self::PROFILE_KEY);
    }
}