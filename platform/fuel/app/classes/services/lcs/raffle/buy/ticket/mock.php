<?php
\Autoloader::add_namespace('Fuel\\Tasks\\Factory\\Utils', APPPATH . 'tasks/factory/utils/');

use Fuel\Core\Date;
use Fuel\Tasks\Factory\Utils\Faker;
use GuzzleHttp\Psr7\Response;

class Services_Lcs_Raffle_Buy_Ticket_Mock implements Services_Lcs_Raffle_Buy_Ticket_Contract
{
    const URL = 'lottery/tickets';

    /**
     * @param array $payload
     * @param string $raffle_slug
     * @param string $raffle_type
     * 'tickets' => [
     *    [
     *       'token' => $token,
     *       'amount' => $this->amount,
     *       'ip' => 127.0.0.1,
     *       'lines' => [[1-1000]]
     *   ]
     * ]
     *
     *
     * @return Services_Lcs_Client_Response
     */
    public function request(array $payload, string $raffle_slug, string $raffle_type = 'closed'): Services_Lcs_Client_Response
    {
        $ticket = reset($payload['tickets']);
        $linesCount = count($ticket['lines']);

        $data = [
            'lottery_tickets' => [
                [
                    'status' => Helpers_General::TICKET_STATUS_PENDING,
                    'is_paid_out' => false,
                    'lines_count' => $linesCount,
                    'amount_sale_point' => $ticket['amount'] / $linesCount,
                    'currency_code_sale_point' => 'USD',
                    'currency_code' => 'USD',
                    'draw_date' => null,
                    'token' => $ticket['token'],
                    'amount' => $ticket['amount'],
                    'ip' => $ticket['ip'] ?? '127.0.0.1',
                    'additional_data' => [],
                    'uuid' => Faker::forge()->uuid(),
                    'ip_country_code' => null,
                    'updated_at' => Date::forge()->format('mysql'),
                    'created_at' => Date::forge()->format('mysql'),
                ]
            ]
        ];
        $response = new Response(200, [], json_encode($data));
        return new Services_Lcs_Client_Response($response);
    }
}
