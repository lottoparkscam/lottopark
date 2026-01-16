<?php


namespace Feature\Repositories\Orm;

use Container;
use Enums\PaymentLogType;
use Factory_Orm_Transaction;
use Models\PaymentLog;
use Models\WhitelabelPaymentMethod;
use Repositories\Orm\PaymentLogRepository;
use Test_Feature;

class PaymentLogRepositoryTest extends Test_Feature
{
    private PaymentLogRepository $repo;
    private Factory_Orm_Transaction $factory;

    public function setUp(): void
    {
        parent::setUp();

        $this->repo = Container::get(PaymentLogRepository::class);
        $this->factory = Container::get(Factory_Orm_Transaction::class);
    }

    /** @test */
    public function save__log_from_transaction(): void
    {
        // Given
        /** @var WhitelabelPaymentMethod $paymentMethod */
        $paymentMethod = WhitelabelPaymentMethod::find(1);
        $transaction = $this->factory->build();
        $transaction->whitelabel_payment_method = $paymentMethod;
        $someData = ['field1' => 1, 'field2' => 'test'];
        $message = 'some message';
        $logType = PaymentLogType::WARNING();

        // When
        $log = PaymentLog::createFromTransaction($transaction, $logType, $message, $someData);
        $this->repo->save($log);

        // Then
        $newInstance = $this->repo->getById($log->id);
        $this->assertSame($newInstance->data_json, $someData);
        $this->assertSame($newInstance->message, $message);
        $this->assertEquals($newInstance->type, (string)$logType);
    }
}
