<?php

declare(strict_types=1);

namespace Tests\E2E\Classes\Forms;

use Forms_Wordpress_Lottery_Basket;
use Forms_Wordpress_Myaccount_Deposit;
use Forms_Whitelabel_Bonuses_Promocodes_Code;
use Forms_Wordpress_User_Register;
use Interfaces\PromoCode\PromoCodeApplicableInterface;
use Interfaces\PromoCode\PromoCodeTransactionApplicableInterface;
use Models\Currency;
use Models\Lottery;
use Models\WhitelabelPromoCode;
use Models\WhitelabelTransaction;
use Helpers_General;
use Helpers_Currency;
use Lotto_Settings;
use Model_Currency;
use Fuel\Core\Session;
use Exception;
use Orm\RecordNotFound;

final class WhitelabelBonusesPromoCodeTest extends AbstractWhitelabelBonusPromoCodeTest
{
    private const PROMO_CODE_FREE_LINE = 'TEST_FREE_LINE';
    private const PROMO_CODE_DISCOUNT = 'TEST_DISCOUNT';
    private const PROMO_CODE_BONUS_MONEY = 'TEST_BONUS_MONEY';

    private const PROMO_CODE_AMOUNT_PERCENTAGE = 5.00;
    private const PROMO_CODE_AMOUNT_PERCENTAGE_FORMATTED = '5.00%';

    private const PROMO_CODE_AMOUNT = 10.00;
    private const PROMO_CODE_AMOUNT_FORMATTED = '€10.00';

    private const LOTTERY_NAME_POWERBALL = 'Powerball';
    private const LOTTERY_SLUG_POWERBALL = 'powerball';

    private Currency $whitelabelUserCurrency;
    private Lottery $lotteryPowerball;

    public function setUp(): void
    {
        parent::setUp();

        /** @var Currency $currency */
        $currency = Currency::find('first', [
            'where' => [
                'id' => $this->whitelabelUser->currency_id
            ]
        ]);

        $this->whitelabelUserCurrency = $currency;
        $this->lotteryPowerball = $this->lottery->get_by_slug(self::LOTTERY_SLUG_POWERBALL);

        Session::set('order', []);

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_HOST'] = $this->whitelabel->domain;

        Lotto_Settings::getInstance()->set('whitelabel', $this->whitelabel->to_array());
        Lotto_Settings::getInstance()->set('user', $this->whitelabelUser->to_array());
        Lotto_Settings::getInstance()->set('currencies', Model_Currency::get_all_currencies());
    }

    public function getTicketPurchaseBonusTypeDataProvider(): array
    {
        $bonusTypeDiscountMessage = sprintf(
            $this->getFormMessage(Helpers_General::PROMO_CODE_BONUS_TYPE_DISCOUNT),
            self::PROMO_CODE_DISCOUNT,
            self::PROMO_CODE_AMOUNT_PERCENTAGE_FORMATTED
        );

        $bonusTypeFreeLineMessage = sprintf(
            $this->getFormMessage(Helpers_General::PROMO_CODE_BONUS_TYPE_FREE_LINE),
            self::PROMO_CODE_FREE_LINE,
            self::LOTTERY_NAME_POWERBALL
        );

        return [
            'bonus type discount' => [Helpers_General::PROMO_CODE_BONUS_TYPE_DISCOUNT, false, true, $bonusTypeDiscountMessage],
            'bonus type free line' => [Helpers_General::PROMO_CODE_BONUS_TYPE_FREE_LINE, true, false, $bonusTypeFreeLineMessage],
        ];
    }

