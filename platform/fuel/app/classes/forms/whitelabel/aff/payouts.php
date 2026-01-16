<?php

/**
 * @deprecated
 * Description of Forms_Whitelabel_Aff_Payouts
 */
class Forms_Whitelabel_Aff_Payouts
{

    /**
     *
     * @var array
     */
    private $whitelabel = [];

    /**
     *
     * @var int
     */
    private $items_per_page = 25;
    
    /**
     *
     * @var View
     */
    private $inside = null;

    /**
     *
     * @var array
     */
    private $eaffs = [];
    
    /**
     *
     * @var int
     */
    private $source = null;
    
    /**
     *
     * @var array
     */
    private $kwithdrawals = [];
    
    /**
     *
     * @var string
     */
    private $start_url = "";
    
    /**
     *
     * @var array
     */
    private $user = null;

    private $pki;
    
    /**
     *
     * @param array $whitelabel
     * @param string $source
     */
    public function __construct(
        array $whitelabel,
        int $source = Helpers_General::SOURCE_WHITELABEL,
        array $user = null
    ) {
        $this->whitelabel = $whitelabel;
        $this->source = $source;
        
        $this->pki = new Helpers_Whitelabel($whitelabel);
        
        if ($this->source === Helpers_General::SOURCE_WHITELABEL) {
            $this->start_url = '/affs';
            
            $withdrawals = Model_Whitelabel_Aff_Withdrawal::get_whitelabel_aff_withdrawals($whitelabel);

            foreach ($withdrawals as $withdrawal) {
                $this->kwithdrawals[$withdrawal['id']] = $withdrawal;
            }
        } else {
            $this->start_url = '';
            $this->user = $user;
        }
    }

    /**
     *
     * @return array
     */
    public function get_whitelabel()
    {
        return $this->whitelabel;
    }
    
    /**
     *
     * @return View
     */
    public function get_inside()
    {
        return $this->inside;
    }

    /**
     *
     * @return array
     */
    private function prepare_filters(): array
    {
        $filter_add = [];
        $params = [];

        if (Input::get("filter.email") != null) {
            $email = Input::get('filter.email');
            $this->eaffs = $this->pki->prepare_eaffs($email);

            $filter_add[] = " AND wap.whitelabel_aff_id = :aff";
            $params[] = [":aff", $this->eaffs[$email]['id']];
        }
        
        return [$filter_add, $params];
    }
    
    /**
     *
     * @return void
     */
    public function process_form(string $view_template): void
    {
        $whitelabel = $this->get_whitelabel();

        $inside = Presenter::forge($view_template);
        
        $currencies = Lotto_Settings::getInstance()->get("currencies");
        
        $count = null;
        if ($this->source === Helpers_General::SOURCE_WHITELABEL) {
            list($filter_add, $params) = $this->prepare_filters();

            $count = Model_Whitelabel_Aff_Payout::count_for_whitelabel_filtered(
                $filter_add,
                $params,
                $whitelabel['id']
            );
        } else {
            $count = Model_Whitelabel_Aff_Payout::count_for_user($this->user['id']);
        }
        
        if (is_null($count)) {
            ;
        }
        
        $config = [
            'pagination_url' => $this->start_url . '/payouts?' . http_build_query(Input::get()),
            'total_items' => $count,
            'per_page' => $this->items_per_page,
            'uri_segment' => 'page'
        ];
        $pagination = Pagination::forge('affspagination', $config);

        $payouts = null;
        if ($this->source === Helpers_General::SOURCE_WHITELABEL) {
            $payouts = Model_Whitelabel_Aff_Payout::get_for_whitelabel_filtered(
                $filter_add,
                $params,
                $pagination,
                $whitelabel['id']
            );
            
            $inside->set("start_url", $this->start_url);
            $inside->set("kwithdrawals", $this->kwithdrawals);
        } else {
            $payouts = Model_Whitelabel_Aff_Payout::get_for_user(
                $pagination,
                $this->user['id']
            );
        }
        
        $inside->set("currencies", $currencies);
        $inside->set("payouts", $payouts);
        $inside->set("pages", $pagination);

        $this->inside = $inside;
    }
    
    /**
     *
     * @param int $payout_id This is ID from Front-end shown on table
     * @return void
     */
    public function process_accept(int $payout_id): void
    {
        $whitelabel = $this->get_whitelabel();

        list($filter_add, $params) = $this->prepare_filters();

        $count = Model_Whitelabel_Aff_Payout::count_for_whitelabel_filtered(
            $filter_add,
            $params,
            $whitelabel['id']
        );

        if (is_null($count)) {
            ;
        }
        
        $config = [
            'pagination_url' => '/affs/payouts?' . http_build_query(Input::get()),
            'total_items' => $count,
            'per_page' => $this->items_per_page,
            'uri_segment' => 'page'
        ];
        $pagination = Pagination::forge('affspagination', $config);

        $payouts = Model_Whitelabel_Aff_Payout::get_for_whitelabel_filtered(
            $filter_add,
            $params,
            $pagination,
            $whitelabel['id']
        );
        
        if ($payout_id !== null &&
            isset($payouts[$payout_id]) &&
            $payouts[$payout_id]['whitelabel_id'] == $whitelabel['id'] &&
            !$payouts[$payout_id]['is_paidout']
        ) {
            $payout_tab = Model_Whitelabel_Aff_Payout::find_by([
                "id" => $payouts[$payout_id]['id']
            ]);
            $payout_obj = $payout_tab[0];
            
            $payout_obj->set([
                "is_paidout" => 1
            ]);
            $payout_obj->save();
            
            Session::set_flash("message", ["success", _("The item has been paid out.")]);
        } else {
            Session::set_flash("message", ["danger", _("Incorrect item!")]);
        }
    }
}
