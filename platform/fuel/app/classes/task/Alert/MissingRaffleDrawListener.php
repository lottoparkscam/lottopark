<?php

namespace Task\Alert;

use Carbon\Carbon;
use Container;
use Helpers_Time;
use Models\RaffleDraw;
use Models\WhitelabelRaffleTicket;
use Repositories\Orm\RaffleRepository;
use Repositories\RaffleDrawRepository;
use Repositories\WhitelabelRaffleTicketRepository;

class MissingRaffleDrawListener extends AbstractAlertListener
{
    protected string $message;
    protected string $type = self::TYPE_MISSING_RAFFLE_DRAW;
    private RaffleDrawRepository $raffleDrawRepository;
    private RaffleRepository $raffleRepository;
    private WhitelabelRaffleTicketRepository $whitelabelRaffleTicketRepository;

    public function __construct()
    {
        parent::__construct();
        $this->raffleDrawRepository = Container::get(RaffleDrawRepository::class);
        $this->raffleRepository = Container::get(RaffleRepository::class);
        $this->whitelabelRaffleTicketRepository = Container::get(WhitelabelRaffleTicketRepository::class);
    }

    public function shouldSendAlert(): bool
    {
        $enabledRaffles = $this->raffleRepository->findByIsEnabled(true);
        foreach ($enabledRaffles as $raffle) {
            /** @var RaffleDraw|null $lastDraw */
            $lastDraw = $this->raffleDrawRepository->findLastDrawByRaffleId($raffle->id);
            $isPoolSoldOut = $raffle->maxBets === $raffle->drawLinesCount;
            if ($isPoolSoldOut) {
                /** @var WhitelabelRaffleTicket $lastBoughtRaffleTicket */
                $lastBoughtRaffleTicket = $this->whitelabelRaffleTicketRepository->getLastByRaffleId($raffle->id);
                $lastBoughtTicketCreatedAt = Carbon::parse($lastBoughtRaffleTicket->createdAt);
                $dayAfterLastBoughtTicket = $lastBoughtTicketCreatedAt->addDay();
                $isOneDayAfterLastBoughtTicket = Carbon::now(Helpers_Time::TIMEZONE)->greaterThan($dayAfterLastBoughtTicket);
                $thereWereNoDrawAfterLastTicketBuying = empty($lastDraw) ||
                    Carbon::parse($lastDraw->date)->lessThan($lastBoughtTicketCreatedAt);
                if ($isOneDayAfterLastBoughtTicket && $thereWereNoDrawAfterLastTicketBuying) {
                    $this->setMessage("There were no draw for raffle: $raffle->slug within last day ");
                    return true;
                }
            }
        }

        return false;
    }
}