    public function getTicketDepositBonusTypeDataProvider(): array
    {
        $bonusTypeBonusMoneyMessage = sprintf(
            $this->getFormMessage(Helpers_General::PROMO_CODE_BONUS_TYPE_BONUS_MONEY),
            self::PROMO_CODE_BONUS_MONEY,
            self::PROMO_CODE_AMOUNT_PERCENTAGE_FORMATTED
        );

        $bonusTypeFreeLineMessage = sprintf(
            $this->getFormMessage(Helpers_General::PROMO_CODE_BONUS_TYPE_FREE_LINE),
            self::PROMO_CODE_FREE_LINE,
            self::LOTTERY_NAME_POWERBALL
        );

        return [
            'bonus type free line' => [Helpers_General::PROMO_CODE_BONUS_TYPE_FREE_LINE, true, false, $bonusTypeFreeLineMessage],
            'bonus type bonus money' => [Helpers_General::PROMO_CODE_BONUS_TYPE_BONUS_MONEY, false, true, $bonusTypeBonusMoneyMessage],
        ];
    }

    /**
     * @test
     */
    public function processContentWithWrongFormType_UnsupportedFormType(): void
    {
        $unsupportedFormType = 3;

        $this->expectExceptionMessage('Unsupported Form Type: ' . $unsupportedFormType);

        $promoCodeForm = $this->getPromoCodeFormInstance(
            $this->whitelabel,
            $unsupportedFormType
        );

        $promoCodeForm->process_content();
    }

    /**
     * @test
     */
    public function processContentWithDiscountPromoCode_InsufficientAmountFormError(): void
    {
        $whitelabelPromoCode = $this->createWhitelabelPromoCodeDiscount(
            self::PROMO_CODE_DISCOUNT,
            Helpers_General::PROMO_CODE_TYPE_PURCHASE,
            self::PROMO_CODE_AMOUNT,
            Helpers_General::PROMO_CODE_DISCOUNT_TYPE_AMOUNT
        );

        // User enters the PromoCode in the Form
        $this->applyPromoCodeOrder($whitelabelPromoCode);
        $this->setLotteryPowerballTicketOrderSession();

        $lotteryBasketForm = $this->getPromoCodeApplicableFormInstance(
            Forms_Wordpress_Lottery_Basket::class,
            Forms_Whitelabel_Bonuses_Promocodes_Code::TYPE_PURCHASE,
        );

        $lotteryBasketForm->process_form();

        $promoCodeForm = $lotteryBasketForm->getPromoCodeForm();

        $errors = $promoCodeForm->get_errors();
        $message = $promoCodeForm->get_message();

        $expectedError = 'Your order amount is insufficient to apply this promo code.';

        $bonusTypeDiscountMessage = sprintf(
            $this->getFormMessage(Helpers_General::PROMO_CODE_BONUS_TYPE_DISCOUNT),
            self::PROMO_CODE_DISCOUNT,
            self::PROMO_CODE_AMOUNT_FORMATTED
        );

        $orderSum = (float) Helpers_Currency::sum_order(false);

        $this->assertFalse($lotteryBasketForm->promoCodeDiscountActive);
        $this->assertTrue($lotteryBasketForm->isPromoCodeBonusTypeDiscount());

        $this->assertTrue($promoCodeForm->isPromoCodeBonusTypeDiscount());
        $this->assertTrue($promoCodeForm->hasErrors());
        $this->assertArrayHasKey('input.promo_code', $errors);
        $this->assertSame($expectedError, $errors['input.promo_code']);
        $this->assertSame($bonusTypeDiscountMessage, $message);

        $this->assertGreaterThan($orderSum, self::PROMO_CODE_AMOUNT);
    }

    /**
     * @test
     */
    public function processForm_Register_WrongPromoCode(): void
    {
        $whitelabelPromoCode = $this->createWhitelabelPromoCodeFreeLine(
            self::PROMO_CODE_FREE_LINE,
            Helpers_General::PROMO_CODE_TYPE_PURCHASE_DEPOSIT,
            null
        );

        // User enters the PromoCode in the Form
        $this->applyPromoCodeRegister($whitelabelPromoCode);

        $form = $this->getPromoCodeApplicableFormInstance(
            Forms_Wordpress_User_Register::class,
            Forms_Whitelabel_Bonuses_Promocodes_Code::TYPE_REGISTER,
        );

        $form->process_form();

        $promoCodeForm = $form->getPromoCodeForm();
        $errors = $promoCodeForm->get_errors();
        $message = $promoCodeForm->get_message();

        $expectedError = 'Wrong promo code!';

        $this->assertFalse($promoCodeForm->issetPromoCode());
        $this->assertTrue($promoCodeForm->hasErrors());
        $this->assertArrayHasKey('register.promo_code', $errors);
        $this->assertSame($expectedError, $errors['register.promo_code']);
        $this->assertEmpty($message);
    }

