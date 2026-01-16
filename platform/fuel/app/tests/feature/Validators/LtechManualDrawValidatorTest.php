<?php

namespace Tests\Unit\Validators\Rules;

use Carbon\Carbon;
use Helpers_Time;
use Lotto_Security;
use Models\AdminUser;
use Models\Lottery;
use Repositories\AdminUserRepository;
use Repositories\LotteryRepository;
use Test_Feature;
use Validators\LtechManualDrawValidator;

class LtechManualDrawValidatorTest extends Test_Feature
{
    private LotteryRepository $lotteryRepository;
    private const CRM_SUPERADMIN_PASSWORD = 'abcdef';
    private Lottery $powerballLottery;
    private AdminUserRepository $adminUserRepository;
    private AdminUser $superadmin;

    public function setUp(): void
    {
        parent::setUp();

        $this->lotteryRepository = $this->container->get(LotteryRepository::class);
        $this->powerballLottery = $this->lotteryRepository->findOneBySlug('powerball');
        $nextDateLocal = (Carbon::now($this->powerballLottery->timezone))->subDays(2);
        $this->powerballLottery->nextDateLocal = $nextDateLocal;
        $this->powerballLottery->nextDateUtc = $nextDateLocal->setTimezone('UTC');
        $this->powerballLottery->save();

        $this->adminUserRepository = $this->container->get(AdminUserRepository::class);
        $superadmin = $this->adminUserRepository->findOneById(1);
        $superadmin->username = 'blacklotto';
        $superadmin->hash = Lotto_Security::generate_hash(self::CRM_SUPERADMIN_PASSWORD, $superadmin->salt);
        $superadmin->save();
        $this->superadmin = $superadmin;
    }

    /**
     * @test
     * @dataProvider isValidDataProvider
     * @runInSeparateProcess
     */
    public function isValid(array $overwriteJsonInput, bool $expectedIsValid, array $expectedErrors = null): void
    {
        // Given
        $preparedJsonInput = array_merge($this->getCorrectInput(), $overwriteJsonInput);
        $isCurrentLotteryOverwritten = array_key_exists('currentLottery', $overwriteJsonInput);
        if ($isCurrentLotteryOverwritten) {
            $preparedJsonInput['currentLottery'] = array_merge(
                $this->getCorrectInput()['currentLottery'],
                $overwriteJsonInput['currentLottery']
            );
        }

        $this->setInput('json', $preparedJsonInput);

        // It's needed to set input firstly
        $validatorUnderTest = $this->container->get(LtechManualDrawValidator::class);

        // When
        $actualIsValid = $validatorUnderTest->isValid();

        // Then
        $this->assertSame($expectedIsValid, $actualIsValid);
        if (!$expectedIsValid) {
            $this->assertSame($expectedErrors, $validatorUnderTest->getErrors());
        }
    }

    public function isValidDataProvider(): array
    {
        return [
            'all correct' => [[], true],
            'wrong char slug' => [
                ['currentLottery' => ['slug' => '!!']],
                false,
                [
                    'currentLottery.slug' => 'The field lotterySlug contains invalid characters.',
                ]
            ],
            'too long currency_code' => [
                ['currentLottery' => ['currency_code' => 'ABCD']],
                false,
                [
                    'currentLottery.currency_code' => 'The field currencyCode must contain exactly 3 characters.',
                ]
            ],
            'wrong char timezone' => [
                ['currentLottery' => ['timezone' => '!!']],
                false,
                [
                    'currentLottery.timezone' => 'The field timezone contains invalid characters.',
                ]
            ],
            'wrong format next_date_local' => [
                ['currentLottery' => ['next_date_local' => '1']],
                false,
                [
                    'currentLottery.next_date_local' => 'The field lotteryCurrentDrawDate must contain a valid formatted date.',
                ]
            ],
            'zero nextJackpot' => [
                ['nextJackpot' => 0],
                true,
            ],
            'minus next_jackpot' => [
                ['nextJackpot' => -1],
                false,
                [
                    'nextJackpot' => 'Wrong balance amount',
                ]
            ],
            'too short password' => [
                ['password' => 'abc'],
                false,
                [
                    'password' => 'The field password has to contain at least 6 characters.',
                ]
            ],
            'wrong format nextDrawDate' => [
                ['nextDrawDate' => '2'],
                false,
                [
                    'nextDrawDate' => 'The field nextDrawDate must contain a valid formatted date.',
                ]
            ],
            'nullable additionalNumber' => [
                ['additionalNumber' => null],
                true,
            ],
            'additionalNumber above range' => [
                ['additionalNumber' => 10],
                false,
                [
                    'additionalNumber' => 'The maximum numeric value of additionalNumber must be 9'
                ]
            ],
            'not a number in prizes' => [
                ['prizes' => [
                    'match' => 'abcd'
                ]],
                false,
                [
                    'errors' => 'Wrong prizes'
                ]
            ],
            'not a number in winners' => [
                ['winners' => [
                    'match' => 'abcd'
                ]],
                false,
                [
                    'errors' => 'Wrong winners'
                ]
            ],
            'wrong lottery slug' => [
                ['currentLottery' => [
                    'slug' => 'abcd'
                ]],
                false,
                [
                    'errors' => 'Wrong lottery slug'
                ]
            ],
            'wrong timezone' => [
                ['currentLottery' => [
                    'timezone' => 'Europe/Kalisz'
                ]],
                false,
                [
                    'errors' => 'Wrong timezone'
                ]
            ],
            'wrong currency code' => [
                ['currentLottery' => [
                    'currency_code' => 'PLN'
                ]],
                false,
                [
                    'errors' => 'Wrong currency code'
                ]
            ],
            'inconsistent nextDrawDate' => [
                ['currentLottery' => [
                    'next_date_local' => '1997-01-01 00:00:00',
                ]],
                false,
                [
                    'errors' => 'Lottery has changed',
                ]
            ],
            'nextDrawDate before nextDateLocal' => [
                ['nextDrawDate' => '1997-01-01 00:00:00'],
                false,
                [
                    'errors' => 'Next draw should be after current draw',
                ]
            ],
            'wrong password' => [
                ['password' => 'qweqwe'],
                false,
                [
                    'errors' => 'Unauthorized',
                ]
            ],
        ];
    }

