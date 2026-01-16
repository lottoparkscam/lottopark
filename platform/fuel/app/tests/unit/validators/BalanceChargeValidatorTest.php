<?php

namespace Tests\Unit\Validators;

use Models\Whitelabel;
use Models\WhitelabelUser;
use Repositories\Orm\CurrencyRepository;
use Repositories\Orm\WhitelabelUserRepository;
use Test_Unit;
use Validators\BalanceChargeValidator;

class BalanceChargeValidatorTest extends Test_Unit
{
    private CurrencyRepository $currencyRepository;

    public function setUp(): void
    {
        $this->currencyRepository = $this->createMock(CurrencyRepository::class);
        $this->currencyRepository->method('getAllCodes')->willReturn(['EUR', 'UAH']);
        $this->resetInput();
    }

    /** @test */
    public function isValid_CorrectData(): void
    {
        $this->setInput('PATCH', ['user_email' => 'email@gg.international', 'amount' => '100.00', 'currency_code' => 'EUR']);

        $whitelabelUserRepository = $this->createMock(WhitelabelUserRepository::class);
        $whitelabelUserRepository->method('findSpecificUser')->willReturn(new WhitelabelUser());
        $balanceChargeValidator = new BalanceChargeValidator($whitelabelUserRepository, $this->currencyRepository);
        $whitelabel = new Whitelabel();
        $whitelabel->isBalanceChangeGlobalLimitEnabledInApi = false;
        $whitelabel->useLoginsForUsers = false;

        $balanceChargeValidator->setBuildArguments($whitelabel);
        $balanceChargeValidator->setExtraCheckArguments($whitelabel);
        $isValid = $balanceChargeValidator->isValid();
        $this->assertTrue($isValid);
    }

    /** @test */
    public function isValid_UsesUserLogin_CorrectData(): void
    {
        $this->setInput('PATCH', ['user_login' => 'username', 'amount' => '100.00', 'currency_code' => 'EUR']);

        $whitelabelUserRepository = $this->createMock(WhitelabelUserRepository::class);
        $whitelabelUserRepository->method('findSpecificUser')->willReturn(new WhitelabelUser());
        $balanceChargeValidator = new BalanceChargeValidator($whitelabelUserRepository, $this->currencyRepository);
        $whitelabel = new Whitelabel();
        $whitelabel->isBalanceChangeGlobalLimitEnabledInApi = false;
        $whitelabel->useLoginsForUsers = true;

        $balanceChargeValidator->setBuildArguments($whitelabel);
        $balanceChargeValidator->setExtraCheckArguments($whitelabel);
        $isValid = $balanceChargeValidator->isValid();
        $this->assertTrue($isValid);
    }

    /** @test */
    public function isValid_GlobalLimitEnabled_CorrectDataBelowLimit(): void
    {
        $this->setInput('PATCH', ['user_email' => 'email@gg.international', 'amount' => '10.00', 'currency_code' => 'EUR']);

        $whitelabelUserRepository = $this->createMock(WhitelabelUserRepository::class);
        $whitelabelUserRepository->method('findSpecificUser')->willReturn(new WhitelabelUser());
        $balanceChargeValidator = new BalanceChargeValidator($whitelabelUserRepository, $this->currencyRepository);
        $whitelabel = new Whitelabel();
        $whitelabel->isBalanceChangeGlobalLimitEnabledInApi = true;
        $whitelabel->useLoginsForUsers = false;
        $whitelabel->userBalanceChangeLimit = 100;

        $balanceChargeValidator->setBuildArguments($whitelabel);
        $balanceChargeValidator->setExtraCheckArguments($whitelabel);
        $isValid = $balanceChargeValidator->isValid();
        $this->assertTrue($isValid);
    }

    /** @test */
    public function isValid_GlobalLimitEnabled_ErrorWhenAboveLimitValue(): void
    {
        $this->setInput('PATCH', ['user_email' => 'email@gg.international', 'amount' => '100.00', 'currency_code' => 'EUR']);

        $whitelabelUserRepository = $this->createMock(WhitelabelUserRepository::class);
        $whitelabelUserRepository->method('findSpecificUser')->willReturn(new WhitelabelUser());
        $balanceChargeValidator = new BalanceChargeValidator($whitelabelUserRepository, $this->currencyRepository);
        $whitelabel = new Whitelabel();
        $whitelabel->isBalanceChangeGlobalLimitEnabledInApi = true;
        $whitelabel->useLoginsForUsers = false;
        $whitelabel->userBalanceChangeLimit = 10;

        $balanceChargeValidator->setBuildArguments($whitelabel);
        $balanceChargeValidator->setExtraCheckArguments($whitelabel);
        $isValid = $balanceChargeValidator->isValid();
        $this->assertFalse($isValid);
        $this->assertEquals('Wrong balance amount. Limit has been reached.', $balanceChargeValidator->getErrors()['amount']);
    }

