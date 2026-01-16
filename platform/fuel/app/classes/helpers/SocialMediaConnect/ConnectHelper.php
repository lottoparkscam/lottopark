<?php

namespace Helpers\SocialMediaConnect;

use Fuel\Core\Session;
use Helpers\FlashMessageHelper;
use Services\SocialMediaConnect\MessageHelper;

class ConnectHelper
{
    public const SOCIAL_CONNECT_KEY = 'socialConnect';

    public static function setSecurityError(): void
    {
        FlashMessageHelper::set(FlashMessageHelper::TYPE_ERROR, MessageHelper::getTranslatedSecureError());
    }

    public static function setSuccessLoginMessage(): void
    {
        FlashMessageHelper::set(FlashMessageHelper::TYPE_SUCCESS, _(MessageHelper::getTranslatedSucceedLoggedIn()), true);
    }

    public static function markRegisterAsSocialConnection(): void
    {
        $isSocialConnection = LastStepsHelper::isLastStepsPage();
        Session::set(self::SOCIAL_CONNECT_KEY, $isSocialConnection);
    }

    public static function isSocialConnection(): bool
    {
        return Session::get(self::SOCIAL_CONNECT_KEY, false);
    }

    public static function removeSocialConnectSession(): void
    {
        /**
         * A lot of people can create account from single device and other people don`t need socialConnection which is that's why we delete social connection session.
         * When session socialConnection is not removed after first user who registered account by social registration,
         * Second user creating account without social connection creating connection not to his social.
         */
        Session::delete(ConnectHelper::SOCIAL_CONNECT_KEY);
    }
}
