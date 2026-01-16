<?php

namespace Tests\Unit\Validators\Rules;

use Models\Lottery;
use Models\LotteryType;
use Repositories\LotteryRepository;
use Test_Feature;
use Validators\LotteryTicketNumbersValidator;

class LotteryTicketNumbersValidatorTest extends Test_Feature
{
    private LotteryRepository $lotteryRepository;
    private Lottery $powerballLottery;
    private LotteryTicketNumbersValidator $validatorUnderTest;

    public function setUp(): void
    {
        parent::setUp();
        $this->lotteryRepository = $this->container->get(LotteryRepository::class);
        $this->powerballLottery = $this->lotteryRepository->findOneBySlug('powerball');
        $this->superenalottoLottery = $this->lotteryRepository->findOneBySlug('superenalotto');

        /** @var LotteryType $lotteryType */
        $lotteryType = $this->powerballLottery->lotteryType;
        $lotteryType->nrange = 69;
        $lotteryType->ncount = 5;
        $lotteryType->brange = 26;
        $lotteryType->bcount = 2;
        $lotteryType->save();

        $this->validatorUnderTest = $this->container->get(LotteryTicketNumbersValidator::class);
        $this->validatorUnderTest->setBuildArguments($this->powerballLottery);
        $this->validatorUnderTest->setExtraCheckArguments($this->powerballLottery);
    }

    /**
     * @test
     * @dataProvider lotteryNumbersProvider
     */
    public function isValid_combinedData(array $normalNumbers, array $bonusNumbers, bool $expectedIsValid): void
    {
        // Given
        $this->setInput('GET', [
            'numbers' => implode(',', $normalNumbers),
            'bnumbers' => implode(',', $bonusNumbers),
        ]);

        // When
        $actualIsValid = $this->validatorUnderTest->isValid();

        // Then
        $this->assertSame($expectedIsValid, $actualIsValid);
    }

    public function lotteryNumbersProvider(): array
    {
        return [
            'number cannot be 0' => [[0, 1, 2, 3, 5], [1, 2], false],
            'bonus number cannot be 0' => [[1, 2, 3, 4, 5], [0, 2], false],

            'correct numbers' => [[1, 2, 3, 4, 5], [1, 2], true],

            'too few numbers' => [[1, 2, 3, 4], [1, 2], false],
            'too few bonus numbers' => [[1, 2, 3, 4, 5], [1], false],

            'too many numbers' => [[1, 2, 3, 4, 5, 6], [1, 2], false],
            'too many bonus numbers' => [[1, 2, 3, 4, 5], [1, 2, 3], false],

            'above range' => [[70, 2, 3, 4, 5], [1, 2], false],
            'max number' => [[1, 2, 3, 4, 69], [1, 2], true],

            'above bonus range' => [[1, 2, 3, 4, 5], [1, 27], false],
            'max bonus range' => [[1, 2, 3, 4, 5], [25, 26], true],

            'repeated number' => [[1, 1, 3, 4, 5], [1, 26], false],
            'repeated bonus number' => [[1, 2, 3, 4, 5], [26, 26], false],
        ];
    }

    /**
     * @test
     * @dataProvider lotteryWithoutBonusNumbersProvider
     */
    public function isValid_lotteryWithoutBonusNumbers_combinedData(
        array $normalNumbers,
        array $bonusNumbers,
        bool $expectedIsValid
    ): void {
        // Given
        /** @var LotteryType $lotteryType */
        $lotteryType = $this->superenalottoLottery->lotteryType;
        $lotteryType->nrange = 69;
        $lotteryType->ncount = 5;
        $lotteryType->brange = 26;
        $lotteryType->bcount = 0;
        $lotteryType->bextra = 0;
        $lotteryType->save();

        $this->validatorUnderTest->setBuildArguments($this->superenalottoLottery);
        $this->validatorUnderTest->setExtraCheckArguments($this->superenalottoLottery);

        $this->setInput('GET', [
            'numbers' => implode(',', $normalNumbers),
            'bnumbers' => implode(',', $bonusNumbers),
        ]);

        // When
        $actualIsValid = $this->validatorUnderTest->isValid();

        // Then
        $this->assertSame($expectedIsValid, $actualIsValid);
    }

    public function lotteryWithoutBonusNumbersProvider(): array
    {
        return [
            'number cannot be 0' => [[0, 1, 2, 3, 5], [], false],
            'bonus number cannot be 0' => [[1, 2, 3, 4, 5], [0], false],

            'correct numbers' => [[1, 2, 3, 4, 5], [], true],

            'too few numbers' => [[1, 2, 3, 4], [], false],

            'too many numbers' => [[1, 2, 3, 4, 5, 6], [], false],
            'too many bonus numbers' => [[1, 2, 3, 4, 5], [1], false],

            'above range' => [[70, 2, 3, 4, 5], [], false],
            'max number' => [[1, 2, 3, 4, 69], [], true],

            'above bonus range' => [[1, 2, 3, 4, 5], [27], false],

            'repeated number' => [[1, 1, 3, 4, 5], [], false],
            'repeated bonus number' => [[1, 2, 3, 4, 5], [26, 26], false],
        ];
    }

    /**
     * @test
     * @dataProvider lotteryWithBonusNumbersProvider
     */
    public function isValid_lotteryWithExtraBonusNumbers_combinedData(
        array $normalNumbers,
        array $bonusNumbers,
        bool $expectedIsValid
    ): void {
        // Given
        /** @var LotteryType $lotteryType */
        $lotteryType = $this->superenalottoLottery->lotteryType;
        $lotteryType->nrange = 69;
        $lotteryType->ncount = 5;
        $lotteryType->brange = 26;
        $lotteryType->bcount = 0;
        $lotteryType->bextra = 5;
        $lotteryType->save();

        $this->validatorUnderTest->setBuildArguments($this->superenalottoLottery);
        $this->validatorUnderTest->setExtraCheckArguments($this->superenalottoLottery);

        $this->setInput('GET', [
            'numbers' => implode(',', $normalNumbers),
            'bnumbers' => implode(',', $bonusNumbers),
        ]);

        // When
        $actualIsValid = $this->validatorUnderTest->isValid();

        // Then
        $this->assertSame($expectedIsValid, $actualIsValid);
    }

    public function lotteryWithBonusNumbersProvider(): array
    {
        return [
            'correct numbers' => [[1, 2, 3, 4, 5], [1, 2, 3, 4, 5], true],
            'too few bonus numbers' => [[1, 2, 3, 4, 5], [1, 2, 3, 4], false],
            'too many bonus numbers' => [[1, 2, 3, 4, 5], [1, 2, 3, 4, 5, 6], false],
        ];
    }
}
