<?php

declare(strict_types=1);

namespace Tests\Unit\Classes\Services;

use PaymentMethodService;
use Repositories\Orm\WhitelabelPaymentMethodCurrencyRepository;
use Repositories\Orm\WhitelabelPaymentMethodRepository;
use Services\Logs\FileLoggerService;
use Validators\Rules\Rule;
use Validators\Rules\Text;
use Fuel\Core\Validation;
use Fuel\Tasks\Factory\Utils\Faker;
use Test_Unit;

class PaymentMethodServiceTest extends Test_Unit
{
    public function setUp(): void
    {
        parent::setUp();

        $this->fileLoggerServiceMock = $this->createMock(FileLoggerService::class);

        $this->paymentMethodServiceUnderTest = new PaymentMethodService(
            $this->container->get(WhitelabelPaymentMethodRepository::class),
            $this->container->get(WhitelabelPaymentMethodCurrencyRepository::class),
            $this->fileLoggerServiceMock
        );
    }

    /**
     * @test
     */
    public function saveFormUserDetailsToCookie_withoutConfiguration(): void
    {
        $this->fileLoggerServiceMock
            ->expects($this->once())
            ->method('error');

        $this->paymentMethodServiceUnderTest->saveFormUserDetailsToCookie();

        $this->assertFalse($this->paymentMethodServiceUnderTest->isUserFormValidationConfigured());
    }

    /**
     * @test
     */
    public function saveFormUserDetailsToCookie_withConfiguration(): void
    {
        $this->fileLoggerServiceMock
            ->expects($this->never())
            ->method('error');

        $faker = Faker::forge();

        $paymentMethodId = 1;

        $validation = Validation::forge('user-payment-method');

        $inputData = [
            'name' => $faker->firstName(),
            'lastname' => $faker->lastName(),
        ];

        $this->applyRule(new Text('name', 'Name'), $validation);
        $this->applyRule(new Text('lastname', 'Lastname'), $validation);

        $validation->run($inputData);

        $this->paymentMethodServiceUnderTest->configureUserFormValidation($paymentMethodId, $validation);
        $this->paymentMethodServiceUnderTest->saveFormUserDetailsToCookie();

        $this->assertTrue($this->paymentMethodServiceUnderTest->isUserFormValidationConfigured());
    }

    private function applyRule(Rule $rule, Validation $validation): void
    {
        $rule->setValidation($validation);
        $rule->applyRules();
    }
}
