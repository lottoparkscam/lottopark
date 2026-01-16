<?php

use Models\WhitelabelRaffleTicket;

class Tests_E2e_Classes_Services_Lcs_Raffle_Ticket_Get_Api extends Test_Feature
{
    private const RAFFLE_SLUG = 'gg-world-raffle';

    /** @var Services_Lcs_Raffle_Ticket_Get_Contract */
    private $api;
    /** @var WhitelabelRaffleTicket */
    private $ticket_dao;

    public function setUp(): void
    {
        parent::setUp();
        $this->api = Container::get(Services_Lcs_Raffle_Ticket_Get_Contract::class);
        $this->ticket_dao = Container::get(WhitelabelRaffleTicket::class);
    }

    public function test_it_returns_tickets()
    {
        $payload = [
            'uuids' => $this->get_some_ticket_uuids()
        ];
        $response = $this->api->request($payload, self::RAFFLE_SLUG);
        $body = $response->get_body();
        $this->assertSame($response->get_status_code(), 200);
        $this->assertIsArray($body);
    }

    public function get_some_ticket_uuids(): array
    {
        $results = $this->ticket_dao->push_criterias([
            new Model_Orm_Criteria_With_Relation('raffle'),
            new Model_Orm_Criteria_Where('raffle.slug', self::RAFFLE_SLUG)
        ])->get_results(5);

        if (empty($results)) {
            $this->skip_due_no_expected_data_retrieved();
        }

        return array_map(function (WhitelabelRaffleTicket $ticket) {
            return $ticket->uuid;
        }, $results);
    }
}
