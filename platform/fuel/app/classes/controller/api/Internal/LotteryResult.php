<?php

use Abstracts\Controllers\Internal\AbstractPublicController;
use Fuel\Core\Input;
use Fuel\Core\Response;
use Helpers\SanitizerHelper;
use Repositories\LotteryDrawRepository;
use Repositories\LotteryRepository;
use Services\LotteryAdditionalDataService;
use Services\LotteryResultService;

class Controller_Api_Internal_LotteryResult extends AbstractPublicController
{
    private LotteryRepository $lotteryRepository;
    private LotteryDrawRepository $lotteryDrawRepository;
    private LotteryResultService $lotteryResultService;
    private LotteryAdditionalDataService $lineServices;

    public function before()
    {
        parent::before();
        $this->lotteryRepository = Container::get(LotteryRepository::class);
        $this->lotteryDrawRepository = Container::get(LotteryDrawRepository::class);
        $this->lotteryResultService = Container::get(LotteryResultService::class);
        $this->lineServices = Container::get(LotteryAdditionalDataService::class);
    }

    public function get_index(): Response
    {
        $lotterySlug = SanitizerHelper::sanitizeString(Input::get('lotteryName') ?: '');
        $selectedDrawDate = SanitizerHelper::sanitizeString(Input::get('drawDate') ?: '');
        $selectedDrawDateTime = (int)SanitizerHelper::sanitizeString(Input::get('drawDateTime') ?: '');

        // Validate date format of selectedDrawDate
        $date = DateTime::createFromFormat('Y-m-d', $selectedDrawDate);
        if (!$date || $date->format('Y-m-d') !== $selectedDrawDate) {
            $selectedDrawDate = '';
        }

        // Validate date format of selectedDrawDateTime
        $dateTime = DateTime::createFromFormat('YmdHi', $selectedDrawDateTime);
        if (!$dateTime || $dateTime->format('YmdHi') != $selectedDrawDateTime) {
            $selectedDrawDateTime = '';
        }

        $lottery = $this->lotteryRepository->findOneBySlug($lotterySlug);
        if (empty($lottery)) {
            return $this->returnResponse([], 404);
        }

        $lotteryDrawDates = $this->lotteryDrawRepository->getLotteryDrawDatesForLotteryId($lottery->id);
        $selectedDrawDate = $selectedDrawDate ?: $lotteryDrawDates[0];
        $lotteryDrawDateTimes = $this->lotteryDrawRepository->getLotteryDrawDateTimesForLotteryId($lottery, $selectedDrawDate);
        $lotteryDraws = $this->lotteryDrawRepository->getLotteryDrawsByLotteryIdAndDrawDate(
            $lottery,
            $selectedDrawDate,
            $selectedDrawDateTime,
        );

        if (empty($lotteryDraws)) {
            return $this->returnResponse([
                'drawDates' => [],
                'drawTimes' => [],
                'winNumbersFormatted' => '',
                'estimatedJackpotValue' => '',
                'lotteryResultTableHtml' => '',
                'extraTitleText' => '',
                'lotteryDrawNumber' => '',
            ], 404);
        }

        $lotteryDraw = $this->lotteryDrawRepository->getLotteryDrawByLotteryIdAndDrawDate(
            $lottery,
            $selectedDrawDate,
            $selectedDrawDateTime,
        );

        $drawData = Model_Lottery_Prize_Data::get_draw_prize_data($lotteryDraw);
        $lotteryType = Model_Lottery_Type::get_lottery_type_for_date($lottery->to_array(), $selectedDrawDate);

        $estimatedJackpotValue = Lotto_View::format_currency($lotteryDraw['jackpot'] * 1000000, $lottery->currency->code);

        $lotteryAdditionalData = $this->lineServices->getAdditionalDataForLottery(
            $lottery->to_array(),
            $lotteryDraw,
        );

        $winNumbersFormatted = Lotto_View::format_line(
            $lotteryDraw['numbers'],
            $lotteryDraw['bnumbers'],
            null,
            null,
            null,
            $lotteryAdditionalData
        );

        $winNumbersFormatted = strip_tags($winNumbersFormatted, ['div', 'span']);

        $lotteryResultTableHtml = $this->lotteryResultService->getLotteryResultTableHtml(
            $lottery->to_array(),
            $drawData,
            $lotteryType,
            $lotteryDraw
        );

        $extraTitleText = Lotto_View::format_date(
            date: $lotteryDraw['date_local'],
            timezonein: $lotteryDraw['timezone'],
        );

        return $this->returnResponse([
            'drawDates' => $lotteryDrawDates,
            'drawTimes' => $lottery->isKeno() ? $lotteryDrawDateTimes[$selectedDrawDate] : [],
            'winNumbersFormatted' => $winNumbersFormatted,
            'estimatedJackpotValue' => $estimatedJackpotValue,
            'lotteryResultTableHtml' => $lotteryResultTableHtml,
            'extraTitleText' => $extraTitleText,
            'lotteryDrawNumber' => $lottery->isKeno() ? $lotteryDraw['draw_no'] : '',
        ]);
    }
}
