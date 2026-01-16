<?php

namespace Presenters\Api\Internal;

use Container;
use Helpers\LogoHelper;
use Helpers\UrlHelper;
use Helpers_Currency;
use Helpers_General;
use Lotto_Helper;
use Lotto_View;
use Models\Lottery;
use Models\LotteryType;
use Models\Whitelabel;
use Repositories\CurrencyRepository;
use Repositories\LotteryRepository;
use Repositories\WhitelabelLotteryRepository;
use Throwable;

class SeoWidgetsPresenter {

    private Whitelabel $whitelabel;
    private string $domain;

    public function __construct(
        private LotteryRepository $lotteryRepository,
        private CurrencyRepository $currencyRepository,
        private WhitelabelLotteryRepository $whitelabelLotteryRepository,
    ) {
        $this->whitelabel = Container::get('whitelabel');
        $this->domain = UrlHelper::addWwwPrefixIfNeeded(Container::get('domain'));
    }

    public function getDataByLotterySlug(
        string $lotterySlug,
        string $orderUrl = null,
        string $currencyCode = null
    ): array {
        /** @var Lottery $lottery */
        $lottery = $this->lotteryRepository->findOneBySlug($lotterySlug);

        $lotteryType = Lotto_Helper::get_next_lottery_type($lottery->to_array());

        $nextRealDraw = Lotto_Helper::get_lottery_real_next_draw($lottery->to_array());
        $nextRealDrawShort = sprintf(_("draw in %s"), $nextRealDraw->diffForHumans(null, true));

        $forcedCurrency = $this->currencyRepository->findOneById($lottery->forceCurrencyId);

        $ballImagePath = Lotto_View::get_lottery_image($lottery->id, $this->whitelabel->to_array());
        $description = sprintf(_('Pick %d numbers and %d bonus'), $lotteryType['ncount'], $lotteryType['bcount']);

        $defaultCurrencyCodeForWhitelabel = Helpers_Currency::get_mtab_currency()['code'] ?? 'USD';

        list($jackpotFormatted) = Lotto_View::get_jackpot_formatted_to_text(
            $lottery->currentJackpot,
            $lottery->currency->code,
            Helpers_General::SOURCE_WORDPRESS,
            $forcedCurrency->code ?? $currencyCode ?: $defaultCurrencyCodeForWhitelabel,
        );

        return [
            'lotteryName' => $lottery->name,
            'description' => $description,
            'normalNumbersRange' => $lotteryType['nrange'],
            'normalNumbersCount' => $lotteryType['ncount'],
            'bonusNumbersRange' => $lotteryType['brange'],
            'bonusNumbersCount' => $lotteryType['bcount'],
            'whitelabelLogoPath' => LogoHelper::getWhitelabelWidgetLogoUrl(),
            'ballImagePath' => $ballImagePath,
            'nextJackpotPrize' => $jackpotFormatted,
            'nextDrawFormatted' => $nextRealDrawShort,
            'quickPickUrl' => $this->getQuickPickUrl($lottery, $orderUrl),
        ];
    }

    private function getQuickPickUrl(Lottery $lottery, string $orderUrl = null): string
    {
        $whitelabelLottery = $this->whitelabelLotteryRepository->getOneByLotteryIdForWhitelabel(
            $lottery->id,
            $this->whitelabel->id,
        );

        if (empty($orderUrl)) {
            $orderUrl = "https://$this->domain/order/";
        }

        return "{$orderUrl}quickpick/$lottery->slug/$whitelabelLottery->minLines/";
    }

    public function getViewNameFromWidgetType(string $widgetType): string
    {
        $fileName = ucfirst($widgetType);
        return "SeoWidgets/$fileName";
    }
}
