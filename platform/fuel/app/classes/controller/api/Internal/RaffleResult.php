<?php

use Abstracts\Controllers\Internal\AbstractPublicController;
use Fuel\Core\Input;
use Fuel\Core\Response;
use Helpers\SanitizerHelper;
use Models\RaffleDraw;
use Repositories\Orm\RaffleRepository;
use Repositories\RaffleDrawRepository;
use Repositories\RafflePrizeRepository;
use Services\RaffleResultService;

class Controller_Api_Internal_RaffleResult extends AbstractPublicController
{
    private RaffleRepository $raffleRepository;
    private RaffleDrawRepository $raffleDrawRepository;
    private RafflePrizeRepository $rafflePrizeRepository;
    private RaffleResultService $raffleResultService;

    public function before()
    {
        parent::before();
        $this->raffleRepository = Container::get(RaffleRepository::class);
        $this->raffleDrawRepository = Container::get(RaffleDrawRepository::class);
        $this->rafflePrizeRepository = Container::get(RafflePrizeRepository::class);
        $this->raffleResultService = Container::get(RaffleResultService::class);
    }

    public function get_index(): Response
    {
        $raffleSlug = SanitizerHelper::sanitizeString(Input::get('raffleSlug') ?: '');
        $drawId = (int)SanitizerHelper::sanitizeString(Input::get('drawId') ?: '');

        $raffle = $this->raffleRepository->findOneBySlug($raffleSlug);

        if (!$raffle) {
            return $this->returnResponse([
                'drawNumbers' => '',
            ], 404);
        }

        $raffleDraws = $this->raffleDrawRepository->getDrawsByRaffleId($raffle->id);

        $firstDraw = null;
        $isEmptyDrawIdAndHasRaffleDraws = empty($drawId) && !empty($raffleDraws);
        $isNonEmptyDrawIdAndHasRaffleDraws = !empty($drawId) && !empty($raffleDraws);
        if ($isEmptyDrawIdAndHasRaffleDraws) {
            $firstDraw = reset($raffleDraws);
            $drawId = $firstDraw['id'];
        } elseif ($isNonEmptyDrawIdAndHasRaffleDraws) {
            /** @var RaffleDraw $draw */
            foreach ($raffleDraws as $draw) {
                if ($draw['id'] == $drawId) {
                    $firstDraw = $draw;
                    break;
                }
            }
        }

        $firstDrawDateFormatted = Lotto_View::format_date_without_timezone(
            $firstDraw['date'],
            IntlDateFormatter::LONG,
            IntlDateFormatter::SHORT,
            null,
            $raffle->timezone,
            $raffle->timezone
        );

        $firstDrawNumbers = $firstDraw['draw_no'];
        $drawNumbersFormatted = <<<HTML
            <b>$firstDrawNumbers</b> ($firstDrawDateFormatted)
        HTML;

        $mainPrizes = $this->rafflePrizeRepository->getMainPrizesByDrawId($drawId);

        $mainPrizesFormatted = '';
        foreach ($mainPrizes as $prize) {
            $lines = $prize->lines;
            foreach ($lines as $line) {
                $numberFormatted = $this->raffleResultService->formatLineNumber($line->number, $raffle);
                $mainPrizesFormatted .= <<<HTML
                    <div class="widget-chosen-ticket">$numberFormatted</div>
                HTML;
            }
        }

        $prizes = $this->rafflePrizeRepository->getPrizesByDraw($drawId);
        $winnersTableHtml = $this->raffleResultService->getWinnersTableHtml(
            $prizes,
            $raffle
        );
        $dateSelectOptions = $this->raffleResultService->getDateSelectOptions(
            $raffleDraws,
            $raffle,
            $drawId
        );

        return $this->returnResponse([
            'drawNumbers' => $drawNumbersFormatted,
            'mainPrizes' => $mainPrizesFormatted,
            'winnersTableHtml' => $winnersTableHtml,
            'dateSelectOptions' => $dateSelectOptions
        ]);
    }
}
