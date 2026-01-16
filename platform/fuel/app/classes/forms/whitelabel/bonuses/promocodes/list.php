<?php

class Forms_Whitelabel_Bonuses_Promocodes_List extends Forms_Main
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
    */
    public function __construct($whitelabel)
    {
        $this->whitelabel = $whitelabel;
    }

    /**
     *
     * @param array @campaign
     * @return array
     */
    public function prepare_campaign_data(&$campaign)
    {
        $campaign['action_url'] = 'promocodes/' . $campaign['token'];
        $type = $campaign['type'];
        switch ($type) {
            case Helpers_General::PROMO_CODE_TYPE_PURCHASE:
                $campaign['type'] = _('Purchase');
            break;
            case Helpers_General::PROMO_CODE_TYPE_DEPOSIT:
                $campaign['type'] = _('Deposit');
            break;
            case Helpers_General::PROMO_CODE_TYPE_REGISTER:
                $campaign['type'] = _('Register');
            break;
            case Helpers_General::PROMO_CODE_TYPE_PURCHASE_DEPOSIT:
                $campaign['type'] = _('Purchase') . ', ' . _('Deposit');
            break;
            case Helpers_General::PROMO_CODE_TYPE_PURCHASE_REGISTER:
                $campaign['type'] = _('Purchase') . ', ' . ('Register');
            break;
            case Helpers_General::PROMO_CODE_TYPE_DEPOSIT_REGISTER:
                $campaign['type'] = _('Deposit') . ', ' . _('Register');
            break;
            case Helpers_General::PROMO_CODE_TYPE_PURCHASE_DEPOSIT_REGISTER:
                $campaign['type'] = _('Purchase') . ', ' . _('Deposit') . ', ' . _('Register');
            break;
        }
        $bonus_type = $campaign['bonus_type'];
        switch ($bonus_type) {
            case Helpers_General::PROMO_CODE_BONUS_TYPE_FREE_LINE:
                $campaign['bonus_type'] = _('Free line');
            break;
            case Helpers_General::PROMO_CODE_BONUS_TYPE_DISCOUNT:
                $campaign['bonus_type'] = _('Discount');
            break;
            case Helpers_General::PROMO_CODE_BONUS_TYPE_BONUS_MONEY:
                $campaign['bonus_type'] = _('Bonus money');
            break;
        }
        $discount_type = $campaign['discount_type'];
        if (isset($discount_type)) {
            switch ($discount_type) {
                case Helpers_General::PROMO_CODE_DISCOUNT_TYPE_PERCENT:
                    $campaign['discount_amount'] .= ' ' . _("%");
                break;
                case Helpers_General::PROMO_CODE_DISCOUNT_TYPE_AMOUNT:
                    $currency = Model_Currency::find_by_pk($this->whitelabel['manager_site_currency_id']);
                    $campaign['discount_amount'] .= ' ' . Lotto_View::format_currency_code($currency['code']);
                break;
            }
        }
        $bonus_balance_type = $campaign['bonus_balance_type'];
        if (isset($bonus_balance_type)) {
            switch ($bonus_balance_type) {
                case Helpers_General::PROMO_CODE_BONUS_BALANCE_TYPE_PERCENT:
                    $campaign['bonus_balance_amount'] .= ' ' . _("%");
                break;
                case Helpers_General::PROMO_CODE_BONUS_BALANCE_TYPE_AMOUNT:
                    $currency = Model_Currency::find_by_pk($this->whitelabel['manager_site_currency_id']);
                    $campaign['bonus_balance_amount'] .= ' ' . Lotto_View::format_currency_code($currency['code']);
                break;
            }
        }
        if (isset($campaign['date_start'])) {
            $campaign['date_start'] = Lotto_View::format_date(
                $campaign['date_start'],
                IntlDateFormatter::MEDIUM,
                IntlDateFormatter::SHORT
            );
        }
        if (isset($campaign['date_end'])) {
            $campaign['date_end'] = Lotto_View::format_date(
                $campaign['date_end'],
                IntlDateFormatter::MEDIUM,
                IntlDateFormatter::SHORT
            );
        }
        $campaign['used'] = '-';
        if ($campaign['codes_count'] > 1) {
            $campaign['used'] = $campaign['used_codes_count'] ?? '0';
            $campaign['used'] .= '/' . $campaign['codes_count'];
        }
        $campaign['used_times'] = $campaign['used_times'] ?? '0';
    }
    
    /**
     *
     * @return void
     */
    public function process_form(): void
    {
        $inside = View::forge("whitelabel/bonuses/promocodes/list.php");

        $campaigns = Model_Whitelabel_Campaign::get_all_campaigns($this->whitelabel['id']);
        foreach ($campaigns as &$campaign) {
            self::prepare_campaign_data($campaign);
        }

        $inside->set('campaigns', $campaigns);
        $this->inside = $inside;
    }
}
