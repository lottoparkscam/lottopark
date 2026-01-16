<?php

/**
 * Test for fetching LCS draws list.
 */
class Tests_E2e_Classes_Services_Lcs_Draws_Api extends Test_Feature
{
    /** @var Services_Lcs_Draws_Contract */
    private $api;

    public function setUp(): void
    {
        parent::setUp();
        $this->api = $this->container->get(Services_Lcs_Draws_Contract::class);
    }

    /**
     * Scenario:
     * API is called without any params,
     * It returns no more than default LIMIT rows.
     */
    public function test_it_gets_data(): void
    {
        $response = $this->api->request('gg-world-raffle');
        $data = $response->get_body();
        $this->assertTrue(is_array($data));
        $this->assertSame(200, $response->get_status_code());
        $this->assertLessThanOrEqual(Services_Lcs_Draws_Contract::DEFAULT_LIMIT, count($data));
    }

    /**
     * Scenario:
     * API is called with limits params,
     * It returns paginated results.
     *
     * @param int $limit
     * @param int $offset
     *
     * @dataProvider limitsProvider
     */
    public function test_it_gets_data_with_limits(int $limit, int $offset): void
    {
        $all_count = count($this->api->request(
            'gg-world-raffle',
            'closed',
            $limit,
            $offset
        )->get_body());

        if ($limit > $all_count) {
            $this->skip_due_no_expected_data_retrieved();
        }

        $response = $this->api->request(
            'gg-world-raffle',
            'closed',
            $limit,
            $offset
        );

        $data = $response->get_body();
        $this->assertCount($limit, $data);
        $this->assertTrue(is_array($data));
        $this->assertSame(200, $response->get_status_code());
    }

    public function limitsProvider(): array
    {
        return [
            [1, 0],
            [1, 1],
            [5, 5],
        ];
    }
}
