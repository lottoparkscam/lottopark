<?php

/**
 * @method static find_by_whitelabel_user_ticket_slip_id(int $id)
 * @method static find_by_lottorisqid(int $id)
 */
class Model_Lottorisq_Ticket extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'lottorisq_ticket';
    
    /**
     *
     * @var array
     */
    public static $cache_list = [];
}
