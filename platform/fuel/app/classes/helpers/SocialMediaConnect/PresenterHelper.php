<?php

namespace Helpers\SocialMediaConnect;

use Models\SocialType;

class PresenterHelper
{
    public static function generateFacebookConnectButton(string $url): string
    {
        return self::generateButton(SocialType::FACEBOOK_TYPE, $url, 'first-row');
    }

    public static function generateGoogleConnectButton(string $url): string
    {
        return self::generateButton(SocialType::GOOGLE_TYPE, $url, 'first-row');
    }

    public static function generateSeparator(): string
    {
        return '<div class="separator-container">
                    <div class="login-separator">OR</div>
                </div>';
    }

    private static function generateButton(string $socialType, string $url, string $customClass = ''): string
    {
         return '<a class="social-button ' . $customClass . ' ' . $socialType . '" href="' . $url . '" rel="nofollow">
                    <i class="fa-brands fa-' . $socialType . ' social-icon"></i>
                    <span class="login-button ">' . _('Connect with') . ' ' . $socialType . '</span>
                </a>';
    }
}
