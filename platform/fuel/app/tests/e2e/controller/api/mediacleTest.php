<?php


use Carbon\Carbon;

class Tests_E2e_Controller_Api_Mediacle extends Test_E2e_Controller_Api
{
    const EXPECTED_REGISTRATION_KEYS = [
        'player_id',
        'brand',
        'country_code',
        'accounting_opening_date',
        'promocode',
        'timestamp',
    ];

    const EXPECTED_SALES_KEYS = [
        'player_id',
        'brand',
        'transaction_date',
        'deposits',
        'bets',
        'wins',
        'chargebacks',
        'released_bonuses',
        'revenue',
        'currency_rate_to_gbp',
        'tracking_id',
        'first_deposit_date',
        'timestamp',
    ];

    /** @test */
    public function get_registrations__no_date_provided__returns_by_current_day(): void
    {
        // Given
        $method = 'GET';
        $endpoint = '/api/mediacle/registrations';

        // When
        $response = $this->get_response_with_security_check($method, $endpoint);

        // Then
        foreach ($response['data'] as $data) {
            foreach (self::EXPECTED_REGISTRATION_KEYS as $key) {
                $this->assertArrayHasKey($key, $data);
            }
        }
    }

    /** @test */
    public function get_registrations__date_provided__returns_by_given_day(): void
    {
        // Given
        $method = 'GET';
        $date = (new Carbon())->format('Y-m-d');
        $endpoint = "/api/mediacle/registrations?date=$date";

        // When
        $response = $this->get_response_with_security_check($method, $endpoint);

        // Then
        foreach ($response['data'] as $data) {
            foreach (self::EXPECTED_REGISTRATION_KEYS as $key) {
                $this->assertArrayHasKey($key, $data);
            }
        }
    }

    /** @test */
    public function get_sales__no_date_provided__returns_by_current_day(): void
    {
        // Given
        $method = 'GET';
        $endpoint = '/api/mediacle/sales';

        // When
        $response = $this->get_response_with_security_check($method, $endpoint);

        // Then
        foreach ($response['data'] as $data) {
            foreach (self::EXPECTED_SALES_KEYS as $key) {
                $this->assertArrayHasKey($key, $data);
            }
        }
    }

    /** @test */
    public function get_sales__date_provided__returns_by_given_day(): void
    {
        // Given
        $method = 'GET';
        $date = (new Carbon())->format('Y-m-d');
        $endpoint = "/api/mediacle/sales?date=$date";

        // When
        $response = $this->get_response_with_security_check($method, $endpoint);

        // Then
        foreach ($response['data'] as $data) {
            foreach (self::EXPECTED_SALES_KEYS as $key) {
                $this->assertArrayHasKey($key, $data);
            }
        }
    }
}
