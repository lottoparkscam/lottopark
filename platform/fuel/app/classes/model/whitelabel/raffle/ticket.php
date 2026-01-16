<?php

use Services\Logs\FileLoggerService;

/**
 * field 'ip' - ip which user had during buying the ticket
 * field 'ip_country_code' - country code which user had during buying the ticket 
 */
class Model_Whitelabel_Raffle_Ticket extends \Model_Model
{
    use Services_Api_Signature, Services_Api_Nonce;
    
    const LCS_TICKET_ENDPOINT = 'lottery/tickets';
    const TAKEN_NUMBERS_ENDPOINT = 'lottery/tickets/raffle/taken_numbers';
    
    const RESULT_OK = 0;
    const RESULT_ERROR = 1;

    const RAFFLE_TICKET_STATUS_PENDING = 0;
    const RAFFLE_TICKET_STATUS_WIN = 1;
    const RAFFLE_TICKET_STATUS_NO_WINNINGS = 2;

    const RAFFLE_TICKET_IS_PAID_OUT_PENDING = 0;
    const RAFFLE_TICKET_IS_PAID_OUT_PAIDOUT = 1;

    /**
     *
     * @var array
     */
    protected static $_properties = [
        "id", 'whitelabel_id', 'whitelabel_user_id', 'whitelabel_transaction_id', 'raffle_id', 'raffle_rule_id',
        'raffle_draw_id', 'currency_id', 'uuid', 'token', 'draw_date', 'status', 'amount', 'prize', 'ip', 'ip_country_code',
        'is_paid_out', 'created_at', 'updated_at'
    ];

    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_raffle_ticket';

    /**
     *
     * @param int $transaction_id
     * @return array
     */
    public static function get_by_transaction_id(int $transaction_id): array
    {
        $query = "SELECT wrt.*, r.name, r.slug, r.main_prize, count(*) as line_count FROM whitelabel_raffle_ticket wrt 
                JOIN whitelabel_raffle_ticket_line wrtl JOIN raffle r 
                WHERE wrt.raffle_id = r.id AND 
                wrt.id = wrtl.whitelabel_raffle_ticket_id AND 
                wrt.whitelabel_transaction_id = :transaction_id";

        $db = DB::query($query);
        $db->param('transaction_id', $transaction_id);
        return $db->execute()->as_array()[0];
    }

    /**
     *
     * @param string $slug
     * @param array $whitelabel
     * @param array $numbers
     * @return int
     */
    public function send_data_to_LCS(string $slug, array $whitelabel, array $numbers): int
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        Config::load('lottery_central_server', true);
        $key = Config::get('lottery_central_server.sale_point.key');
        $secret = Config::get('lottery_central_server.sale_point.secret');
        $url = Config::get('lottery_central_server.url.base') . self::LCS_TICKET_ENDPOINT;
        
        $token = $whitelabel['prefix'] . 'T' . $this->token;
        
        $payload_lines = [];
        foreach ($numbers as $number) {
            $payload_lines[] = ['numbers' => [[$number]]];
        }
        
        $payload = [
            'tickets' => [
                [
                    'token' => $token,
                    'amount' => $this->amount,
                    'ip' => Input::server('SERVER_ADDR'),
                    'lines' => $payload_lines
                ]
            ]
        ];
        
        $payload_json = json_encode($payload);
        $nonce = $this->generate_nonce();
        $signature = $this->build_signature($secret, $nonce, '/' . self::LCS_TICKET_ENDPOINT, $payload_json);
        
        $headers = [
            'api-key: '. $key,
            'api-signature: '. $signature,
            'api-nonce: '. $nonce,
            'lottery-slug: '. $slug
        ];
        
