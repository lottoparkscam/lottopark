<?php

use Models\WhitelabelAff;
use Repositories\Aff\WhitelabelAffRepository;
use Repositories\Aff\WhitelabelUserAffRepository;
/**
 * @deprecated
 * Description of Forms_Whitelabel_Aff_Commissions
 */
class Forms_Whitelabel_Aff_Commissions
{
    /**
     *
     * Get Trait for CSV Export
     */
    use Traits_Gets_Csv;
    
    /**
     * Get Trait for date range preparation
     */
    use Traits_Gets_Date;
    
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
    private $countries = [];

    private WhitelabelAffRepository $whitelabelAffRepository;

    private WhitelabelUserAffRepository $whitelabelUserAffRepository;
    
    /**
     *
     * @param array $whitelabel Should be table, but sometimes
     *                          is given as Whitelabel object
     * @param array $countries
     * @param bool $pull_aff_settings If false the object is needed only for calculate
     *                                  commissions and insert them into DB
     */
    public function __construct(
        $whitelabel,
        $countries = [],
        $pull_aff_settings = true
    ) {
        $this->whitelabel = $whitelabel;
        $this->countries = $countries;
        $this->whitelabelAffRepository = Container::get(WhitelabelAffRepository::class);
        $this->whitelabelUserAffRepository = Container::get(WhitelabelUserAffRepository::class);
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
        $filterAdd = [];
        $params = [];

        if (Input::get("filter.id") != null) {
            $filterAdd[] = " AND whitelabel_user.token = :token";
            $token_ticket_temp = $this->whitelabel['prefix'] . 'U';
            $token_ticket = str_ireplace($token_ticket_temp, "", Input::get("filter.id"));
            $params[] = [":token", intval($token_ticket)];
        }

        if (Input::get("filter.email") != null) {
            $filterAdd[] = " AND whitelabel_user.email LIKE :email";
            $params[] = [":email", '%' . Input::get("filter.email") . '%'];
        }

        if (Input::get("filter.country") != null &&
            Input::get("filter.country") != "a"
        ) {
            $filterAdd[] = " AND whitelabel_user.country = :country";
            $params[] = [":country", Input::get("filter.country")];
        }

        if (Input::get("filter.name") != null) {
            $filterAdd[] = " AND whitelabel_user.name LIKE :name";
            $params[] = [":name", '%' . Input::get("filter.name") . '%'];
        }

        if (Input::get("filter.surname") != null) {
            $filterAdd[] = " AND whitelabel_user.surname LIKE :surname";
            $params[] = [":surname", '%' . Input::get("filter.surname") . '%'];
        }

        if (Input::get("filter.range_start") != '') {
            // get date ranges
            $dates = $this->prepare_dates();

            $filterAdd[] = " AND date_confirmed >= :date_start";
            $params[] = [":date_start", $dates['date_start']];

            $filterAdd[] = "  AND date_confirmed <= :date_end";
            $params[] = [":date_end", $dates['date_end']];
        }

        //$allFilters = implode("", $filterAdd);
        // This time filters should be pass further as array, because
        // SQL string build within model
        $allFilters = $filterAdd;
        return [$allFilters, $params];
    }

    /**
     *
     * @param int $export
     * @return array
     */
    private function get_data(int $export): array
    {
        list($filter_add, $params) = $this->prepare_filters();
        
        $count = Model_Whitelabel_Aff_Commission::fetch_count_for_commissions_for_whitelabel(
            $filter_add,
            $params,
            $this->whitelabel['id']
        );
        
        $config = [
            'pagination_url' => '/affs/commissions?' . http_build_query(Input::get()),
            'total_items' => $count,
            'per_page' => $this->items_per_page,
            'uri_segment' => 'page'
        ];
        $pagination = Pagination::forge('affspagination', $config);

        $commissions = Model_Whitelabel_Aff_Commission::fetch_commissions_for_whitelabel(
            $filter_add,
            $params,
            $pagination,
            $this->whitelabel['id'],
            $export
        );
        
        return [
            $pagination,
            $commissions
        ];
    }
    
    /**
     *
     * @param array $user This is rather Model_Whitelabel_Aff (not null only
     *                      in the case of source of AFF)
     *
     * @return void
     */
    public function process_form($user = null): void
    {
        $export = 0;
        
        list(
            $pagination,
            $commissions
        ) = $this->get_data($export);
        
        $inside = Presenter::forge("whitelabel/affs/reports/commissions");
        $inside->set("countries", $this->countries);
        $inside->set("pages", $pagination);
        $inside->set("commissions", $commissions);

        $this->inside = $inside;
    }
    
