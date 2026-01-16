<?php

namespace Tests\Unit\Helpers;

use Helpers\TransactionHelper;
use Helpers_General;
use Test_Unit;

final class TransactionHelperTest extends Test_Unit
{
    /**
     * @test
     */
    public function getTypes(): void
    {
        $expected = [
            Helpers_General::TYPE_TRANSACTION_PURCHASE => 'Purchase',
            Helpers_General::TYPE_TRANSACTION_DEPOSIT => 'Deposit',
        ];

        $typeFormatted = TransactionHelper::getTypes();

        $this->assertSame($expected, $typeFormatted);
    }

    /**
     * @test
     */
    public function getStatuses(): void
    {
        $expected = [
            Helpers_General::STATUS_TRANSACTION_PENDING => 'Pending',
            Helpers_General::STATUS_TRANSACTION_APPROVED => 'Approved',
            Helpers_General::STATUS_TRANSACTION_ERROR => 'Error',
        ];

        $typeFormatted = TransactionHelper::getStatuses();

        $this->assertSame($expected, $typeFormatted);
    }
}
