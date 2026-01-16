<?php

namespace Tests\Feature\Classes\Services;

use Models\WhitelabelTransaction;
use Repositories\{
    Orm\WhitelabelPaymentMethodRepository,
    Orm\WhitelabelUserRepository,
    WhitelabelTransactionRepository
};
use Services\Logs\FileLoggerService;
use Services\Plugin\MauticPluginService;
use Services\TransactionService;
use Tests\Fixtures\{
    WhitelabelTransactionFixture,
    WhitelabelUserFixture
};
use Helpers_General;
use Exception;
use Test_Feature;

final class PluginServiceTest extends Test_Feature
{
    private const TEST_PAYMENT_METHOD_ID = 1;

    private WhitelabelUserRepository $whitelabelUserRepository;
    private WhitelabelTransactionRepository $whitelabelTransactionRepository;
    private WhitelabelPaymentMethodRepository $whitelabelPaymentMethodRepository;

    private MauticPluginService $mauticPluginService;
    private FileLoggerService $fileLoggerService;

    private WhitelabelUserFixture $whitelabelUserFixture;
    private WhitelabelTransactionFixture $whitelabelTransactionFixture;

    public function setUp(): void
    {
        parent::setUp();

        $this->whitelabelUserRepository = $this->container->get(WhitelabelUserRepository::class);
        $this->whitelabelTransactionRepository = $this->container->get(WhitelabelTransactionRepository::class);
        $this->whitelabelPaymentMethodRepository = $this->container->get(WhitelabelPaymentMethodRepository::class);
        $this->transactionService = $this->container->get(TransactionService::class);

        $this->whitelabelUserFixture = $this->container->get(WhitelabelUserFixture::class);
        $this->whitelabelTransactionFixture = $this->container->get(WhitelabelTransactionFixture::class);
    }

    public function getMauticPluginDepositDataProvider(): array
    {
        return [
            'user first deposit' => [true, false],
            'user second deposit' => [false, false],
            'user first deposit casino' => [true, true],
            'user second deposit casino' => [false, true],
        ];
    }

    /**
     * @test
     */
    public function mauticPlugin_createDeposit_InvalidTransactionType(): void
    {
        $this->fileLoggerServiceMock();

        $this->mauticPluginService = new MauticPluginService(
            $this->whitelabelUserRepository,
            $this->whitelabelTransactionRepository,
            $this->whitelabelPaymentMethodRepository,
            $this->transactionService,
            $this->fileLoggerService
        );

        $whitelabelPaymentMethod = $this->whitelabelPaymentMethodRepository->findOneById(self::TEST_PAYMENT_METHOD_ID);
        $paymentMethodId = $whitelabelPaymentMethod->payment_method_id;

        $whitelabelTransaction = $this->createWhitelabelTransactionPurchase($paymentMethodId);
        $whitelabelTransactionType = $this->transactionService->getTransactionType($whitelabelTransaction);

        $this->expectExceptionMessage(sprintf(
            "Invalid WhitelabelTransaction type '%s' for transaction ID: %s",
            $whitelabelTransactionType,
            $whitelabelTransaction->id,
        ));

        $this->mauticPluginService->setWhitelabelUser($whitelabelTransaction->whitelabel_user_id);

        $pluginData = $this->mauticPluginService->createDeposit($whitelabelTransaction->id);

        $this->assertEmpty($pluginData);
    }

