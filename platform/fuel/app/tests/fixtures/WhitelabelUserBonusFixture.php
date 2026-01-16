<?php

namespace Tests\Fixtures;

use Models\{WhitelabelBonus, WhitelabelUser, WhitelabelUserBonus};

final class WhitelabelUserBonusFixture extends AbstractFixture
{
    public const WHITELABEL_USER = 'whitelabel_user';

    public function getDefaults(): array
    {
        return [
            'bonus_id' => WhitelabelBonus::WELCOME,
            'type' => WhitelabelUserBonus::TYPE_PURCHASE,
            'lottery_type' => WhitelabelUserBonus::TYPE_LOTTERY,
            'whitelabel_user_id' => null,
            'used_at' => null,
        ];
    }

    public static function getClass(): string
    {
        return WhitelabelUserBonus::class;
    }

    public function getStates(): array
    {
        return [
            self::BASIC => $this->basic(),
            self::WHITELABEL_USER => $this->reference('user', WhitelabelUserFixture::class),
        ];
    }

    public function withType(string $type): self
    {
        $this->with(function (WhitelabelUserBonus $model, array $attributes = []) use ($type) {
            $model->type = $type;
        });

        return $this;
    }

    public function withLotteryType(string $lotteryType): self
    {
        $this->with(function (WhitelabelUserBonus $model, array $attributes = []) use ($lotteryType) {
            $model->lotteryType = $lotteryType;
        });

        return $this;
    }

    public function withUser(WhitelabelUser $user, bool $used = false): self
    {
        $this->with(function (WhitelabelUserBonus $model, array $attributes = []) use ($user, $used) {
            $model->user = $user;

            if ($used) {
                $model->usedAt = $this->faker->date();
            }
        });

        return $this;
    }

    private function basic()
    {
        return function (WhitelabelUserBonus $model, array $attributes = []) {
            $model->bonusId = WhitelabelBonus::WELCOME;
        };
    }
}
