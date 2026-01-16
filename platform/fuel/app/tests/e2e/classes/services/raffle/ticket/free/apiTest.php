<?php

class Tests_E2e_Classes_Services_Lcs_Raffle_Ticket_Free_Api extends Test_Feature
{
    /** @var Services_Lcs_Raffle_Ticket_Free_Contract */
    private $api;

    public function setUp(): void
    {
        parent::setUp();
        $this->api = Container::get(Services_Lcs_Raffle_Ticket_Free_Contract::class);
    }

    public function test_it_returns_free_tickets()
    {
        $response = $this->api->request('gg-world-raffle');
        $body = $response->get_body();
        $this->assertSame($response->get_status_code(), 200);
        $this->assertIsArray($body);
        if (isset($body['data']['free_numbers'])) {
            $this->assertSame($body['data']['free_numbers'], $response->get_data());
        }
    }
}
