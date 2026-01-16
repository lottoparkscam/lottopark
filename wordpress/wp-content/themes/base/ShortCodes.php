<?php

/** Usage in wp-admin page edit: [tagname] */

use Helpers\UrlHelper;
use Services\Api\Internal\SeoWidgetsService;
use Services\Logs\FileLoggerService;
use Repositories\LotteryDrawRepository;
use Models\Whitelabel;

if (!defined('WPINC')) {
    die;
}

function getWhitelabelProperty(string $propertyName): string
{
    /** @var Whitelabel $whitelabel */
    $whitelabel = Container::get('whitelabel');

    if (empty($whitelabel->$propertyName)) {
        $fileLoggerService = Container::get(FileLoggerService::class);
        $fileLoggerService->error(
            "Cannot find property $propertyName for provided whitelabel $whitelabel->name"
        );
        return '';
    }

    return $whitelabel->$propertyName;
}

// Place short codes init here
// IMPORTANT: Existing short codes should not be removed.
// If you really need to delete something from here you should check all wls/texts and replace short code invocations from content
add_shortcode('winningNumbersMock', 'winningNumbersMock');
add_shortcode('winningNumbersForLottery', 'winningNumbersForLottery');
add_shortcode('whitelabelLicence', 'whitelabelLicence');
add_shortcode('whitelabelDomain', 'whitelabelDomain');
add_shortcode('whitelabelCasinoDomain', 'whitelabelCasinoDomain');
add_shortcode('whitelabelName', 'whitelabelName');
add_shortcode('whitelabelCompany', 'whitelabelCompany');
add_shortcode('whitelabelCompanyName', function (): string {
    return whitelabelCompanyField('name');
});
add_shortcode('whitelabelCompanyAddress', function (): string {
    return whitelabelCompanyField('address');
});
add_shortcode('whitelabelSupportEmail', 'whitelabelSupportEmail');
add_shortcode('whitelabelPaymentEmail', 'whitelabelPaymentEmail');
add_shortcode('whitelabelRegisterPageUrl', 'whitelabelRegisterPageUrl');
add_shortcode('seoWidget', 'seoWidget');
add_shortcode('lotteryJackpot', 'lotteryJackpot');

function lotteryJackpot($attributes): string
{
    $attributes = shortcode_atts([
        'slug' => '',
    ], $attributes);

    if (empty($attributes['slug'])) {
        return 'No lottery slug provided.';
    }

    $lottery = lotto_platform_get_lottery_by_slug($attributes['slug']);
    $currencies = Helpers_Currency::getCurrencies();

    if (!$lottery) {
        return 'Lottery not found.';
    }

    if (empty($lottery['current_jackpot'])) {
        return 'Jackpot is not set.';
    }

    return Lotto_View::format_currency(
        $lottery['current_jackpot'] * 1000000,
        $currencies[$lottery['currency_id']]['code']
    );
}
add_shortcode('lotteryJackpot', 'lotteryJackpot');


function whitelabelRegisterPageUrl(): string
{
    return UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('signup'));
}

function winningNumbersMock($attributes): string
{
    //Setting default attributes
    $attributes = shortcode_atts(array(
        'numbers_count' => 5,
        'bonus_numbers_count' => 2,
    ), $attributes);

    //Daily changed seed
    $seed = floor(time() / 86400);
    srand($seed);

    $alreadyDrawnNumbers = [];
    $html = '<div class="ticket-line">';
    for ($i = 0; $i < $attributes['numbers_count']; $i++) {
        $number = rand(0, 99);
        if (in_array($number, $alreadyDrawnNumbers)) {
            $i--;
        } else {
            $html .= '<div class="ticket-line-number">' . $number . '</div>';
            $alreadyDrawnNumbers[] = $number;
        }
    }
    for ($i = 0; $i < $attributes['bonus_numbers_count']; $i++) {
        $number = rand(0, 99);
        if (in_array($number, $alreadyDrawnNumbers)) {
            $i--;
        } else {
            $html .= '<div class="ticket-line-bnumber">' . $number . '</div>';
            $alreadyDrawnNumbers[] = $number;
        }
    }
    $html .= "</div>";

    return $html;
}

