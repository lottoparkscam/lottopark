<?php

namespace Services;

class LotteryAdditionalDataService
{
    public const ADDITIONAL_SUPER = 'super';
    public const ADDITIONAL_REFUND = 'refund';
    public const SUPER_BALL_SHORT_NAME = 'S';
    public const REFUND_BALL_SHORT_NAME = 'R';

    public function getExtraBall(?string $additionalData): ?string
    {
        $additionalData = unserialize($additionalData);
        if ($additionalData) {
            return $this->createExtraBall($additionalData);
        }
        return null;
    }

    public function getBallShortName(array $additionalData): string
    {
        $ballShortName = '';
        if ($this->isSetSuperAdditionalData($additionalData)) {
            $ballShortName = self::SUPER_BALL_SHORT_NAME;
        }
        if ($this->isSetRefundAdditionalData($additionalData)) {
            $ballShortName = self::REFUND_BALL_SHORT_NAME;
        }

        return $ballShortName;
    }

    private function createExtraBall(array $additionalData): ?string
    {
        if ($this->isSetSuperAdditionalData($additionalData)) {
            return $additionalData[self::ADDITIONAL_SUPER] . ' ' . $this->getBallShortName($additionalData);
        }

        if ($this->isSetRefundAdditionalData($additionalData)) {
            return $additionalData[self::ADDITIONAL_REFUND] . ' ' . $this->getBallShortName($additionalData);
        }

        return null;
    }

    public function isSetSuperAdditionalData(array $additionalData): bool
    {
        return isset($additionalData[self::ADDITIONAL_SUPER]);
    }

    public function isSetRefundAdditionalData(array $additionalData): bool
    {
        return isset($additionalData[self::ADDITIONAL_REFUND]);
    }

    public function getAdditionalDataForLottery(array $lottery, array $lotteryDraw): ?array
    {
        $additionalData = null;
        if ($lottery['additional_data']) {
            $additionalData = unserialize($lotteryDraw['additional_data']);
        }

        if ($additionalData === false) {
            return null;
        }

        return $additionalData;
    }
}
