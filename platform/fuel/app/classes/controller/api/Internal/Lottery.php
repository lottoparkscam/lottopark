<?php

use Abstracts\Controllers\Internal\AbstractPublicController;
use Carbon\Carbon;
use Carbon\Translator;
use Fuel\Core\Input;
use Fuel\Core\Response;
use Helpers\SanitizerHelper;
use Models\Lottery;
use Models\Raffle;
use Models\RaffleRuleTier;
use Repositories\LotteryRepository;
use Repositories\Orm\RaffleRepository;
use Repositories\WhitelabelLotteryRepository;
use Services\Logs\FileLoggerService;
use Services\WidgetService;

class Controller_Api_Internal_Lottery extends AbstractPublicController
{
    private LotteryRepository $lotteryRepository;
    private WidgetService $widgetService;
    private RaffleRepository $raffleRepository;
    private WhitelabelLotteryRepository $whitelabelLotteryRepository;
    private FileLoggerService $fileLoggerService;

    public function before()
    {
        parent::before();
        $this->lotteryRepository = Container::get(LotteryRepository::class);
        $this->widgetService = Container::get(WidgetService::class);
        $this->raffleRepository = Container::get(RaffleRepository::class);
        $this->whitelabelLotteryRepository = Container::get(WhitelabelLotteryRepository::class);
        $this->fileLoggerService = Container::get(FileLoggerService::class);
    }

