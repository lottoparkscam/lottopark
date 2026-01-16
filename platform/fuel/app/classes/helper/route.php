<?php

/*
 * @deprecated
 */
final class Helper_Route
{
    public const ORDER_CONFIRM = '/order/confirm/';
    public const ORDER_SUCCESS = '/order/success/';
    public const ORDER_FAILURE = '/order/failure/';
    public const ORDER_RESULT = '/order/result/';
    public const RESEND_ACTIVATION_EMAIL = '/gresend/';

    public const CASINO_HOMEPAGE = 'casino';
    public const CASINO_PLAY = 'casino-play';
    public const CASINO_LOBBY = 'casino-lobby';
    public const CASINO_PRIVACY_POLICY = 'casino-privacy-policy';

    public static function get_by_slug(string $slug, string $after = ''): string
    {
        $url = lotto_platform_get_permalink_by_slug($slug);

        return parse_url($url . $after, PHP_URL_PATH);
    }
}