    /**
     * @test
     * @dataProvider getPurchaseDepositFormTypeMethodsDataProvider
     */
    public function processContentWithFreeLinePromoCode_BonusTicketCannotBeApplied(
        string $formClassName,
        int $formType
    ): void {
        $whitelabelPromoCode = $this->createWhitelabelPromoCodeFreeLine(
            self::PROMO_CODE_FREE_LINE,
            Helpers_General::PROMO_CODE_TYPE_PURCHASE_DEPOSIT,
            null
        );

        // User enters the PromoCode in the Form
        $this->applyPromoCodeOrder($whitelabelPromoCode);

        $form = $this->getPromoCodeApplicableFormInstance(
            $formClassName,
            $formType
        );

        $form->process_form();

        $promoCodeForm = $form->getPromoCodeForm();
        $errors = $promoCodeForm->get_errors();
        $message = $promoCodeForm->get_message();

        $promoCodeCampaign = $promoCodeForm->getPromoCodeCampaign();

        $expectedError = 'Bonus ticket cannot be applied!';

        $this->assertTrue($promoCodeForm->isPromoCodeBonusTypeFreeLine());
        $this->assertTrue($promoCodeForm->hasErrors());
        $this->assertArrayHasKey('input.promo_code', $errors);
        $this->assertSame($expectedError, $errors['input.promo_code']);
        $this->assertNull($promoCodeCampaign['lottery_id']);
        $this->assertEmpty($message);
    }

    /**
     * @test
     */
    public function processContentWithFreeLinePromoCode_UserRegister_BonusTicketCannotBeApplied(): void
    {
        $whitelabelPromoCode = $this->createWhitelabelPromoCodeFreeLine(
            self::PROMO_CODE_FREE_LINE,
            Helpers_General::PROMO_CODE_TYPE_REGISTER,
            null
        );

        $this->expectExceptionMessage(sprintf(
            'Could not get valid \'lottery_id\' for WhitelabelCampaign ID: %s, User ID: %s',
            $whitelabelPromoCode->whitelabel_campaign_id,
            $this->whitelabelUser->id
        ));

        // User enters the PromoCode in the Form
        $this->applyPromoCodeRegister($whitelabelPromoCode);

        $form = $this->getPromoCodeApplicableFormInstance(
            Forms_Wordpress_User_Register::class,
            Forms_Whitelabel_Bonuses_Promocodes_Code::TYPE_REGISTER
        );

        $form->process_form();

        $promoCodeForm = $form->getPromoCodeForm();

        $errors = $promoCodeForm->get_errors();
        $message = $promoCodeForm->get_message();

        $promoCodeCampaign = $promoCodeForm->getPromoCodeCampaign();

        $expectedError = 'Bonus ticket cannot be applied!';

        $this->assertTrue($promoCodeForm->isPromoCodeBonusTypeFreeLine());
        $this->assertTrue($promoCodeForm->hasErrors());
        $this->assertArrayHasKey('register.promo_code', $errors);
        $this->assertSame($expectedError, $errors['register.promo_code']);
        $this->assertNull($promoCodeCampaign['lottery_id']);
        $this->assertEmpty($message);

        $this->expectException(RecordNotFound::class);
        $this->whitelabelUserTicketRepository->getOneByUserAndLotteryId(
            $this->whitelabelUser->id,
            $this->lotteryPowerball->id
        );
    }

