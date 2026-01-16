<?php

namespace unit\classes\services\MiniGame;

use PHPUnit\Framework\TestCase;
use Repositories\MiniGameTransactionRepository;
use Repositories\Orm\WhitelabelUserRepository;
use Services\Logs\FileLoggerService;
use Services\MiniGame\MiniGameTransactionService;
use Services\MiniGame\ValueObject\MiniGameBalanceTransactionObject;
use Models\Whitelabel;
use Models\WhitelabelUser;

class MiniGameTransactionServiceTest extends TestCase
{
    private WhitelabelUserRepository $whitelabelUserRepositoryMock;
    private MiniGameTransactionService $transactionService;

    protected function setUp(): void
    {
        parent::setUp();

        $transactionRepositoryMock = $this->createMock(MiniGameTransactionRepository::class);
        $this->whitelabelUserRepositoryMock = $this->createMock(WhitelabelUserRepository::class);
        $loggerServiceMock = $this->createMock(FileLoggerService::class);
        $whitelabelMock = $this->createMock(Whitelabel::class);

        $loggerServiceMock
            ->method('setSource');

        $this->transactionService = new MiniGameTransactionService(
            $transactionRepositoryMock,
            $this->whitelabelUserRepositoryMock,
            $loggerServiceMock,
            $whitelabelMock
        );
    }

    public function testItDoesNotRemoveAnyAmountWhenItIsFreeSpinAndAddsWinToBonusBalance()
    {
        $userId = 1;
        $currentBalance = 100.0;
        $currentBonus = 50.0;
        $betAmount = 10.0;
        $prizeAmount = 30.0;

        $updateUserBalanceCalls = [];
        $updateUserBonusBalanceCalls = [];

        $this->whitelabelUserRepositoryMock
            ->expects($this->never())
            ->method('updateUserBalance');

        $this->whitelabelUserRepositoryMock
            ->expects($this->once())
            ->method('updateUserBonusBalance')
            ->willReturnCallback(function ($actualUserId, $amountChange) use (&$updateUserBonusBalanceCalls) {
                $updateUserBonusBalanceCalls[] = [
                    'userId' => $actualUserId,
                    'amountChange' => $amountChange
                ];
            });

        $balanceTransactionObject = new MiniGameBalanceTransactionObject(
            $this->createWhitelabelUser($userId, $currentBalance, $currentBonus),
            $betAmount,
            $prizeAmount,
            true,
            true,
        );

        $isUsedBonus = $this->transactionService->updateUserBalance($balanceTransactionObject);

        $this->assertTrue($isUsedBonus);
        $this->assertEmpty($updateUserBalanceCalls);
        $this->assertCount(1, $updateUserBonusBalanceCalls);
        $this->assertSame($userId, $updateUserBonusBalanceCalls[0]['userId']);
        $this->assertSame($prizeAmount, $updateUserBonusBalanceCalls[0]['amountChange']);
    }

    public function testItDoesNotRemoveAnyAmountWhenIsFreeSpinAndLose()
    {
        $userId = 1;
        $currentBalance = 100.0;
        $currentBonus = 50.0;
        $betAmount = 10.0;
        $prizeAmount = 0.0; // przegrana

        $updateUserBalanceCalls = [];
        $updateUserBonusBalanceCalls = [];

        $this->whitelabelUserRepositoryMock
            ->expects($this->never())
            ->method('updateUserBalance');

        $this->whitelabelUserRepositoryMock
            ->expects($this->never())
            ->method('updateUserBonusBalance');

        $balanceTransactionObject = new MiniGameBalanceTransactionObject(
            $this->createWhitelabelUser($userId, $currentBalance, $currentBonus),
            $betAmount,
            $prizeAmount,
            false, // isWin
            true, // isFreeSpin
        );

        $isUsedBonus = $this->transactionService->updateUserBalance($balanceTransactionObject);

        $this->assertTrue($isUsedBonus);
        $this->assertEmpty($updateUserBalanceCalls);
        $this->assertEmpty($updateUserBonusBalanceCalls);
    }

    public function testItRemovesAmountFromRealBalanceIfSufficientAndAddsWinToRealBalance()
    {
        $userId = 1;
        $currentBalance = 100.00;
        $currentBonus = 50.00;
        $betAmount = 1.00;
        $prizeAmount = 1.85;

        $updateUserBalanceCalls = [];

        $this->whitelabelUserRepositoryMock
            ->expects($this->exactly(2))
            ->method('updateUserBalance')
            ->willReturnCallback(function ($actualUserId, $amountChange) use (&$updateUserBalanceCalls) {
                $updateUserBalanceCalls[] = [
                    'userId' => $actualUserId,
                    'amountChange' => $amountChange
                ];
            });

        $this->whitelabelUserRepositoryMock
            ->expects($this->never())
            ->method('updateUserBonusBalance');

        $balanceTransactionObject = new MiniGameBalanceTransactionObject(
            $this->createWhitelabelUser($userId, $currentBalance, $currentBonus),
            $betAmount,
            $prizeAmount,
            true,
            false
        );

        $isUsedBonus = $this->transactionService->updateUserBalance($balanceTransactionObject);

        $this->assertFalse($isUsedBonus);
        $this->assertCount(2, $updateUserBalanceCalls);

        $this->assertSame($userId, $updateUserBalanceCalls[0]['userId']);
        $this->assertSame(-$betAmount, $updateUserBalanceCalls[0]['amountChange']);

        $this->assertSame($userId, $updateUserBalanceCalls[1]['userId']);
        $this->assertSame($prizeAmount, $updateUserBalanceCalls[1]['amountChange']);
    }

