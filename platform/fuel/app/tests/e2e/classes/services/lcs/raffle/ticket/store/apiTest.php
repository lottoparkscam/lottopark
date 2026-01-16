<?php

use Models\WhitelabelRaffleTicket;

class Tests_E2e_Classes_Services_Lcs_Raffle_Ticket_Store_Api extends Test_Feature
{
    private const RAFFLE_SLUG = 'gg-world-raffle';

    /** @var WhitelabelRaffleTicket */
    private $ticket_dao;
    /** @var Services_Lcs_Raffle_Ticket_Store_Contract */
    private $api;

    public function setUp(): void
    {
        parent::setUp();

        $this->skip_on_production_or_staging_env();

        $this->ticket_dao = $this->container->get(WhitelabelRaffleTicket::class);
        $this->api = $this->container->get(Services_Lcs_Raffle_Ticket_Store_Contract::class);
    }

    public function test_it_stores_ticket_as_paid_out(): void
    {
        $unsynchronized_tickets = $this->ticket_dao->get_all_unsynchronized_tickets(self::RAFFLE_SLUG);
        if (empty($unsynchronized_tickets)) {
            $this->skip_due_no_expected_data_retrieved();
        }
        $payload['uuids'] = array_map(function (WhitelabelRaffleTicket $ticket) {
            return $ticket->uuid;
        }, $unsynchronized_tickets);

        $response = $this->api->request($payload, self::RAFFLE_SLUG, 'closed');
        $data = $response->get_body();
        $this->assertSame(200, $response->get_status_code());
        $this->assertSame(count($payload['uuids']), $data['updated_count']);
    }
}
