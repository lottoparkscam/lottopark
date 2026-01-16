<?php

class Tests_E2e_Classes_Services_Lcs_Raffle_Ticket_Taken_Api extends Test_Feature
{
    /**
     * @param string $raffle_type
     *
     * @throws DI\DependencyException
     * @throws DI\NotFoundException
     * @dataProvider type_provider
     */
    public function test_it_returns_taken_tickets(string $raffle_type)
    {
        /** @var Services_Lcs_Raffle_Ticket_Taken_Contract $api */
        $api = Container::get(Services_Lcs_Raffle_Ticket_Taken_Contract::class);
        $response = $api->request('gg-world-raffle');
        $body = $response->get_body();
        $this->assertSame($response->get_status_code(), 200);
        $this->assertIsArray($body);
        if (isset($body['data']['taken_numbers'])) {
            $this->assertSame($body['data']['taken_numbers'], $response->get_data());
        }
    }

    public function type_provider(): array
    {
        return [
//            [Services_Lcs_Raffle_Helper::OPEN_TYPE],
            ['closed'],
        ];
    }
}
