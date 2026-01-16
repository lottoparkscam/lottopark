<?php

class Forms_Whitelabel_Bonuses_Promocodes_Users extends Forms_Main
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
     * @param array $data
     */
    public function prepare_data(&$data)
    {
        $data['full_code'] = $data['cprefix'] . $data['ctoken'];
       
        $user_token = $this->whitelabel['prefix'] . 'U' .
                $data['utoken'];

        $user_name = _("anonymous");
        if (!empty($data['name']) ||
                !empty($data['surname'])
            ) {
            $user_name = $data['name'] . ' ' . $data['surname'];
        }
        $data["user_data"] = $user_token . " &bull; " . $user_name;

        $data['show_deleted'] = false;
        if ($data['is_deleted']) {
            $data['show_deleted'] = true;
            $data['show_user_url'] = '/deleted?filter[id]=' . $data['utoken'];
        } elseif (($this->whitelabel['user_activation_type'] == Helpers_General::ACTIVATION_TYPE_REQUIRED &&
                        $data['is_active'] && $data['is_confirmed']) ||
                    ($this->whitelabel['user_activation_type'] != Helpers_General::ACTIVATION_TYPE_REQUIRED &&
                        $data['is_active'])
                ) {
            $data['show_user_url'] = '/users?filter[id]=' . $data['utoken'];
        } else {
            $data['show_user_url'] = '/inactive?filter[id]=' . $data['utoken'];
        }

        $data["full_transaction_token"] = '-';
        if (isset($data['ttoken'])) {
            $trans_token = $this->whitelabel['prefix'];
            if ((int)$data['ttype'] === Helpers_General::TYPE_TRANSACTION_PURCHASE) {
                $trans_token .= 'P';
            } else {
                $trans_token .= 'D';
            }
            $trans_token .= $data['ttoken'];
            
            $data["full_transaction_token"] = $trans_token;
        }

        $view_transaction_url = "/";
        $additional_t = "deposits";
        if ($data['ttype'] == Helpers_General::TYPE_TRANSACTION_PURCHASE) {
            $additional_t = 'transactions';
        }
        $view_transaction_url .= $additional_t;
        $view_transaction_url .= "?filter[id]=";
        $view_transaction_url .= $data['ttoken'];
        $data['view_transaction_url'] = $view_transaction_url;
    }

    /**
     *
     * @return void
     */
    public function process_form(): void
    {
        $inside = View::forge("whitelabel/bonuses/promocodes/users.php");
        $data = Model_Whitelabel_Promo_Code::get_users_promo_codes_for_campaign($this->token);

        foreach ($data as &$item) {
            $this->prepare_data($item);
        }

        $inside->set("data", $data);
        $this->inside = $inside;
    }
}