    /**
     *
     * @return void
     */
    public function process_export(): void
    {
        $export = 1;
        
        list(
            $pagination,
            $commissions
        ) = $this->get_data($export);
        
        
        //Todo: Do poprawy
        
        // Prepare headers and data for CSV
        $csv_results = $this->prepare_csv_commissions($commissions);

        // Export CSV
        $this->get_csv_export(
            'commissions',
            $csv_results['headers'],
            $csv_results['data']
        );
    }
    
    /**
     *
     * Prepare Commissions data for CSV
     *
     * @param array $commissions
     * @return array
     */
    private function prepare_csv_commissions(array $commissions): array
    {
        $data = [];

        //$currencies = Lotto_Settings::getInstance()->get("currencies");
        
        /*Prepare Headers*/
        $headers = [
            _("Affiliate Name"),
            _("Affiliate Login"),
            _("Affiliate Email"),
            _("User Name"),
            _("User ID"),
            _("User Email"),
            _("Transaction ID"),
            _("Type"),
            _("Tier"),
            _("Date"),
            _("Amount"),
            _("Ticket cost"),
            _("Payment cost"),
            _("Income"),
            _("Commission")
        ];

        /*Prepare Data*/

        foreach ($commissions as $key => $commission) {
            // AFFILIATE
            $aff_full_name = '';
            if (!empty($commission['aff_name']) || !empty($commission['aff_surname'])) {
                $aff_full_name .= $commission['aff_name'] . ' ' . $commission['aff_surname'];
            } else {
                $aff_full_name .= _("anonymous");
            }
            $affiliate = Security::htmlentities($aff_full_name);
            $affiliate_login = Security::htmlentities($commission['aff_login']);
            $affiliate_email = Security::htmlentities($commission['aff_email']);

            //USER
            $user_full_name = '';
            if (!empty($commission['name']) || !empty($commission['surname'])) {
                $user_full_name .= $commission['name'];
                $user_full_name .= ' ';
                $user_full_name .= $commission['surname'];
            } else {
                $user_full_name .= _("anonymous");
            }
            $user = Security::htmlentities($user_full_name);
            
            $user_id = $this->whitelabel['prefix'] . 'U' . $commission['token'];
            
            $user_email_t = '';
            if (!empty($commission['email'])) {
                $user_email_t = $commission['email'];
            }
            $user_email = Security::htmlentities($user_email_t);

            //TRANSACTION ID
            $transaction_t = $this->whitelabel['prefix'];
            $add_trans_text = 'D';
            if ($commission['ttype'] == Helpers_General::TYPE_TRANSACTION_PURCHASE) {
                $add_trans_text = 'P';
            }
            $transaction_t .= $add_trans_text;
            $transaction_t .= $commission['ttoken'];
            $transaction_id = $transaction_t;

            //TYPE / TIER
            $aff_type_t = _("FTP");
            if ($commission['type'] == Helpers_General::TYPE_AFF_SALE) {
                $aff_type_t = _("Sale");
            }
            $type = $aff_type_t;
            
            $tier = $commission['tier'];

            //DATE
            $date = Lotto_View::format_date(
                $commission['date_confirmed'],
                IntlDateFormatter::SHORT,
                IntlDateFormatter::SHORT
            );

            //AMOUNT
            $amount_manager_text = Lotto_View::format_currency(
                $commission['amount_manager'],
                $commission['manager_currency_code'],
                true
            );
            $cost_manager_text = Lotto_View::format_currency(
                $commission['cost_manager'],
                $commission['manager_currency_code'],
                true
            );
            $payment_cost_manager_text = Lotto_View::format_currency(
                $commission['payment_cost_manager'],
                $commission['manager_currency_code'],
                true
            );
            $real_income_manager = round(
                $commission['income_manager'] - $commission['payment_cost_manager'],
                2
            );
            $real_income_manager_text = Lotto_View::format_currency(
                $real_income_manager,
                $commission['manager_currency_code'],
                true
            );
            $commission_manager_text = Lotto_View::format_currency(
                $commission['commission_manager'],
                $commission['manager_currency_code'],
                true
            );

            $amounts_other = '';
            $costs_other = '';
            $payment_costs_other = '';
            $real_incomes_other = '';
            $commissions_other = '';
            if ($commission['manager_currency_code'] !== $commission['user_currency_code']) {
                $amount_text = Lotto_View::format_currency(
                    $commission['amount'],
                    $commission['user_currency_code'],
                    true
                );
                $amounts_other = _("User currency") . ": " . $amount_text;
                
                $cost_text = Lotto_View::format_currency(
                    $commission['cost'],
                    $commission['user_currency_code'],
                    true
                );
                $costs_other = _("User currency") . ": " . $cost_text;
                
                $payment_cost_text = Lotto_View::format_currency(
                    $commission['payment_cost'],
                    $commission['user_currency_code'],
                    true
                );
                $payment_costs_other = _("User currency") . ": " . $payment_cost_text;
                
                $real_income = round(
                    $commission['income'] - $commission['payment_cost'],
                    2
                );
                $real_income_text = Lotto_View::format_currency(
                    $real_income,
                    $commission['user_currency_code'],
                    true
                );
                $real_incomes_other = _("User currency") . ": " . $real_income_text;
                
                $commission_text = Lotto_View::format_currency(
                    $commission['commission'],
                    $commission['user_currency_code'],
                    true
                );
                $commissions_other = _("User currency") . ": " . $commission_text;
            }
            
            $amounts = $amount_manager_text . "\n";
            $amounts .= $amounts_other;
            
            $costs = $cost_manager_text . "\n";
            $costs .= $costs_other;
            
            $payment_costs = $payment_cost_manager_text . "\n";
            $payment_costs .= $payment_costs_other;

            $real_incomes = $real_income_manager_text . "\n";
            $real_incomes .= $real_incomes_other;

            $commissions_text = $commission_manager_text . "\n";
            $commissions_text .= $commissions_other;
            
            /*Add data to array*/

            $data[] = [
                $affiliate,
                $affiliate_login,
                $affiliate_email,
                $user,
                $user_id,
                $user_email,
                $transaction_id,
                $type,
                $tier,
                $date,
                $amounts,
                $costs,
                $payment_costs,
                $real_incomes,
                $commissions_text
            ];
        }

        // Return headers and data for CSV
        return [
            'headers' => $headers,
            'data' => $data
        ];
    }
    
