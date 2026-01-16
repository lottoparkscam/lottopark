<?php

/**
 * Model for Lottery Central Server ticket.
 */
class Model_Lcs_Ticket extends Model_Model
{
    /**
     * Name of the database table binded to this model.
     * @var string
     */
    protected static $_table_name = 'lcs_ticket';
    
    /**
     * List of cached items for this model.
     * @var array
     */
    public static $cache_list = [];
}
