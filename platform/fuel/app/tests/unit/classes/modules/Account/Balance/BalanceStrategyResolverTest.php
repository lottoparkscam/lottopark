<?php

namespace Tests\Unit\Classes\Modules\Account\Balance;

use Models\Raffle;
use Models\WhitelabelRaffle;
use Models\WhitelabelUser;
use Modules\Account\Balance\BalanceStrategyResolver;
use Modules\Account\Balance\BonusBalance;
use Modules\Account\Balance\InteractsWithBalance;
use Modules\Account\Balance\RegularBalance;
use PHPUnit\Framework\MockObject\MockObject;
use Test_Unit;

class BalanceStrategyResolverTest extends Test_Unit
{
    /** @var Raffle|MockObject */
    private $raffle;
    /** @var WhitelabelRaffle|MockObject */
    private $wl_raffle;
    /** @var WhitelabelUser|MockObject */
    private $user;
    /** @var MockObject|BonusBalance */
    private $bonus;
    /** @var MockObject|RegularBalance */
    private $regular;
    /** @var MockObject|InteractsWithBalance */
    private $service;
    /** @var BalanceStrategyResolver */
    private $resolver;

    public function setUp(): void
    {
        parent::setUp();
        $this->raffle = new Raffle();
        $this->wl_raffle = new WhitelabelRaffle();
        $this->raffle->whitelabel_raffle = $this->wl_raffle;
        $this->user = new WhitelabelUser();

        $this->bonus = $this->createStub(BonusBalance::class);
        $this->regular = $this->createStub(RegularBalance::class);
        $this->service = $this->createMock(InteractsWithBalance::class);

        $this->resolver = new BalanceStrategyResolver($this->bonus, $this->regular);
    }

    /** @test */
    public function determinePaymentMethod_BonusBalanceIsNotEnabledForWhitelabel_SetsRegularType(): void
    {
        // Given
        $this->wl_raffle->is_bonus_balance_in_use = false;

        $this->service
            ->expects($this->once())
            ->method('setBalanceStrategy')
            ->with($this->regular);

        // When
        $this->resolver->determinePaymentMethod(
            $this->service,
            $this->raffle,
            $this->user,
            20.20
        );
    }

    /** @test */
    public function determinePaymentMethod_BonusBalanceIsEnabledButNoSufficientBalanceToProcess_SetsRegularType(): void
    {
        // Given
        $this->wl_raffle->is_bonus_balance_in_use = true;
        $this->bonus
            ->expects($this->once())
            ->method('hasSufficientBalanceToProcess')
            ->willReturn(false);

        $this->service->expects($this->once())->method('setBalanceStrategy')->with($this->regular);

        // When
        $this->resolver->determinePaymentMethod(
            $this->service,
            $this->raffle,
            $this->user,
            20.20
        );
    }

    /** @test */
    public function determinePaymentMethod_BonusBalanceIsEnabledAndSufficientBalanceToProcess_SetsBalanceType(): void
    {
        // Given
        $this->wl_raffle->is_bonus_balance_in_use = true;
        $this->bonus
            ->expects($this->once())
            ->method('hasSufficientBalanceToProcess')
            ->willReturn(true);

        $this->service->expects($this->once())->method('setBalanceStrategy')->with($this->bonus);

        // When
        $this->resolver->determinePaymentMethod(
            $this->service,
            $this->raffle,
            $this->user,
            20.20
        );
    }
}