    public function get_all(): Response
    {
        $results = [];

        $titleStartTag = strip_tags(Input::get('titleStartTag'), ['<h2>', '<div>']);
        $titleEndTag = strip_tags(Input::get('titleEndTag'), ['<h2>', '<div>']);
        $lotteryLink = SanitizerHelper::sanitizeString(Input::get('lotteryLink') ?? '');

        $lotteries = $this->lotteryRepository->findEnabledForCurrentWhitelabel();
        $lastResultsHtml = $this->widgetService->getLastResultsHtml(
            $lotteries,
            $titleStartTag,
            $titleEndTag,
            $lotteryLink
        );

        $userCurrencyCode = Helpers_Currency::getUserCurrencyTable()['code'];

        foreach ($lotteries as $lottery) {
            $quickPickLines = 3;
            if ((int)$lottery['quick_pick_lines'] > 0) {
                $quickPickLines = (int)$lottery['quick_pick_lines'] * 2;
            }

            $linePrice = (float)Helpers_Lottery::getPricing($lottery);
            $price = round($linePrice * $quickPickLines, 2);

            $priceFormatted = Lotto_View::format_currency(
                $price,
                $userCurrencyCode,
                true
            );

            $nextRealDraw = Lotto_Helper::get_lottery_real_next_draw($lottery);
            /*
             * Temporary fix for Hebrew, where on smaller screens it would always show English.
             * It overrides Carbon locale with custom translation.
             */
            if ($this->languageWithLocale === 'he_IL.utf8') {
                $translator = Translator::get($this->languageWithLocale);
                $translator->setTranslations(
                    [
                        'day' => ':count יום|:count ימים',
                        'hour' => ':count שעה|:count שעות',
                        'minute' => ':count דקה|:count דקות',
                        'second' => ':count שניה|:count שניות',
                    ]
                );
                $nextRealDraw = Lotto_Helper::get_lottery_real_next_draw($lottery)->locale($this->languageWithLocale);
            }

            $nextRealDrawTimestamp = $nextRealDraw->getTimestamp();
            $nextRealDrawFormatted = sprintf(
                _("draw in %s"),
                $nextRealDraw->diffForHumans()
            );

            $nextDrawDate =  Lotto_View::format_date(
                $lottery['next_date_local'],
                IntlDateFormatter::MEDIUM,
                IntlDateFormatter::NONE,
                $lottery['timezone'],
                false
            );

            $nextRealDrawShort = sprintf(_("draw in %s"), $nextRealDraw->diffForHumans(null, true));

            list($jackpotFormatted, $jackpotHasThousands) = Lotto_View::get_jackpot_formatted_to_text(
                $lottery['current_jackpot'],
                $lottery['currency'],
                Helpers_General::SOURCE_WORDPRESS,
                $lottery['force_currency'] ?: $userCurrencyCode
            );

            // We save current_jackpot in database in millions, so we need to multiply it
            $jackpot = Lotto_View::format_currency(
                $lottery['current_jackpot'] * 1000000,
                $lottery['currency'],
                true
            );

            $quickPickPath = 'quickpick/' . $lottery['slug'] . '/' . $quickPickLines . '/';

            $pendingText = _('Pending');

            $lotteryAdditionalData = null;
            if ($lottery['additional_data']) {
                $lotteryAdditionalData = unserialize($lottery['additional_data']);
                if ($lotteryAdditionalData === false) {
                    $lotteryAdditionalData = null;
                }
            }

            $lastNumbersFormatted = Lotto_View::format_line(
                $lottery['last_numbers'],
                $lottery['last_bnumbers'],
                null,
                null,
                null,
                $lotteryAdditionalData
            );

            $lastNumbersFormatted = strip_tags($lastNumbersFormatted, ['div', 'span']);

            $lastDrawTextFormatted = Lotto_View::format_date(
                $lottery['last_date_local'],
                IntlDateFormatter::MEDIUM,
                IntlDateFormatter::LONG,
                $lottery['timezone'],
                false,
            );

            $isKeno = $lottery['type'] === Helpers_Lottery::TYPE_KENO;

            $results[] = [
                'name' => $lottery['name'],
                'slug' => $lottery['slug'],
                'type' => $lottery['type'],
                'isKeno' => $isKeno,
                'nextRealDrawFromNow' => $nextRealDrawFormatted,
                'nextRealDrawShort' => $nextRealDrawShort,
                'nextRealDrawTimestamp' => $nextRealDrawTimestamp,
                'nextDrawDate' => $nextDrawDate,
                'nextDrawForListWidget' => $this->widgetService->getNextDrawFormattedForListWidget($lottery),
                'jackpotHasThousands' => $jackpotHasThousands ? 'true' : 'false',
                'jackpot' => $jackpot,
                'jackpotFormatted' => $jackpotFormatted,
                'quickPickPath' => $quickPickPath,
                'quickPickLinesText' => sprintf(_("%d Quick-Pick lines"), $quickPickLines),
                'quickPickLinesPriceText' => sprintf(_("only %s"), $priceFormatted),
                'isPending' => Helpers_Lottery::isPending($lottery),
                'pendingText' => $pendingText,
                'lastNumbersFormatted' => $lastNumbersFormatted,
                'lastDrawTextFormatted' => $lastDrawTextFormatted,
                'isPlayable' => (bool)$lottery['playable'],
            ];
        }

        $raffles = $this->raffleRepository->findByIsEnabled(true);

        /** @var Raffle $raffle */
        foreach ($raffles as $raffle) {
            # wee assumed that first tier is always main prize (suppose to be), then we check it it prize in kind
            $mainPrizeTier = array_filter($raffle->getFirstRule()->tiers, function (RaffleRuleTier $tier) {
                return $tier->isMainPrize;
            });
            /** @var RaffleRuleTier $mainPrizeTier */
            $mainPrizeTier = reset($mainPrizeTier);

            if (!empty($mainPrizeTier->tierPrizeInKind)) {
                $mainPrize = $mainPrizeTier->tierPrizeInKind->name .
                    ' (' .
                    Lotto_View::format_currency(
                        $mainPrizeTier->tierPrizeInKind->perUser,
                        $raffle->currency->code
                    ) . ')';
            } else {
                $mainPrize = Lotto_View::format_currency($raffle->mainPrize, $raffle->currency->code);
            }

            // raffle- prefix was made to make sure that there is not the same slug in regular and raffle lotteries
            $raffleSlug = 'raffle-' . $raffle->slug;

            $availableTicketRemain = 0;
            try {
                $availableTicketRemain = $raffle->getFirstRule()->max_lines_per_draw - $raffle->draw_lines_count;
            } catch (Throwable $exception) {
                $this->fileLoggerService->error('Unable to get the remaining number of tickets.');
            }

            $results[] = [
                'name' => $raffle->name,
                'slug' => $raffleSlug,
                'type' => 'raffle',
                'jackpotFormatted' => $mainPrize,
                'ticketRemainingCount' => $availableTicketRemain
            ];
        }

        return $this->returnResponse([
            'lotteries' => $results,
            'lastResultsHtml' => $lastResultsHtml
        ]);
    }

