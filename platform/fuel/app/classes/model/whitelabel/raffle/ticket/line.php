<?php

class Model_Whitelabel_Raffle_Ticket_Line extends Model_Model
{
    protected static $_properties = [
        "id", 'whitelabel_id', 'whitelabel_raffle_ticket_id', 'raffle_draw_id', 'number', 'status'
    ];

    protected static $_table_name = 'whitelabel_raffle_ticket_line';

    public static function get_numbers_by_ticket_id($ticket_id)
    {
        $query = "SELECT number FROM whitelabel_raffle_ticket_line
                  WHERE whitelabel_raffle_ticket_id = :ticket_id";
        $db = DB::query($query);
        $db->param('ticket_id', $ticket_id);
        return $db->execute()->as_array();
    }

    public static function get_all_batched_for_crm(array $ids): array
    {
        $lines = [];
        $query = DB::select(
            'wrtl.id',
            'wrtl.number',
            'wrtl.whitelabel_raffle_ticket_id',
            'wrtl.raffle_prize_id',
            'rp.per_user',
            ['currency.code', 'prize_currency_code']
        )
        ->from(['whitelabel_raffle_ticket_line', 'wrtl'])
        ->join(['raffle_prize', 'rp'], 'LEFT')->on('wrtl.raffle_prize_id', '=', 'rp.id')
        ->join('currency', 'LEFT')->on('rp.currency_id', '=', 'currency.id')
        ->where('wrtl.whitelabel_raffle_ticket_id', 'in', $ids)
        ->order_by('number');

        $result = $query->execute()->as_array();

        if (count($result) > 0) {
            foreach ($result as $data) {
                if (!array_key_exists($data['whitelabel_raffle_ticket_id'], $lines)) {
                    $lines[$data['whitelabel_raffle_ticket_id']] = [];
                }
                $lines[$data['whitelabel_raffle_ticket_id']][] = $data;
            }
        }
        
        return $lines;
    }

    public static function get_by_ticket_id(int $id): array
    {
        $query = DB::select(
            'wrtl.id',
            'wrtl.number',
            'wrtl.whitelabel_raffle_ticket_id',
            'wrtl.raffle_prize_id',
            'rp.per_user',
            ['currency.code', 'prize_currency_code']
        )
        ->from(['whitelabel_raffle_ticket_line', 'wrtl'])
        ->join(['raffle_prize', 'rp'], "LEFT")->on('wrtl.raffle_prize_id', '=', 'rp.id')
        ->join('currency', 'LEFT')->on('rp.currency_id', '=', 'currency.id')
        ->where('wrtl.whitelabel_raffle_ticket_id', '=', $id)
        ->order_by('number');

        $result = $query->execute()->as_array();
        
        return $result;
    }
}
