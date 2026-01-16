<?php

namespace Tests\Fixtures;

use Models\Raffle;
use Models\WhitelabelBonus;

final class WhitelabelBonusFixture extends AbstractFixture
{
    public function getDefaults(): array
    {
        return [
            'whitelabel_id' => 1,
            'bonus_id' => WhitelabelBonus::WELCOME,
            'purchase_lottery_id' => null,
            'register_lottery_id' => null,
            'purchase_raffle_id' => null,
            'register_raffle_id' => 1,
            'register_website' => 1,
            'register_api' => 1,
        ];
    }

    public static function getClass(): string
    {
        return WhitelabelBonus::class;
    }

    public function getStates(): array
    {
        return [
            self::BASIC => $this->basic(),
        ];
    }

    public function withRegisterRaffle(int $bonusId, Raffle $raffle): self
    {
        $this->with(function (WhitelabelBonus $model, array $attributes = []) use ($bonusId, $raffle) {
            $model->bonusId = $bonusId;
            $model->purchaseLotteryId = null;
            $model->registerLotteryId = null;
            $model->purchaseRaffleId = null;
            $model->registerRaffleId = $raffle->id;
        });

        return $this;
    }

    private function basic()
    {
        return function (WhitelabelBonus $model, array $attributes = []) {
            $model->bonusId = WhitelabelBonus::WELCOME;
        };
    }
}
