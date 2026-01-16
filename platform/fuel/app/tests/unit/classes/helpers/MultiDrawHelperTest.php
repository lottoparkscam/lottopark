<?php

namespace Tests\Unit\Classes\Helpers;

use Helpers\MultiDrawHelper;
use Models\WhitelabelMultiDrawOption;
use Test_Unit;

class MultiDrawHelperTest extends Test_Unit
{
    /** @test */
    public function calculateMultiDrawTicketPrice()
    {
        $whitelabelMultiDrawOption = new WhitelabelMultiDrawOption(['tickets' => 200, 'discount' => 30]);
        $ticketPrice = MultiDrawHelper::calculateMultiDrawTicketPrice($whitelabelMultiDrawOption, 5.00);
        $this->assertSame(700.0, $ticketPrice);
    }
}
