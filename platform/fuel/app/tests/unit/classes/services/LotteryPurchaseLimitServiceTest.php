<?php

namespace Tests\Unit\Classes\Services;

use ErrorException;
use Models\Lottery;
use Models\WhitelabelLottery;
use Models\WhitelabelLotteryPurchaseLimit;
use Repositories\WhitelabelLotteryPurchaseLimitRepository;
use Repositories\WhitelabelLotteryRepository;
use Services\LotteryPurchaseLimitService;
use Test_Unit;

/**
 * @covers LotteryPurchaseLimitService
 */
class LotteryPurchaseLimitServiceTest extends Test_Unit
{
    private WhitelabelLotteryRepository $whitelabelLotteryRepository;
    private WhitelabelLotteryPurchaseLimitRepository $whitelabelLotteryPurchaseLimitRepository;
    private array $basicBasketOneLineData;

    public function setUp(): void
    {
        $this->whitelabelLotteryRepository = $this->createMock(WhitelabelLotteryRepository::class);
        $this->whitelabelLotteryRepository->method('withRelation')->willReturn($this->whitelabelLotteryRepository);
        $this->whitelabelLotteryPurchaseLimitRepository = $this->createMock(WhitelabelLotteryPurchaseLimitRepository::class);
        $basketRawData = '
        [
           {
              "lottery":"3",
              "lines":[
                 {
                    "numbers":[
                       "6",
                       "12",
                       "16",
                       "30",
                       "43"
                    ],
                    "bnumbers":[
                       "1",
                       "2"
                    ]
                 }
              ],
              "ticket_multiplier":1
           }
        ]
        ';

        $this->basicBasketOneLineData = json_decode($basketRawData, true);
    }

    /** @test */
    public function isAllowedToPurchaseBasket_WhitelabelNotPassed_ThrowsException(): void
    {
        $lotteryPurchaseLimitService = new LotteryPurchaseLimitService($this->whitelabelLotteryRepository, $this->whitelabelLotteryPurchaseLimitRepository);
        $this->expectExceptionMessage('Whitelabel has to configured before using this method');
        $lotteryPurchaseLimitService->isAllowedToPurchaseBasketUsingBonusBalance($this->basicBasketOneLineData);
    }

    /** @test */
    public function isAllowedToPurchaseBasket_ThrowsExceptionWhenWhitelabelNotSet(): void
    {
        $lotteryPurchaseLimitService = new LotteryPurchaseLimitService($this->whitelabelLotteryRepository, $this->whitelabelLotteryPurchaseLimitRepository);
        $lotteryPurchaseLimitService->setWhitelabelId(1);
        $this->expectExceptionMessage('User has to be configured before using this method');
        $lotteryPurchaseLimitService->isAllowedToPurchaseBasketUsingBonusBalance($this->basicBasketOneLineData);
    }

    /** @test */
    public function isAllowedToPurchaseBasket_EmptyBasket(): void
    {
        $lotteryPurchaseLimitService = new LotteryPurchaseLimitService($this->whitelabelLotteryRepository, $this->whitelabelLotteryPurchaseLimitRepository);
        $lotteryPurchaseLimitService->setUserId(1);
        $lotteryPurchaseLimitService->setWhitelabelId(1);

        $this->expectExceptionMessage('Basket is empty, nothing to purchase');
        $lotteryPurchaseLimitService->isAllowedToPurchaseBasketUsingBonusBalance([]);
    }

    /** @test */
    public function isAllowedToPurchaseBasket_Allowed(): void
    {
        $basketRawData = '
        [
           {
              "lottery":"3",
              "lines":[
                 {
                    "numbers":[
                       "6",
                       "12",
                       "16",
                       "30",
                       "43"
                    ],
                    "bnumbers":[
                       "1",
                       "2"
                    ]
                 }
              ],
              "ticket_multiplier":1
           }
        ]
        ';

        $basketData = json_decode($basketRawData, true);
        $userId = 7;
        $whitelabelId = 1;

        $whitelabelLottery = new WhitelabelLottery();
        $whitelabelLottery->is_bonus_balance_in_use = true;
        $whitelabelLottery->bonusBalancePurchaseLimitPerUser = 2;
        $whitelabelLottery->id = 6; // whitelabel_lottery = 6 -> lottery = 3
        $this->whitelabelLotteryRepository->method('getOneByLotteryIdForWhitelabel')->with(3, 1)->willReturn($whitelabelLottery);


        $lotteryPurchaseLimitService = new LotteryPurchaseLimitService($this->whitelabelLotteryRepository, $this->whitelabelLotteryPurchaseLimitRepository);
        $lotteryPurchaseLimitService->setUserId($userId);
        $lotteryPurchaseLimitService->setWhitelabelId($whitelabelId);
        $isAllowedToPurchaseBasket = $lotteryPurchaseLimitService->isAllowedToPurchaseBasketUsingBonusBalance($basketData);

        $this->assertTrue($isAllowedToPurchaseBasket);
    }

