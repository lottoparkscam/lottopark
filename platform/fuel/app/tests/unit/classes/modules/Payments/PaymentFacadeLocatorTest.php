<?php

namespace Unit\Modules\Payments;

use DI\NotFoundException;
use Modules\Payments\PaymentFacadeContract;
use Modules\Payments\PaymentFacadeLocator;
use Psr\Container\ContainerInterface;
use Test_Unit;
use Wrappers\Decorators\ConfigContract;

class PaymentFacadeLocatorTest extends Test_Unit
{
    private ContainerInterface $containerService;
    private ConfigContract $config;
    private PaymentFacadeLocator $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->containerService = $this->createMock(ContainerInterface::class);
        $this->config = $this->createMock(ConfigContract::class);
        $this->service = new PaymentFacadeLocator($this->containerService, $this->config);
    }

    /** @test */
    public function getAlias__slug_is_upper_cased__returns_lower_cased_full_alias(): void
    {
        // Given
        $slug = 'Jeton';
        $expectedAlias = 'payments.jeton.facade';

        // When
        $actual = $this->service->getAlias($slug);

        // Then
        $this->assertSame($expectedAlias, $actual);
    }

    /** @test */
    public function hasFacade__with_slug_when_not_found_exception__returns_false(): void
    {
        // Expects
        $message = 'Not found';
        $exception = new NotFoundException($message);

        // Given
        $slug = 'jeton';
        $expectedAlias = 'payments.jeton.facade';
        $expectedResult = false;

        $this->containerService
            ->expects($this->once())
            ->method('get')
            ->with($expectedAlias)
            ->willThrowException($exception);

        // When
        $actual = $this->service->hasFacade($slug);

        // Then
        $this->assertSame($expectedResult, $actual);
    }

    /** @test */
    public function hasFacade__with_string_looking_as_number__calls_getById(): void
    {
        // Given
        $idLookingAsNumber = '1';
        $expected = true;

        $locator = $this->createPartialMock(PaymentFacadeLocator::class, ['getById']);
        $locator
            ->expects($this->once())
            ->method('getById')
            ->willReturn($this->createMock(PaymentFacadeContract::class));

        // When
        $actual = $locator->hasFacade($idLookingAsNumber);

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function hasFacade__not_slug_given__returns_false(): void
    {
        // Given
        $expected = false;
        $slug = '';

        // When
        $actual = $this->service->hasFacade($slug);

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function hasFacade__payments_by_slug_exists__returns_true(): void
    {
        // Given
        $expected = true;
        $slug = 'slug';

        $facade = $this->createMock(PaymentFacadeContract::class);

        $this->containerService
            ->expects($this->once())
            ->method('get')
            ->with('payments.slug.facade')
            ->willReturn($facade);

        // When
        $actual = $this->service->hasFacade($slug);

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getBySlug__checks_container_using_alias_name(): void
    {
        // Given
        $slug = 'jeton';
        $expectedAlias = 'payments.jeton.facade';
        $expectedResult = $this->createMock(PaymentFacadeContract::class);

        $this->containerService
            ->expects($this->once())
            ->method('get')
            ->with($expectedAlias)
            ->willReturn($expectedResult);

        // When
        $actual = $this->service->getBySlug($slug);

        // Then
        $this->assertSame($expectedResult, $actual);
    }

    /** @test */
    public function getById__payments_config_exists__extracts_slug_and_get_results(): void
    {
        // Given
        $expectedAlias = 'payments.jeton.facade';
        $expectedResult = $this->createMock(PaymentFacadeContract::class);

        $this->containerService
            ->expects($this->once())
            ->method('get')
            ->with($expectedAlias)
            ->willReturn($expectedResult);

        $paymentConfigs = [
            'jeton' => ['id' => 1, 'slug' => 'jeton']
        ];

        $this->config
            ->expects($this->once())
            ->method('get')
            ->with('payments')
            ->willReturn($paymentConfigs);

        // When
        $actual = $this->service->getById(1);

        // Then
        $this->assertSame($expectedResult, $actual);
    }

    /** @test */
    public function getById__by_not_existing_payment_id__throws_di_exception(): void
    {
        // Except
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Unable to find payment by id #1');

        // Given
        $paymentId = 1;

        $this->config
            ->expects($this->once())
            ->method('get')
            ->with('payments')
            ->willReturn([]);

        // When
        $this->service->getById($paymentId);
    }
}