    /**
     *
     * @return void
     */
    public function process_accept(int $token): void
    {
        $export = 0;
        
        list(
            $pagination,
            $commissions
        ) = $this->get_data($export);
        
        $kcommissions = [];
        foreach ($commissions as $com) {
            $kcommissions[$com['id']] = $com;
        }
        
        if (!empty($kcommissions[$token]) &&
            !$kcommissions[$token]['is_accepted']
        ) {
            $dt_now = new DateTime("now", new DateTimeZone("UTC"));
            $dt_now->setDate(
                $dt_now->format('Y'),
                $dt_now->format('m'),
                1
            );
            $dt_now->setTime(0, 0, 0);
            
            $dt_item = new DateTime(
                $kcommissions[$token]['date_confirmed'],
                new DateTimeZone("UTC")
            );
            $dt_item->setDate(
                $dt_item->format('Y'),
                $dt_item->format('m'),
                1
            );
            $dt_item->setTime(0, 0, 0);
            
            $can_edit = true;
            if ($dt_item < $dt_now) {
                $can_edit = false;
            }
            
            if ($can_edit) {
                $com = Model_Whitelabel_Aff_Commission::find_by_pk($kcommissions[$token]['id']);
                $com->set([
                    "is_accepted" => 1
                ]);
                $com->save();
                
                Session::set_flash("message", ["success", _("Commission has been accepted.")]);
            } else {
                Session::set_flash("message", ["danger", _("It is too late to edit this commission!")]);
            }
        } else {
            Session::set_flash("message", ["danger", _("Incorrect commission!")]);
        }
    }
    
    /**
     *
     * @param int $token
     * @return void
     */
    public function process_delete(int $token): void
    {
        $export = 0;
        
        list(
            $pagination,
            $commissions
        ) = $this->get_data($export);
        
        $kcommissions = [];
        foreach ($commissions as $com) {
            $kcommissions[$com['id']] = $com;
        }
        
        if (!empty($kcommissions[$token])) {
            $dt_now = new DateTime("now", new DateTimeZone("UTC"));
            $dt_now->setDate(
                $dt_now->format('Y'),
                $dt_now->format('m'),
                1
            );
            $dt_now->setTime(0, 0, 0);
            
            $dt_item = new DateTime(
                $kcommissions[$token]['date_confirmed'],
                new DateTimeZone("UTC")
            );
            $dt_item->setDate(
                $dt_item->format('Y'),
                $dt_item->format('m'),
                1
            );
            $dt_item->setTime(0, 0, 0);
            
            $can_edit = true;
            if ($dt_item < $dt_now) {
                $can_edit = false;
            }
            if ($can_edit) {
                $com = Model_Whitelabel_Aff_Commission::find_by_pk($kcommissions[$token]['id']);
                $com->delete();
                
                Session::set_flash("message", ["success", _("Commission has been deleted!")]);
            } else {
                Session::set_flash("message", ["danger", _("It is too late to delete this commission!")]);
            }
        } else {
            Session::set_flash("message", ["danger", _("Incorrect commission!")]);
        }
    }
    
