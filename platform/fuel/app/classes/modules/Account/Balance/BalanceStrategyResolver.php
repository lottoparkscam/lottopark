<?php

namespace Modules\Account\Balance;

use Container;
use Models\{
    Raffle,
    WhitelabelUser,
    WhitelabelUserBonus
};

/**
 * Class BalanceStrategyResolver
 * This is service locator anty-pattern, but it has more pros than cons.
 */
class BalanceStrategyResolver
{
    private BonusBalance $bonus;
    private RegularBalance $regular;
    private ?WelcomeBonusBalance $welcomeBonus = null;

    public function __construct(BonusBalance $bonus, RegularBalance $regular)
    {
        $this->bonus = $bonus;
        $this->regular = $regular;
    }

    public function addUserBonus(WhitelabelUserBonus $bonus): void
    {
        $this->welcomeBonus = Container::get(WelcomeBonusBalance::class);
        $this->welcomeBonus->bonus = $bonus;
    }

    public function determinePaymentMethod(
        InteractsWithBalance $service,
        Raffle $raffle,
        WhitelabelUser $user,
        float $requestedAmountInUserCurrency
    ): void {
        if ($this->canUserUseWelcomeBonus($user)) {
            $service->setBalanceStrategy($this->welcomeBonus);
            return;
        }

        if (!$this->isBonusBalanceInUse($raffle) || !$this->bonus->hasSufficientBalanceToProcess($user, $requestedAmountInUserCurrency)) {
            $service->setBalanceStrategy($this->regular);
            return;
        }

        $service->setBalanceStrategy($this->bonus);
    }

    private function isBonusBalanceInUse(Raffle $raffle): bool
    {
        return $raffle->whitelabel_raffle->is_bonus_balance_in_use;
    }

    private function canUserUseWelcomeBonus(WhitelabelUser $user): bool
    {
        return $this->welcomeBonus && $this->welcomeBonus->bonus->isFreeTicketRaffleAvailableForUser($user->id);
    }
}
