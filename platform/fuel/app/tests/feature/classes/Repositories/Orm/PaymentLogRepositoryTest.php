<?php

namespace Tests\Feature\Classes\Repositories\Orm;

use Models\PaymentLog;
use Enums\PaymentLogType;
use Models\PaymentMethod;
use Repositories\Orm\PaymentLogRepository;
use Repositories\WhitelabelRepository;
use Test_Feature;

class PaymentLogRepositoryTest extends Test_Feature
{
    private PaymentLogRepository $repo;

    public function setUp(): void
    {
        parent::setUp();
        $this->repo = $this->container->get(PaymentLogRepository::class);

        if (empty($this->container->get(WhitelabelRepository::class)->findOne())) {
            $this->skip('Unable to find whitelabel data. Add factory in test.');
        }

        if (empty($this->container->get(PaymentMethod::class)->find_one())) {
            $this->skip('Unable to find PaymentMethod data. Add factory in test.');
        }
    }

    /**
     * @test
     * @dataProvider save__payment_log_from_dataProvider
     * @param array $data
     */
    public function save__payment_log_from_data(array $data): void
    {
        // Given
        $message = 'message';

        $log = PaymentLog::createFromData($data, PaymentLogType::SUCCESS(), $message);

        // When
        $this->repo->save($log);

        $logFromDb = $this->repo->getById($log->id);

        $this->assertNotEmpty($logFromDb);

        foreach ($data as $key => $value) {
            $this->assertSame($logFromDb->$key, $log->$key);
        }
    }

    public function save__payment_log_from_dataProvider(): array
    {
        return [
            'empty data' => [[]],
            'some data' => [[
                'whitelabel_id' => 1,
                'payment_method_id' => 1,
            ]],
        ];
    }
}
