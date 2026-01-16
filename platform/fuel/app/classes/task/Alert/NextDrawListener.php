<?php

namespace Task\Alert;

use Carbon\Carbon;
use Container;
use Helpers\CaseHelper;
use Helpers_Time;
use Models\Lottery;
use Repositories\LotteryRepository;
use Services\ExternalNextDraw\ExternalNextDrawAbstract;
use Services\Logs\FileLoggerService;
use Throwable;

/** Documentation -> https://bookstack.gginternational.work/books/wl-lottery/page/health-check-next-draw */
class NextDrawListener extends AbstractAlertListener
{
    protected string $message;
    protected string $type = self::TYPE_NEXT_DRAW_LISTENER;

    private LotteryRepository $lotteryRepository;
    private FileLoggerService $fileLoggerService;
    private const LOTTERIES_SLUGS_TO_CHECK = [
        Lottery::SUPERENA_SLUG,
        Lottery::LOTTO_AUSTRIA_SLUG,
        Lottery::LOTTO_6AUS49_SLUG,
        Lottery::MEGA_SENA_SLUG,
        Lottery::QUINA_SLUG,
    ];

    public function __construct()
    {
        parent::__construct();
        $this->lotteryRepository = Container::get(LotteryRepository::class);
        $this->fileLoggerService = Container::get(FileLoggerService::class);
    }

