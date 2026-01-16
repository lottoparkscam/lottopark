<?php

namespace Fuel\Tasks\Seeders;

use Fuel\Core\DB;

/**
 * Payment Synchronizer uses payment method names to determine correct slug and implementation to execute.
 * Currently, the synchronizer does not fire as it expects "Astro" to be the name.
 * Production has "Astro", but local environments have not been updated.
 */
final class UpdateAstroPayOneTouchPaymentMethodName extends Seeder
{
    protected function columnsStaging(): array
    {
        return [];
    }

    protected function rowsStaging(): array
    {
        return [];
    }

    public function execute(): void
    {
        DB::update('payment_method')
            ->value('name', 'Astro')
            ->where('name', '=', 'AstroPay One Touch')
            ->execute();
    }
}