    /**
     * @test
     * @dataProvider getTicketPurchaseBonusTypeDataProvider
     */
    public function useForWhitelabelTransaction_TicketPurchaseOrder_GetPromoCodeUsedForTheGivenWhitelabelTransaction(
        int $bonusType,
        bool $bonusTypeFreeLineExpected,
        bool $bonusTypeDiscountExpected,
        string $bonusTypeExpectedMessage = '',
    ): void {

        $promoCodeFormType = Forms_Whitelabel_Bonuses_Promocodes_Code::TYPE_PURCHASE;

        $whitelabelPromoCode = $this->createWhitelabelPromoCodeForPurchase($bonusType);

        // User enters the PromoCode in the Form
        $this->applyPromoCodeOrder($whitelabelPromoCode);
        $this->setLotteryPowerballTicketOrderSession();

        $lotteryBasketForm = $this->getPromoCodeApplicableFormInstance(
            Forms_Wordpress_Lottery_Basket::class,
            $promoCodeFormType
        );

        $lotteryBasketForm->process_form();

        $promoCodeForm = $lotteryBasketForm->getPromoCodeForm();
        $errors = $promoCodeForm->get_errors();
        $message = $promoCodeForm->get_message();

        $userPromoCode = $this->whitelabelUserPromoCodeRepository->findOneByCodeIdAndUserId(
            $whitelabelPromoCode->id,
            $this->whitelabelUser->id
        );

        $this->assertSame($bonusTypeDiscountExpected, $lotteryBasketForm->promoCodeDiscountActive);
        $this->assertSame($bonusTypeFreeLineExpected, $promoCodeForm->isPromoCodeBonusTypeFreeLine());

        $this->assertEmpty($errors);
        $this->assertSame($bonusTypeExpectedMessage, $message);

        $this->assertSame($userPromoCode->type, $promoCodeFormType);
        $this->assertNotNull($userPromoCode->whitelabel_transaction_id);
        $this->assertNotNull($userPromoCode->usedAt);
    }

    /**
     * @test
     * @dataProvider getTicketDepositBonusTypeDataProvider
     */
    public function useForWhitelabelTransaction_DepositOrder_GetPromoCodeUsedForTheGivenWhitelabelTransaction(
        int $bonusType,
        bool $bonusTypeFreeLineExpected,
        bool $bonusTypeBonusMoneyExpected,
        string $bonusTypeExpectedMessage = '',
    ): void {
        $promoCodeFormType = Forms_Whitelabel_Bonuses_Promocodes_Code::TYPE_DEPOSIT;

        $whitelabelPromoCode = $this->createWhitelabelPromoCodeForDeposit($bonusType);

        // User enters the PromoCode in the Form
        $this->applyPromoCodeOrder($whitelabelPromoCode);

        $myAccountDepositForm = $this->getPromoCodeApplicableFormInstance(
            Forms_Wordpress_Myaccount_Deposit::class,
            $promoCodeFormType
        );

        $myAccountDepositForm->process_form();

        $promoCodeForm = $myAccountDepositForm->getPromoCodeForm();
        $errors = $promoCodeForm->get_errors();
        $message = $promoCodeForm->get_message();

        $userPromoCode = $this->whitelabelUserPromoCodeRepository->findOneByCodeIdAndUserId(
            $whitelabelPromoCode->id,
            $this->whitelabelUser->id
        );

        $this->assertSame($bonusTypeBonusMoneyExpected, $promoCodeForm->isPromoCodeBonusTypeBonusMoney());
        $this->assertSame($bonusTypeFreeLineExpected, $promoCodeForm->isPromoCodeBonusTypeFreeLine());

        $this->assertEmpty($errors);
        $this->assertSame($bonusTypeExpectedMessage, $message);

        $this->assertSame($userPromoCode->type, $promoCodeFormType);
        $this->assertNotNull($userPromoCode->whitelabel_transaction_id);
        $this->assertNotNull($userPromoCode->usedAt);
    }