    /** @test */
    public function isAllowedToPurchaseBasket_AboveLimit_NotAllowed(): void
    {
        $basketRawData = '
        [
           {
              "lottery":"3",
              "lines":[
                 {
                    "numbers":[
                       30,
                       34,
                       14,
                       27,
                       9
                    ],
                    "bnumbers":[
                       3,
                       7
                    ]
                 },
                 {
                    "numbers":[
                       27,
                       36,
                       46,
                       35,
                       16
                    ],
                    "bnumbers":[
                       7,
                       10
                    ]
                 }
              ]
           }
        ]
        ';

        $basketData = json_decode($basketRawData, true);
        $userId = 7;
        $whitelabelId = 1;

        $lottery = new Lottery();
        $lottery->name = 'Eurojackpot';

        $whitelabelLottery = new WhitelabelLottery();
        $whitelabelLottery->is_bonus_balance_in_use = true;
        $whitelabelLottery->bonusBalancePurchaseLimitPerUser = 1;
        $whitelabelLottery->id = 6; // whitelabel_lottery = 6 -> lottery = 3
        $whitelabelLottery->lottery = $lottery;
        $this->whitelabelLotteryRepository->method('getOneByLotteryIdForWhitelabel')->with(3, 1)->willReturn($whitelabelLottery);

        $lotteryPurchaseLimitService = new LotteryPurchaseLimitService($this->whitelabelLotteryRepository, $this->whitelabelLotteryPurchaseLimitRepository);
        $lotteryPurchaseLimitService->setUserId($userId);
        $lotteryPurchaseLimitService->setWhitelabelId($whitelabelId);
        $isAllowedToPurchaseBasket = $lotteryPurchaseLimitService->isAllowedToPurchaseBasketUsingBonusBalance($basketData);

        $this->assertFalse($isAllowedToPurchaseBasket);
    }

