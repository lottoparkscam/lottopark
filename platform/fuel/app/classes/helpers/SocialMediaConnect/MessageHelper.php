<?php

namespace Services\SocialMediaConnect;

class MessageHelper
{
    public static function getTranslatedSecureError(): string
    {
        return _('Social security error! Please try again.');
    }

    public static function getTranslatedAuthenticationError(): string
    {
        return _('Authentication error. Please try again.');
    }

    public static function getTranslatedSucceedConfirmedMail(): string
    {
        return _('Your social media login is confirmed. You can log in faster now.');
    }

    public static function getTranslatedSucceedLoggedIn(): string
    {
        return _('You have been successfully logged in!');
    }

    public static function getTranslatedSocialAccountIsConnectingWithOtherEmail(): string
    {
        return _('Your account has been connected with another social email. Try removing the email in the social application to login.');
    }

    public static function getTranslatedAccountHasBeenActivatedBefore(): string
    {
        return _('Your account has been activated before. Please login to access your account.');
    }

    public static function getTranslatedAccountHasBeenActivatedBeforeLoggedInUser(): string
    {
        return _('Your account has been activated before.');
    }

    public static function getTranslatedActivationSecureError(): string
    {
        return _('Social activation security error! Please try again.');
    }

    public static function getTranslatedActivationIncorrectLink(): string
    {
        return _('Incorrect social activation link. Please contact us for manual activation.');
    }

    public static function getTranslatedActivationConfirmEmail(): string
    {
        return _('Please confirm your email to login with social media.');
    }

    public static function getTranslatedActivationConfirmEmailBeforeDayPassed(): string
    {
        return _('Check your email and confirm the connection of our app with your social media.
         You can resend the social media activation link once every 24 hours.
          Please try again later or contact us.');
    }
}
