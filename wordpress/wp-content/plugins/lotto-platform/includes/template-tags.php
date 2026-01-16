<?php

use Helpers\FlashMessageHelper;
use Helpers\UrlHelper;
use Helpers\Wordpress\LanguageHelper;

if (!defined('WPINC')) {
    die;
}

/**
 *
 * @return array
 */
function lotto_platform_get_js_currency_format()
{
    $language = LanguageHelper::getCurrentWhitelabelLanguage();
    return $language['js_currency_format'];
}

/**
 *
 * @param array $lottery
 * @param null  $ticket_multiplier
 *
 * @return string
 */
function lotto_platform_get_pricing($lottery, $ticket_multiplier = 1)
{
    return Helpers_Lottery::getPricing($lottery, $ticket_multiplier);
}

function lotto_platform_get_lotteries(): array
{
    return IS_CASINO ? [] : Helpers_Lottery::getLotteries();
}

/**
 * @return array|bool
 */
function lotto_platform_get_lottery_by_slug(string $slug)
{
    return lotto_platform_get_lotteries()['__by_slug'][$slug] ?? false;
}

function lotto_platform_get_raffles(): array
{
    return Database_Service_Lottery::get_lotteries(function (array $whitelabel): array {
        return Model_Raffle::for_whitelabel_wl_enabled_with_currency($whitelabel['id']);
    });
}

function lotto_platform_get_raffle_by_slug(string $slug): array
{
    return lotto_platform_get_raffles()['__by_slug'][$slug] ?? [];
}

function lotto_platform_get_raffle_taken_numbers(string $slug): array
{
    $ticket = Model_Whitelabel_Raffle_Ticket::forge();
    return $ticket->get_taken_numbers_from_LCS($slug);
}

/**
 *
 * @return array|null
 */
function lotto_platform_user(): ?array
{
    return Lotto_Settings::getInstance()->get('user');
}

/**
 *
 * @return boolean
 */
function lotto_platform_is_user()
{
    return Lotto_Settings::getInstance()->get('is_user');
}

/**
 *
 * @return int|null
 */
function lotto_platform_user_id(): ?int
{
    return lotto_platform_is_user() ? lotto_platform_user()["id"] : null;
}

/**
 *
 * @return string Currency as code ex. USD
 */
function lotto_platform_user_currency()
{
    $currency = Helpers_Currency::getUserCurrencyTable();
    return $currency['code'];
}


function lotto_platform_messages(bool $front = false, bool $optional = false): string
{
    return FlashMessageHelper::getAll($front, $optional);
}

/**
 *
 */
$lotto_cache = array();

/**
 *
 * @param string $slug
 * @param string $type
 * @return boolean|array|int
 * @throws \DI\DependencyException
 * @throws \DI\NotFoundException
 * @global type $sitepress
 * @global array $lotto_cache
 */
function lotto_platform_get_post_id_by_slug($slug, $type = 'page', $languageCode = null)
{
    /** @var OptimizeQueryService $optimizeQueryService */
    $optimizeQueryService = Container::get(OptimizeQueryService::class);
    return $optimizeQueryService->getPostIdBySlug($slug, $type, $languageCode);
}

/**
 *
 */
$lotto_mcache = array();

/**
 * @param string $slug
 * @param string $type
 * @param bool &$siteExists
 * @return array|string|bool
 * @throws \DI\DependencyException
 * @throws \DI\NotFoundException
 * @global array $lotto_mcache
 */
function lotto_platform_get_permalink_by_slug($slug, $type = 'page', &$siteExists = true)
{
    /** @var OptimizeQueryService $optimizeQueryService */
    $optimizeQueryService = Container::get(OptimizeQueryService::class);
    $permalink = $optimizeQueryService->getPermalinkBySlug($slug, $type);

    if (is_null($permalink)) {
        $siteExists = false;

        return lotto_platform_home_url('/');
    }

    $siteExists = true;

    return UrlHelper::changeAbsoluteUrlToCasinoUrl(strtolower($permalink));
}

/**
 *
 * @return Fuel\Core\Request
 */
function lotto_platform_login_box()
{
    return Request::forge('wordpress/login')->execute();
}

/**
 *
 * @return Fuel\Core\Request
 */
function lotto_platform_myaccount_remove()
{
    return Request::forge('wordpress/myaccount_remove')->execute();
}

/**
 *
 * @return Fuel\Core\Request
 */
function lotto_platform_order_box()
{
    return Request::forge('wordpress/order')->execute();
}

/**
 *
 * @return Fuel\Core\Request
 */
function lotto_platform_register_box()
{
    return Request::forge('wordpress/register')->execute();
}

/**
 *
 * @return Fuel\Core\Request
 */
function lotto_platform_lostpassword_box()
{
    return Request::forge('wordpress/lostpassword')->execute();
}

/**
 *
 * @return Fuel\Core\Request
 */
function lotto_platform_myaccount_box()
{
    return Request::forge('wordpress/myaccount')->execute();
}

/**
 *
 * @return Fuel\Core\Request
 */
function lotto_platform_myaccount_nav()
{
    return Request::forge('wordpress/myaccount_nav')->execute();
}

/**
 *
 * @return Fuel\Core\Request
 */
function lotto_platform_payment_success_box()
{
    return Request::forge('wordpress/payment_success')->execute();
}

/**
 *
 * @return Fuel\Core\Request
 */
function lotto_platform_payment_failure()
{
    return Request::forge('wordpress/payment_failure')->execute();
}

/**
 *
 * @param mixed $content
 * @return mixed
 */
function lotto_platform_filter_news_headers($content)
{
    return str_ireplace(
        array('<h1', '<h2', '<h3', '<h4', '<h5', '<h6', '/h1>', '/h2>', '/h3>', '/h4>', '/h5>', '/h6>'),
        array('<strong', '<strong', '<strong', '<strong', '<strong', '<strong', '/strong>', '/strong>', '/strong>', '/strong>', '/strong>', '/strong>'),
        $content
    );
}

/**
 * It's the wrapper of home_url function that adds casino. suffix if it's necessary
 *
 * Retrieves the URL for the current site where the front end is accessible.
 *
 * Returns the 'home' option with the appropriate protocol. The protocol will be 'https'
 * if is_ssl() evaluates to true; otherwise, it will be the same as the 'home' option.
 * If `$scheme` is 'http' or 'https', is_ssl() is overridden.
 *
 * @since 3.0.0
 *
 * @param string      $path   Optional. Path relative to the home URL. Default empty.
 * @param string|null $scheme Optional. Scheme to give the home URL context. Accepts
 *                            'http', 'https', 'relative', 'rest', or null. Default null.
 * @return string Home URL link with optional path appended.
 */
function lotto_platform_home_url($path = '', $scheme = null)
{
    return UrlHelper::changeAbsoluteUrlToCasinoUrl(get_home_url(null, $path, $scheme));
}

function lotto_platform_home_url_without_language($path = ''): string
{
    return UrlHelper::getHomeUrlWithoutLanguage($path);
}

/** If cannot find returns auto en */
function getLanguage(): string
{
    return defined('ICL_LANGUAGE_CODE') && !empty(ICL_LANGUAGE_CODE) ? ICL_LANGUAGE_CODE : 'en';
}