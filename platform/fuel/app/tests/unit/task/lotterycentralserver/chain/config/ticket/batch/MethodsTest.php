<?php

namespace Tests\Unit\Task\LotteryCentralServer\Chain\Config\Ticket\Batch;

use Helpers_Lottery;

/**
 * Task Lotterycentralserver Chain Config Ticket Batch Methods.
 */
final class MethodsTest extends \Test_Unit
{
    use \Task_Lotterycentralserver_Chain_Config_Ticket_Batch_Methods;

    /** @test */
    public function config(): void
    {
        $this->assertEquals($this->get_batch_size(Helpers_Lottery::ZAMBIA_ID), 50);
        $this->assertEquals($this->get_batch_size(Helpers_Lottery::GGWORLD_ID), 50);
        $this->assertEquals($this->get_max_iterations(Helpers_Lottery::GGWORLD_ID), 7);
    }
}