    /** @test */
    public function isAllowedToPurchaseBasket_TwoDifferentLotteriesBelowLimit_AllowedToPurchase(): void
    {
        $basketRawData = '
        [
           {
              "lottery":"3",
              "lines":[
                 {
                    "numbers":[
                       30,
                       34,
                       14,
                       27,
                       9
                    ],
                    "bnumbers":[
                       3,
                       7
                    ]
                 }
              ]
           },
           {
              "lottery":"1",
              "lines":[
                 {
                    "numbers":[
                       64,
                       44,
                       11,
                       62,
                       25
                    ],
                    "bnumbers":[
                       1
                    ]
                 }
              ]
           }
        ]
        ';

        $basketData = json_decode($basketRawData, true);
        $userId = 7;
        $whitelabelId = 1;

        $whitelabelLottery = new WhitelabelLottery();
        $whitelabelLottery->is_bonus_balance_in_use = true;
        $whitelabelLottery->bonusBalancePurchaseLimitPerUser = 1;
        $whitelabelLottery->id = 6; // whitelabel_lottery = 6 -> lottery = 3

        $whitelabelLottery2 = new WhitelabelLottery();
        $whitelabelLottery2->is_bonus_balance_in_use = true;
        $whitelabelLottery2->bonusBalancePurchaseLimitPerUser = 2;
        $whitelabelLottery2->id = 9; // whitelabel_lottery = 9 -> lottery = 1
        $this->whitelabelLotteryRepository->expects($this->exactly(2))->method('getOneByLotteryIdForWhitelabel')->withConsecutive([3, 1], [1, 1])->will($this->onConsecutiveCalls($whitelabelLottery, $whitelabelLottery2));

        $lotteryPurchaseLimitService = new LotteryPurchaseLimitService($this->whitelabelLotteryRepository, $this->whitelabelLotteryPurchaseLimitRepository);
        $lotteryPurchaseLimitService->setUserId($userId);
        $lotteryPurchaseLimitService->setWhitelabelId($whitelabelId);
        $isAllowedToPurchaseBasket = $lotteryPurchaseLimitService->isAllowedToPurchaseBasketUsingBonusBalance($basketData);

        $this->assertTrue($isAllowedToPurchaseBasket);

        /**
         * Verify that user limit counter is sent properly to persist.
         * Here isAllowedToPurchaseBasketUsingBonusBalance returns true, so all lotteries are sent to update counter
         */
        $whitelabelLotteryPurchaseLimit = new WhitelabelLotteryPurchaseLimit();
        $whitelabelLotteryPurchaseLimit->counter = 1;
        $whitelabelLotteryPurchaseLimit->whitelabelLotteryId = $whitelabelLottery->id;
        $whitelabelLotteryPurchaseLimit->whitelabelUserId = $userId;

        $whitelabelLotteryPurchaseLimit2 = new WhitelabelLotteryPurchaseLimit();
        $whitelabelLotteryPurchaseLimit2->counter = 1;
        $whitelabelLotteryPurchaseLimit2->whitelabelLotteryId = $whitelabelLottery2->id;
        $whitelabelLotteryPurchaseLimit2->whitelabelUserId = $userId;

        $this->whitelabelLotteryPurchaseLimitRepository->expects($this->once())->method('insertOrUpdateEntries')->with([$whitelabelLotteryPurchaseLimit, $whitelabelLotteryPurchaseLimit2])->willReturn(true);
        $isUpdatedCorrectly = $lotteryPurchaseLimitService->addOrUpdatePurchaseLimitEntriesForAllowedBasket();
        $this->assertTrue($isUpdatedCorrectly);
    }

    /** @test */
    public function isAllowedToPurchaseBasket_TwoDifferentLotteriesAndOneLotteryAboveLimit_NotAllowedToPurchase(): void
    {
        $basketRawData = '
        [
           {
              "lottery":"3",
              "lines":[
                 {
                    "numbers":[
                       30,
                       34,
                       14,
                       27,
                       9
                    ],
                    "bnumbers":[
                       3,
                       7
                    ]
                 },
                 {
                    "numbers":[
                       30,
                       34,
                       14,
                       27,
                       9
                    ],
                    "bnumbers":[
                       3,
                       7
                    ]
                 }
              ]
           },
           {
              "lottery":"1",
              "lines":[
                 {
                    "numbers":[
                       64,
                       44,
                       11,
                       62,
                       25
                    ],
                    "bnumbers":[
                       1
                    ]
                 }
              ]
           }
        ]
        ';

        $basketData = json_decode($basketRawData, true);
        $userId = 7;
        $whitelabelId = 1;

        $lottery = new Lottery();
        $lottery->name = 'Eurojackpot';

        $lottery2 = new Lottery();
        $lottery2->name = 'Powerball';

        $whitelabelLottery = new WhitelabelLottery();
        $whitelabelLottery->is_bonus_balance_in_use = true;
        $whitelabelLottery->bonusBalancePurchaseLimitPerUser = 1;
        $whitelabelLottery->id = 6; // whitelabel_lottery = 6 -> lottery = 3
        $whitelabelLottery->lottery = $lottery;

        $whitelabelLottery2 = new WhitelabelLottery();
        $whitelabelLottery2->is_bonus_balance_in_use = true;
        $whitelabelLottery2->bonusBalancePurchaseLimitPerUser = 2;
        $whitelabelLottery2->id = 9; // whitelabel_lottery = 9 -> lottery = 1
        $whitelabelLottery2->lottery = $lottery2;
        $this->whitelabelLotteryRepository->expects($this->once())->method('getOneByLotteryIdForWhitelabel')->with(3, 1)->willReturn($whitelabelLottery);

        $lotteryPurchaseLimitService = new LotteryPurchaseLimitService($this->whitelabelLotteryRepository, $this->whitelabelLotteryPurchaseLimitRepository);
        $lotteryPurchaseLimitService->setUserId($userId);
        $lotteryPurchaseLimitService->setWhitelabelId($whitelabelId);
        $isAllowedToPurchaseBasket = $lotteryPurchaseLimitService->isAllowedToPurchaseBasketUsingBonusBalance($basketData);

        $this->assertFalse($isAllowedToPurchaseBasket);

        /**
         * User is not allowed to buy one of the lottery tickets. Nothing should be updated
         */
        $this->whitelabelLotteryPurchaseLimitRepository->expects($this->never())->method('insertOrUpdateEntries');
        $isUpdatedCorrectly = $lotteryPurchaseLimitService->addOrUpdatePurchaseLimitEntriesForAllowedBasket();
        $this->assertFalse($isUpdatedCorrectly);
    }