    /**
     * @test
     */
    public function calcUserBonusBalance_RegisterWithBonusMoneyPromoCode_ShouldReturnCorrectBonusBalance(): void
    {
        $bonusAmount = 10.00;
        $promoCodeFormType = Forms_Whitelabel_Bonuses_Promocodes_Code::TYPE_REGISTER;
        $whitelabelPromoCode = $this->createWhitelabelPromoCodeBonusMoney(
            self::PROMO_CODE_BONUS_MONEY,
            Helpers_General::PROMO_CODE_TYPE_REGISTER,
            $bonusAmount,
            Helpers_General::PROMO_CODE_BONUS_BALANCE_TYPE_AMOUNT // User Register allows only type amount
        );

        // User enters the PromoCode in the Form
        $this->applyPromoCodeRegister($whitelabelPromoCode);

        $userRegisterForm = $this->getPromoCodeApplicableFormInstance(
            Forms_Wordpress_User_Register::class,
            $promoCodeFormType,
        );

        $userRegisterForm->process_form();

        $promoCodeForm = $userRegisterForm->getPromoCodeForm();
        $errors = $promoCodeForm->get_errors();
        $message = $promoCodeForm->get_message();

        $bonusBalance = $promoCodeForm->calcUserBonusBalance($this->whitelabelUserCurrency->id);

        $this->assertTrue($promoCodeForm->isPromoCodeBonusTypeBonusMoney());
        $this->assertFalse($promoCodeForm->hasErrors());
        $this->assertSame($bonusAmount, $bonusBalance);

        $this->assertEmpty($errors);
        $this->assertEmpty($message);
    }

    /**
     * @test
     */
    public function saveUserPromoCode_RegisterWithFreeLinePromoCode_GetPromoCodeUsed(): void
    {
        $promoCodeFormType = Forms_Whitelabel_Bonuses_Promocodes_Code::TYPE_REGISTER;
        $whitelabelPromoCode = $this->createWhitelabelPromoCodeFreeLine(
            self::PROMO_CODE_FREE_LINE,
            Helpers_General::PROMO_CODE_TYPE_REGISTER,
            $this->lotteryPowerball->id
        );

        // User enters the PromoCode in the Form
        $this->applyPromoCodeRegister($whitelabelPromoCode);

        $userRegisterForm = $this->getPromoCodeApplicableFormInstance(
            Forms_Wordpress_User_Register::class,
            $promoCodeFormType,
        );

        $userRegisterForm->process_form();

        $promoCodeForm = $userRegisterForm->getPromoCodeForm();
        $errors = $promoCodeForm->get_errors();
        $message = $promoCodeForm->get_message();

        $userPromoCode = $this->whitelabelUserPromoCodeRepository->findOneByCodeIdAndUserId(
            $whitelabelPromoCode->id,
            $this->whitelabelUser->id
        );

        $this->assertTrue($promoCodeForm->isPromoCodeBonusTypeFreeLine());
        $this->assertFalse($promoCodeForm->isDiscountPromoCodeApplicable());

        $this->assertEmpty($errors);
        $this->assertEmpty($message);

        $this->assertSame($userPromoCode->type, $promoCodeFormType);
        $this->assertNull($userPromoCode->whitelabel_transaction_id);
        $this->assertNotNull($userPromoCode->usedAt);
    }

