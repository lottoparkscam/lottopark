<?php

use Models\WhitelabelRaffleTicket;

class Tests_Feature_Services_Calc_Wrappers_Cost extends Test_Feature
{
    /** @var Services_Ticket_Calc_Wrappers_Cost */
    private $cost_calc;

    private $ticket;

    public function setUp(): void
    {
        parent::setUp();
        $this->cost_calc = $this->container->get(Services_Ticket_Calc_Wrappers_Cost::class);
        /** @var WhitelabelRaffleTicket $dao */
        $dao = $this->container->get(WhitelabelRaffleTicket::class);

        $this->ticket = $dao->push_criterias([
            new Model_Orm_Criteria_With_Relation('raffle')
        ])->find_one();

        if (empty($this->ticket)) {
            $this->skip_due_no_expected_data_retrieved();
        }
    }

    public function test_it_calculates_values_for_raffle_ticket(): void
    {
        $result = $this->cost_calc->calculate_raffle_cost($this->ticket);
        $this->assertIsFloat($result);
    }
}
