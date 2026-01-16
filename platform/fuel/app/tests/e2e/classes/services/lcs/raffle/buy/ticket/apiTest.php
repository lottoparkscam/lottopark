<?php

use Fuel\Tasks\Factory\Utils\Faker;

class Tests_E2e_Classes_Services_Lcs_Raffle_Buy_Ticket_Api extends Test_Feature
{
    /** @var Services_Lcs_Raffle_Buy_Ticket_Contract */
    private $service;

    private $free_tickets_slice = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->skip_on_production_or_staging_env();
        
        $this->service = $this->container->get(Services_Lcs_Raffle_Buy_Ticket_Contract::class);
        $free_tickets_api = $this->container->get(Services_Lcs_Raffle_Ticket_Free_Contract::class);

        $free_tickets = $free_tickets_api->request('gg-world-raffle')->get_data();
        $this->free_tickets_slice = array_slice($free_tickets, 0, 2);

        if (empty($this->free_tickets_slice)) {
            $this->skip_due_no_expected_data_retrieved();
        }
    }

    /**
     *  Scenario:
     *  API is called with payload to buy tickets with proper numbers,
     *  it returns success status code when invoked.
     */
    public function test_it_buys_tickets()
    {
        $response = $this->service->request([
            'tickets' => [
                [
                    'token'  => Faker::forge()->uuid(),
                    'amount' => 20,
                    'ip'     => Faker::forge()->ipv4(),
                    'lines'  => array_map(function (int $number) {
                        return [
                            'numbers' => [[$number]],
                        ];
                    }, $this->free_tickets_slice)
                ]
            ]
        ], 'gg-world-raffle');
        $this->assertTrue($response->is_success());
    }
}