    /**
     * @test
     * @dataProvider getMauticPluginDepositDataProvider
     */
    public function mauticPlugin_createDeposit(bool $firstDeposit, bool $isCasino): void
    {
        $this->fileLoggerServiceMock();

        $this->mauticPluginService = new MauticPluginService(
            $this->whitelabelUserRepository,
            $this->whitelabelTransactionRepository,
            $this->whitelabelPaymentMethodRepository,
            $this->transactionService,
            $this->fileLoggerService
        );

        $depositAmount = 50.00;
        $whitelabelPaymentMethod = $this->whitelabelPaymentMethodRepository->findOneById(self::TEST_PAYMENT_METHOD_ID);
        $paymentMethodId = $whitelabelPaymentMethod->payment_method_id;

        $whitelabelTransaction = $this->createWhitelabelTransactionDeposit($depositAmount, $paymentMethodId, $firstDeposit, $isCasino);
        $this->mauticPluginService->setWhitelabelUser($whitelabelTransaction->whitelabel_user_id);

        $pluginData = $this->mauticPluginService->createDeposit($whitelabelTransaction->id);

        $whitelabelUser = $this->whitelabelUserRepository->findOneById($whitelabelTransaction->whitelabel_user_id);
        $totalDepositManager = $whitelabelUser->total_deposit_manager ? (float) $whitelabelUser->total_deposit_manager : null;

        $this->assertNotEmpty($pluginData);

        $whitelabelTransactionStatus = $this->transactionService->getTransactionStatus($whitelabelTransaction);

        if ($firstDeposit) {
            $this->assertSame($whitelabelTransaction->isCasino, $pluginData['first_deposit_casino']);
            $this->assertSame($whitelabelTransaction->amount_manager, $pluginData['first_deposit_amount_manager']);
            $this->assertSame($whitelabelPaymentMethod?->name, $pluginData['first_deposit_method_manager']);
            $this->assertSame($whitelabelTransactionStatus, $pluginData['first_deposit_status_manager']);
        } else {
            $this->assertArrayNotHasKey('first_deposit_casino', $pluginData);
            $this->assertArrayNotHasKey('first_deposit_amount_manager', $pluginData);
            $this->assertArrayNotHasKey('first_deposit_method_manager', $pluginData);
            $this->assertArrayNotHasKey('first_deposit_status_manager', $pluginData);
        }

        $this->assertSame($whitelabelTransaction->isCasino, $pluginData['last_deposit_casino']);
        $this->assertSame($whitelabelTransaction->amount_manager, $pluginData['last_deposit_amount_manager']);
        $this->assertSame($whitelabelPaymentMethod?->name, $pluginData['last_deposit_method_manager']);
        $this->assertSame($whitelabelTransactionStatus, $pluginData['last_deposit_status_manager']);

        $this->assertSame($totalDepositManager, $pluginData['total_deposit_manager']);
    }

    private function createWhitelabelTransactionDeposit(
        float $depositAmount,
        int $whitelabelPaymentMethodId,
        bool $firstDeposit = true,
        bool $isCasino = false,
    ): WhitelabelTransaction {
        $whitelabelUser = $this->whitelabelUserFixture
            ->with('basic')
            ->createOne([
                'first_deposit_amount_manager' => !$firstDeposit ? $depositAmount : null,
                'total_deposit_manager' => !$firstDeposit ? $depositAmount : null
            ]);

        return $this->whitelabelTransactionFixture
            ->with('basic')
            ->withUser($whitelabelUser)
            ->createOne([
                'whitelabel_payment_method_id' => $whitelabelPaymentMethodId,
                'amount_manager' => $depositAmount,
                'type' => Helpers_General::TYPE_TRANSACTION_DEPOSIT,
                'is_casino' => $isCasino
            ]);
    }

    private function createWhitelabelTransactionPurchase(int $whitelabelPaymentMethodId): WhitelabelTransaction
    {
        return $this->whitelabelTransactionFixture
            ->with('basic')
            ->createOne([
                'whitelabel_payment_method_id' => $whitelabelPaymentMethodId,
                'type' => Helpers_General::TYPE_TRANSACTION_PURCHASE,
            ]);
    }

    private function fileLoggerServiceMock(): void
    {
        $this->fileLoggerService = $this->createMock(FileLoggerService::class);

        $this->fileLoggerService
            ->method('error')
            ->willReturnCallback(function ($message) {
                throw new Exception($message);
            });
    }
}
