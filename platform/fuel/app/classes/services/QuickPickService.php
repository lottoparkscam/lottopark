<?php

namespace Services;

use Exceptions\WrongLotteryNumbersException;
use Fuel\Core\Input;
use Models\Lottery;
use Validators\LotteryTicketNumbersValidator;

class QuickPickService
{
    public function __construct(
        public LotteryTicketNumbersValidator $lotteryTicketNumbersValidator,
    )
    {}

    public function shouldGetFirstLineFromUser(): bool
    {
        return !empty(Input::get(LotteryTicketNumbersValidator::NORMAL_NUMBERS_URL_QUERY_PARAMETER_NAME));
    }

    /** @throws WrongLotteryNumbersException */
    public function getUsersFirstLineNumbers(Lottery $lottery): array
    {
        $this->lotteryTicketNumbersValidator->setBuildArguments($lottery);
        $this->lotteryTicketNumbersValidator->setExtraCheckArguments($lottery);

        $isRequestValid = $this->lotteryTicketNumbersValidator->isValid();
        if ($isRequestValid) {
            return $this->lotteryTicketNumbersValidator->getValidatedProperties();
        }

        throw new WrongLotteryNumbersException();
    }
}
