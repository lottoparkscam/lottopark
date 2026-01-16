<?php

namespace Tests\Unit\Validators\Rules;

use LogicException;
use Repositories\Orm\CurrencyRepository;
use Test_Unit;
use Validation;
use Validators\Rules\CurrencyCode;

class CurrencyCodeTest extends Test_Unit
{
    private CurrencyRepository $currencyRepository;

    public function setUp(): void
    {
        $this->currencyRepository = $this->createMock(CurrencyRepository::class);
        $this->currencyRepository->method('getAllCodes')->willReturn(['EUR', 'UAH']);
    }

    /** @test */
    public function build_ThrowsExceptionWhenNotConfiguredProperly(): void
    {
        $validation = Validation::forge('incorrect_rule_on_form');
        $rule = new CurrencyCode('currency_code_one', 'currency code one label');
        $rule->setValidation($validation);

        $this->expectException(LogicException::class);
        $rule->applyRules();
    }

    /** @test */
    public function multipleFieldsPass(): void
    {
        $validation = Validation::forge('correct_currencies');
        $inputData = [
            'currency_code_one' => 'EUR',
            'currency_code_two' => 'UAH',
        ];

        $this->addRuleToValidationAndApplyRules($validation, 'currency_code_one', 'currency code one label');
        $this->addRuleToValidationAndApplyRules($validation, 'currency_code_two', 'currency code two label');

        $this->assertTrue($validation->run($inputData));
    }

    /** @test */
    public function oneNonExistingCurrencyField_ReturnsFalse(): void
    {
        $validation = Validation::forge('non_existing_currency');
        $inputData = [
            'currency_code_one' => 'UKK', // does not exist
            'currency_code_two' => 'UAH',
        ];

        $this->addRuleToValidationAndApplyRules($validation, 'currency_code_one', 'currency code one label');
        $this->addRuleToValidationAndApplyRules($validation, 'currency_code_two', 'currency code two label');

        $this->assertFalse($validation->run($inputData));
    }

    /** @test */
    public function getName_TestAbstractClassImplementation(): void
    {
        $name = 'test_name';
        $rule = CurrencyCode::build($name, 'testLabel');
        $this->assertEquals($name, $rule->getName());
    }

    /** @test */
    public function getType_TestAbstractClassImplementation(): void
    {
        $name = 'test_name';
        $rule = CurrencyCode::build($name, 'testLabel');
        $this->assertEquals('string', $rule->getType());
    }

    private function addRuleToValidationAndApplyRules(Validation $validation, string $name, string $label): void
    {
        /** @var CurrencyCode $rule */
        $rule = CurrencyCode::build($name, $label);
        $rule->configure($this->currencyRepository);
        $rule->setValidation($validation);
        $rule->applyRules();
    }
}
