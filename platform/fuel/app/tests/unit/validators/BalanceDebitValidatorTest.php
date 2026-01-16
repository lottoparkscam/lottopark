<?php

namespace Tests\Unit\Validators;

use Models\Whitelabel;
use Models\WhitelabelUser;
use Repositories\Orm\CurrencyRepository;
use Repositories\Orm\WhitelabelUserRepository;
use Test_Unit;
use Validators\BalanceDebitValidator;

class BalanceDebitValidatorTest extends Test_Unit
{
    private CurrencyRepository $currencyRepository;

    public function setUp(): void
    {
        $this->currencyRepository = $this->createMock(CurrencyRepository::class);
        $this->currencyRepository->method('getAllCodes')->willReturn(['EUR', 'UAH']);
        $this->resetInput();
    }

    /** @test */
    public function isValid_CorrectDataByUserEmail(): void
    {
        $this->setInput('PATCH', ['user_email' => 'email@gg.international', 'amount' => '100.00', 'currency_code' => 'EUR']);

        $whitelabelUserRepository = $this->createMock(WhitelabelUserRepository::class);
        $whitelabelUserRepository->method('findSpecificUser')->willReturn(new WhitelabelUser());
        $balanceDebitValidator = new BalanceDebitValidator($whitelabelUserRepository, $this->currencyRepository);
        $whitelabel = new Whitelabel();

        $identifyByLogin = false;
        $balanceDebitValidator->setBuildArguments($identifyByLogin);
        $balanceDebitValidator->setExtraCheckArguments($whitelabel);
        $isValid = $balanceDebitValidator->isValid();
        $this->assertTrue($isValid);
    }

    /** @test */
    public function isValid_CorrectDataByUserLogin(): void
    {
        $this->setInput('PATCH', ['user_login' => 'username', 'amount' => '100.00', 'currency_code' => 'EUR']);

        $whitelabelUserRepository = $this->createMock(WhitelabelUserRepository::class);
        $whitelabelUserRepository->method('findSpecificUser')->willReturn(new WhitelabelUser());
        $balanceDebitValidator = new BalanceDebitValidator($whitelabelUserRepository, $this->currencyRepository);
        $whitelabel = new Whitelabel();

        $identifyByLogin = true;
        $balanceDebitValidator->setBuildArguments($identifyByLogin);
        $balanceDebitValidator->setExtraCheckArguments($whitelabel);
        $isValid = $balanceDebitValidator->isValid();
        $this->assertTrue($isValid);
    }

    /** @test */
    public function isValid_WrongAmount(): void
    {
        $this->setInput('PATCH', ['user_email' => 'email@gg.international', 'amount' => '-100.00', 'currency_code' => 'EUR']);

        $whitelabelUserRepository = $this->createMock(WhitelabelUserRepository::class);
        $whitelabelUserRepository->method('findSpecificUser')->willReturn(new WhitelabelUser());
        $balanceDebitValidator = new BalanceDebitValidator($whitelabelUserRepository, $this->currencyRepository);
        $whitelabel = new Whitelabel();

        $identifyByLogin = false;
        $balanceDebitValidator->setBuildArguments($identifyByLogin);
        $balanceDebitValidator->setExtraCheckArguments($whitelabel);
        $isValid = $balanceDebitValidator->isValid();
        $this->assertFalse($isValid);
        $this->assertEquals('Wrong balance amount', $balanceDebitValidator->getErrors()['amount']);
    }

    /** @test */
    public function isValid_FieldsDoNotExistAndAreRequired(): void
    {
        $this->setInput('PATCH', []);

        $whitelabelUserRepository = $this->createMock(WhitelabelUserRepository::class);
        $whitelabelUserRepository->method('findSpecificUser')->willReturn(new WhitelabelUser());
        $balanceDebitValidator = new BalanceDebitValidator($whitelabelUserRepository, $this->currencyRepository);
        $whitelabel = new Whitelabel();

        $identifyByLogin = false;
        $balanceDebitValidator->setBuildArguments($identifyByLogin);
        $balanceDebitValidator->setExtraCheckArguments($whitelabel);
        $isValid = $balanceDebitValidator->isValid();
        $this->assertFalse($isValid);
        $this->assertEquals('Field User Email is required', $balanceDebitValidator->getErrors()['user_email']);
        $this->assertEquals('The field Amount is required.', $balanceDebitValidator->getErrors()['amount']);
        $this->assertEquals('The field Currency Code is required.', $balanceDebitValidator->getErrors()['currency_code']);
    }

    /** @test */
    public function isValid_UserDoesNotExist(): void
    {
        $this->setInput('PATCH', ['user_email' => 'email@gg.international', 'amount' => '100.00', 'currency_code' => 'EUR']);

        $whitelabelUserRepository = $this->createMock(WhitelabelUserRepository::class);
        $whitelabelUserRepository->method('findSpecificUser')->willReturn(null);
        $balanceDebitValidator = new BalanceDebitValidator($whitelabelUserRepository, $this->currencyRepository);
        $whitelabel = new Whitelabel();

        $identifyByLogin = false;
        $balanceDebitValidator->setBuildArguments($identifyByLogin);
        $balanceDebitValidator->setExtraCheckArguments($whitelabel);
        $isValid = $balanceDebitValidator->isValid();
        $this->assertFalse($isValid);
        $this->assertEquals('User does not exist', $balanceDebitValidator->getErrors()['errors']);
    }
}
