<?php

/**
 * Description of index
 */
class Presenter_Whitelabel_Reports_Index extends Presenter_Presenter
{
    
    /**
     *
     */
    public function view()
    {
        $winners = $this->prepare_winners_data();
        $this->set("winners", $winners);
    }
    
    /**
     * Prepare data get from query of winners
     *
     * @return array
     */
    private function prepare_winners_data(): array
    {
        $prepared_winners = [];
        
        $manager_currency_tab = Helpers_Currency::get_mtab_currency(
            false,
            null,
            $this->whitelabel['manager_site_currency_id']
        );
        
        foreach ($this->winners as $winner) {
            // Prepare urls for showing tickets buttons
            // Not for all data!!!
            $start_url = "tickets?filter[lottery]=" . $winner['lottery_id'];
            $start_url .= "&amp;filter[status]=";
            
            $tmpdate = new DateTime(
                $winner['date_local'],
                new DateTimeZone("UTC")
            );
            $middle_url = "&amp;filter[date]=";
            $middle_url .= rawurlencode($tmpdate->format('m/d/Y'));
            // Till here
            
            $winner['date_download'] = Lotto_View::format_date(
                $winner['date_download'],
                IntlDateFormatter::MEDIUM,
                IntlDateFormatter::MEDIUM
            );
            
            $winner['date_local'] = Lotto_View::format_date(
                $winner['date_local'],
                IntlDateFormatter::MEDIUM,
                IntlDateFormatter::NONE
            );
            
            $total_winners_t = intval($winner['total_winners']);
            $winner['total_winners'] = Lotto_View::format_number($total_winners_t);
            
            $total_prize_t = floatval($winner['total_prize']);
            $winner['total_prize'] = Lotto_View::format_currency(
                $total_prize_t,
                $winner['lottery_currency_code'],
                true
            );
            
            $total_jackpot_winners_text = '';
            if ($winner['total_jackpot_winners'] > 0) {
                $total_jackpot_winners_text = "<br>";
                $total_jackpot_winners_text .= _("Jackpots");
                $total_jackpot_winners_text .= ": ";
                
                $total_jackpot_winners_t = intval($winner['total_jackpot_winners']);
                $total_jackpot_winners_text .= Lotto_View::format_number($total_jackpot_winners_t);
            }
            $winner['total_jackpot_winners'] = $total_jackpot_winners_text;
            
            $total_quickpick_winners_text = '';
            if ($winner['total_quickpick_winners'] > 0) {
                $total_quickpick_winners_text = "<br>";
                $total_quickpick_winners_text .= _("Quick Picks");
                $total_quickpick_winners_text .= ": ";
                
                $total_quickpick_winners_t = intval($winner['total_quickpick_winners']);
                $total_quickpick_winners_text .= Lotto_View::format_number($total_quickpick_winners_t);
            }
            $winner['total_quickpick_winners'] = $total_quickpick_winners_text;
            
            $site_ticket_winners_t = intval($winner['site_ticket_winners']);
            $winner['site_ticket_winners'] = Lotto_View::format_number($site_ticket_winners_t);
            
            $site_winners_t = intval($winner['site_winners']);
            $winner['site_winners'] = Lotto_View::format_number($site_winners_t);
            
            $show_ticket_winners_button = false;
            $ticket_winners_button_url = '';
            if ($site_ticket_winners_t > 0) {
                $show_ticket_winners_button = true;
                
                $ticket_winners_button_url = $start_url;
                $ticket_winners_button_url .= Helpers_General::TICKET_STATUS_WIN;
                $ticket_winners_button_url .= $middle_url;
                $winner['ticket_winners_button_url'] = $ticket_winners_button_url;
            }
            $winner['show_ticket_winners_button'] = $show_ticket_winners_button;
            
            $site_prizes_manager_t = floatval($winner['site_prizes_manager']);
            $winner['site_prizes_manager'] = Lotto_View::format_currency(
                $site_prizes_manager_t,
                $manager_currency_tab['code'],
                true
            );
            
            $payout_sum_manager_t = floatval($winner['payout_sum_manager']);
            $winner['payout_sum_manager'] = Lotto_View::format_currency(
                $payout_sum_manager_t,
                $manager_currency_tab['code'],
                true
            );
            
            $npayout_sum_manager_t = floatval($winner['npayout_sum_manager']);
            $winner['npayout_sum_manager'] = Lotto_View::format_currency(
                $npayout_sum_manager_t,
                $manager_currency_tab['code'],
                true
            );
            
            if ($manager_currency_tab['code'] !== $winner['lottery_currency_code']) {
                if ($site_prizes_manager_t !== 0.00) {
                    $site_prizes_local = Lotto_View::format_currency(
                        $winner['site_prizes_local'],
                        $winner['lottery_currency_code'],
                        true
                    );
                    $site_prizes_others = _("Lottery currency") . ": " . $site_prizes_local;
                    $winner['site_prizes_others'] = $site_prizes_others;
                }
                
                if ($payout_sum_manager_t !== 0.00) {
                    $payout_sum_local = Lotto_View::format_currency(
                        $winner['payout_sum_local'],
                        $winner['lottery_currency_code'],
                        true
                    );
                    $payout_sum_others = _("Lottery currency") . ": " . $payout_sum_local;
                    $winner['payout_sum_others'] = $payout_sum_others;
                }
                
                if ($npayout_sum_manager_t !== 0.00) {
                    $npayout_sum_local = Lotto_View::format_currency(
                        $winner['npayout_sum_local'],
                        $winner['lottery_currency_code'],
                        true
                    );
                    $npayout_sum_others = _("Lottery currency") . ": " . $npayout_sum_local;
                    $winner['npayout_sum_others'] = $npayout_sum_others;
                }
            }
            
            $site_jackpot_winners_text = '';
            if ($winner['site_jackpot_winners'] > 0) {
                $site_jackpot_winners_text = "<br>";
                $site_jackpot_winners_text .= _("Jackpots");
                $site_jackpot_winners_text .= ": ";
                $site_jackpot_winners_text .= intval($winner['site_jackpot_winners']);
            }
            $winner['site_jackpot_winners'] = $site_jackpot_winners_text;

            $site_quickpick_winners_text = '';
            if ($winner['site_quickpick_winners'] > 0) {
                $site_quickpick_winners_text = "<br>";
                $site_quickpick_winners_text .= _("Quick Picks");
                $site_quickpick_winners_text .= ": ";
                $site_quickpick_winners_text .= intval($winner['site_quickpick_winners']);
            }
            $winner['site_quickpick_winners'] = $site_quickpick_winners_text;
            
            $payout_ticket_count_t = intval($winner['payout_ticket_count']);
            $winner['payout_ticket_count'] = Lotto_View::format_number($payout_ticket_count_t);
            
            $payout_count_t = intval($winner['payout_count']);
            $winner['payout_count'] = Lotto_View::format_number($payout_count_t);
            
            $show_ticket_payout_button = false;
            $ticket_payout_button_url = '';
            if (intval($winner['payout_ticket_count']) > 0) {
                $show_ticket_payout_button = true;
                
                $ticket_payout_button_url = $start_url;
                $ticket_payout_button_url .= Helpers_General::TICKET_STATUS_WIN;
                $ticket_payout_button_url .= $middle_url;
                $ticket_payout_button_url .= "&amp;filter[payout]=";
                $ticket_payout_button_url .= Helpers_General::TICKET_PAYOUT_PAIDOUT;
                $winner['ticket_payout_button_url'] = $ticket_payout_button_url;
            }
            $winner['show_ticket_payout_button'] = $show_ticket_payout_button;
           
            $npayout_ticket_count_t = intval($winner['npayout_ticket_count']);
            $winner['npayout_ticket_count'] = Lotto_View::format_number($npayout_ticket_count_t);
                
            $npayout_count_t = intval($winner['npayout_count']);
            $winner['npayout_count'] = Lotto_View::format_number($npayout_count_t);
            
            $show_ticket_npayout_button = false;
            $ticket_npayout_button_url = '';
            if (intval($winner['npayout_ticket_count']) > 0) {
                $show_ticket_npayout_button = true;
                
                $ticket_npayout_button_url = $start_url;
                $ticket_npayout_button_url .= Helpers_General::TICKET_STATUS_WIN;
                $ticket_npayout_button_url .= $middle_url;
                $ticket_npayout_button_url .= "&amp;filter[payout]=";
                $ticket_npayout_button_url .= Helpers_General::TICKET_PAYOUT_PENDING;
                $winner['ticket_npayout_button_url'] = $ticket_npayout_button_url;
            }
            $winner['show_ticket_npayout_button'] = $show_ticket_npayout_button;
            
            $ticket_nowinners_t = intval($winner['ticket_nowinners']);
            $winner['ticket_nowinners'] = Lotto_View::format_number($ticket_nowinners_t);
            
            $nowinners_t = intval($winner['nowinners']);
            $winner['nowinners'] = Lotto_View::format_number($nowinners_t);
            
            $show_ticket_nowinners_button = false;
            $ticket_nowinners_button_url = '';
            if (intval($winner['ticket_nowinners']) > 0) {
                $show_ticket_nowinners_button = true;
                
                $ticket_nowinners_button_url = $start_url;
                $ticket_nowinners_button_url .= Helpers_General::TICKET_STATUS_NO_WINNINGS;
                $ticket_nowinners_button_url .= $middle_url;
                $winner['ticket_nowinners_button_url'] = $ticket_nowinners_button_url;
            }
            $winner['show_ticket_nowinners_button'] = $show_ticket_nowinners_button;

            $prepared_winners[] = $winner;
        }
        
        return $prepared_winners;
    }
}