    /**
     * Get commission values from whitelabel or aff group (if defined) as manager values
     *
     * @param int $whitelabel_aff_group_id Could be null
     * @param int $type_tier Depend on this values will be pulled from different columns
     * @return array
     */
    private function get_commission_values(
        $whitelabel_aff_group_id,
        int $type_tier
    ): array {
        $commission_value_manager = null;
        $ftp_value_manager = null;
        
        $additional_key = ""; // For Tier type First
        if ($type_tier === Helpers_General::TYPE_TIER_SECOND) {
            $additional_key = "_2";
        }
        
        // If group is set than pull settings for that
        if (isset($whitelabel_aff_group_id)) {
            $group = Model_Whitelabel_Aff_Group::find_by_pk($whitelabel_aff_group_id);
            
            $commission_value_manager = $group['commission_value'  . $additional_key . '_manager'];
            $ftp_value_manager = $group['ftp_commission_value'  . $additional_key . '_manager'];
        } else {            // Pull default values from whitelabel
            $commission_value_manager = $this->whitelabel['def_commission_value'  . $additional_key . '_manager'];
            $ftp_value_manager = $this->whitelabel['def_ftp_commission_value'  . $additional_key . '_manager'];
        }
        
        return [
            $commission_value_manager,
            $ftp_value_manager
        ];
    }
    
    /**
     * Get or calculate single commission value
     * 
     * @param string $commission_value_manager
     * @param array $transaction
     * @return string
     */
    private function calculate_single_commission(
        $commission_value_manager,
        $transaction
    ): string {
        $commission_manager = "0";
        
        if (!empty($commission_value_manager)) {
            $payment_cost = "0";
            if (!empty($transaction['payment_cost_manager'])) {
                $payment_cost = $transaction['payment_cost_manager'];
            }
            $transaction_income = round($transaction['income_manager'] - $payment_cost, 4);
            $com_value_div = round($commission_value_manager / 100, 4);
            $commission_manager = round($transaction_income * $com_value_div, 2);
        }
        
        return $commission_manager;
    }
    
    /**
     * Within this function commission for manager is recalculated to
     * currency from transaction (user currency), payment currency and
     * default currency (USD).
     *
     * @param array $transaction
     * @param string $commission_manager
     * @return array
     */
    private function get_commission_in_other_currencies(
        $transaction,
        $commission_manager
    ): array {
        $manager_currency_tab = Helpers_Currency::get_mtab_currency(
            false,
            null,
            $this->whitelabel['manager_site_currency_id']
        );
        
        $default_currency_tab = Helpers_Currency::get_mtab_currency(false, "USD");
        
        $user_currency_tab = Helpers_Currency::get_mtab_currency(
            false,
            null,
            $transaction['currency_id']
        );
        
        $payment_currency_tab = Helpers_Currency::get_mtab_currency(
            false,
            null,
            $transaction['payment_currency_id']
        );
        
        $commission_usd = Helpers_Currency::get_recalculated_to_given_currency(
            $commission_manager,
            $manager_currency_tab,
            $default_currency_tab['code']
        );
        $commission_user = Helpers_Currency::get_recalculated_to_given_currency(
            $commission_manager,
            $manager_currency_tab,
            $user_currency_tab['code']
        );
        $commission_payment = Helpers_Currency::get_recalculated_to_given_currency(
            $commission_manager,
            $manager_currency_tab,
            $payment_currency_tab['code']
        );
        
        return [
            $commission_usd,
            $commission_user,
            $commission_payment
        ];
    }

