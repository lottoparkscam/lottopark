<?php

namespace Validators;

use Exception;
use Fuel\Core\Input;
use Models\Lottery;
use Models\LotteryType;
use Validators\Rules\LotteryNumber;

class LotteryTicketNumbersValidator extends Validator
{
    protected static string $method = Validator::GET;
    public const NORMAL_NUMBERS_URL_QUERY_PARAMETER_NAME = 'numbers';
    public const BONUS_NUMBERS_URL_QUERY_PARAMETER_NAME = 'bnumbers';

    private function sanitizeNumbers(): void
    {
        $normalNumbersQuery = Input::get(self::NORMAL_NUMBERS_URL_QUERY_PARAMETER_NAME, '');
        $bonusNumbersQuery = Input::get(self::BONUS_NUMBERS_URL_QUERY_PARAMETER_NAME, '');
        $normalNumbers = strlen($normalNumbersQuery) > 0 ? explode(',', $normalNumbersQuery) : [];
        $bonusNumbers = strlen($bonusNumbersQuery) > 0 ? explode(',', $bonusNumbersQuery) : [];

        $this->setCustomInput([
            'normalNumbers' => array_map(fn($number) => (int)$number, $this->input['normalNumbers'] ?? $normalNumbers),
            'bonusNumbers' => array_map(fn($number) => (int)$number, $this->input['bonusNumbers'] ?? $bonusNumbers),
        ]);
    }

    protected function buildValidation(...$args): void
    {
        $this->sanitizeNumbers();

        /** @var Lottery $lottery */
        $lottery = $args[0];
        /** @var LotteryType $lotteryType */
        $lotteryType = $lottery->lotteryType;

        ['normalNumbers' => $normalNumbers, 'bonusNumbers' => $bonusNumbers] = $this->input;

        foreach ($normalNumbers as $key => $normalNumber) {
            $normalNumberRule = LotteryNumber::build("normalNumbers[$key]", "normalNumbers[$key]");
            $this->addFieldRule($normalNumberRule);
            $normalNumberRule->addRule('numeric_between', 1, $lotteryType->nrange);
        }

        foreach ($bonusNumbers as $key => $bonusNumber) {
            $bonusNumberRule = LotteryNumber::build("bonusNumbers[$key]", "bonusNumbers[$key]");
            $this->addFieldRule($bonusNumberRule);
            $bonusRange = $lotteryType->bextra > 0 ? $lotteryType->nrange : $lotteryType->brange;
            $bonusNumberRule->addRule('numeric_between', 1, $bonusRange);
        }
    }

    /**
     * @param mixed ...$args
     * @return bool
     * @throws Exception
     */
    protected function extraChecks(...$args): bool
    {
        ['normalNumbers' => $normalNumbers, 'bonusNumbers' => $bonusNumbers] = $this->input;

        /** @var Lottery $lottery */
        $lottery = $args[0];
        /** @var LotteryType $lotteryType */
        $lotteryType = $lottery->lotteryType;

        $normalNumbersCount = count($normalNumbers);
        $wrongNormalNumbersCount = count($normalNumbers) !== $lotteryType->ncount;
        if ($wrongNormalNumbersCount) {
            $this->setErrors([
                'errors' => "Wrong normal numbers count. Current count: $normalNumbersCount, expected count: $lotteryType->ncount",
            ]);
            return false;
        }

        $bonusNumbersCount = count($bonusNumbers);
        $expectedBonusNumbersCount = $lotteryType->bextra > 0 ? $lotteryType->bextra : $lotteryType->bcount;
        $wrongBonusNumbersCount = count($bonusNumbers) !== $expectedBonusNumbersCount;
        if ($wrongBonusNumbersCount) {
            $this->setErrors([
                'errors' => "Wrong bonus numbers count. Current count: $bonusNumbersCount, expected count: $expectedBonusNumbersCount",
            ]);
            return false;
        }

        $areNormalNumbersNotUnique = count(array_unique($normalNumbers)) !== $normalNumbersCount;
        if ($areNormalNumbersNotUnique) {
            $this->setErrors([
                'errors' => 'There are some repeated normal numbers.'
            ]);
            return false;
        }

        $areBonusNumbersNotUnique = count(array_unique($bonusNumbers)) !== $bonusNumbersCount;
        if ($areBonusNumbersNotUnique) {
            $this->setErrors([
                'errors' => 'There are some repeated bonus numbers.'
            ]);
            return false;
        }

        return true;
    }

    /** Use this function to get numbers after validation */
    public function getValidatedProperties(array $properties = []): array
    {
        return [
            'normalNumbers' => $this->input['normalNumbers'],
            'bonusNumbers' => $this->input['bonusNumbers'],
        ];
    }
}