    /** @test */
    public function isAllowedToPurchaseBasket_TwoDifferentLotteriesAndOneLotteryAboveLimit_NotAllowedToPurchase1(): void
    {
        $basketRawData = '
        [
            {
              "lottery":"1",
              "lines":[
                 {
                    "numbers":[
                       64,
                       44,
                       11,
                       62,
                       25
                    ],
                    "bnumbers":[
                       1
                    ]
                 }
              ]
           },
           {
              "lottery":"3",
              "lines":[
                 {
                    "numbers":[
                       30,
                       34,
                       14,
                       27,
                       9
                    ],
                    "bnumbers":[
                       3,
                       7
                    ]
                 },
                 {
                    "numbers":[
                       30,
                       34,
                       14,
                       27,
                       9
                    ],
                    "bnumbers":[
                       3,
                       7
                    ]
                 }
              ]
           }
        ]
        ';

        $basketData = json_decode($basketRawData, true);
        $userId = 7;
        $whitelabelId = 1;

        $lottery = new Lottery();
        $lottery->name = 'Eurojackpot';

        $lottery2 = new Lottery();
        $lottery2->name = 'Powerball';

        $whitelabelLottery = new WhitelabelLottery();
        $whitelabelLottery->is_bonus_balance_in_use = true;
        $whitelabelLottery->bonusBalancePurchaseLimitPerUser = 1;
        $whitelabelLottery->id = 6; // whitelabel_lottery = 6 -> lottery = 3
        $whitelabelLottery->lottery = $lottery;

        $whitelabelLottery2 = new WhitelabelLottery();
        $whitelabelLottery2->is_bonus_balance_in_use = true;
        $whitelabelLottery2->bonusBalancePurchaseLimitPerUser = 2;
        $whitelabelLottery2->id = 9; // whitelabel_lottery = 9 -> lottery = 1
        $whitelabelLottery2->lottery = $lottery2;
        $this->whitelabelLotteryRepository->expects($this->exactly(2))->method('getOneByLotteryIdForWhitelabel')->withConsecutive([1, 1], [3, 1])->will($this->onConsecutiveCalls($whitelabelLottery2, $whitelabelLottery));

        $lotteryPurchaseLimitService = new LotteryPurchaseLimitService($this->whitelabelLotteryRepository, $this->whitelabelLotteryPurchaseLimitRepository);
        $lotteryPurchaseLimitService->setUserId($userId);
        $lotteryPurchaseLimitService->setWhitelabelId($whitelabelId);
        $isAllowedToPurchaseBasket = $lotteryPurchaseLimitService->isAllowedToPurchaseBasketUsingBonusBalance($basketData);

        $this->assertFalse($isAllowedToPurchaseBasket);

        /**
         * User is not allowed to buy one of the lottery tickets. Nothing should be updated
         */
        $this->whitelabelLotteryPurchaseLimitRepository->expects($this->never())->method('insertOrUpdateEntries');
        $isUpdatedCorrectly = $lotteryPurchaseLimitService->addOrUpdatePurchaseLimitEntriesForAllowedBasket();
        $this->assertFalse($isUpdatedCorrectly);
    }

