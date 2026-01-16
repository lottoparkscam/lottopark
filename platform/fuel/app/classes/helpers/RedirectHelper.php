<?php

namespace Helpers;

use Container;
use Lotto_Helper;
use Throwable;
use Fuel\Core\Response;
use Services\Logs\FileLoggerService;

final class RedirectHelper
{
    public const HOMEPAGE_SLUG = '/';

    /** This function has auto translations for flash messages, we don't need to provide them in _() */
    public static function redirect(
        string $slug,
        string $flashMessageType = '',
        string $flashMessage = '',
        bool $isGlobalFlash = false,
        string $slackErrorMessage = ''
    ): void {
        if (!empty($slackErrorMessage)) {
            self::saveErrorLog($slackErrorMessage);
        }

        $hasFlashMessage = !empty($flashMessageType) && !empty($flashMessage);
        if ($hasFlashMessage) {
            FlashMessageHelper::set(
                $flashMessageType,
                $flashMessage,
                $isGlobalFlash
            );
        }

        try {
            Response::redirect(lotto_platform_home_url($slug));
        } catch (Throwable $e) {
            self::saveErrorLog("Cannot find site with provided slug: $slug. Probably page doesn't exist.");
        }
    }

    public static function redirectIf(
        bool $condition,
        string $slug,
        string $flashMessageType = '',
        string $flashMessage = '',
        bool $isGlobalFlash = false,
        string $slackErrorMessage = ''
    ): void {
        if ($condition) {
            self::redirect(
                $slug,
                $flashMessageType,
                $flashMessage,
                $isGlobalFlash,
                $slackErrorMessage
            );
        }
    }

    private static function saveErrorLog(string $message): void
    {
        /** @var FileLoggerService $fileLoggerService */
        $fileLoggerService = Container::get(FileLoggerService::class);
        $fileLoggerService->error($message);
    }
}