    public function shouldSendAlert(): bool
    {
        try {
            $lotteriesWithIncorrectNextDrawDate = '';
            $lotteriesWithIncorrectClosingDate = '';
            foreach (self::LOTTERIES_SLUGS_TO_CHECK as $lotterySlugToCheck) {
                /** @var Lottery $lottery */
                $lottery = $this->lotteryRepository->findOneBySlug($lotterySlugToCheck);

                /**
                 * If lottery is pending external source can return next next draw.
                 * This could generate useless error.
                 */
                $isPendingLottery = empty($lottery->nextDateLocal) || $lottery->nextDateLocal->isPast();
                if ($isPendingLottery) {
                    continue;
                }

                $path = $this->getLotteriesExternalDrawServicePathPerSlug($lotterySlugToCheck);
                /** @var ExternalNextDrawAbstract $externalNextDrawService */
                $externalNextDrawService = Container::get($path);

                $nextDrawFromFirstSource = $externalNextDrawService->getNextDrawFromFirstSource();
                $nextDrawFromSecondSource = $externalNextDrawService->getNextDrawFromSecondSource();

                $nextDrawFromFirstSourceCarbon = Carbon::parse($nextDrawFromFirstSource);
                $nextDrawFromSecondSourceCarbon = Carbon::parse($nextDrawFromSecondSource);

                $firstDrawDateIsFuture = $nextDrawFromFirstSourceCarbon->isFuture();
                $secondDrawDateIsFuture = $nextDrawFromSecondSourceCarbon->isFuture();
                $firstDrawDateExist = !empty($nextDrawFromFirstSource);
                $secondDrawDateExist = !empty($nextDrawFromSecondSource);

                $externalSourcesArePast = !$firstDrawDateIsFuture && !$secondDrawDateIsFuture;
                $sourcesAreEmpty = !$firstDrawDateExist && !$secondDrawDateExist;
                if ($externalSourcesArePast || $sourcesAreEmpty) {
                    $this->fileLoggerService->error("
                        Lottery with slug $lotterySlugToCheck doesn't check next draw date in nextDrawListener. 
                        Check dates manually if the problem occurred several times, change the sources.
                    ");
                    continue;
                }

                $shouldCheckFirstSource = $firstDrawDateExist && $firstDrawDateIsFuture;
                $shouldCheckSecondSource = $secondDrawDateExist && $secondDrawDateIsFuture;
                if ($externalNextDrawService->shouldCheckClosingTimes) {
                    foreach ($lottery->lottery_providers as $lotteryProvider) {
                        $lotteryDayOfWeek = $lottery->nextDateLocal->dayOfWeekIso;
                        $closingTimes = json_decode(json_encode($lotteryProvider->closing_times), true);
                        $currentClosingTime = $closingTimes[$lotteryDayOfWeek] ?? $lottery->nextDateLocal->format(Helpers_Time::TIME_FORMAT);
                        $lotteryClosingDateTime = Carbon::parse($lottery->nextDateLocal->format(Helpers_Time::DATE_FORMAT . $currentClosingTime));
                        $ourNextDateIsAfterFirstExternalDrawDate = $shouldCheckFirstSource && $lotteryClosingDateTime->isAfter($nextDrawFromFirstSourceCarbon);
                        $ourNextDateIsAfterSecondExternalDrawDate = $shouldCheckSecondSource && $lotteryClosingDateTime->isAfter($nextDrawFromSecondSourceCarbon);
                        $isClosingTimeIncorrect = $ourNextDateIsAfterFirstExternalDrawDate ||
                            $ourNextDateIsAfterSecondExternalDrawDate;
                        if ($isClosingTimeIncorrect) {
                            $currentClosingTime = $lotteryClosingDateTime->format(Helpers_Time::DATETIME_FORMAT);
                            $lotteriesWithIncorrectClosingDate .= "$lotterySlugToCheck (actual closing time $currentClosingTime), ";
                        }
                    }

                    $currentNextDateWithoutHour = $lottery->nextDateLocal->format(Helpers_Time::DATE_FORMAT);
                    $isFirstSourceIncorrect = $shouldCheckFirstSource && $nextDrawFromFirstSourceCarbon->format(Helpers_Time::DATE_FORMAT) !== $currentNextDateWithoutHour;
                    $isSecondSourceIncorrect = $shouldCheckSecondSource && $nextDrawFromSecondSourceCarbon->format(Helpers_Time::DATE_FORMAT) !== $currentNextDateWithoutHour;
                } else {
                    $currentNextDate = $lottery->nextDateLocal->format(Helpers_Time::DATETIME_FORMAT);
                    $isFirstSourceIncorrect = $shouldCheckFirstSource && $nextDrawFromFirstSource !== $currentNextDate;
                    $isSecondSourceIncorrect = $shouldCheckSecondSource && $nextDrawFromSecondSource !== $currentNextDate;
                }

                $lotteryContainBadDrawDate =
                    $isFirstSourceIncorrect ||
                    $isSecondSourceIncorrect;
                if ($lotteryContainBadDrawDate) {
                    $lotteriesWithIncorrectNextDrawDate .= "$lotterySlugToCheck (actual next draw $lottery->nextDateLocal), ";
                }
            }

            $isLotteriesCorrect = empty($lotteriesWithIncorrectNextDrawDate) &&
                empty($lotteriesWithIncorrectClosingDate);
            if ($isLotteriesCorrect) {
                return false;
            }

            $message = '';
            if (!empty($lotteriesWithIncorrectNextDrawDate)) {
                $message .= "Lotteries with slug $lotteriesWithIncorrectNextDrawDate contains incorrect next draw date please check this manually on official page. 
                    Probably lottery changed draw dates or add bonus draw. If lottery changed rules set new draw dates and closing times in data base but if bonus
                    draw was add, change next_date_local and next_date_utc in lottery table.";
            }

            if (!empty($lotteriesWithIncorrectClosingDate)) {
                $message .= " Lotteries with slug $lotteriesWithIncorrectClosingDate contains incorrect closing time please check this manually on official page. 
                    Probably lottery changed draw dates. If lottery changed rules set new draw dates and closing times in data base.
                    Sometimes lotteries contains big cutoff time, ask business if we want the same.";
            }

            $this->setMessage($message);
            return true;
        } catch (Throwable $exception) {
            $this->fileLoggerService->error("Sometimes nextDrawListener gets bad scraper data during external counter update, 
            check sources for $lotterySlugToCheck. " . $exception->getMessage());
            return false;
        }
    }

    private function getLotteriesExternalDrawServicePathPerSlug(string $slug): string
    {
        $className = CaseHelper::kebabToPascal($slug) . 'Service';
        return "Services\ExternalNextDraw\\$className";
    }
}