    /** @test */
    public function isAllowedToPurchaseBasket_TwoBasketItemsForSameLotteryAndSumIsAboveLimit_NotAllowedToPurchase(): void
    {
        $basketRawData = '
        [
           {
              "lottery":"3",
              "lines":[
                 {
                    "numbers":[
                       30,
                       34,
                       14,
                       27,
                       9
                    ],
                    "bnumbers":[
                       3,
                       7
                    ]
                 },
                 {
                    "numbers":[
                       30,
                       34,
                       14,
                       27,
                       9
                    ],
                    "bnumbers":[
                       3,
                       7
                    ]
                 }
              ]
           },
           {
              "lottery":"3",
              "lines":[
                 {
                    "numbers":[
                       64,
                       44,
                       11,
                       62,
                       25
                    ],
                    "bnumbers":[
                       1
                    ]
                 }
              ]
           }
        ]
        ';

        $basketData = json_decode($basketRawData, true);
        $userId = 7;
        $whitelabelId = 1;

        $lottery = new Lottery();
        $lottery->name = 'Eurojackpot';

        $whitelabelLottery = new WhitelabelLottery();
        $whitelabelLottery->is_bonus_balance_in_use = true;
        $whitelabelLottery->bonusBalancePurchaseLimitPerUser = 2;
        $whitelabelLottery->id = 6; // whitelabel_lottery = 6 -> lottery = 3
        $whitelabelLottery->lottery = $lottery;

        $this->whitelabelLotteryRepository->expects($this->once())->method('getOneByLotteryIdForWhitelabel')->with(3, 1)->willReturn($whitelabelLottery);

        $lotteryPurchaseLimitService = new LotteryPurchaseLimitService($this->whitelabelLotteryRepository, $this->whitelabelLotteryPurchaseLimitRepository);
        $lotteryPurchaseLimitService->setUserId($userId);
        $lotteryPurchaseLimitService->setWhitelabelId($whitelabelId);
        $isAllowedToPurchaseBasket = $lotteryPurchaseLimitService->isAllowedToPurchaseBasketUsingBonusBalance($basketData);

        $this->assertFalse($isAllowedToPurchaseBasket);
    }

    /** @test */
    public function isAllowedToPurchaseBasket_NumberOfTicketsBelowLimitAndHadAlreadyPurchased_AllowedToPurchase(): void
    {
        $basketRawData = '
        [
           {
              "lottery":"3",
              "lines":[
                 {
                    "numbers":[
                       30,
                       34,
                       14,
                       27,
                       9
                    ],
                    "bnumbers":[
                       3,
                       7
                    ]
                 },
                 {
                    "numbers":[
                       30,
                       34,
                       14,
                       27,
                       9
                    ],
                    "bnumbers":[
                       3,
                       7
                    ]
                 }
              ]
           },
           {
              "lottery":"3",
              "lines":[
                 {
                    "numbers":[
                       64,
                       44,
                       11,
                       62,
                       25
                    ],
                    "bnumbers":[
                       1
                    ]
                 }
              ]
           }
        ]
        ';

        $basketData = json_decode($basketRawData, true);
        $userId = 7;
        $whitelabelId = 1;

        $whitelabelLottery = new WhitelabelLottery();
        $whitelabelLottery->is_bonus_balance_in_use = true;
        $whitelabelLottery->bonusBalancePurchaseLimitPerUser = 5;
        $whitelabelLottery->id = 6; // whitelabel_lottery = 6 -> lottery = 3

        $this->whitelabelLotteryRepository->expects($this->once())->method('getOneByLotteryIdForWhitelabel')->with(3, 1)->willReturn($whitelabelLottery);
        $whitelabelLotteryPurchaseLimit = new WhitelabelLotteryPurchaseLimit();
        $whitelabelLotteryPurchaseLimit->counter = 2;
        $this->whitelabelLotteryPurchaseLimitRepository->expects($this->once())->method('findOneByUserIdAndWhitelabelLotteryId')->with($userId, $whitelabelLottery->id)->willReturn($whitelabelLotteryPurchaseLimit);

        $lotteryPurchaseLimitService = new LotteryPurchaseLimitService($this->whitelabelLotteryRepository, $this->whitelabelLotteryPurchaseLimitRepository);
        $lotteryPurchaseLimitService->setUserId($userId);
        $lotteryPurchaseLimitService->setWhitelabelId($whitelabelId);
        $isAllowedToPurchaseBasket = $lotteryPurchaseLimitService->isAllowedToPurchaseBasketUsingBonusBalance($basketData);

        $this->assertTrue($isAllowedToPurchaseBasket);
    }

