<?php

namespace Services\ExternalNextDraw\Provider;

use Carbon\Carbon;
use Container;
use Helpers_Time;
use Lotto_Helper;
use Models\Lottery;
use Repositories\LotteryRepository;
use Services\Logs\FileLoggerService;
use Throwable;

class Superena
{
    private const API_URL = 'http://www.gntn-pgd.it/gntn-info-web/rest/gioco/superenalotto/estrazioni/archivioconcorso/';
    private const PARENT_ID = '?idPartner=GIOCHINUMERICI_INFO';

    public function __construct(
        public LotteryRepository $lotteryRepository,
    )
    {
    }

    public function getNextDrawDateTime(): string
    {
        try {
        /** @var Lottery $lottery */
        $lottery = $this->lotteryRepository->findOneBySlug(Lottery::SUPERENA_SLUG);

        $drawDates = [];
        $currentMonthResponse = $this->getDrawDatesPerMonth($lottery->lastDateLocal);
        $this->extractDrawDates($drawDates, $currentMonthResponse);

        $drawDateForNextMonth = $lottery->lastDateLocal->clone()->addMonth();
        $nextMonthResponse = $this->getDrawDatesPerMonth($drawDateForNextMonth);
        $this->extractDrawDates($drawDates, $nextMonthResponse);

        $lastDateLocalInUtc = $lottery->lastDateLocal->setTimezone('UTC')->format(Helpers_Time::DATETIME_FORMAT);
        $currentDateKey = array_search($lastDateLocalInUtc, $drawDates);
        $nextDrawDateKey = $currentDateKey + 1;
        return $drawDates[$nextDrawDateKey];
        } catch (Throwable) {
            $fileLoggerService = Container::get(FileLoggerService::class);
            $fileLoggerService->error(Lottery::SUPERENA_SLUG . ' can not download next draw date');
            return '';
        }
    }

    private function extractDrawDates(array &$drawDates, array $apiResponse): void
    {
        foreach ($apiResponse['concorsi'] as $item) {
            $drawDates[] = Carbon::createFromTimestamp($item['dataEstrazione'] / 1000)->format(Helpers_Time::DATETIME_FORMAT);
        }
    }

    private function getDrawDatesPerMonth(Carbon $date): array
    {
        $date = $date->format('Y\/n');
        return json_decode(Lotto_Helper::load_HTML_url(
            $this->prepareNextDrawUrl($date),
            30,
            'application/json, text/javascript, */*; q=0.01',
            'http://www.superenalotto.it/'
        ), true);
    }

    private function prepareNextDrawUrl(string $date): string
    {
        return self::API_URL . $date . self::PARENT_ID;
    }
}