    /** @test */
    public function isValid_GlobalLimitDisabled_ErrorWhenBalanceChangeIsNegative(): void
    {
        $this->setInput('PATCH', ['user_email' => 'email@gg.international', 'amount' => '-10.00', 'currency_code' => 'EUR']);

        $whitelabelUserRepository = $this->createMock(WhitelabelUserRepository::class);
        $whitelabelUserRepository->method('findSpecificUser')->willReturn(new WhitelabelUser());
        $balanceChargeValidator = new BalanceChargeValidator($whitelabelUserRepository, $this->currencyRepository);
        $whitelabel = new Whitelabel();
        $whitelabel->isBalanceChangeGlobalLimitEnabledInApi = false;
        $whitelabel->useLoginsForUsers = false;

        $balanceChargeValidator->setBuildArguments($whitelabel);
        $balanceChargeValidator->setExtraCheckArguments($whitelabel);
        $isValid = $balanceChargeValidator->isValid();
        $this->assertFalse($isValid);
        $this->assertEquals('Wrong balance amount', $balanceChargeValidator->getErrors()['amount']);
    }

    /** @test */
    public function isValid_GlobalLimitDisabled_ErrorWhenWrongCurrency(): void
    {
        $this->setInput('PATCH', ['user_email' => 'email@gg.international', 'amount' => '10.00', 'currency_code' => 'UKR']);

        $whitelabelUserRepository = $this->createMock(WhitelabelUserRepository::class);
        $whitelabelUserRepository->method('findSpecificUser')->willReturn(new WhitelabelUser());
        $balanceChargeValidator = new BalanceChargeValidator($whitelabelUserRepository, $this->currencyRepository);
        $whitelabel = new Whitelabel();
        $whitelabel->isBalanceChangeGlobalLimitEnabledInApi = false;
        $whitelabel->useLoginsForUsers = false;

        $balanceChargeValidator->setBuildArguments($whitelabel);
        $balanceChargeValidator->setExtraCheckArguments($whitelabel);
        $isValid = $balanceChargeValidator->isValid();
        $this->assertFalse($isValid);
        $this->assertEquals('Validation rule match_collection failed for Currency Code', $balanceChargeValidator->getErrors()['currency_code']);
    }

    /** @test */
    public function isValid_GlobalLimitDisabled_FieldsDoNotExistAndAreRequired(): void
    {
        $this->setInput('PATCH', []);

        $whitelabelUserRepository = $this->createMock(WhitelabelUserRepository::class);
        $whitelabelUserRepository->method('findSpecificUser')->willReturn(new WhitelabelUser());
        $balanceChargeValidator = new BalanceChargeValidator($whitelabelUserRepository, $this->currencyRepository);
        $whitelabel = new Whitelabel();
        $whitelabel->isBalanceChangeGlobalLimitEnabledInApi = false;
        $whitelabel->useLoginsForUsers = false;

        $balanceChargeValidator->setBuildArguments($whitelabel);
        $balanceChargeValidator->setExtraCheckArguments($whitelabel);
        $isValid = $balanceChargeValidator->isValid();
        $this->assertFalse($isValid);
        $this->assertEquals('Field User Email is required', $balanceChargeValidator->getErrors()['user_email']);
        $this->assertEquals('The field Amount is required.', $balanceChargeValidator->getErrors()['amount']);
        $this->assertEquals('The field Currency Code is required.', $balanceChargeValidator->getErrors()['currency_code']);
    }

    /** @test */
    public function isValid_GlobalLimitDisabled_UserForBalanceChangeDoesNotExist(): void
    {
        $this->setInput('PATCH', ['user_email' => 'email@gg.international', 'amount' => '100.00', 'currency_code' => 'EUR']);

        $whitelabelUserRepository = $this->createMock(WhitelabelUserRepository::class);
        $whitelabelUserRepository->method('findSpecificUser')->willReturn(null);
        $balanceChargeValidator = new BalanceChargeValidator($whitelabelUserRepository, $this->currencyRepository);
        $whitelabel = new Whitelabel();
        $whitelabel->isBalanceChangeGlobalLimitEnabledInApi = false;
        $whitelabel->useLoginsForUsers = false;

        $balanceChargeValidator->setBuildArguments($whitelabel);
        $balanceChargeValidator->setExtraCheckArguments($whitelabel);
        $isValid = $balanceChargeValidator->isValid();
        $this->assertFalse($isValid);
        $this->assertEquals('User does not exist', $balanceChargeValidator->getErrors()['errors']);
    }
}
