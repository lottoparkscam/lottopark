<?php

namespace Services\Api\Internal;

use Container;
use Helpers\Wordpress\LanguageHelper;
use Helpers_Currency;
use Lotto_Settings;
use Models\Lottery;
use Repositories\LotteryRepository;

class SeoWidgetsService
{
    public function __construct(
        public LotteryRepository $lotteryRepository,
    ) {}

    public function isWidgetNotAvailable(string $lotterySlug): bool
    {
        /** @var Lottery $lottery */
        $lottery = $this->lotteryRepository->findOneBySlug($lotterySlug);
        return $lottery->isKeno();
    }

    /** Use only in wordpress */
    public function generateIframe(
        string $lotterySlug,
        string $widgetType,
        int $width = 400,
        int $height = 700,
    ): string {
        // Lottohoy.com domain's needs www prefix but not for the api domain
        $whitelabelDomain = Container::get('domain');
        $apiDomain = "api.$whitelabelDomain";

        LanguageHelper::configureLocale();
        $languageWithLocale = Lotto_Settings::getInstance()->get('locale_default');

        if (function_exists('lotto_platform_get_permalink_by_slug')) {
            $orderUrl = lotto_platform_get_permalink_by_slug('order');
        } else {
            $orderUrl = "https://$whitelabelDomain/order/";
        }

        $currencyCode = Helpers_Currency::getUserCurrencyTable()['code'];

        $widgetPath = 'api/internal/seoWidgets/';
        $widgetUrl = "https://$apiDomain/$widgetPath?";
        $widgetUrl .= "lotterySlug=$lotterySlug&";
        $widgetUrl .= "widgetType=$widgetType&";
        $widgetUrl .= "language=$languageWithLocale&";
        $widgetUrl .= "orderUrl=$orderUrl&";
        $widgetUrl .= "currencyCode=$currencyCode";

        return <<<HTML
<iframe src="$widgetUrl" 
width="100%" 
height="$height" 
style="border: none;max-width: {$width}px;"></iframe>
HTML;
    }
}