    /**
     *
     * @param array $transaction
     * @param string $commission_manager In fact this is value in float but formatted
     * @param int $whitelabel_aff_id
     * @param int $whitelabelUserAffId
     * @param int $type
     * @param int $type_tier
     * @param int $is_accepted
     * @return void
     */
    private function save_commission(
        $transaction,
        $commission_manager,
        $whitelabel_aff_id,
        $whitelabelUserAffId,
        $type,
        $type_tier,
        $is_accepted
    ): void {
        list(
            $commission_usd,
            $commission_user,
            $commission_payment
        ) = $this->get_commission_in_other_currencies($transaction, $commission_manager);

        $set = [
            "whitelabel_aff_id" => $whitelabel_aff_id,
            "whitelabel_user_aff_id" => $whitelabelUserAffId,
            "whitelabel_transaction_id" => $transaction->id,
            "currency_id" => $transaction->currency_id,                 // This is in fact user currency
            "payment_currency_id" => $transaction->payment_currency_id, // Payment currency
            "type" => $type,
            "tier" => $type_tier,
            "commission" => $commission_user,
            "commission_usd" => $commission_usd,
            "commission_payment" => $commission_payment,
            "commission_manager" => $commission_manager,
            "is_accepted" => $is_accepted
        ];

        $aff_commission_model = Model_Whitelabel_Aff_Commission::forge();
        $aff_commission_model->set($set);
        $aff_commission_model->save();
    }

    private function affiliateIsActive(WhitelabelAff $affiliate): bool
    {
        return !$affiliate->isDeleted && $affiliate->isActive && $affiliate->isAccepted;
    }

    public function calculateCommissionForAff(
        Model_Whitelabel_User $user, 
        Model_Whitelabel_Transaction $transaction, 
        int $type): void
    {
        $userAffiliate = $this->whitelabelUserAffRepository->findUserAffiliate($this->whitelabel['id'], $user['id']);

        if (!$userAffiliate) {
            return;
        }

        $affiliate = $this->whitelabelAffRepository->findAffiliateById($userAffiliate->whitelabelAffId);

        if (!$affiliate || !$this->affiliateIsActive($affiliate)) {
            return;
        }

        list(
            $commissionValueManagerTierFirst,
            $ftpValueManagerTierFirst
        ) = $this->get_commission_values(
            $affiliate->whitelabelAffGroupId,
            Helpers_General::TYPE_TIER_FIRST
        );

        $commissionValueManagerTierSecond = null;
        $ftpValueManagerTierSecond = null;
        $parentUserAffiliate = null;

        if (!is_null($affiliate->whitelabelAffParentId)) {
            $parentUserAffiliate = $this->whitelabelAffRepository->findAffiliateById($affiliate->whitelabelAffParentId);

            if ($parentUserAffiliate && $this->affiliateIsActive($parentUserAffiliate)) {
                list(
                    $commissionValueManagerTierSecond,
                    $ftpValueManagerTierSecond
                ) = $this->get_commission_values(
                    $parentUserAffiliate->whitelabelAffGroupId,
                    Helpers_General::TYPE_TIER_SECOND
                );
            }
        }

        $commissionManagerTierFirst = 0;
        $commissionManagerTierSecond = 0;

        switch ($type) {
            case Helpers_General::TYPE_AFF_SALE:
                $commissionManagerTierFirst = $this->calculate_single_commission(
                    $commissionValueManagerTierFirst,
                    $transaction,
                );
                $commissionManagerTierSecond = $this->calculate_single_commission(
                    $commissionValueManagerTierSecond,
                    $transaction,
                );
                break;
            case Helpers_General::TYPE_AFF_FTP:
                if (isset($ftpValueManagerTierFirst)) {
                    $commissionManagerTierFirst = $this->calculate_single_commission(
                        $ftpValueManagerTierFirst,
                        $transaction,
                    );
                }

                if (isset($ftpValueManagerTierSecond)) {
                    $commissionManagerTierSecond = $this->calculate_single_commission(
                        $ftpValueManagerTierSecond,
                        $transaction,
                    );
                }
                break;
            default:
            break;
        }

        if (round($commissionManagerTierFirst, 2) < 0) {
            $commissionManagerTierFirst = '0.00';
        }

        if (round($commissionManagerTierSecond, 2) < 0) {
            $commissionManagerTierSecond = '0.00';
        }

        $isAccepted = 0;
        if ($userAffiliate->isAccepted) {
            $isAccepted = $this->whitelabel['aff_payout_type'];
        }

        if (!empty($commissionManagerTierFirst)) {
            $this->save_commission(
                $transaction,
                $commissionManagerTierFirst,
                $affiliate->id,
                $userAffiliate->whitelabelAffId,
                $type,
                Helpers_General::TYPE_TIER_FIRST,
                $isAccepted
            );
        }

        if (!empty($commissionManagerTierSecond) && 
            !is_null($affiliate->whitelabelAffParentId) && $parentUserAffiliate) {
            $this->save_commission(
                $transaction,
                $commissionManagerTierSecond,
                $parentUserAffiliate->id,
                $userAffiliate->whitelabelAffId,
                $type,
                Helpers_General::TYPE_TIER_SECOND,
                $isAccepted
            );
        }
    }
}
