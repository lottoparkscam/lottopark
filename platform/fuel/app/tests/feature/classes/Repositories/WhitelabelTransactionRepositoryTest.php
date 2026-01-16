<?php

namespace Tests\Feature\Classes\Repositories;

use Carbon\Carbon;
use Models\Whitelabel;
use Models\WhitelabelTransaction;
use Models\WhitelabelUser;
use Orm\RecordNotFound;
use Repositories\Orm\TransactionRepository;
use Helpers_General;
use Test_Feature;
use Tests\Fixtures\WhitelabelUserFixture;
use Tests\Fixtures\WhitelabelPaymentMethodFixture;
use Tests\Fixtures\WhitelabelTransactionFixture;

final class WhitelabelTransactionRepositoryTest extends Test_Feature
{
    private TransactionRepository $transactionRepository;
    private Whitelabel $whitelabel;
    private WhitelabelUser $whitelabelUser;
    private WhitelabelUserFixture $whitelabelUserFixture;
    private WhitelabelTransactionFixture $whitelabelTransactionFixture;
    private WhitelabelPaymentMethodFixture $whitelabelPaymentMethodFixture;
    private int $defaultLanguageId;

    public function setUp(): void
    {
        parent::setUp();
        $this->whitelabel = $this->container->get('whitelabel');
        $this->transactionRepository = $this->container->get(TransactionRepository::class);
        $this->whitelabelUserFixture = $this->container->get(WhitelabelUserFixture::class);
        /** @var WhitelabelUser $whitelabelUser */
        $whitelabelUser = $this->whitelabelUserFixture->createOne([
            'whitelabel_id' => $this->whitelabel->id,
            'currency_id' => 2,
            'email' => 'testSocial@Mediatestowo.pl' . random_int(1, 3),
        ]);
        $this->whitelabelUser = $whitelabelUser;
        $this->whitelabelPaymentMethodFixture = $this->container->get(WhitelabelPaymentMethodFixture::class);
        $this->whitelabelTransactionFixture = $this->container->get(WhitelabelTransactionFixture::class);
        $this->transactionRepository = $this->container->get(TransactionRepository::class);
        $this->whitelabel = $this->container->get('whitelabel');

        $this->defaultLanguageId = Helpers_General::get_default_language_id();
    }

    /** @test */
    public function getByTokenIdentifiedByPrefix_FindsTransaction(): void
    {
        $transaction = new WhitelabelTransaction();
        $transaction->whitelabel = $this->whitelabel;
        $transaction->currency_id = 2;
        $transaction->date = Carbon::now();
        $transaction->payment_currency_id = 1;
        $transaction->status = 0;
        $transaction->token = 1357997531;
        $transaction->type = 1;
        $transaction->whitelabel_user_id = $this->whitelabelUser->id;
        $transaction->save();

        $resultTransaction = $this->transactionRepository->getByPrefixedToken('LPP1357997531');
        $this->assertNotEmpty($resultTransaction);
    }

    /** @test */
    public function getByTokenIdentifiedByPrefix_WrongToken(): void
    {
        $transaction = new WhitelabelTransaction();
        $transaction->whitelabel = $this->whitelabel;
        $transaction->currency_id = 2;
        $transaction->date = Carbon::now();
        $transaction->payment_currency_id = 1;
        $transaction->status = 0;
        $transaction->token = 123456789;
        $transaction->type = 1;
        $transaction->whitelabel_user_id = $this->whitelabelUser->id;
        $transaction->save();

        $this->expectException(RecordNotFound::class);
        $this->expectExceptionMessage('Unable to find transaction with token #1357997531 and whitelabel prefix LP');
        $resultTransaction = $this->transactionRepository->getByPrefixedToken('LPP1357997531');
        $this->assertEmpty($resultTransaction);
    }

    /** @test */
    public function getByTokenIdentifiedByPrefix_WrongWhitelabelPrefix(): void
    {
        $this->whitelabel->prefix = 'XO';

        $transaction = new WhitelabelTransaction();
        $transaction->whitelabel = $this->whitelabel;
        $transaction->currency_id = 2;
        $transaction->date = Carbon::now();
        $transaction->payment_currency_id = 1;
        $transaction->status = 0;
        $transaction->token = 1357997531;
        $transaction->type = 1;
        $transaction->whitelabel_user_id = $this->whitelabelUser->id;
        $transaction->save();

        $this->expectException(RecordNotFound::class);
        $resultTransaction = $this->transactionRepository->getByPrefixedToken('LPP1357997531');
        $this->assertEmpty($resultTransaction);
    }

    /** @test */
    public function getByTransactionOutId_FindsTransaction(): void
    {
        $outId = 'fbc05cb6-100f-46d4-9d97-3f9e9d89bef8';

        $whitelabelPaymentMethod = $this->whitelabelPaymentMethodFixture
            ->with('basic')
            ->createOne([
                'language_id' => $this->defaultLanguageId,
                'name' => 'Test Payment'
            ]);

        /** @var WhitelabelTransaction $transaction */
        $transaction = $this->whitelabelTransactionFixture
            ->withUser($this->whitelabelUser)
            ->createOne([
                'whitelabel_id' => $this->whitelabel->id,
                'whitelabel_user_id' => $this->whitelabelUser->id,
                'currency_id' => 2,
                'payment_currency_id' => $whitelabelPaymentMethod->payment_currency_id,
                'whitelabel_payment_method_id' => $whitelabelPaymentMethod->id,
                'transaction_out_id' => $outId
            ]);

        $expectedOrderId = $transaction->getOrderId();

        $resultTransaction = $this->transactionRepository->getByTransactionOutId(
            $whitelabelPaymentMethod->id,
            $outId
        );

        $this->assertNotEmpty($resultTransaction);
        $this->assertSame($expectedOrderId, $resultTransaction->getOrderId());
    }

    /** @test */
    public function getByTransactionOutId_WrongWhitelabelPaymentMethodId(): void
    {
        $wrongWhitelabelPaymentMethodId = 999;
        $outId = 'fbc05cb6-100f-46d4-9d97-3f9e9d89bef8';

        $this->expectException(RecordNotFound::class);
        $this->expectExceptionMessage("Unable to find transaction with out ID $outId and whitelabel payment method ID $wrongWhitelabelPaymentMethodId");

        $whitelabelPaymentMethod = $this->whitelabelPaymentMethodFixture
            ->with('basic')
            ->createOne([
                'language_id' => $this->defaultLanguageId,
                'name' => 'Test Payment'
            ]);

        $this->whitelabelTransactionFixture
            ->withUser($this->whitelabelUser)
            ->createOne([
                'whitelabel_id' => $this->whitelabel->id,
                'whitelabel_user_id' => $this->whitelabelUser->id,
                'currency_id' => 2,
                'payment_currency_id' => $whitelabelPaymentMethod->payment_currency_id,
                'whitelabel_payment_method_id' => $whitelabelPaymentMethod->id,
                'transaction_out_id' => $outId
            ]);

        $this->transactionRepository->getByTransactionOutId(
            $wrongWhitelabelPaymentMethodId,
            $outId
        );
    }
}