    /** @test */
    public function isAllowedToPurchaseBasket_NumberOfTicketsAboveLimitAndHadAlreadyPurchased_NotAllowedToPurchase(): void
    {
        $basketRawData = '
        [
           {
              "lottery":"3",
              "lines":[
                 {
                    "numbers":[
                       30,
                       34,
                       14,
                       27,
                       9
                    ],
                    "bnumbers":[
                       3,
                       7
                    ]
                 },
                 {
                    "numbers":[
                       30,
                       34,
                       14,
                       27,
                       9
                    ],
                    "bnumbers":[
                       3,
                       7
                    ]
                 }
              ]
           },
           {
              "lottery":"3",
              "lines":[
                 {
                    "numbers":[
                       64,
                       44,
                       11,
                       62,
                       25
                    ],
                    "bnumbers":[
                       1
                    ]
                 }
              ]
           }
        ]
        ';

        $basketData = json_decode($basketRawData, true);
        $userId = 7;
        $whitelabelId = 1;

        $lottery = new Lottery();
        $lottery->name = 'Eurojackpot';

        $whitelabelLottery = new WhitelabelLottery();
        $whitelabelLottery->is_bonus_balance_in_use = true;
        $whitelabelLottery->bonusBalancePurchaseLimitPerUser = 4;
        $whitelabelLottery->id = 6; // whitelabel_lottery = 6 -> lottery = 3
        $whitelabelLottery->lottery = $lottery;

        $this->whitelabelLotteryRepository->expects($this->once())->method('getOneByLotteryIdForWhitelabel')->with(3, 1)->willReturn($whitelabelLottery);
        $whitelabelLotteryPurchaseLimit = new WhitelabelLotteryPurchaseLimit();
        $whitelabelLotteryPurchaseLimit->counter = 2;
        $this->whitelabelLotteryPurchaseLimitRepository->expects($this->once())->method('findOneByUserIdAndWhitelabelLotteryId')->with($userId, $whitelabelLottery->id)->willReturn($whitelabelLotteryPurchaseLimit);

        $lotteryPurchaseLimitService = new LotteryPurchaseLimitService($this->whitelabelLotteryRepository, $this->whitelabelLotteryPurchaseLimitRepository);
        $lotteryPurchaseLimitService->setUserId($userId);
        $lotteryPurchaseLimitService->setWhitelabelId($whitelabelId);
        $isAllowedToPurchaseBasket = $lotteryPurchaseLimitService->isAllowedToPurchaseBasketUsingBonusBalance($basketData);

        $this->assertFalse($isAllowedToPurchaseBasket);
    }

    /** @test */
    public function isAllowedToPurchaseBasket_BonusBalancePurchasesNotEnabledForLottery_NotAllowed(): void
    {
        $basketRawData = '
        [
           {
              "lottery":"3",
              "lines":[
                 {
                    "numbers":[
                       64,
                       44,
                       11,
                       62,
                       25
                    ],
                    "bnumbers":[
                       1
                    ]
                 }
              ]
           }
        ]
        ';

        $basketData = json_decode($basketRawData, true);
        $userId = 7;
        $whitelabelId = 1;

        $whitelabelLottery = new WhitelabelLottery();
        $whitelabelLottery->is_bonus_balance_in_use = false;
        $whitelabelLottery->bonusBalancePurchaseLimitPerUser = 4;
        $whitelabelLottery->id = 6; // whitelabel_lottery = 6 -> lottery = 3

        $this->whitelabelLotteryRepository->expects($this->once())->method('getOneByLotteryIdForWhitelabel')->with(3, 1)->willReturn($whitelabelLottery);

        $lotteryPurchaseLimitService = new LotteryPurchaseLimitService($this->whitelabelLotteryRepository, $this->whitelabelLotteryPurchaseLimitRepository);
        $lotteryPurchaseLimitService->setUserId($userId);
        $lotteryPurchaseLimitService->setWhitelabelId($whitelabelId);

        $this->expectException(ErrorException::class);
        $lotteryPurchaseLimitService->isAllowedToPurchaseBasketUsingBonusBalance($basketData);
    }