    private function getCorrectInput(): array
    {
        return [
            'currentLottery' => [
                'slug' => $this->powerballLottery->slug,
                'next_date_local' => $this->powerballLottery->nextDateLocal->format(Helpers_Time::DATETIME_FORMAT),
                'currency_code' => $this->powerballLottery->currency->code,
                'timezone' => $this->powerballLottery->timezone,
            ],
            'nextJackpot' => '2000000',
            'prizes' => [
                'match-5-1' => 10,
                'match 5-0' => 15,
                'match-4-1' => 20,
                'match 4-0' => 25,
                'match-3-1' => 30,
                'match 3-0' => 35,
                'match 2-1' => 40,
                'match 1-1' => 45,
                'match 0-1' => 50,
            ],
            'winners' => [
                'match-5-1' => 0,
                'match 5-0' => 1,
                'match-4-1' => 2,
                'match 4-0' => 3,
                'match-3-1' => 4,
                'match 3-0' => 5,
                'match 2-1' => 6,
                'match 1-1' => 7,
                'match 0-1' => 8,
            ],
            'password' => self::CRM_SUPERADMIN_PASSWORD,
            'nextDrawDate' => $this->powerballLottery->nextDateLocal->addDay()->format(Helpers_Time::DATETIME_FORMAT),
        ];
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function nextDrawInTheFuture_isInvalid(): void
    {
        // Given
        $this->powerballLottery->nextDateLocal = (Carbon::now($this->powerballLottery->timezone))->addDay();
        $this->powerballLottery->save();

        $this->setInput('json', $this->getCorrectInput());

        // It's needed to set input firstly
        $validatorUnderTest = $this->container->get(LtechManualDrawValidator::class);

        // When
        $actualIsValid = $validatorUnderTest->isValid();

        // Then
        $this->assertFalse($actualIsValid);
        $expectedErrorMessage = ['errors' => 'Next draw is in the future'];
        $this->assertSame($expectedErrorMessage, $validatorUnderTest->getErrors());
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function unauthorizedByUsername(): void
    {
        // Given
        $this->superadmin->username = 'test';
        $this->superadmin->save();

        $this->setInput('json', $this->getCorrectInput());

        // It's needed to set input firstly
        $validatorUnderTest = $this->container->get(LtechManualDrawValidator::class);

        // When
        $actualIsValid = $validatorUnderTest->isValid();

        // Then
        $this->assertFalse($actualIsValid);
        $expectedErrorMessage = ['errors' => 'Unauthorized'];
        $this->assertSame($expectedErrorMessage, $validatorUnderTest->getErrors());
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function getPrizes(): void
    {
        // Given
        $this->setInput('json', $this->getCorrectInput());

        // It's needed to set input firstly
        $validatorUnderTest = $this->container->get(LtechManualDrawValidator::class);

        // When
        $actualIsValid = $validatorUnderTest->isValid();

        // Then
        $this->assertTrue($actualIsValid);
        $expectedPrizes = [
            'match-5-1' => 10.0,
            'match 5-0' => 15.0,
            'match-4-1' => 20.0,
            'match 4-0' => 25.0,
            'match-3-1' => 30.0,
            'match 3-0' => 35.0,
            'match 2-1' => 40.0,
            'match 1-1' => 45.0,
            'match 0-1' => 50.0,
        ];
        $this->assertSame($expectedPrizes, $validatorUnderTest->getPrizes());
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function getWinners(): void
    {
        // Given
        $this->setInput('json', $this->getCorrectInput());

        // It's needed to set input firstly
        $validatorUnderTest = $this->container->get(LtechManualDrawValidator::class);

        // When
        $actualIsValid = $validatorUnderTest->isValid();

        // Then
        $this->assertTrue($actualIsValid);
        $expectedWinners = [
            'match-5-1' => 0,
            'match 5-0' => 1,
            'match-4-1' => 2,
            'match 4-0' => 3,
            'match-3-1' => 4,
            'match 3-0' => 5,
            'match 2-1' => 6,
            'match 1-1' => 7,
            'match 0-1' => 8,
        ];
        $this->assertSame($expectedWinners, $validatorUnderTest->getWinners());
    }
}