function winningNumbersForLottery($attributes): string
{
    //Setting default attributes
    $attributes = shortcode_atts(array(
        'lottery-slug' => 'gg-world'
    ), $attributes);
    $lottery = lotto_platform_get_lottery_by_slug($attributes['lottery-slug']);
    //If wrong slug provided return empty string
    if (!$lottery) {
        return "";
    }

    try {
        /** @var LotteryDrawRepository $lotteryDrawRepository */
        $lotteryDrawRepository = Container::get(LotteryDrawRepository::class);
        $lotteryDrawNumbers = $lotteryDrawRepository->getLastWinningNumbersAndBnumbersForLottery($lottery['id']);
    } catch (Exception $e) {
        $fileLoggerService = Container::get(FileLoggerService::class);
        $fileLoggerService->error(
            "Couldn't get winning numbers for lottery_id: {$lottery['id']}. Error message: {$e->getMessage()}"
        );
        return '';
    }

    $numbersHtml = '<div class="ticket-line">';

    if (!empty($lotteryDrawNumbers->numbers)) {
        $numbers = explode(',', $lotteryDrawNumbers->numbers);
        foreach ($numbers as $number) {
            $numbersHtml .= '<div class="ticket-line-number">' . $number . '</div>';
        }
    }

    if (!empty($lotteryDrawNumbers->bnumbers)) {
        $bNumbers = explode(',', $lotteryDrawNumbers->bnumbers);
        foreach ($bNumbers as $number) {
            $numbersHtml .= '<div class="ticket-line-bnumber">' . $number . '</div>';
        }
    }

    $numbersHtml .= "</div>";
    //Get translated strings
    $playNowString = _("Play now");
    $resultsString = _("Results");

    $lotteryBallSrc = Lotto_View::get_lottery_image($lottery['id']);
    $lotteryPlayUrl = UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('play/' . $lottery['slug']));
    return <<<HTML
    <div class="small-widget small-widget-draw winning-numbers-for-lottery">
            <div class="small-widget-draw-image">
                <img src="{$lotteryBallSrc}" alt="{$lottery['name']}">
            </div>
            <h2 class="small-widget-draw-title">
                {$lottery['name']} {$resultsString}
            </h2>
                {$numbersHtml}
                <div class="widget-small-draw-button-container">
                <a href="{$lotteryPlayUrl}" 
                class="btn btn-primary widget-small-lottery-button play-button" 
                data-lottery-slug="{$lottery['slug']}">
                    {$playNowString}
                </a>
            </div>
    </div>
HTML;
}

function whitelabelLicence(): string
{
    return getWhitelabelProperty('licence');
}

function whitelabelDomain(): string
{
    return getWhitelabelProperty('domain');
}

function whitelabelName(): string
{
    return getWhitelabelProperty('name');
}

function whitelabelCompany(): string
{
    return getWhitelabelProperty('company_details');
}

/** @param string $field - address or name */
function whitelabelCompanyField(string $field): string
{
    $column = 0;
    switch ($field) {
        case 'address':
            $column = 1;
            break;
        case 'name':
            $column = 0;
            break;
        default:
            return '';
    }
    $companyNameAndAddress = getWhitelabelProperty('company_details');

    $companyDetails = explode("\n", $companyNameAndAddress);
    $companyName = $companyDetails[$column] ?? '';

    $shouldReturnBoth = empty($companyName);
    if ($shouldReturnBoth) {
        return $companyNameAndAddress;
    }

    return trim($companyName);
}

function whitelabelCasinoDomain(): string
{
    $domain = str_replace('www.', '', whitelabelDomain());
    $casinoPrefix = UrlHelper::getCasinoPrefixForWhitelabel($domain);
    return $casinoPrefix . '.' . $domain;
}

function whitelabelSupportEmail(): string
{
    $supportEmail = getWhitelabelProperty('support_email');
    if (!empty($supportEmail)) {
        return $supportEmail;
    }

    return 'support@' . str_replace('www.', '', whitelabelDomain());
}

function whitelabelPaymentEmail(): string
{
    $paymentEmail = getWhitelabelProperty('payment_email');
    if (!empty($paymentEmail)) {
        return $paymentEmail;
    }

    return 'payments@' . str_replace('www.', '', whitelabelDomain());
}

/**
 * We want to have actual data even if page is in page cache
 * To achieve it we use iframe that will make HTTP call to get widget
 */
function seoWidget(string|array $attributes): string
{
    $lotterySlugWordpressAttributeName = 'lottery_slug';
    $widgetTypeWordpressAttributeName = 'widget_type';

    $thereAreNoNeededAttributes = !is_array($attributes) ||
        !key_exists($lotterySlugWordpressAttributeName, $attributes)
        || !key_exists($widgetTypeWordpressAttributeName, $attributes);
    if ($thereAreNoNeededAttributes) {
        return '';
    }

    [
        $lotterySlugWordpressAttributeName => $lotterySlug,
        $widgetTypeWordpressAttributeName => $widgetType,
    ] = $attributes;

    $iframeWidth = $attributes['width'] ?? null;
    $iframeHeight = $attributes['height'] ?? null;

    $seoWidgetsService = Container::get(SeoWidgetsService::class);
    $isSizeDefined = !empty($iframeWidth) && !empty($iframeHeight);
    if ($isSizeDefined) {
        $iframeHtml = $seoWidgetsService->generateIframe(
            $lotterySlug,
            $widgetType,
            $iframeWidth,
            $iframeHeight,
        );
    } else {
        $iframeHtml = $seoWidgetsService->generateIframe(
            $lotterySlug,
            $widgetType,
        );
    }

    return $iframeHtml;
}
