<?php

use Models\RaffleDraw;

/**
 *
 */
trait Traits_Prepare_Tickets
{
    /**
     *
     * @param array &  $ticket
     * @param int|null $whitelabel
     * @param array    $lottery
     * @param string   $locale
     */
    private function prepare_whitelabel_user_ticket_data(array &$ticket, ?int $whitelabel, array $lottery, string $locale)
    {
        $full_token = "-";
        if (isset($ticket['token'])) {
            $full_token = $ticket['prefix'] . "T" . $ticket['token'];
        }
        $ticket['full_token'] = $full_token;

        $full_token_transaction = "-";
        if (isset($ticket['ttoken'])) {
            $full_token_transaction = $ticket['prefix'] . "P" . $ticket['ttoken'];
        }
        $ticket['transaction_full_token'] = $full_token_transaction;

        self::prepare_user_data($ticket);

        $currency = 'USD';
        $bonus_cost = $ticket['bonus_cost_usd'];
        $prize = $ticket['prize_usd'];
        $prize_net = $ticket['prize_net_usd'];
        if ($whitelabel) {
            $bonus_cost = $ticket['bonus_cost_manager'];
            $prize = $ticket['prize_manager'];
            $prize_net = $ticket['prize_net_manager'];
        }
        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
        self::prepare_pricing($ticket, $whitelabel, $formatter);
            
        if ($bonus_cost !== '0.00') {
            $bonus_cost_display = $formatter->formatCurrency($bonus_cost, $currency);
            $ticket['bonus_cost_display'] = $bonus_cost_display;

            $bonus_cost_other_t = [];
            if ($currency !== $ticket['user_currency_code']) {
                $bonus_cost_user = $formatter->formatCurrency($ticket['bonus_cost'], $ticket['user_currency_code']);
                $bonus_cost_other_t[] = _("User currency") .
                        ": " . $bonus_cost_user;
            }
            if ($currency !== $ticket['lottery_currency_code']) {
                $bonus_cost_lottery = $formatter->formatCurrency($ticket['bonus_cost_local'], $ticket['lottery_currency_code']);
                $bonus_cost_other_t[] = _("Lottery currency") .
                        ": " . $bonus_cost_lottery;
            }
            $bonus_cost_other = implode("\n", $bonus_cost_other_t);
            $ticket['bonus_cost_other'] = $bonus_cost_other;
        }

        $ticket_model = "";
        $ticket_model_extra = "";
        switch ($ticket['model']) {
            case Helpers_General::LOTTERY_MODEL_PURCHASE:
                $ticket_model = _("Purchase");
                break;
            case Helpers_General::LOTTERY_MODEL_MIXED:
                $ticket_model = _("Mixed");
                if ($ticket['is_insured']) {
                    $ticket_model_extra = ' (' . _("Insured") . ')';
                } else {
                    $ticket_model_extra = ' (' . _("Purchased") . ')';
                }
                break;
            case Helpers_General::LOTTERY_MODEL_PURCHASE_SCAN:
                $ticket_model = _("Purchase (Scan)");
                break;
            case Helpers_General::LOTTERY_MODEL_NONE:
                $ticket_model = _("None");
                break;
        }
        $model_name = $ticket_model;
        $model_name .= $ticket_model_extra;
        $ticket['model_name'] = $model_name;

        $ticket_insured_text = "";
        if ($ticket['is_insured'] && !empty($ticket['tier'])) {
            $ticket_insured_text = _("Tier");
            $ticket_insured_text .= ': ' . $ticket['tier'];
        }
        $ticket['tier_display'] = $ticket_insured_text;

        switch ($ticket['status']) {
            case Helpers_General::TICKET_STATUS_PENDING:
                $ticket['status_display'] = _("Pending");
            break;
            case Helpers_General::TICKET_STATUS_WIN:
                $ticket['status_display'] = _("Win");
            break;
            case Helpers_General::TICKET_STATUS_NO_WINNINGS:
                $ticket['status_display'] = _("No winnings");
            break;
            case Helpers_General::TICKET_STATUS_CANCELED:
                $ticket['status_display'] = _("Canceled");
                break;
        }

        $ticket['status_win'] = false;
        if ($ticket['status'] == Helpers_General::TICKET_STATUS_WIN) {
            $ticket['status_win'] = true;

            $jackpot_text = "";
            if ($ticket['prize_jackpot']) {
                $jackpot_text = _("Jackpot");
            }
            $ticket['jackpot_prize_text'] = $jackpot_text;

            self::prepare_prizes($ticket, $whitelabel, $formatter);

            if ($ticket['prize_local'] !== $ticket['prize_net_local']) {
                $prize_net_display = $formatter->formatCurrency($prize_net, $currency);
                $ticket['prize_net_display'] = $prize_net_display;

                if ($currency !== $ticket['lottery_currency_code']) {
                    $prize_net_lottery_temp = $formatter->formatCurrency($ticket['prize_net_local'], $ticket['lottery_currency_code']);
                    $prize_net_lottery = _("Lottery currency") .
                                ": " . $prize_net_lottery_temp;
                    $ticket['prizes_net_other'] = $prize_net_lottery;
                }
            }
        }

        if (!empty($ticket['draw_date'])) {
            $ticket['draw_date_display'] = Lotto_View::format_date(
                $ticket['draw_date'],
                IntlDateFormatter::MEDIUM,
                IntlDateFormatter::LONG,
                $lottery['timezone'],
                false,
                true
            );
        }

        $ticket_payout_class = "";
        $ticket_payout = "";
        if ($ticket['status'] == Helpers_General::TICKET_STATUS_WIN) {
            if ($ticket['payout'] == Helpers_General::TICKET_PAYOUT_PAIDOUT) {
                $ticket_payout_class = 'text-success';
                $ticket_payout = _("Yes");
            } else {
                $ticket_payout_class = 'text-danger';
                $ticket_payout = _("No");
            }
        }
        $ticket['payout_class'] = $ticket_payout_class;
        $ticket['payout_display'] = $ticket_payout;
        switch ((int)$ticket['payout']) {
            case Helpers_General::TICKET_PAYOUT_PENDING:
                $ticket['payout_class'] = '';
                $ticket['payout_text'] = _("Pending");
                break;
            case Helpers_General::TICKET_PAYOUT_PAIDOUT:
                $ticket['payout_class'] = 'text-success';
                $ticket['payout_text'] = _("Paid out");
                break;
        }

        $prize_payout_percent = (float)$ticket['prize_payout_percent'] / 100;
        $formatter = new NumberFormatter(
            $locale,
            NumberFormatter::PERCENT
        );
        $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, 2);
        $ticket['prize_payout_display'] = $formatter->format($prize_payout_percent);
        $keno_data = Model_Whitelabel_User_Ticket_Keno_Data::find_one_by('whitelabel_user_ticket_id', $ticket['id']);
        if (!empty($keno_data)){
            $ticket['numbers_per_line'] = $keno_data['numbers_per_line'];
            if (!is_null($keno_data['lottery_type_multiplier_id'])) {
                $multiplier = Model_Lottery_Type_Multiplier::find_one_by('id', $keno_data['lottery_type_multiplier_id']);
                $ticket['ticket_multiplier'] = $multiplier['multiplier'];
            }
        }
    }

    /**
     *
     * @param array &$ticket
     * @param int $whitelabel
     * @param string $locale
     */
    private function prepare_multidraw_ticket_data(array &$ticket, ?int $whitelabel, string $locale)
    {
        $full_token = "-";
        if (isset($ticket['token'])) {
            $full_token = $ticket['prefix'] . "M" . $ticket['token'];
        }
        $ticket['full_token'] = $full_token;

        self::prepare_user_data($ticket);
    }

    /**
     *
     * @param int $whitelabel_id
     * @param string $locale
     * @param array $ticket
     * @param array $lines
     * @param array $line_type
     * @return array
     */
    private function prepare_line_data(
        ?int $whitelabel_id,
        string $locale,
        array $ticket,
        array $lines,
        array $line_type
    ): array {
        $payout = true;
        $lines_data = [];
        
        $draw_data = null;
        if ((int)$ticket['status'] !== Helpers_General::TICKET_STATUS_PENDING
        ) {
            $draws = Model_Lottery_Draw::find([
                'where' => [
                    'lottery_id' => $ticket['lottery_id'],
                    'date_local' => $ticket['draw_date']
                ]
            ]);
            if (!is_null($draws) && isset($draws[0])) {
                $draw_data = $draws[0];
            }
        }
        
        foreach ($lines as $lkey => $line) {
            $line_data = [];

            $slip_additional_data = null;

            $line_data['draw'] = false;

            if (isset($line['additional_data'])) {
                $slip_additional_data = unserialize($line['additional_data']);
                if ($slip_additional_data === false) {
                    $slip_additional_data = null;
                }
            }
            
            $line_data['numbers'] = $line['numbers'];
            $line_data['bnumbers'] = $line['bnumbers'];
            $line_data['slip_additional_data'] = $slip_additional_data;
            
            if (isset($draw_data)) {
                $line_data['draw'] = true;
                $draw_additional_data = unserialize($draw_data['additional_data']);
                $line_additional_data = unserialize($line['additional_data']);
                $slip_additional_data_numbers = null;

                $slip = Model_Whitelabel_User_Ticket_Slip::find_by_pk($line['whitelabel_user_ticket_slip_id']);
                if (isset($slip['additional_data'])) {
                    $slip_additional_data_numbers = unserialize($slip['additional_data']);
                    if ($slip_additional_data_numbers === false) {
                        $slip_additional_data_numbers = null;
                    }
                }
                
                $line_data['draw_numbers'] = $draw_data['numbers'];
                $line_data['draw_bnumbers'] = $draw_data['bnumbers'];
                $line_data['bextra'] = $line_type['bextra'];
                $line_data['slip_additional_data_numbers'] = $slip_additional_data_numbers;
                
                $prize = $line['prize_usd'];
                $currency = 'USD';
                if (isset($whitelabel_id)) {
                    $prize = $line['prize_manager'];
                    $currency = $ticket['manager_currency_code'];
                }
                $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
                
                if ($line['is_jackpot']) {
                    $line_data['prize_display'] = _("Jackpot");
                } else {
                    $line_prize_manager = $formatter->formatCurrency($prize, $currency);
                    $line_data['prize_display'] = $line_prize_manager;

                    if (!empty($line['prize_local'])) {
                        $line_prize_lottery = $formatter->formatCurrency($line['prize_local'], $ticket['lottery_currency_code']);
                        $line_prize_other_text = _("Lottery prize currency") .
                            ": " . $line_prize_lottery;

                        if (!empty($line['prize_net_local']) &&
                                $line['prize_local'] != $line['prize_net_local']
                        ) {
                            $line_prize_net_local = $formatter->formatCurrency($line['prize_net_local'], $ticket['lottery_currency_code']);
                            $line_prize_other_text .= "\n" . _("Lottery prize net currency") .
                                ": " . $line_prize_net_local;
                        }

                        if (!empty($line['prize_net']) &&
                                (string)$ticket['lottery_currency_code'] !== (string)$ticket['user_currency_code']
                        ) {
                            $line_prize_net = $formatter->formatCurrency($line['prize_net'], $ticket['user_currency_code']);
                            $line_prize_other_text .= "\n" . _("User prize net currency") .
                                ": " . $line_prize_net;
                        }
                        $line_data['other_prizes'] = $line_prize_other_text;
                    }
                }

                $line_match = _("none");
                if (isset($line['lottery_type_data_id'])) {
                    $isKeno = (int)$ticket['lottery_id'] === Helpers_Lottery::KENO_ID;
                    if ($isKeno) {
                        $line_match = sprintf("%s / %s", $line['match_b'], $line['match_n']);
                    } else {
                        $line_match = $line['match_n'] .
                            ($line_type['brange'] ? ' + ' . $line['match_b'] :
                                ($line_type['bextra'] && $line['match_b'] ?
                                    " + {$line['match_b']}" :
                                    '')
                            );
                    }

                    if ($draw_additional_data !== false &&
                        $line_additional_data !== false &&
                        $draw_additional_data == $line_additional_data
                    ) {
                        $line_match .= "+";
                        $line_match .= 'R';
                    }

                    $line_data['lottery_type_data_id'] = $line['lottery_type_data_id'];
                }
                $line_data['match'] = Security::htmlentities($line_match);

                $line_payout = "";
                switch ((int)$line['payout']) {
                    case Helpers_General::TICKET_PAYOUT_PENDING:
                        $line_payout = _("Pending");
                        $line_data['manual_confirm'] = false;
                        if ((int)$line['status'] === Helpers_General::TICKET_STATUS_WIN) {
                            $payout = false;
                            if ($line['is_jackpot'] == 0) {
                                $line_data['lkey'] = $lkey;
                                $line_data['manual_confirm'] = true;
                            }
                        }
                        break;
                    case Helpers_General::TICKET_PAYOUT_PAIDOUT:
                        $line_payout = _("Paid out");
                        break;
                }

                $line_data['payout'] = Security::htmlentities($line_payout);
            }
            
            $lines_data[] = $line_data;
        }
        
        return [
            $lines_data,
            $payout
        ];
    }

    /**
     *
     * @param int $whitelabel_id
     * @param array $ticket_data
     * @param array $lines
     * @param string $locale
     * @return string
     */
    private function prepare_lines_data_for_export(
        ?int $whitelabel_id,
        array $ticket_data,
        array $lines,
        string $locale
    ): string {
        $payout = true;
        $lines_data = [];
        $data = [];
        
        foreach ($lines as $line) {
            $line_data = [];
            $winning_lines = [];
            
            $line_data['numbers'] = $line['numbers'];
            $line_data['bonus_numbers'] = $line['bnumbers'];
            
            if ((isset($line['prize'])) && ((float)$line['prize'] > 0)) {
                $prize_data = [];

                $prize = $line['prize_usd'];
                $currency = 'USD';
                if (isset($whitelabel_id)) {
                    $prize = $line['prize_manager'];
                    $currency = $ticket_data['manager_currency_code'];
                }
                $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);

                $line_prize_manager = $formatter->formatCurrency($prize, $currency);
                $prize_data['prize'] = $line_prize_manager;
                    
                $winning_line = array_merge($line_data, $prize_data);
                $winning_lines[] = $winning_line;
            }
            
            $lines_data[] = $line_data;
        }
        if (count($lines_data) > 0) {
            $data['lines'] = $lines_data;
        }
        if (count($winning_lines) > 0) {
            $data['winning_lines'] = $winning_lines;
        }
        
        $lines_data_json = json_encode($data);
        
        return $lines_data_json;
    }

    private function prepare_raffle_ticket_data(array &$ticket, ?int $whitelabel, string $locale)
    {
        $full_token = "-";
        if (isset($ticket['token'])) {
            $full_token = $ticket['prefix'] . "R" . $ticket['token'];
        }
        $ticket['full_token'] = $full_token;

        $full_token_transaction = "-";
        if (isset($ticket['ttoken'])) {
            $full_token_transaction = $ticket['prefix'] . "P" . $ticket['ttoken'];
        }
        $ticket['transaction_full_token'] = $full_token_transaction;

        self::prepare_user_data($ticket);

        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
        self::prepare_pricing($ticket, $whitelabel, $formatter);

        switch ($ticket['status']) {
            case Model_Whitelabel_Raffle_Ticket::RAFFLE_TICKET_STATUS_PENDING:
                $ticket['status_display'] = _("Pending");
            break;
            case Model_Whitelabel_Raffle_Ticket::RAFFLE_TICKET_STATUS_WIN:
                $ticket['status_display'] = _("Win");
            break;
            case Model_Whitelabel_Raffle_Ticket::RAFFLE_TICKET_STATUS_NO_WINNINGS:
                $ticket['status_display'] = _("No winnings");
            break;
        }

        $ticket['status_win'] = false;
        if ($ticket['status'] == Model_Whitelabel_Raffle_Ticket::RAFFLE_TICKET_STATUS_WIN) {
            $ticket['status_win'] = true;
            self::prepare_prizes($ticket, $whitelabel, $formatter);
        }

        $ticket_payout_class = "";
        $ticket_payout = "";
        if ($ticket['status'] == Model_Whitelabel_Raffle_Ticket::RAFFLE_TICKET_STATUS_WIN) {
            if ($ticket['is_paid_out'] == Model_Whitelabel_Raffle_Ticket::RAFFLE_TICKET_IS_PAID_OUT_PAIDOUT) {
                $ticket_payout_class = 'text-success';
                $ticket_payout = _("Yes");
            } else {
                $ticket_payout_class = 'text-danger';
                $ticket_payout = _("No");
            }
        }
        $ticket['payout_class'] = $ticket_payout_class;
        $ticket['payout_display'] = $ticket_payout;
        switch ((int)$ticket['is_paid_out']) {
            case Model_Whitelabel_Raffle_Ticket::RAFFLE_TICKET_IS_PAID_OUT_PENDING:
                $ticket['payout_class'] = '';
                $ticket['payout_text'] = _("Pending");
                break;
            case Model_Whitelabel_Raffle_Ticket::RAFFLE_TICKET_IS_PAID_OUT_PAIDOUT:
                $ticket['payout_class'] = 'text-success';
                $ticket['payout_text'] = _("Paid out");
                break;
        }
    }

    private function prepare_raffle_line_data(
        ?int $whitelabel_id,
        string $locale,
        array $ticket,
        array $lines
    ): array {
        $lines_data = [];
        
        $draw_data = null;
        if ((int)$ticket['status'] !== Model_Whitelabel_Raffle_Ticket::RAFFLE_TICKET_STATUS_PENDING) {
            $draws = RaffleDraw::find('all', [
                'where' => [
                    'raffle_id' => $ticket['raffle_id'],
                    'date' => $ticket['draw_date']
                ]
            ]);
            if (!empty($draws)) {
                /** @var RaffleDraw $draw_data */
                $draw_data = current($draws);
            }
        }
        
        foreach ($lines as $line) {
            $line_data = [];
            $line_data['draw'] = false;
            $line_data['raffle_prize_id'] = $line['raffle_prize_id'];
            $line_data['number'] = $line['number'];
            
            if (isset($draw_data)) {
                $line_data['draw'] = true;
                $line_data['draw_numbers'] = $draw_data->numbers;
                
                $prize = $line['per_user'];
                $currency = $line['prize_currency_code'];
                $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
                $prize = $formatter->formatCurrency($prize, $currency);

                $line_data['prize_display'] = $prize;
            }
            
            $lines_data[] = $line_data;
        }
        
        return $lines_data;
    }

    private function prepare_raffle_lines_data_for_export(
        ?int $whitelabel_id,
        array $ticket_data,
        array $lines,
        string $locale
    ): string {
        $lines_data = [];
        $winning_lines = [];
        $data = [];
        
        foreach ($lines as $line) {
            $line_data = [];
            
            $line_data['number'] = $line['number'];
            
            if (isset($line['raffle_prize_id'])) {
                $prize_data = [];

                $prize = $line['per_user'];
                $currency = $line['prize_currency_code'];
                $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
                $prize = $formatter->formatCurrency($prize, $currency);
                $prize_data['prize'] = $prize;
                    
                $winning_line = array_merge($line_data, $prize_data);
                $winning_lines[] = $winning_line;
            }
            
            $lines_data[] = $line_data;
        }
        if (count($lines_data) > 0) {
            $data['lines'] = $lines_data;
        }
        if (count($winning_lines) > 0) {
            $data['winning_lines'] = $winning_lines;
        }
        
        $lines_data_json = json_encode($data);
        
        return $lines_data_json;
    }

    private function prepare_user_data(array &$ticket): void
    {
        $user_full_token = "-";
        if (isset($ticket['utoken'])) {
            $user_full_token = $ticket['prefix'] . "U" . $ticket['utoken'];
        }
        $ticket['user_full_token'] = $user_full_token;
        
        $user_fullname = _("Anonymous");
        if (!empty($ticket['name']) || !empty($ticket['surname'])) {
            $user_fullname = $ticket['name'] . ' ' . $ticket['surname'];
        }
        $ticket['user_fullname'] = $user_fullname;

        $user_login = "-";
        if (isset($ticket['user_login'])) {
            $user_login = $ticket['user_login'];
        }
        $ticket['user_login'] = $user_login;
    }

    private function prepare_pricing(array &$ticket, ?int $whitelabel, NumberFormatter $formatter): void
    {
        $currency = 'USD';
        $amount = $ticket['amount_usd'];
        $bonus_amount = $ticket['bonus_amount_usd'];
        $cost = $ticket['cost_usd'];
        $income = $ticket['income_usd'];
        $margin = $ticket['margin_usd'];
        
        if ($whitelabel) {
            $currency = $ticket['manager_currency_code'];
            $amount = $ticket['amount_manager'];
            $bonus_amount = $ticket['bonus_amount_manager'];
            $cost = $ticket['cost_manager'];
            $income = $ticket['income_manager'];
            $margin = $ticket['margin_manager'];
        }

        if ($amount === '0.00' && $bonus_amount === '0.00') {
            $amount_manager_text = _("Free");
            $bonus_amount_manager_text = _("Free");
        } else {
            $amount_manager_text = $formatter->formatCurrency($amount, $currency);
            $bonus_amount_manager_text = $formatter->formatCurrency($bonus_amount, $currency);
        }

        $amounts_temp = [];
        $amounts_other = "";
        $lottery_amount = "";

        if ((float)$amount > 0) {
            $amount_manager_text = $formatter->formatCurrency($amount, $currency);
            if ($ticket['user_currency_code'] !== $currency) {
                $amount_text = $formatter->formatCurrency($ticket['amount'], $ticket['user_currency_code']);
                $amounts_temp[] = _("User currency") . ": " . $amount_text;
            }
                
            if ($ticket['lottery_currency_code'] !== $currency) {
                $lottery_amount = $formatter->formatCurrency($ticket['amount_local'], $ticket['lottery_currency_code']);
                $amounts_temp[] = _("Lottery currency") .
                        ": " . $lottery_amount;
            }
                
            $amounts_other = implode("\n", $amounts_temp);
        }
        $ticket['amount_display'] = $amount_manager_text;
        $ticket['amounts_other'] = $amounts_other;

        $bonus_amounts_temp = [];
        $bonus_amounts_other = "";
        $lottery_bonus_amount = "";

        if ((float)$bonus_amount > 0) {
            if ($ticket['user_currency_code'] !== $currency) {
                $bonus_amount_text = $formatter->formatCurrency($ticket['bonus_amount'], $ticket['user_currency_code']);
                $bonus_amounts_temp[] = _("User currency") . ": " . $bonus_amount_text;
            }
                
            if ($ticket['lottery_currency_code'] !== $currency) {
                $lottery_bonus_amount = $formatter->formatCurrency($ticket['bonus_amount_local'], $ticket['lottery_currency_code']);
                $bonus_amounts_temp[] = _("Lottery currency") .
                        ": " . $lottery_bonus_amount;
            }
                
            $bonus_amounts_other = implode("\n", $bonus_amounts_temp);
        }
        $ticket['bonus_amount_display'] = $bonus_amount_manager_text;
        $ticket['bonus_amounts_other'] = $bonus_amounts_other;

        $cost_display = $formatter->formatCurrency($cost, $currency);
        $ticket['cost_display'] = $cost_display;

        $costs_other_t = [];
        if ($currency !== $ticket['user_currency_code']) {
            $cost_user = $formatter->formatCurrency($ticket['cost'], $ticket['user_currency_code']);
            $costs_other_t[] = _("User currency") .
                    ": " . $cost_user;
        }
        if ($currency !== $ticket['lottery_currency_code']) {
            $cost_lottery = $formatter->formatCurrency($ticket['cost_local'], $ticket['lottery_currency_code']);
            $costs_other_t[] = _("Lottery currency") .
                    ": " . $cost_lottery;
        }
        $costs_other = implode("\n", $costs_other_t);
        $ticket['costs_other'] = $costs_other;

        $income_display = $formatter->formatCurrency($income, $currency);
        $ticket['income_display'] = $income_display;

        $incomes_other_t = [];
        if ($currency !== $ticket['user_currency_code']) {
            $income_user = $formatter->formatCurrency($ticket['income'], $ticket['user_currency_code']);
            $incomes_other_t[] = _("User currency") .
                    ": " . $income_user;
        }
        if ($currency !== $ticket['lottery_currency_code']) {
            $income_lottery = $formatter->formatCurrency($ticket['income_local'], $ticket['lottery_currency_code']);
            $incomes_other_t[] = _("Lottery currency") .
                    ": " . $income_lottery;
        }
        $incomes_other = implode("\n", $incomes_other_t);
        $ticket['incomes_other'] = $incomes_other;

        $margin_display = $formatter->formatCurrency($margin, $currency);
        $ticket['margin_display'] = $margin_display;

        $margins_other_t = [];
        if ($currency !== $ticket['user_currency_code']) {
            $margin_user = $formatter->formatCurrency($ticket['margin'], $ticket['user_currency_code']);
            $margins_other_t[] = _("User currency") .
                    ": " . $margin_user;
        }
        if ($currency !== $ticket['lottery_currency_code']) {
            $margin_lottery = $formatter->formatCurrency($ticket['margin_local'], $ticket['lottery_currency_code']);
            $margins_other_t[] = _("Lottery currency") .
                    ": " . $margin_lottery;
        }
        $margins_other = implode("\n", $margins_other_t);
        $ticket['margins_other'] = $margins_other;
    }

    private function prepare_prizes(array &$ticket, ?int $whitelabel, NumberFormatter $formatter): void
    {
        $currency = 'USD';
        $prize = $ticket['prize_usd'];
        if ($whitelabel) {
            $prize = $ticket['prize_manager'];
        }
        
        $prize_display = "";
        $prizes_other = "";
        $prize_display = $formatter->formatCurrency($prize, $currency);

        $prizes_other_temp = [];
        if ($currency !== $ticket['user_currency_code']) {
            $prize_user = $formatter->formatCurrency($ticket['prize'], $ticket['user_currency_code']);
            $prizes_other_temp[] = _("User currency") .
                            ": " . $prize_user;
        }
        if ($currency !== $ticket['lottery_currency_code']) {
            $prize_lottery = $formatter->formatCurrency($ticket['prize_local'], $ticket['lottery_currency_code']);
            $prizes_other_temp[] = _("Lottery currency") .
                            ": " . $prize_lottery;
        }
        $prizes_other = implode("\n", $prizes_other_temp);

        $ticket['prize_display'] = $prize_display;
        $ticket['prizes_other'] = $prizes_other;
    }
}
