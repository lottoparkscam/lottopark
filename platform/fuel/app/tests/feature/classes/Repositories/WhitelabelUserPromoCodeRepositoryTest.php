<?php

declare(strict_types=1);

namespace Tests\Feature\Classes\Repositories;

use Models\{
    Currency,
    Lottery,
    WhitelabelPromoCode,
    WhitelabelTransaction
};
use Repositories\WhitelabelUserPromoCodeRepository;
use Tests\Fixtures\{
    WhitelabelUserFixture,
    WhitelabelCampaignFixture,
    WhitelabelPromoCodeFixture,
    WhitelabelTransactionFixture,
    WhitelabelUserPromoCodeFixture
};
use Helpers_General;
use Test_Feature;

final class WhitelabelUserPromoCodeRepositoryTest extends Test_Feature
{
    private const PROMO_CODE_FREE_LINE = 'TEST_FREE_LINE';
    private const LOTTERY_SLUG_POWERBALL = 'powerball';

    private Lottery $lottery;
    private WhitelabelTransactionFixture $whitelabelTransactionFixture;
    private WhitelabelCampaignFixture $whitelabelCampaignFixture;
    private WhitelabelPromoCodeFixture $whitelabelPromoCodeFixture;
    private WhitelabelUserPromoCodeFixture $whitelabelUserPromoCodeFixture;
    private WhitelabelUserFixture $whitelabelUserFixture;
    private WhitelabelUserPromoCodeRepository $whitelabelUserPromoCodeRepository;

    private Currency $whitelabelUserCurrency;
    private Lottery $lotteryPowerball;

    public function setUp(): void
    {
        parent::setUp();

        $this->whitelabel = $this->container->get('whitelabel');

        $this->whitelabelUserFixture = $this->container->get(WhitelabelUserFixture::class);
        $this->whitelabelTransactionFixture = $this->container->get(WhitelabelTransactionFixture::class);
        $this->whitelabelCampaignFixture = $this->container->get(WhitelabelCampaignFixture::class);
        $this->whitelabelPromoCodeFixture = $this->container->get(WhitelabelPromoCodeFixture::class);
        $this->whitelabelUserPromoCodeFixture = $this->container->get(WhitelabelUserPromoCodeFixture::class);
        $this->whitelabelUserPromoCodeRepository = $this->container->get(WhitelabelUserPromoCodeRepository::class);

        $this->whitelabelUser = $this->whitelabelUserFixture
            ->with(WhitelabelUserFixture::BASIC, WhitelabelUserFixture::EUR)
            ->createOne();

        /** @var Currency $currency */
        $currency = Currency::find('first', [
            'where' => [
                'id' => $this->whitelabelUser->currency_id
            ]
        ]);

        $this->whitelabelUserCurrency = $currency;
        $this->lottery = $this->container->get(Lottery::class);
        $this->lotteryPowerball = $this->lottery->get_by_slug(self::LOTTERY_SLUG_POWERBALL);
    }

    /**
     * @test
     */
    public function newPromoCode_ShouldNotGetPromoCodeUsedByUser(): void
    {
        $type = Helpers_General::PROMO_CODE_TYPE_DEPOSIT;
        $transactionTypeDeposit = Helpers_General::TYPE_TRANSACTION_DEPOSIT;

        $whitelabelPromoCode = $this->createWhitelabelPromoCodeFreeLine(
            self::PROMO_CODE_FREE_LINE,
            $type,
            $this->lotteryPowerball->id
        );

        $this->expectExceptionMessage('Cannot update promo code ID: ' . $whitelabelPromoCode->id);

        $transaction = $this->createTransaction($transactionTypeDeposit);

        $this->whitelabelUserPromoCodeRepository->setPromoCodeUsedForTransaction(
            $transaction->id,
            $whitelabelPromoCode->id,
            $this->whitelabelUser->id,
            $type
        );

        $isCodeUsedByUser = $this->whitelabelUserPromoCodeRepository->isCodeUsedByUser(
            $whitelabelPromoCode->id,
            $this->whitelabelUser->id
        );

        $this->assertFalse($isCodeUsedByUser);
    }