    /** @test */
    public function isAllowedToPurchaseBasket_BonusBalancePurchasesLimitSetToZeroMeansNoLimit_Allowed(): void
    {
        $basketRawData = '
        [
           {
              "lottery":"3",
              "lines":[
                 {
                    "numbers":[
                       64,
                       44,
                       11,
                       62,
                       25
                    ],
                    "bnumbers":[
                       1
                    ]
                 }
              ]
           }
        ]
        ';

        $basketData = json_decode($basketRawData, true);
        $userId = 7;
        $whitelabelId = 1;

        $whitelabelLottery = new WhitelabelLottery();
        $whitelabelLottery->is_bonus_balance_in_use = true;
        $whitelabelLottery->bonusBalancePurchaseLimitPerUser = 0;
        $whitelabelLottery->id = 6; // whitelabel_lottery = 6 -> lottery = 3

        $this->whitelabelLotteryRepository->expects($this->once())->method('getOneByLotteryIdForWhitelabel')->with(3, 1)->willReturn($whitelabelLottery);

        $lotteryPurchaseLimitService = new LotteryPurchaseLimitService($this->whitelabelLotteryRepository, $this->whitelabelLotteryPurchaseLimitRepository);
        $lotteryPurchaseLimitService->setUserId($userId);
        $lotteryPurchaseLimitService->setWhitelabelId($whitelabelId);

        $isAllowed = $lotteryPurchaseLimitService->isAllowedToPurchaseBasketUsingBonusBalance($basketData);
        $this->assertTrue($isAllowed);
    }

    /** @test */
    public function getErrorMessage_UserPurchasedBefore_GeneratesUserFriendlyErrorMessage(): void
    {
        $basketRawData = '
        [
           {
              "lottery":"3",
              "lines":[
                 {
                    "numbers":[
                       64,
                       44,
                       11,
                       62,
                       25
                    ],
                    "bnumbers":[
                       1
                    ]
                 },
                 {
                    "numbers":[
                       64,
                       44,
                       11,
                       62,
                       25
                    ],
                    "bnumbers":[
                       1
                    ]
                 }
              ]
           }
        ]
        ';

        $basketData = json_decode($basketRawData, true);
        $userId = 7;
        $whitelabelId = 1;

        $whitelabelLottery = new WhitelabelLottery();
        $whitelabelLottery->is_bonus_balance_in_use = true;
        $whitelabelLottery->bonusBalancePurchaseLimitPerUser = 2;
        $whitelabelLottery->id = 6; // whitelabel_lottery = 6 -> lottery = 3
        $lottery = new Lottery();
        $lottery->name = 'Eurojackpot';
        $whitelabelLottery->lottery = $lottery;

        $this->whitelabelLotteryRepository->expects($this->once())->method('getOneByLotteryIdForWhitelabel')->with(3, 1)->willReturn($whitelabelLottery);
        $whitelabelLotteryPurchaseLimit = new WhitelabelLotteryPurchaseLimit();
        $whitelabelLotteryPurchaseLimit->counter = 1;
        $this->whitelabelLotteryPurchaseLimitRepository->expects($this->once())->method('findOneByUserIdAndWhitelabelLotteryId')->with($userId, $whitelabelLottery->id)->willReturn($whitelabelLotteryPurchaseLimit);

        $lotteryPurchaseLimitService = new LotteryPurchaseLimitService($this->whitelabelLotteryRepository, $this->whitelabelLotteryPurchaseLimitRepository);
        $lotteryPurchaseLimitService->setUserId($userId);
        $lotteryPurchaseLimitService->setWhitelabelId($whitelabelId);

        $isAllowed = $lotteryPurchaseLimitService->isAllowedToPurchaseBasketUsingBonusBalance($basketData);
        $this->assertFalse($isAllowed);
        $expectedMessage = 'Purchase of tickets for Eurojackpot lottery is exceeding allowed bonus balance purchase of 2. You have 1 remaining to buy.';
        $this->assertEquals($expectedMessage, $lotteryPurchaseLimitService->getErrorMessage());
    }

