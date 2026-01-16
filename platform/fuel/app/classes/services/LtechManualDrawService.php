<?php

use Carbon\Carbon;
use Models\Currency;
use Models\Lottery;
use Models\LotteryTypeData;
use Models\LtechManualDraw;
use Repositories\LotteryRepository;
use Repositories\LotteryTypeDataRepository;
use Repositories\LtechManualDrawRepository;

class LtechManualDrawService
{
    /** These lotteries currently use other source of draws than ltech  */
    public const EXCLUDED_SLUGS = [
        'gg-world',
        'gg-world-million',
        'gg-world-x',
        'gg-world-keno',
        'hatoslotto',
        'multi-multi',
        'lotto-pl',
        'monday-wednesday-lotto-au',
        'otoslotto',
        'oz-lotto',
        'powerball-au',
        'skandinav-lotto',
        'superenalotto',
        'eurojackpot',
    ];

    public function __construct(
        public LotteryRepository $lotteryRepository,
        public LotteryTypeDataRepository $lotteryTypeDataRepository,
        public LtechManualDrawRepository $ltechManualDrawRepository,
    ) {}

    public function getLotteriesForManualDraw(): array
    {
        $lotteries = $this->lotteryRepository->findWaitingForDraw();
        $pendingManualDrawLotteryIds = $this->ltechManualDrawRepository->getPendingLotteryIds();

        // Exclude specific slugs and lotteries with many draws per day
        $lotteries = array_values(array_filter($lotteries, function($lottery) use ($pendingManualDrawLotteryIds) {
            return !in_array($lottery['slug'], self::EXCLUDED_SLUGS) &&
                !in_array($lottery['id'], $pendingManualDrawLotteryIds) &&
                !Lottery::hasManyDrawsPerDay(json_decode($lottery['draw_dates'], true) ?? []);
        }));

        foreach ($lotteries as $key => &$lottery) {
            // Add lottery_type information
            try {
                $lotteryType = Lotto_Helper::get_next_lottery_type($lottery);
            } catch (Throwable) {
                // Any whitelabel has this lottery
                unset($lotteries[$key]);
                continue;
            }
            $lottery['normal_numbers_range'] = $lotteryType['nrange'];
            $lottery['normal_numbers_count'] = $lotteryType['ncount'];
            $lottery['bonus_numbers_range'] = $lotteryType['bextra'] > 0 ? $lotteryType['nrange'] : $lotteryType['brange'];
            $lottery['bonus_numbers_count'] = $lotteryType['bextra'] > 0 ? $lotteryType['bextra'] : $lotteryType['bcount'];

            // Clear lottery_type_id
            unset($lottery['id']);

            // Get rid of empty properties on model and get rules
            $lottery = array_filter($lottery);

            // Add currency sign
            $lottery['currency_sign'] = Lotto_View::format_currency_code($lottery['currency_code']);

            // Add tiers
            /** @var LotteryTypeData $tiers */
            $tiers = $this->lotteryTypeDataRepository->findByLotteryTypeId($lotteryType['id']);
            $tiersNormalized = [];
            foreach ($tiers as $tier) {
                $isLotteryWithAdditionalNumber = LtechManualDraw::needLotteryAdditionalNumber($lottery['slug']);
                $tiersNormalized[] = [
                    'normal_numbers' => $tier->match_n,
                    'bonus_numbers' => $tier->match_b,
                    'additional_number' => $isLotteryWithAdditionalNumber && key_exists(
                        LtechManualDraw::getAdditionalNumberName($lottery['slug']),
                        unserialize($tier->additionalData) ?: []
                    ),
                ];
            }
            $lottery['tiers'] = $tiersNormalized;

            // Set additionalNumber information
            if (LtechManualDraw::needLotteryAdditionalNumber($lottery['slug'])) {
                $lottery['additionalNumberName'] = LtechManualDraw::getAdditionalNumberName($lottery['slug']);
            }
        }

        return array_values($lotteries);
    }

    public function add(
        Lottery $lottery,
        string $nextDrawDate,
        array $normalNumbers,
        array $bonusNumbers,
        float $nextJackpot,
        Currency $currency,
        array $prizes,
        array $winners,
        int $additionalNumber = null,
    ): void {
        $ltechManualDraw = new LtechManualDraw();
        $ltechManualDraw->lottery = $lottery;
        $ltechManualDraw->nextDrawDate = $nextDrawDate;
        $ltechManualDraw->currentDrawDate = $lottery->nextDateLocal->format(Helpers_Time::DATETIME_FORMAT);
        $ltechManualDraw->currentDrawDateUtc = $lottery->nextDateUtc->format(Helpers_Time::DATETIME_FORMAT);
        $ltechManualDraw->normalNumbers = $normalNumbers;
        $ltechManualDraw->bonusNumbers = $bonusNumbers;
        $ltechManualDraw->additionalNumber = $additionalNumber;
        $ltechManualDraw->nextJackpot = $nextJackpot;
        $ltechManualDraw->prizes = $prizes;
        $ltechManualDraw->winners = $winners;
        $ltechManualDraw->currency = $currency;
        $ltechManualDraw->createdAt = Carbon::now('UTC');
        $ltechManualDraw->save();
    }
}
