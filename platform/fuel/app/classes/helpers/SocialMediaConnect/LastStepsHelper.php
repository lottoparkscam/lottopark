<?php

namespace Helpers\SocialMediaConnect;

use Container;
use Exceptions\SocialMedia\IncorrectTypeException;
use Fuel\Core\Input;
use Helpers\UrlHelper;
use Hybridauth\Exception\InvalidAuthorizationStateException;
use Models\SocialType;
use Throwable;

class LastStepsHelper
{
    public const SOCIAL_NAME_PARAMETER = 'socialName';

    /**
     * @throws IncorrectTypeException
     */
    public static function getSocialType(): string
    {
        $socialAdapter = strtolower(Input::get(self::SOCIAL_NAME_PARAMETER, ''));
        switch ($socialAdapter) {
            case SocialType::FACEBOOK_TYPE:
                return $socialAdapter;
            case SocialType::GOOGLE_TYPE:
                return $socialAdapter;
            default:
                throw new IncorrectTypeException();
        }
    }

    public static function isLastStepsPage(): bool
    {
        return str_contains(Input::server('REQUEST_URI'), 'auth/signup/last-steps');
    }

    public static function generateLastStepsUrlPerSocial(string $socialType): string
    {
        $whitelabelDomain = Container::get('domain');
        return UrlHelper::changeAbsoluteUrlToCasinoUrl('https://' . $whitelabelDomain . '/auth/signup/last-steps/?socialName=' . $socialType);
    }

    /** Use only in wordpress */
    public static function redirectToLastSteps(string $socialType, bool $withExit = true): void
    {
        header('Location: ' . lotto_platform_get_permalink_by_slug('last-steps') . '?socialName=' . strtolower($socialType));
        if ($withExit) {
            exit;
        }
    }

    public static function isSocialAccessTokenExpired(Throwable $exception): bool
    {
        return str_contains($exception->getMessage(), 'Error validating access token: The user has not authorized application')
            || $exception::class === InvalidAuthorizationStateException::class;
    }

    public static function isSocialAccessTokenNotExpired(Throwable $exception): bool
    {
        return !self::isSocialAccessTokenExpired($exception);
    }
}
