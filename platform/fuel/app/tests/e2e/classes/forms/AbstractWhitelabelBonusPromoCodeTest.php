<?php

declare(strict_types=1);

namespace Tests\E2E\Classes\Forms;

use Forms_Status;
use Forms_Whitelabel_Bonuses_Promocodes_Code;
use Forms_Wordpress_Lottery_Basket;
use Forms_Wordpress_Myaccount_Deposit;
use Interfaces\PromoCode\PromoCodeApplicableInterface;
use Models\Lottery;
use Models\Whitelabel;
use Models\WhitelabelPromoCode;
use Models\WhitelabelUser;
use Repositories\WhitelabelUserPromoCodeRepository;
use Repositories\WhitelabelUserTicketRepository;
use Tests\Fixtures\WhitelabelCampaignFixture;
use Tests\Fixtures\WhitelabelFixture;
use Tests\Fixtures\WhitelabelPromoCodeFixture;
use Tests\Fixtures\WhitelabelTransactionFixture;
use Tests\Fixtures\WhitelabelUserFixture;
use Tests\Fixtures\WhitelabelUserPromoCodeFixture;
use Helpers_General;
use Helpers_Lottery;
use Test_Feature;
use PHPUnit\Framework\MockObject\MockObject;

abstract class AbstractWhitelabelBonusPromoCodeTest extends Test_Feature
{
    protected Whitelabel $whitelabel;
    protected WhitelabelUser $whitelabelUser;
    protected Lottery $lottery;

    protected WhitelabelFixture $whitelabelFixture;
    protected WhitelabelUserFixture $whitelabelUserFixture;
    protected WhitelabelTransactionFixture $whitelabelTransactionFixture;
    protected WhitelabelCampaignFixture $whitelabelCampaignFixture;
    protected WhitelabelPromoCodeFixture $whitelabelPromoCodeFixture;
    protected WhitelabelUserPromoCodeFixture $whitelabelUserPromoCodeFixture;
    protected WhitelabelUserPromoCodeRepository $whitelabelUserPromoCodeRepository;
    protected WhitelabelUserTicketRepository $whitelabelUserTicketRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->whitelabelFixture = $this->container->get(WhitelabelFixture::class);
        $this->whitelabelUserFixture = $this->container->get(WhitelabelUserFixture::class);
        $this->whitelabelTransactionFixture = $this->container->get(WhitelabelTransactionFixture::class);
        $this->whitelabelCampaignFixture = $this->container->get(WhitelabelCampaignFixture::class);
        $this->whitelabelPromoCodeFixture = $this->container->get(WhitelabelPromoCodeFixture::class);
        $this->whitelabelUserPromoCodeFixture = $this->container->get(WhitelabelUserPromoCodeFixture::class);
        $this->whitelabelUserPromoCodeRepository = $this->container->get(WhitelabelUserPromoCodeRepository::class);
        $this->whitelabelUserTicketRepository = $this->container->get(WhitelabelUserTicketRepository::class);

        $this->whitelabel = $this->container->get('whitelabel');
        $this->lottery = $this->container->get(Lottery::class);

