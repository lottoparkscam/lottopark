<?php

/**
 * Description of raffle order
 */
class Forms_Wordpress_Myaccount_Raffle_Order
{
    public $errors = [];

    public $validate = false;

    private $raffle_id;
    private $whitelabel_id;

    private $is_user = false;
    private $user;
    private $ticket_price;

    public function __construct($whitelabel, $raffle, $numbers, $line_cost)
    {
        $this->validate = $this->validate_numbers($numbers);
        if(!$this->validate) {
            return;
        }

        $this->is_user = Lotto_Settings::getInstance()->get("is_user");
        if ($this->is_user) {
            $this->user = Lotto_Settings::getInstance()->get("user");
        }

        $this->ticket_price = bcmul(count($numbers), $line_cost, 2);
        $this->whitelabel_id = $whitelabel['id'];
        $this->raffle_id = $raffle['id'];
    }
}