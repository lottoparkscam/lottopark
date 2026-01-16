<?php

namespace Task\Alert;

use Container;
use Repositories\LotteryRepository;

class IncorrectNextDrawListener extends AbstractAlertListener
{
    protected string $message;
    protected string $type = self::TYPE_INCORRECT_NEXT_DRAW;
    private LotteryRepository $lotteryRepository;
    public function __construct()
    {
        parent::__construct();
        $this->lotteryRepository = Container::get(LotteryRepository::class);
    }

    public function shouldSendAlert(): bool
    {
        $lotteriesWithError = $this->lotteryRepository->findLotteriesWhereNextDrawDateEqualsLastDrawDate();
        if (empty($lotteriesWithError)) {
            return false;
        }

        $lotteryRepository = Container::get(LotteryRepository::class);
        $lotteriesWithError = $lotteryRepository->findLotteriesWhereNextDrawDateEqualsLastDrawDate();
        $slugs = '';

        foreach ($lotteriesWithError as $lotteryWithError) {
            $slugs .= $lotteryWithError['slug'] . ', ';
        }

        $message = 'Lotteries with slug ' . $slugs . 'contains last_date_local equal next_date_local.
        Set correct next draw date. When draw date contains old date user can buy ticket with bad next draw, 
        necessarily check purchased ticket for this lotteries.';
        $this->setMessage($message);

        return true;
    }
}
