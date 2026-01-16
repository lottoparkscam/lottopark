<?php

namespace Feature\Modules\Payments;

use Models\PaymentLog;
use Enums\PaymentLogType;
use Modules\Payments\PaymentLogger;
use Test_Feature;

class PaymentLoggerTest extends Test_Feature
{
    private PaymentLogger $logger;

    public function setUp(): void
    {
        parent::setUp();

        $this->logger = $this->container->get(PaymentLogger::class);
    }

    /**
     * @test
     * @dataProvider methods_dataProvider
     * @param string $method
     * @param int $type
     */
    public function log__by_given_type_without_transaction_object_in_context__stores_in_db(string $method, int $type): void
    {
        // Given
        $message = 'some message';
        $context = ['field1' => 1, 'field2' => 2];

        // When
        $this->logger->$method($message, $context);

        // Then
        /** @var PaymentLog $actual */
        $actual = PaymentLog::query()->order_by('id', 'desc')->get_one();

        $this->assertSame($message, $actual->message);
        $this->assertEquals($type, $actual->type);
        $this->assertSame($context, $actual->data_json);
        $this->assertNotEmpty($actual->date);
    }

    public function methods_dataProvider(): array
    {
        return [
            'logInfo' => ['logInfo', PaymentLogType::INFO],
            'logWarning' => ['logWarning', PaymentLogType::WARNING],
            'logError' => ['logError', PaymentLogType::ERROR],
            'logSuccess' => ['logSuccess', PaymentLogType::SUCCESS],
        ];
    }
}