        $response = null;
        try {
            $response_json = Services_Curl::post_json($url, $payload, $headers);
            $response = json_decode($response_json);
            
            if (isset($response->error)) {
                $fileLoggerService->error($response_json);
                return self::RESULT_ERROR;
            }

            $this->uuid = $response->lottery_tickets[0]->uuid;
            $this->save();
        } catch (Exception $e) {
            $fileLoggerService->error($e->getMessage());
            return self::RESULT_ERROR;
        }
        return self::RESULT_OK;
    }
    
    /**
     *
     * @param string $slug
     * @return array
     */
    public function get_taken_numbers_from_LCS(string $slug): array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        Config::load('lottery_central_server', true);
        $key = Config::get('lottery_central_server.sale_point.key');
        $secret = Config::get('lottery_central_server.sale_point.secret');
        $url = Config::get('lottery_central_server.url.base') . self::TAKEN_NUMBERS_ENDPOINT;
        
        $nonce = $this->generate_nonce();
        $signature = $this->build_signature($secret, $nonce, '/' . self::TAKEN_NUMBERS_ENDPOINT, '');
        
        $headers = [
            'api-key: '. $key,
            'api-signature: '. $signature,
            'api-nonce: '. $nonce,
            'lottery-slug: '. $slug
        ];
        
        try {
            $response_json = Services_Curl::get_json($url, $headers);
            $response = json_decode($response_json);
            
            if (isset($response->error)) {
                $fileLoggerService->error($response_json);
                return [];
            }
            
            return $response->data->taken_numbers;
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }
        return [];
    }
    
    /**
     *
     * @param int $raffle_id
     * @return array
     */
    public static function get_tickets_to_update(int $raffle_id): array
    {
        $tickets = Model_Whitelabel_Raffle_Ticket::find_by(['status' => Helpers_General::TICKET_PAYOUT_PENDING]);
        return $tickets;
    }

    public static function get_full_data_for_crm(
        ?int $whitelabel_id,
        string $active_tab,
        array $filters,
        ?int $page = null,
        ?int $items_per_page = null,
        ?string $sort_by = null,
        ?string $order = null,
        bool $is_cache_disabled = false
    ): array {
        $res = [];

        $query = DB::select(
            'wrt.*',
            'whitelabel.prefix',
            ['wlcurr.code', 'manager_currency_code'],
            ['lcurr.code', 'lottery_currency_code'],
            ['ucurr.code', 'user_currency_code'],
            ['raffle.name', 'rname'],
            ['wt.token', 'ttoken'],
            ['wu.token', 'utoken'],
            'wu.name',
            'wu.surname',
            'wu.email',
            ['wu.login', 'user_login']
        )
        ->from([self::$_table_name, 'wrt'])
        ->join('whitelabel')->on('wrt.whitelabel_id', '=', 'whitelabel.id')
        ->join(['whitelabel_transaction', 'wt'], 'LEFT')->on('wrt.whitelabel_transaction_id', '=', 'wt.id')
        ->join(['whitelabel_user', 'wu'], 'LEFT')->on('wrt.whitelabel_user_id', '=', 'wu.id')
        ->join('raffle')->on('wrt.raffle_id', '=', 'raffle.id')
        ->join(['currency', 'lcurr'])->on('raffle.currency_id', '=', 'lcurr.id')
        ->join(['currency', 'ucurr'])->on('wu.currency_id', '=', 'ucurr.id')
        ->join(['currency', 'wlcurr'], 'LEFT')->on('whitelabel.manager_site_currency_id', '=', 'wlcurr.id');

        switch ($active_tab) {
            case 'pending':
                $query->and_where('wrt.status', '=', self::RAFFLE_TICKET_STATUS_PENDING);
            break;
            case 'win':
                $query->and_where('wrt.status', '=', self::RAFFLE_TICKET_STATUS_WIN);
            break;
            case 'nowinnings':
                $query->and_where('wrt.status', '=', self::RAFFLE_TICKET_STATUS_NO_WINNINGS);
            break;
        }

        if ($whitelabel_id) {
            $query->and_where('wrt.whitelabel_id', '=', $whitelabel_id);
        }

        $query = self::prepare_filters($filters, $query, $whitelabel_id);

        if ($sort_by) {
            $query->order_by($sort_by, $order);
        }

        if ($items_per_page) {
            $query->limit($items_per_page)->offset($items_per_page * ($page - 1));
        }

        if ($is_cache_disabled) {
            $query->caching(false);
        }

        $res = $query->execute()->as_array();
        
        return $res;
    }

    private static function prepare_filters(array $filters, Database_Query_Builder_Select $query, ?int $whitelabel): Database_Query_Builder_Select
    {
        foreach ($filters as $filter) {
            if ($filter['column'] === 'token') {
                $value = '%' . $filter['value'] . '%';
                $query->and_where_open();
                $query->where('wrt.token', 'LIKE', $value)
                        ->or_where('wt.token', 'LIKE', $value)
                        ->or_where('raffle.name', 'LIKE', $value);
                $query->and_where_close();
            }
            if ($filter['column'] === 'utoken') {
                $value = '%' . $filter['value'] . '%';
                $query->and_where_open();
                $query->where('wu.name', 'LIKE', $value)
                        ->or_where('wu.login', 'LIKE', $value)
                        ->or_where('wu.token', 'LIKE', $value)
                        ->or_where('wu.surname', 'LIKE', $value)
                        ->or_where('wu.email', 'LIKE', $value);
                $query->and_where_close();
            }
            if ($filter['column'] === 'status') {
                $query->and_where('wrt.status', '=', intval($filter['value']));
            }
            if ($filter['column'] === 'is_paid_out') {
                $query->and_where('wrt.is_paid_out', '=', intval($filter['value']))
                ->and_where('wrt.status', '=', self::RAFFLE_TICKET_STATUS_WIN);
            }
            if ($filter['column'] === 'amount') {
                if ($filter['start'] > 0 && $filter['end'] == 0) {
                    $query->and_where('wrt.amount', '>=', intval($filter['start']));
                } elseif ($filter['start'] > 0 && $filter['end'] > 0) {
                    $query->and_where('wrt.amount', '>=', intval($filter['start']))
                    ->and_where('wrt.amount', '<=', intval($filter['end']));
                } elseif ($filter['start'] == 0 && $filter['end'] > 0) {
                    $query->and_where('wrt.amount', '<=', intval($filter['end']));
                }
            }
            if ($filter['column'] === 'bonus_amount') {
                if ($filter['start'] > 0 && $filter['end'] == 0) {
                    $query->and_where('wrt.bonus_amount', '>=', intval($filter['start']));
                } elseif ($filter['start'] > 0 && $filter['end'] > 0) {
                    $query->and_where('wrt.bonus_amount', '>=', intval($filter['start']))
                    ->and_where('wrt.bonus_amount', '<=', intval($filter['end']));
                } elseif ($filter['start'] == 0 && $filter['end'] > 0) {
                    $query->and_where('wrt.bonus_amount', '<=', intval($filter['end']));
                }
            }
            if ($filter['column'] === 'prize') {
                if ($filter['start'] > 0 && $filter['end'] == 0) {
                    $query->and_where('wrt.prize', '>=', intval($filter['start']));
                } elseif ($filter['start'] > 0 && $filter['end'] > 0) {
                    $query->and_where('wrt.prize', '>=', intval($filter['start']))
                    ->and_where('wrt.prize', '<=', intval($filter['end']));
                } elseif ($filter['start'] == 0 && $filter['end'] > 0) {
                    $query->and_where('wrt.prize', '<=', intval($filter['end']));
                }
            }
            if ($filter['column'] === 'created_at') {
                $start = Helpers_Crm_General::prepare_start_date($filter['startDate']);
                $end = Helpers_Crm_General::prepare_end_date($filter['endDate']);
                $query->and_where('wrt.created_at', '>=', $start);
                $query->and_where('wrt.created_at', '<=', $end);
            }
            if ($filter['column'] == 'draw_date') {
                $start = Helpers_Crm_General::prepare_start_date($filter['startDate']);
                $end = Helpers_Crm_General::prepare_end_date($filter['endDate']);
                $query->and_where('wrt.draw_date', '>=', $start);
                $query->and_where('wrt.draw_date', '<=', $end);
            }
        }

        return $query;
    }

    public static function get_raffle_tickets_counts_for_crm(?int $whitelabel_id, array $filters, ?int $status): int
    {
        $res = 0;
        
        $query = DB::select(
            DB::expr('COUNT(*) as count')
        )
        ->from([self::$_table_name, 'wrt'])
        ->join('whitelabel')->on('wrt.whitelabel_id', '=', 'whitelabel.id')
        ->join(['whitelabel_transaction', 'wt'], 'LEFT')->on('wrt.whitelabel_transaction_id', '=', 'wt.id')
        ->join(['whitelabel_user', 'wu'], 'LEFT')->on('wrt.whitelabel_user_id', '=', 'wu.id')
        ->join('raffle')->on('wrt.raffle_id', '=', 'raffle.id')
        ->join(['currency', 'lcurr'])->on('raffle.currency_id', '=', 'lcurr.id')
        ->join(['currency', 'ucurr'])->on('wu.currency_id', '=', 'ucurr.id')
        ->join(['currency', 'wlcurr'], 'LEFT')->on('whitelabel.manager_site_currency_id', '=', 'wlcurr.id');

        if (isset($status)) {
            $query->and_where('wrt.status', '=', $status);
        }
        
        if (isset($whitelabel_id)) {
            $query->and_where('wrt.whitelabel_id', '=', $whitelabel_id);
        }

        $query = self::prepare_filters($filters, $query, $whitelabel_id);

        $result = $query->execute()->as_array();
        if (isset($result[0])) {
            $res = $result[0]['count'];
        }
        return $res;
    }

    /**
     *
     * @param string $token
     * @return array
     */
    public static function get_single_for_crm(string $token, int $whitelabelId): array
    {
        $ticket = [];

        $query = DB::select(
            'wrt.*',
            'whitelabel.prefix',
            ['wlcurr.code', 'manager_currency_code'],
            ['lcurr.code', 'lottery_currency_code'],
            ['ucurr.code', 'user_currency_code'],
            ['raffle.name', 'rname'],
            ['wt.token', 'ttoken'],
            ['wu.token', 'utoken'],
            'wu.name',
            'wu.surname',
            'wu.email',
            ['wu.login', 'user_login']
        )
        ->from([self::$_table_name, 'wrt'])
        ->join('whitelabel')->on('wrt.whitelabel_id', '=', 'whitelabel.id')
        ->join(['whitelabel_transaction', 'wt'], 'LEFT')->on('wrt.whitelabel_transaction_id', '=', 'wt.id')
        ->join(['whitelabel_user', 'wu'], 'LEFT')->on('wrt.whitelabel_user_id', '=', 'wu.id')
        ->join('raffle')->on('wrt.raffle_id', '=', 'raffle.id')
        ->join(['currency', 'lcurr'])->on('raffle.currency_id', '=', 'lcurr.id')
        ->join(['currency', 'ucurr'])->on('wu.currency_id', '=', 'ucurr.id')
        ->join(['currency', 'wlcurr'], 'LEFT')->on('whitelabel.manager_site_currency_id', '=', 'wlcurr.id')
        ->where('wrt.token', '=', $token)
        ->and_where('whitelabel.id', '=', $whitelabelId);

        $result = $query->execute()->as_array();

        if (isset($result[0])) {
            $ticket = $result[0];
        }
        
        return $ticket;
    }
}