    /** @test */
    public function getErrorMessage_NoPreviousPurchases_GeneratesUserFriendlyErrorMessage(): void
    {
        $basketRawData = '
        [
           {
              "lottery":"3",
              "lines":[
                 {
                    "numbers":[
                       64,
                       44,
                       11,
                       62,
                       25
                    ],
                    "bnumbers":[
                       1
                    ]
                 },
                 {
                    "numbers":[
                       64,
                       44,
                       11,
                       62,
                       25
                    ],
                    "bnumbers":[
                       1
                    ]
                 },
                 {
                    "numbers":[
                       64,
                       44,
                       11,
                       62,
                       25
                    ],
                    "bnumbers":[
                       1
                    ]
                 }
              ]
           }
        ]
        ';

        $basketData = json_decode($basketRawData, true);
        $userId = 7;
        $whitelabelId = 1;

        $whitelabelLottery = new WhitelabelLottery();
        $whitelabelLottery->is_bonus_balance_in_use = true;
        $whitelabelLottery->bonusBalancePurchaseLimitPerUser = 2;
        $whitelabelLottery->id = 6; // whitelabel_lottery = 6 -> lottery = 3
        $lottery = new Lottery();
        $lottery->name = 'Eurojackpot';
        $whitelabelLottery->lottery = $lottery;

        $this->whitelabelLotteryRepository->expects($this->once())->method('getOneByLotteryIdForWhitelabel')->with(3, 1)->willReturn($whitelabelLottery);

        $lotteryPurchaseLimitService = new LotteryPurchaseLimitService($this->whitelabelLotteryRepository, $this->whitelabelLotteryPurchaseLimitRepository);
        $lotteryPurchaseLimitService->setUserId($userId);
        $lotteryPurchaseLimitService->setWhitelabelId($whitelabelId);

        $isAllowed = $lotteryPurchaseLimitService->isAllowedToPurchaseBasketUsingBonusBalance($basketData);
        $this->assertFalse($isAllowed);
        $expectedMessage = 'Purchase of tickets for Eurojackpot lottery is exceeding allowed bonus balance purchase of 2.';
        $this->assertEquals($expectedMessage, $lotteryPurchaseLimitService->getErrorMessage());
    }

    /** @test */
    public function getErrorMessage_PreviousPurchasesAboveLotteryLimit_GeneratesUserFriendlyErrorMessageWithoutIncludingPreviousPurchases(): void
    {
        $basketRawData = '
        [
           {
              "lottery":"3",
              "lines":[
                 {
                    "numbers":[
                       64,
                       44,
                       11,
                       62,
                       25
                    ],
                    "bnumbers":[
                       1
                    ]
                 },
                 {
                    "numbers":[
                       64,
                       44,
                       11,
                       62,
                       25
                    ],
                    "bnumbers":[
                       1
                    ]
                 },
                 {
                    "numbers":[
                       64,
                       44,
                       11,
                       62,
                       25
                    ],
                    "bnumbers":[
                       1
                    ]
                 }
              ]
           }
        ]
        ';

        $basketData = json_decode($basketRawData, true);
        $userId = 7;
        $whitelabelId = 1;

        $whitelabelLottery = new WhitelabelLottery();
        $whitelabelLottery->is_bonus_balance_in_use = true;
        $whitelabelLottery->bonusBalancePurchaseLimitPerUser = 2;
        $whitelabelLottery->id = 6; // whitelabel_lottery = 6 -> lottery = 3
        $lottery = new Lottery();
        $lottery->name = 'Eurojackpot';
        $whitelabelLottery->lottery = $lottery;

        $this->whitelabelLotteryRepository->expects($this->once())->method('withRelation')->with(WhitelabelLottery::LOTTERY_RELATION);
        $this->whitelabelLotteryRepository->expects($this->once())->method('getOneByLotteryIdForWhitelabel')->with(3, 1)->willReturn($whitelabelLottery);
        $whitelabelLotteryPurchaseLimit = new WhitelabelLotteryPurchaseLimit();
        $whitelabelLotteryPurchaseLimit->counter = 1; // Error message does not include this number in remaining to buy
        $this->whitelabelLotteryPurchaseLimitRepository->expects($this->never())->method('findOneByUserIdAndWhitelabelLotteryId')->with($userId, $whitelabelLottery->id)->willReturn($whitelabelLotteryPurchaseLimit);

        $lotteryPurchaseLimitService = new LotteryPurchaseLimitService($this->whitelabelLotteryRepository, $this->whitelabelLotteryPurchaseLimitRepository);
        $lotteryPurchaseLimitService->setUserId($userId);
        $lotteryPurchaseLimitService->setWhitelabelId($whitelabelId);

        $isAllowed = $lotteryPurchaseLimitService->isAllowedToPurchaseBasketUsingBonusBalance($basketData);
        $this->assertFalse($isAllowed);
        $expectedMessage = 'Purchase of tickets for Eurojackpot lottery is exceeding allowed bonus balance purchase of 2.';
        $this->assertEquals($expectedMessage, $lotteryPurchaseLimitService->getErrorMessage());
    }
}