    public function get_index(): Response
    {
        $lotterySlug = SanitizerHelper::sanitizeSlug(Input::get('lotterySlug', ''));
        if (empty($lotterySlug)) {
            return $this->returnResponse([
                'message' => 'lotterySlug is required'
            ], 400);
        }

        /** @var ?Lottery $lottery */
        $lottery = $this->lotteryRepository->findOneBySlug($lotterySlug);
        if (empty($lottery)) {
            return $this->returnResponse([
                'message' => "Lottery with slug $lotterySlug does not exist"
            ], 400);
        }

        $whitelabel = Container::get('whitelabel');
        $whitelabelLottery = $this->whitelabelLotteryRepository->getOneByLotteryIdForWhitelabel(
            $lottery->id,
            $whitelabel->id
        );
        if (empty($whitelabelLottery)) {
            return $this->returnResponse([
                'message' => "Lottery with slug $lotterySlug does not exist"
            ], 400);
        }

        $lotteryIsDisabled = !$lottery->isEnabled || $lottery->isTemporarilyDisabled || !$whitelabelLottery->is_enabled;
        if ($lotteryIsDisabled) {
            return $this->returnResponse([
                'message' => "Lottery with slug $lotterySlug is disabled"
            ], 404);
        }

        $response = [];
        if ($lottery->isNotKeno()) {
            $drawDatesFormatted = [];
            foreach ($lottery->drawDates as $drawDate) {
                $drawDatesFormatted[] = Lotto_View::format_single_day_with_hour(
                    Carbon::createFromTimeString($drawDate, $lottery['timezone']),
                    new DateTimeZone($lottery['timezone']),
                    new DateTimeZone(Lotto_View::get_user_timezone())
                );
            }
            $response['drawDatesFormatted'] = $drawDatesFormatted;
        }

        $lotteryTypes = Model_Lottery_Type_Data::get_lottery_type_data($lottery->to_array());
        $lotteryCurrency = $lottery->currency->code;

        $estimatedJackpotsPerTiers = array_map(function ($lotteryType) use ($lotteryCurrency) {
            return Lotto_View::format_currency(
                $lotteryType['estimated'],
                $lotteryCurrency,
                true,
                null,
                1,
                true
            );
        }, $lotteryTypes);

        $nextRealDraw = Lotto_Helper::get_lottery_real_next_draw($lottery->to_array());
        $nextRealDrawTimestamp = $nextRealDraw->getTimestamp();

        $response['estimatedJackpotsPerTiers'] = $estimatedJackpotsPerTiers;
        $response['nextRealDrawTimestamp'] = $nextRealDrawTimestamp;

        $usersCurrencyCode = Helpers_Currency::getUserCurrencyTable()['code'];
        $preparedLotteryForLinePrice = $this->lotteryRepository->findEnabledForCurrentWhitelabel($lotterySlug)[0];
        $linePrice = Helpers_Lottery::getPricing($preparedLotteryForLinePrice) ?? '0.00';
        $response['linePrice'] = $linePrice;
        $response['linePriceFormatted'] = Lotto_View::format_currency(
            $linePrice,
            $usersCurrencyCode,
            true
        );

        $defaultQuickPickCount = 3;
        $firstQuickPickCount = max($defaultQuickPickCount, $whitelabelLottery->quick_pick_lines, $whitelabelLottery->min_lines);
        $secondQuickPickCount = $firstQuickPickCount * 2;

        $response['firstQuickPickCount'] = $firstQuickPickCount;
        $response['secondQuickPickCount'] = $secondQuickPickCount;
        $response['firstQuickPickPrice'] = Lotto_View::format_currency(
            $linePrice * $firstQuickPickCount,
            $usersCurrencyCode,
            true
        );
        $response['secondQuickPickPrice'] = Lotto_View::format_currency(
            $linePrice * $secondQuickPickCount,
            $usersCurrencyCode,
            true
        );
        $response['firstQuickPickPath'] = "quickpick/$lotterySlug/$firstQuickPickCount/";
        $response['secondQuickPickPath'] = "quickpick/$lotterySlug/$secondQuickPickCount/";
        $response['firstQuickPickDescription'] = sprintf(_('%d Quick-Pick lines'), $firstQuickPickCount);
        $response['secondQuickPickDescription'] = sprintf(_('%d Quick-Pick lines'), $secondQuickPickCount);

        $firstMultiplier = $preparedLotteryForLinePrice['multiplier'] ?? 0;
        $secondMultiplier = $firstMultiplier * 2;

        $response['firstMultiplier'] = $firstMultiplier;
        $response['secondMultiplier'] = $secondMultiplier;

        return $this->returnResponse($response);
    }

    public function get_isBuyingDisabled(): Response
    {
        $lotterySlug = SanitizerHelper::sanitizeSlug(Input::get('lotterySlug', ''));
        if (empty($lotterySlug)) {
            return $this->returnResponse([
                'message' => 'lotterySlug is required'
            ], 400);
        }

        $lottery = $this->lotteryRepository->findOneBySlug($lotterySlug);
        if (empty($lottery)) {
            return $this->returnResponse([
                'message' => "Lottery with slug $lotterySlug does not exist"
            ], 400);
        }

        $possibleOrder = Lotto_Helper::get_possible_order();
        $possibleOrderCount = Lotto_Helper::get_possible_order_count();
        $pricing = Helpers_Lottery::getPricing($lottery);
        $whitelabel = Container::get('whitelabel');
        try {
            $whitelabelLottery = $this->whitelabelLotteryRepository->getOneByLotteryIdForWhitelabel(
                $lottery->id,
                $whitelabel->id
            );
        } catch (Throwable $exception) {
            return $this->returnResponse([
                'message' => "Lottery with slug $lotterySlug does not exist for current whitelabel"
            ], 400);
        }
        $minimumLines = $whitelabelLottery->minLines > 0 ? $whitelabelLottery->minLines : 1;
        $isBuyingDisabled = $possibleOrderCount == 0 || bccomp($possibleOrder, bcmul($pricing, $minimumLines, 2), 2) < 0;

        return $this->returnResponse([
            'isBuyingDisabled' => $isBuyingDisabled,
            'buyingDisabledAlert' => _('You cannot add more tickets to your order!'),
        ]);
    }
}