    public function testItRemovesAmountFromRealBalanceIfSufficientAndLose()
    {
        $userId = 1;
        $currentBalance = 100.0;
        $currentBonus = 50.0;
        $betAmount = 20.0;
        $prizeAmount = 0.0; // przegrana

        $updateUserBalanceCalls = [];

        $this->whitelabelUserRepositoryMock
            ->expects($this->exactly(1))
            ->method('updateUserBalance')
            ->willReturnCallback(function ($actualUserId, $amountChange) use (&$updateUserBalanceCalls) {
                $updateUserBalanceCalls[] = [
                    'userId' => $actualUserId,
                    'amountChange' => $amountChange
                ];
            });

        $this->whitelabelUserRepositoryMock
            ->expects($this->never())
            ->method('updateUserBonusBalance');

        $balanceTransactionObject = new MiniGameBalanceTransactionObject(
            $this->createWhitelabelUser($userId, $currentBalance, $currentBonus),
            $betAmount,
            $prizeAmount,
            false,
            false
        );

        $isUsedBonus = $this->transactionService->updateUserBalance($balanceTransactionObject);

        $this->assertFalse($isUsedBonus);
        $this->assertCount(1, $updateUserBalanceCalls);
        $this->assertSame($userId, $updateUserBalanceCalls[0]['userId']);
        $this->assertSame(-$betAmount, $updateUserBalanceCalls[0]['amountChange']);
    }

    public function testItRemovesAmountFromBonusBalanceIfRealBalanceIsInsufficientAndAddsWinToRealBalance()
    {
        $userId = 1;
        $currentBalance = 10.0; // za mało na bet 20
        $currentBonus = 100.0;
        $betAmount = 20.0;
        $prizeAmount = 40.0;

        $updateUserBalanceCalls = [];
        $updateUserBonusBalanceCalls = [];

        $this->whitelabelUserRepositoryMock
            ->expects($this->once())
            ->method('updateUserBonusBalance')
            ->willReturnCallback(function ($actualUserId, $amountChange) use (&$updateUserBonusBalanceCalls) {
                $updateUserBonusBalanceCalls[] = [
                    'userId' => $actualUserId,
                    'amountChange' => $amountChange
                ];
            });

        $this->whitelabelUserRepositoryMock
            ->expects($this->once())
            ->method('updateUserBalance')
            ->willReturnCallback(function ($actualUserId, $amountChange) use (&$updateUserBalanceCalls) {
                $updateUserBalanceCalls[] = [
                    'userId' => $actualUserId,
                    'amountChange' => $amountChange
                ];
            });

        $balanceTransactionObject = new MiniGameBalanceTransactionObject(
            $this->createWhitelabelUser($userId, $currentBalance, $currentBonus),
            $betAmount,
            $prizeAmount,
            true,
            false
        );

        $isUsedBonus = $this->transactionService->updateUserBalance($balanceTransactionObject);

        $this->assertTrue($isUsedBonus);
        $this->assertCount(1, $updateUserBonusBalanceCalls);
        $this->assertSame($userId, $updateUserBonusBalanceCalls[0]['userId']);
        $this->assertSame(-$betAmount, $updateUserBonusBalanceCalls[0]['amountChange']);
        $this->assertCount(1, $updateUserBalanceCalls);
        $this->assertSame($userId, $updateUserBalanceCalls[0]['userId']);
        $this->assertSame($prizeAmount, $updateUserBalanceCalls[0]['amountChange']);
    }

    public function testItRemovesAmountFromBonusBalanceIfRealBalanceIsInsufficientAndLose()
    {
        $userId = 1;
        $currentBalance = 10.0;
        $currentBonus = 100.0;
        $betAmount = 20.0;
        $prizeAmount = 0.0; // przegrana

        $updateUserBalanceCalls = [];
        $updateUserBonusBalanceCalls = [];

        $this->whitelabelUserRepositoryMock
            ->expects($this->once())
            ->method('updateUserBonusBalance')
            ->willReturnCallback(function ($actualUserId, $amountChange) use (&$updateUserBonusBalanceCalls) {
                $updateUserBonusBalanceCalls[] = [
                    'userId' => $actualUserId,
                    'amountChange' => $amountChange
                ];
            });

        $this->whitelabelUserRepositoryMock
            ->expects($this->never())
            ->method('updateUserBalance');

        $balanceTransactionObject = new MiniGameBalanceTransactionObject(
            $this->createWhitelabelUser($userId, $currentBalance, $currentBonus),
            $betAmount,
            $prizeAmount,
            false,
            false
        );

        $isUsedBonus = $this->transactionService->updateUserBalance($balanceTransactionObject);

        $this->assertTrue($isUsedBonus);
        $this->assertCount(1, $updateUserBonusBalanceCalls);
        $this->assertSame($userId, $updateUserBonusBalanceCalls[0]['userId']);
        $this->assertSame(-$betAmount, $updateUserBonusBalanceCalls[0]['amountChange']);
        $this->assertEmpty($updateUserBalanceCalls);
    }

    private function createWhitelabelUser(int $userId, float $balance, float $bonusBalance): WhitelabelUser
    {
        $user = new WhitelabelUser();

        $user->id = $userId;
        $user->balance = $balance;
        $user->bonusBalance = $bonusBalance;

        return $user;
    }
}