    /**
     * @test
     */
    public function savePromoCodeUsed_ShouldGetPromoCodeUsedByUser(): void
    {
        $type = Helpers_General::PROMO_CODE_TYPE_REGISTER;

        $whitelabelPromoCode = $this->createWhitelabelPromoCodeFreeLine(
            self::PROMO_CODE_FREE_LINE,
            $type,
            $this->lotteryPowerball->id
        );

        $this->whitelabelUserPromoCodeRepository->savePromoCodeUsed(
            $whitelabelPromoCode->id,
            $this->whitelabelUser->id,
            $type
        );

        $isCodeUsedByUser = $this->whitelabelUserPromoCodeRepository->isCodeUsedByUser(
            $whitelabelPromoCode->id,
            $this->whitelabelUser->id
        );

        $savedPromoCode = $this->whitelabelUserPromoCodeRepository->findOneByCodeIdAndUserId(
            $whitelabelPromoCode->id,
            $this->whitelabelUser->id
        );

        $this->assertTrue($isCodeUsedByUser);
        $this->assertNotNull($savedPromoCode);
        $this->assertNull($savedPromoCode->whitelabel_transaction_id);
        $this->assertSame((int)$savedPromoCode->type, $type);
        $this->assertNotNull($savedPromoCode->used_at);
    }

    /**
     * @test
     */
    public function setPromoCodeUsedForTransaction_ShouldGetPromoCodeUsedByUser(): void
    {
        $type = Helpers_General::PROMO_CODE_TYPE_DEPOSIT;

        $whitelabelPromoCode = $this->createWhitelabelPromoCodeFreeLine(
            self::PROMO_CODE_FREE_LINE,
            $type,
            $this->lotteryPowerball->id
        );

        $transactionTypeDeposit = Helpers_General::TYPE_TRANSACTION_DEPOSIT;
        $transaction = $this->createTransaction($transactionTypeDeposit);

        $this->whitelabelUserPromoCodeFixture
            ->withWhitelabelUser($this->whitelabelUser)
            ->withWhitelabelPromoCode($whitelabelPromoCode)
            ->createOne();

        $this->whitelabelUserPromoCodeRepository->setPromoCodeUsedForTransaction(
            $transaction->id,
            $whitelabelPromoCode->id,
            $this->whitelabelUser->id,
            $type
        );

        $isCodeUsedByUser = $this->whitelabelUserPromoCodeRepository->isCodeUsedByUser(
            $whitelabelPromoCode->id,
            $this->whitelabelUser->id
        );

        $savedPromoCode = $this->whitelabelUserPromoCodeRepository->findOneByCodeIdAndUserId(
            $whitelabelPromoCode->id,
            $this->whitelabelUser->id
        );

        $this->assertTrue($isCodeUsedByUser);
        $this->assertNotNull($savedPromoCode);
        $this->assertSame($savedPromoCode->whitelabel_transaction_id, $transaction->id);
        $this->assertSame((int)$savedPromoCode->type, $type);
        $this->assertNotNull($savedPromoCode->used_at);
    }

    private function createTransaction(int $type): WhitelabelTransaction
    {
        return $this->whitelabelTransactionFixture
            ->withWhitelabel($this->whitelabel)
            ->withUser($this->whitelabelUser)
            ->withCurrency($this->whitelabelUserCurrency)
            ->createOne([
                'type' => $type
            ]);
    }

    private function createWhitelabelPromoCodeFreeLine(
        string $prefix,
        int $type,
        ?int $lotteryId
    ): WhitelabelPromoCode {
        $whitelabelCampaign = $this->whitelabelCampaignFixture
            ->withWhitelabel($this->whitelabel)
            ->withBonusTypeFreeLine($lotteryId)
            ->withValidityThisMonth()
            ->createOne([
                'prefix' => $prefix,
                'type' => $type,
            ]);

        return $this->whitelabelPromoCodeFixture
            ->withWhitelabelCampaign($whitelabelCampaign)
            ->createOne();
    }
}
