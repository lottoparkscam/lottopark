<?php

class Forms_Whitelabel_Bonuses_Promocodes_Codes extends Forms_Main
{
    /**
     *
     * @var array
     */
    private $whitelabel = [];

    /**
     *
     * @var View
     */
    private $inside = null;

    /**
     *
     * @return type
     */
    public function get_inside()
    {
        return $this->inside;
    }

    /**
    *
    * @param array $whitelabel
    * @param int $token
    */
    public function __construct($whitelabel, $token)
    {
        $this->whitelabel = $whitelabel;
        $this->token = $token;
    }

    /**
     *
     * @return void
     */
    public function process_form(): void
    {
        $inside = View::forge("whitelabel/bonuses/promocodes/codes.php");

        $codes = Model_Whitelabel_Promo_Code::get_promo_codes_for_campaign($this->token);
        $inside->set("codes", $codes);
        $this->inside = $inside;
    }
}
