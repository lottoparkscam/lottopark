<?php

namespace Services\MiniGame\Dto;

use Lotto_View;
use Services\MiniGame\ValueObject\MiniGameTransactionObject;

final class MiniGameResult
{
    private bool $isWin;
    private bool $isFreeSpin;
    private string $balanceBeforeFlipResult;
    private string $balanceAfterFlipResult;
    private string $bonusBalanceBeforeFlipResult;
    private string $bonusBalanceAfterFlipResult;
    private float $prize;
    private float $amount;
    private int $userSelectedNumber;
    private bool $isUsedBonusBalance;
    private array $additionalData;

    public function __construct(MiniGameTransactionObject $transaction, bool $isUsedBonusBalance)
    {
        $this->isWin = $transaction->isWin();
        $this->isFreeSpin = $transaction->isFreeSpin();
        $this->prize  = $transaction->getPrizeAmountInEur();
        $this->amount = $transaction->getBetAmountInEur();
        $this->userSelectedNumber = $transaction->getSelectedNumber();
        $this->additionalData = $transaction->getAdditionalData();
        $currencyCode = $transaction->getUserCurrencyCode();
        $this->isUsedBonusBalance = $isUsedBonusBalance;

        $this->balanceBeforeFlipResult = Lotto_View::format_currency(
            $isUsedBonusBalance
                ? $transaction->getUserBalance()
                : $transaction->getBalanceBefore(),
            $currencyCode,
            true
        );

        $this->balanceAfterFlipResult = Lotto_View::format_currency(
            $isUsedBonusBalance
                ? ($transaction->isWin()
                ? $transaction->getUserBalance() + $transaction->getPrizeAmountInUserCurrency()
                : $transaction->getUserBalance()
            )
                : $transaction->getBalanceAfter(),
            $currencyCode,
            true
        );

        $this->bonusBalanceBeforeFlipResult = Lotto_View::format_currency(
            $isUsedBonusBalance
                ? $transaction->getBonusBalanceBefore()
                : $transaction->getUserBonusBalance(),
            $currencyCode,
            true
        );

        $this->bonusBalanceAfterFlipResult = Lotto_View::format_currency(
            $isUsedBonusBalance
                ? $transaction->getBonusBalanceAfter()
                : $transaction->getUserBonusBalance(),
            $currencyCode,
            true
        );
    }

    public function toArray(): array
    {
        return [
            'isWin'                        => $this->isWin,
            'isFreeSpin'                   => $this->isFreeSpin,
            'isUsedBonusBalance'           => $this->isUsedBonusBalance,
            'balanceBeforeFlipResult'      => $this->balanceBeforeFlipResult,
            'balanceAfterFlipResult'       => $this->balanceAfterFlipResult,
            'bonusBalanceBeforeFlipResult' => $this->bonusBalanceBeforeFlipResult,
            'bonusBalanceAfterFlipResult'  => $this->bonusBalanceAfterFlipResult,
            'prize'                        => number_format($this->prize, 2),
            'amount'                       => number_format($this->amount, 2),
            'userSelectedNumber'           => $this->userSelectedNumber,
            'additionalData'               => $this->additionalData,
        ];
    }
}
