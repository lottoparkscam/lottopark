<?php

use Helpers\FlashMessageHelper;

class WordpressNoticeHelper
{
    public static function showWarningNotice(string $message): void
    {
        echo self::createNotice(FlashMessageHelper::TYPE_WARNING, $message);
    }

    private static function createNotice(string $noticeType, string $message): string
    {
        $warningNoticeClass = 'notice notice-' . $noticeType;
        return '<div class="' . $warningNoticeClass . '"><p> ' . $message . '</p></div>';
    }
}
