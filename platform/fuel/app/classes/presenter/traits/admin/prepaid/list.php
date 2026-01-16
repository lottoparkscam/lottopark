<?php

/**
 *
 */
trait Presenter_Traits_Admin_Prepaid_List
{
    /**
     *
     * @return void
     */
    public function main_process(): void
    {
        $prepared_urls = $this->prepare_urls();
        $this->set('urls', $prepared_urls);

        $prepared_prepaids = $this->get_prepared_prepaids();
        $this->set('prepaids', $prepared_prepaids);

        $sum_value = Security::htmlentities($this->sum_to_show);
        $this->set('sum_value', $sum_value);
    }
    
    /**
     *
     * @return array
     */
    private function prepare_urls(): array
    {
        $urls = [
            'new' => $this->start_url . '/prepaid/' . $this->whitelabel['id'] . '/new',
            'back' => $this->start_url
        ];
        
        return $urls;
    }
    
    /**
     *
     * @return array
     */
    public function get_prepared_prepaids(): array
    {
        $prepared_prepaids = [];
        
        foreach ($this->prepaids as $prepaid) {
            $single_prepaid = [];
            
            $single_prepaid['date'] = Lotto_View::format_date($prepaid['date']);
            
            $amount_formatted = Lotto_View::format_currency(
                $prepaid['amount'],
                $this->manager_currency_code,
                true
            );
            $single_prepaid['amount'] = Security::htmlentities($amount_formatted);
            
            $show_manage_view = false;
            $transaction_ID = _("NA");
            if (!empty($prepaid['whitelabel_transaction_id']) &&
                !empty($prepaid['transaction_token'])
            ) {
                $show_manage_view = true;
                
                $transaction_ID = $this->whitelabel['prefix'] .
                    'P' . $prepaid['transaction_token'];
                
                $prepaid_transaction_id_text = '';
                if ((int)$this->source === Helpers_General::SOURCE_ADMIN) {
                    $prepaid_transaction_id_text = $prepaid['transaction_id'];    // This is for empire
                } else {
                    $prepaid_transaction_id_text = $prepaid['transaction_token'];
                }
                
                $view_url = '/transactions/view/';
                $view_url .= $prepaid_transaction_id_text;
                $view_url .= Lotto_View::query_vars();
                $single_prepaid["transaction_view_url"] = $view_url;
            }
            $single_prepaid["transaction_id"] = Security::htmlentities($transaction_ID);
            $single_prepaid["show_manage_view"] = Security::htmlentities($show_manage_view);
            
            $prepared_prepaids[] = $single_prepaid;
        }
        
        return $prepared_prepaids;
    }
}