    /**
     * @test
     */
    public function addBonusTicket_RegisterWithFreeLinePromoCode_GetFreeTicket(): void
    {
        $promoCodeFormType = Forms_Whitelabel_Bonuses_Promocodes_Code::TYPE_REGISTER;
        $whitelabelPromoCode = $this->createWhitelabelPromoCodeFreeLine(
            self::PROMO_CODE_FREE_LINE,
            Helpers_General::PROMO_CODE_TYPE_REGISTER,
            $this->lotteryPowerball->id
        );

        // User enters the PromoCode in the Form
        $this->applyPromoCodeRegister($whitelabelPromoCode);

        $userRegisterForm = $this->getPromoCodeApplicableFormInstance(
            Forms_Wordpress_User_Register::class,
            $promoCodeFormType,
        );

        $userRegisterForm->process_form();

        $promoCodeForm = $userRegisterForm->getPromoCodeForm();
        $errors = $promoCodeForm->get_errors();
        $message = $promoCodeForm->get_message();

        $userPromoCode = $this->whitelabelUserPromoCodeRepository->findOneByCodeIdAndUserId(
            $whitelabelPromoCode->id,
            $this->whitelabelUser->id
        );

        $this->assertTrue($promoCodeForm->isPromoCodeBonusTypeFreeLine());
        $this->assertFalse($promoCodeForm->isDiscountPromoCodeApplicable());

        $this->assertEmpty($errors);
        $this->assertEmpty($message);

        $this->assertSame($userPromoCode->type, $promoCodeFormType);
        $this->assertNull($userPromoCode->whitelabel_transaction_id);
        $this->assertNotNull($userPromoCode->usedAt);

        $userFreeTicket = $this->whitelabelUserTicketRepository->getOneByUserAndLotteryId(
            $this->whitelabelUser->id,
            $this->lotteryPowerball->id
        );

        $this->assertNotNull($userFreeTicket);
        $this->assertNull($userFreeTicket->whitelabel_transaction_id);
        $this->assertSame($this->whitelabelUser->currency_id, $userFreeTicket->currency_id) ;
        $this->assertSame(0.00, $userFreeTicket->amount);
    }

    /**
     * @throws Exception
     */
    private function createWhitelabelPromoCodeForPurchase(int $bonusType): WhitelabelPromoCode
    {
        switch ($bonusType) {
            case Helpers_General::PROMO_CODE_BONUS_TYPE_FREE_LINE:
                return $this->createWhitelabelPromoCodeFreeLine(
                    self::PROMO_CODE_FREE_LINE,
                    Helpers_General::PROMO_CODE_TYPE_PURCHASE,
                    $this->lotteryPowerball->id
                );
            case Helpers_General::PROMO_CODE_BONUS_TYPE_DISCOUNT:
                return $this->createWhitelabelPromoCodeDiscount(
                    self::PROMO_CODE_DISCOUNT,
                    Helpers_General::PROMO_CODE_TYPE_PURCHASE,
                    self::PROMO_CODE_AMOUNT_PERCENTAGE
                );
        }

        throw new Exception('Unsupported Bonus Type');
    }

    /**
     * @throws Exception
     */
    private function createWhitelabelPromoCodeForDeposit(int $bonusType): WhitelabelPromoCode
    {
        switch ($bonusType) {
            case Helpers_General::PROMO_CODE_BONUS_TYPE_FREE_LINE:
                return $this->createWhitelabelPromoCodeFreeLine(
                    self::PROMO_CODE_FREE_LINE,
                    Helpers_General::PROMO_CODE_TYPE_DEPOSIT,
                    $this->lotteryPowerball->id
                );
            case Helpers_General::PROMO_CODE_BONUS_TYPE_BONUS_MONEY:
                return $this->createWhitelabelPromoCodeBonusMoney(
                    self::PROMO_CODE_BONUS_MONEY,
                    Helpers_General::PROMO_CODE_TYPE_DEPOSIT,
                    self::PROMO_CODE_AMOUNT_PERCENTAGE
                );
        }

        throw new Exception('Unsupported Bonus Type');
    }

    protected function getUsePromoCodeCallback(PromoCodeApplicableInterface $form): callable
    {
        return function () use ($form): void {

            if ($form instanceof PromoCodeTransactionApplicableInterface) {

                $transactionType = $form->getTransactionType();
                $transaction = $this->createTransaction($transactionType);

                $form->usePromoCodeForWhitelabelTransaction($transaction->id);
            } else {
                $form->saveUserPromoCode($this->whitelabelUser->id);
                $form->addBonusTicket($this->whitelabelUser);
            }
        };
    }

    private function setLotteryPowerballTicketOrderSession(): void
    {
        $lines = [
            'numbers' => [10, 20, 30, 40],
            'bnumbers' => [15],
        ];

        $order[] = [
            'lottery' => $this->lotteryPowerball->id,
            'lines' => $lines,
            'ticket_multiplier' => 1
        ];

        Session::set('order', $order);
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

}