        $this->whitelabelUser = $this->whitelabelUserFixture
            ->with(WhitelabelUserFixture::BASIC, WhitelabelUserFixture::EUR)
            ->createOne();
    }

    public function getPurchaseDepositFormTypeMethodsDataProvider(): array
    {
        return [
            'Lottery Basket Form' => [
                Forms_Wordpress_Lottery_Basket::class,
                Forms_Whitelabel_Bonuses_Promocodes_Code::TYPE_PURCHASE
            ],
            'MyAccount Deposit Form' => [
                Forms_Wordpress_Myaccount_Deposit::class,
                Forms_Whitelabel_Bonuses_Promocodes_Code::TYPE_DEPOSIT
            ],
        ];
    }

    protected function getFormMessage(int $bonusType): string
    {
        switch ($bonusType) {
            case Helpers_General::PROMO_CODE_BONUS_TYPE_FREE_LINE:
                return 'You used <b>%s</b> promo code. Your free <b>%s</b> ticket will be added to your account after the transaction is completed.';
            case Helpers_General::PROMO_CODE_BONUS_TYPE_DISCOUNT:
                return 'You used <b>%s</b> promo code. You have received <b>%s</b> discount.';
            case Helpers_General::PROMO_CODE_BONUS_TYPE_BONUS_MONEY:
                return 'You used <b>%s</b> promo code. <b>%s</b> of the deposit amount will be added to your bonus balance after the transaction is completed.';
        }

        return '';
    }

    protected function applyPromoCodeRegister(WhitelabelPromoCode $whitelabelPromoCode): void
    {
        $this->setInput('POST', [
            'register.promo_code' => $whitelabelPromoCode->whitelabel_campaign->prefix
        ]);
    }

    protected function applyPromoCodeOrder(WhitelabelPromoCode $whitelabelPromoCode): void
    {
        $this->setInput('POST', [
            'input.promo_code' => $whitelabelPromoCode->whitelabel_campaign->prefix
        ]);

        $this->whitelabelUserPromoCodeFixture
            ->withWhitelabelUser($this->whitelabelUser)
            ->withWhitelabelPromoCode($whitelabelPromoCode)
            ->createOne();
    }

    protected function createWhitelabelPromoCodeFreeLine(
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

    protected function createWhitelabelPromoCodeDiscount(
        string $prefix,
        int $type,
        float $amount,
        int $discountType = Helpers_General::PROMO_CODE_DISCOUNT_TYPE_PERCENT
    ): WhitelabelPromoCode {
        $whitelabelCampaign = $this->whitelabelCampaignFixture
            ->withWhitelabel($this->whitelabel)
            ->withBonusTypeDiscount($amount, $discountType)
            ->withValidityThisMonth()
            ->createOne([
                'prefix' => $prefix,
                'type' => $type,
            ]);

        return $this->whitelabelPromoCodeFixture
            ->withWhitelabelCampaign($whitelabelCampaign)
            ->createOne();
    }

    protected function createWhitelabelPromoCodeBonusMoney(
        string $prefix,
        int $type,
        float $amount,
        int $balanceType = Helpers_General::PROMO_CODE_BONUS_BALANCE_TYPE_PERCENT
    ): WhitelabelPromoCode {
        $whitelabelCampaign = $this->whitelabelCampaignFixture
            ->withWhitelabel($this->whitelabel)
            ->withBonusTypeBalance($amount, $balanceType)
            ->withValidityThisMonth()
            ->createOne([
                'prefix' => $prefix,
                'type' => $type,
            ]);

        return $this->whitelabelPromoCodeFixture
            ->withWhitelabelCampaign($whitelabelCampaign)
            ->createOne();
    }

    protected function getPromoCodeApplicableFormInstance(
        string $formClassName,
        int $promoCodeFormType,
    ): MockObject|PromoCodeApplicableInterface {
        $promoCodeForm = $this->getPromoCodeFormInstance(
            $this->whitelabel,
            $promoCodeFormType
        );

        /** @var MockObject|PromoCodeApplicableInterface $form */

        $form = $this->getMockBuilder($formClassName)
            ->disableOriginalConstructor()
            ->onlyMethods(['process_form'])
            ->getMock();

        $form->setPromoCodeForm($promoCodeForm);

        $usePromoCodeCallback = $this->getUsePromoCodeCallback($form);
        $processFormCallback = function () use ($form, $usePromoCodeCallback) : int {

            $form->processPromoCode();
            $usePromoCodeCallback();

            return Forms_Status::RESULT_OK;
        };

        $form
            ->expects($this->once())
            ->method('process_form')
            ->willReturnCallback($processFormCallback);

        return $form;
    }

    protected function getPromoCodeFormInstance(Whitelabel $whitelabel, int $type): MockObject|Forms_Whitelabel_Bonuses_Promocodes_Code
    {
        $isCasino = false;

        $lotteriesCallback = function () use ($isCasino): array {
            if (!$isCasino) {
                return Helpers_Lottery::getLotteries();
            }

            return [];
        };

        $pricingCallback = function($lottery, $ticket_multiplier = 1): string {
            return Helpers_Lottery::getPricing($lottery, $ticket_multiplier);
        };

        $promoCodeForm = $this->getMockBuilder(Forms_Whitelabel_Bonuses_Promocodes_Code::class)
            ->setConstructorArgs([$whitelabel, $type])
            ->onlyMethods(['getLotteries', 'getPricing'])
            ->getMock();

        $promoCodeForm
            ->expects($this->any())
            ->method('getLotteries')
            ->willReturnCallback($lotteriesCallback);

        $promoCodeForm
            ->expects($this->any())
            ->method('getPricing')
            ->willReturnCallback($pricingCallback);

        return $promoCodeForm;
    }

    abstract protected function getUsePromoCodeCallback(PromoCodeApplicableInterface $form): callable;
}
