<?php

use Repositories\WhitelabelLotteryRepository;
use Services\Logs\FileLoggerService;
use Traits\Scans\ScansTrait;

/**
 * Description of Forms_Whitelabel_Ticket_View
 */
final class Forms_Whitelabel_Ticket_View extends Forms_Main
{
    use ScansTrait;
    /**
     *
     * @var array
     */
    private $whitelabel = [];
    private FileLoggerService $fileLoggerService;

    /**
     *
     * @var int|null
     */
    private $token = null;
    
    /**
     *
     * @var View
     */
    private $inside = null;
    
    /**
     *
     * @param int $token
     * @param array $whitelabel
     */
    public function __construct(int $token = null, array $whitelabel = [])
    {
        $this->token = $token;
        $this->whitelabel = $whitelabel;
        $this->fileLoggerService = Container::get(FileLoggerService::class);
    }

    /**
     *
     * @return array
     */
    public function get_whitelabel(): array
    {
        return $this->whitelabel;
    }

    /**
     *
     * @return int
     */
    public function get_token():? int
    {
        return $this->token;
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
     * @param array $ticket
     * @param array $lottery
     * @param array $transaction
     * @param array $user
     * @param array $keno_data
     *
     * @return array
     * @throws Exception
     */
    private function prepare_data(
        array $ticket,
        array $lottery,
        array $transaction,
        array $user,
        array $keno_data = []
    ): array {
        $whitelabel = $this->get_whitelabel();
        
        $ticket_data = [];
    
        $ticket_token = "-";
        if (!empty($ticket['token'])) {
            $ticket_token = $whitelabel['prefix'] . 'T' . $ticket['token'];
        }
        $ticket_data['ticket_token'] = Security::htmlentities($ticket_token);

        $transaction_token = "-";
        if (!empty($transaction['token'])) {
            $transaction_token = $whitelabel['prefix'] . 'P' . $transaction['token'];
        }
        $ticket_data['transaction_token'] = Security::htmlentities($transaction_token);

        $user_token = "-";
        if (!empty($user['token'])) {
            $user_token = $whitelabel['prefix'] . 'U' . $user['token'];
        }
        $ticket_data['user_token'] = Security::htmlentities($user_token);

        $user_name = _("Anonymous");
        if (!empty($user['name'])) {
            $user_name = $user['name'];
        }
        $ticket_data['user_name'] = Security::htmlentities($user_name);

        $user_surname = _("Anonymous");
        if (!empty($user['surname'])) {
            $user_surname = $user['surname'];
        }
        $ticket_data['user_surname'] = Security::htmlentities($user_surname);

        $ticket_data['user_email'] = Security::htmlentities($user['email']);

        $user_login = "-";
        if (!empty($user['login'])) {
            $user_login = $user['login'];
        }
        $ticket_data['user_login'] = Security::htmlentities($user_login);

        $lottery_name = "";
        if (!empty($lottery['name'])) {
            $lottery_name = _($lottery['name']);
        }
        $ticket_data['lottery_name'] = Security::htmlentities($lottery_name);

        $ticket_data['date'] = Lotto_View::format_date(
            $ticket['date'],
            IntlDateFormatter::MEDIUM,
            IntlDateFormatter::SHORT
        );

        if (!empty($ticket['draw_date'])) {
            $ticket_data['draw_date'] = Lotto_View::format_date(
                $ticket['draw_date'],
                IntlDateFormatter::MEDIUM,
                IntlDateFormatter::LONG,
                $lottery['timezone'],
                false
            );
        }

        if ($ticket['amount'] === '0.00' && $ticket['bonus_amount'] === '0.00') {
            $amount_manager_text = _("Free");
            $bonus_amount_manager_text = _("Free");
        } else {
            $amount_manager_text = Lotto_View::format_currency(
                $ticket['amount_manager'],
                $ticket['manager_currency_code'],
                true
            );
        
            $bonus_amount_manager_text = Lotto_View::format_currency(
                $ticket['bonus_amount_manager'],
                $ticket['manager_currency_code'],
                true
            );
        }

        $amounts_temp = [];
        $amounts_other = "";
        $lottery_amount = "";
        if ($ticket['amount'] != 0) {
            $amount_manager_text = Lotto_View::format_currency(
                $ticket['amount_manager'],
                $ticket['manager_currency_code'],
                true
            );
            
            if ((string)$ticket['user_currency_code'] !== (string)$ticket['manager_currency_code']) {
                $amount_text = Lotto_View::format_currency(
                    $ticket['amount'],
                    $ticket['user_currency_code'],
                    true
                );
                $amounts_temp[] = _("User currency") . ": " . $amount_text;
            }

            if ((string)$ticket['lottery_currency_code'] !== (string)$ticket['manager_currency_code']) {
                $lottery_amount = Lotto_View::format_currency(
                    $ticket['amount_local'],
                    $ticket['lottery_currency_code'],
                    true
                );
                $amounts_temp[] = _("Lottery currency") .
                    ": " . $lottery_amount;
            }
            
            $amounts_other = implode("<br>", $amounts_temp);
        }
        $ticket_data['amount_manager'] = $amount_manager_text;
        $ticket_data['amounts_other'] = $amounts_other;

        $bonus_amounts_temp = [];
        $bonus_amounts_other = "";
        
        $lottery_bonus_amount = "";
        if ((float)$ticket['bonus_amount'] > 0.00) {
            if ($ticket['user_currency_code'] !== $ticket['manager_currency_code']) {
                $bonus_amount_text = Lotto_View::format_currency(
                    $ticket['bonus_amount'],
                    $ticket['user_currency_code'],
                    true
                );
                $bonus_amounts_temp[] = _("User currency") . ": " . $bonus_amount_text;
            }
                
            if ($ticket['lottery_currency_code'] !== $ticket['manager_currency_code']) {
                $lottery_bonus_amount = Lotto_View::format_currency(
                    $ticket['bonus_amount_local'],
                    $ticket['lottery_currency_code'],
                    true
                );
                $bonus_amounts_temp[] = _("Lottery currency") .
                        ": " . $lottery_bonus_amount;
            }
                
            $bonus_amounts_other = implode("<br>", $bonus_amounts_temp);
        }
        $ticket_data['bonus_amount_manager'] = $bonus_amount_manager_text;
        $ticket_data['bonus_amounts_other'] = $bonus_amounts_other;

        $ticket_model = "";
        $ticket_model_extra = "";
        switch ((int)$ticket['model']) {
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
        $ticket_data['model_name'] = $model_name;
        
        $ticket_insured_text = "";
        if ($ticket['is_insured'] && !empty($ticket['tier'])) {
            $ticket_insured_text = '<br>' . _("Tier");
            $ticket_insured_text .= ': ' . $ticket['tier'];
        }
        $ticket_data['tier'] = $ticket_insured_text;

        $cost_manager = Lotto_View::format_currency(
            $ticket['cost_manager'],
            $ticket['manager_currency_code'],
            true
        );
        $ticket_data['cost_manager'] = $cost_manager;

        $costs_other_t = [];
        if ((string)$ticket['manager_currency_code'] !== (string)$ticket['user_currency_code']) {
            $cost_user = Lotto_View::format_currency(
                $ticket['cost'],
                $ticket['user_currency_code'],
                true
            );
            $costs_other_t[] = _("User currency") .
                ": " . $cost_user;
        }
        if ((string)$ticket['manager_currency_code'] !== (string)$ticket['lottery_currency_code']) {
            $cost_lottery = Lotto_View::format_currency(
                $ticket['cost_local'],
                $ticket['lottery_currency_code'],
                true
            );
            $costs_other_t[] = _("Lottery currency") .
                ": " . $cost_lottery;
        }
        $costs_other = implode("<br>", $costs_other_t);
        $ticket_data['costs_other'] = $costs_other;

        $income_manager = Lotto_View::format_currency(
            $ticket['income_manager'],
            $ticket['manager_currency_code'],
            true
        );
        $ticket_data['income_manager'] = $income_manager;

        $incomes_other_t = [];
        if ((string)$ticket['manager_currency_code'] !== (string)$ticket['user_currency_code']) {
            $income_user = Lotto_View::format_currency(
                $ticket['income'],
                $ticket['user_currency_code'],
                true
            );
            $incomes_other_t[] = _("User currency") .
                ": " . $income_user;
        }
        if ((string)$ticket['manager_currency_code'] !== (string)$ticket['lottery_currency_code']) {
            $income_lottery = Lotto_View::format_currency(
                $ticket['income_local'],
                $ticket['lottery_currency_code'],
                true
            );
            $incomes_other_t[] = _("Lottery currency") .
                ": " . $income_lottery;
        }
        $incomes_other = implode("<br>", $incomes_other_t);
        $ticket_data['incomes_other'] = $incomes_other;

        $margin_manager = Lotto_View::format_currency(
            $ticket['margin_manager'],
            $ticket['manager_currency_code'],
            true
        );
        $ticket_data['margin_manager'] = $margin_manager;

        $margins_other_t = [];
        if ((string)$ticket['manager_currency_code'] !== (string)$ticket['user_currency_code']) {
            $margin_user = Lotto_View::format_currency(
                $ticket['margin'],
                $ticket['user_currency_code'],
                true
            );
            $margins_other_t[] = _("User currency") .
                ": " . $margin_user;
        }
        if ((string)$ticket['manager_currency_code'] !== (string)$ticket['lottery_currency_code']) {
            $margin_lottery = Lotto_View::format_currency(
                $ticket['margin_local'],
                $ticket['lottery_currency_code'],
                true
            );
            $margins_other_t[] = _("Lottery currency") .
                ": " . $margin_lottery;
        }
        $margins_other = implode("<br>", $margins_other_t);
        $ticket_data['margins_other'] = $margins_other;
        
        if ($ticket['bonus_cost_manager'] != '0.00') {
            $bonus_cost_manager = Lotto_View::format_currency(
                $ticket['bonus_cost_manager'],
                $ticket['manager_currency_code'],
                true
            );
            $ticket_data['bonus_cost_manager'] = $bonus_cost_manager;

            $bonus_cost_other_t = [];
            if ((string)$ticket['manager_currency_code'] !== (string)$ticket['user_currency_code']) {
                $bonus_cost_user = Lotto_View::format_currency(
                    $ticket['bonus_cost'],
                    $ticket['user_currency_code'],
                    true
                );
                $bonus_cost_other_t[] = _("User currency") .
                    ": " . $bonus_cost_user;
            }
            if ((string)$ticket['manager_currency_code'] !== (string)$ticket['lottery_currency_code']) {
                $bonus_cost_lottery = Lotto_View::format_currency(
                    $ticket['bonus_cost_local'],
                    $ticket['lottery_currency_code'],
                    true
                );
                $bonus_cost_other_t[] = _("Lottery currency") .
                    ": " . $bonus_cost_lottery;
            }
            
            $bonus_cost_other = implode("<br>", $bonus_cost_other_t);
            $ticket_data['bonus_cost_other'] = $bonus_cost_other;
        }
            
        switch ((int)$ticket['status']) {
            case Helpers_General::TICKET_STATUS_PENDING:
                $ticket_data['status_class'] = '';
                $ticket_data['status_text'] = _("Pending");
                break;
            case Helpers_General::TICKET_STATUS_WIN:
                $ticket_data['status_class'] = 'text-success';
                $ticket_data['status_text'] = _("Win");
                break;
            case Helpers_General::TICKET_STATUS_NO_WINNINGS:
                $ticket_data['status_class'] = 'text-danger';
                $ticket_data['status_text'] = _("No winnings");
                break;
            case Helpers_General::TICKET_STATUS_QUICK_PICK:
                $ticket_data['status_class'] = '';
                $ticket_data['status_text'] = _("Quick Pick");
                $lottery_closed = Lotto_Helper::is_lottery_closed(
                    $lottery,
                    $ticket['valid_to_draw'],
                    $whitelabel
                );
                if ($lottery_closed) {
                    $ticket_data['status_extra_class'] = 'text-warning';
                    $ticket_data['status_extra_text'] = ", " . _("Expired");
                }
                break;
            case Helpers_General::TICKET_STATUS_CANCELED:
                $ticket_data['status_class'] = 'text-danger';
                $ticket_data['status_text'] = _("Cancelled");
                break;
        }

        switch ((int)$ticket['payout']) {
            case Helpers_General::TICKET_PAYOUT_PENDING:
                $ticket_data['payout_class'] = '';
                $ticket_data['payout_text'] = _("Pending");
                break;
            case Helpers_General::TICKET_PAYOUT_PAIDOUT:
                $ticket_data['payout_class'] = 'text-success';
                $ticket_data['payout_text'] = _("Paid out");
                break;
        }
        
        $ticket_data['status_win'] = false;
        if ((int)$ticket['status'] === Helpers_General::TICKET_STATUS_WIN) {
            $ticket_data['status_win'] = true;
            
            $jackpot_text = "";
            if ($ticket['prize_jackpot']) {
                $jackpot_text = _("Jackpot");
                if ($ticket['prize_net'] > 0) {
                    $jackpot_text .= " + ";
                }
            }
            $ticket_data['jackpot_prize_text'] = $jackpot_text;
            
            $prize_manager = "";
            $prizes_other = "";
            if ($ticket['prize_net'] > 0) {
                $prize_manager = Lotto_View::format_currency(
                    $ticket['prize_manager'],
                    $ticket['manager_currency_code'],
                    true
                );
                
                $prizes_other_t = [];
                if ($ticket['manager_currency_code'] !== $ticket['user_currency_code']) {
                    $prize_lottery = Lotto_View::format_currency(
                        $ticket['prize'],
                        $ticket['user_currency_code'],
                        true
                    );
                    $prizes_other_t[] = _("User currency") .
                        ": " . $prize_lottery;
                }
                if ($ticket['manager_currency_code'] !== $ticket['lottery_currency_code']) {
                    $prize_local_lottery = Lotto_View::format_currency(
                        $ticket['prize_local'],
                        $ticket['lottery_currency_code'],
                        true
                    );
                    $prizes_other_t[] = _("Lottery currency") .
                        ": " . $prize_local_lottery;
                }
                $prizes_other = implode("<br>", $prizes_other_t);
                
                if ($ticket['prize_local'] != $ticket['prize_net_local']) {
                    $prize_net_manager = Lotto_View::format_currency(
                        $ticket['prize_net_manager'],
                        $ticket['manager_currency_code'],
                        true
                    );
                    $ticket_data['prize_net_manager'] = Security::htmlentities($prize_net_manager);
                    
                    if ($ticket['manager_currency_code'] !== $ticket['lottery_currency_code']) {
                        $prize_net_lottery_temp = Lotto_View::format_currency(
                            $ticket['prize_net_local'],
                            $ticket['lottery_currency_code'],
                            true
                        );
                        $prize_net_local = _("Lottery currency") .
                            ": " . $prize_net_lottery_temp;
                        $ticket_data['prize_net_local'] = Security::htmlentities($prize_net_local);
                    }
                }
            }
            $ticket_data['prize_manager'] = Security::htmlentities($prize_manager);
            $ticket_data['prizes_other'] = Security::htmlentities($prizes_other);
            
            $prize_quickpic_text = "";
            if ($ticket['prize_quickpick'] > 0) {
                if ($ticket['prize_net'] > 0 ||
                    $ticket['prize_jackpot'] > 0
                ) {
                    $prize_quickpic_text .=  " + ";
                }

                $prize_quickpic_text .= $ticket['prize_quickpick'];
                $prize_quickpic_text .= "&times;";
                $prize_quickpic_text .= _("Quick Pick");
            }
            $ticket_data['prize_quickpick'] = Security::htmlentities($prize_quickpic_text);
        }
        if (!empty($keno_data)){
            $ticket_data['numbers_per_line'] = $keno_data['numbers_per_line'];
            if (!is_null($keno_data['lottery_type_multiplier_id'])) {
                $multiplier = Model_Lottery_Type_Multiplier::find_one_by('id', $keno_data['lottery_type_multiplier_id']);
                $ticket_data['ticket_multiplier'] = $multiplier['multiplier'];
            }
        }
        
        return $ticket_data;
    }
    
    /**
     *
     * @param array $ticket
     * @param array $lines
     * @param array $line_type
     * @return array
     */
    private function prepare_line_data(
        array $ticket,
        array $lines,
        array $line_type
    ): array {
        $payout = true;
        $lines_data = [];
        
        $draw_data = null;
        if ((int)$ticket['status'] !== Helpers_General::TICKET_STATUS_PENDING &&
            (int)$ticket['status'] !== Helpers_General::TICKET_STATUS_QUICK_PICK
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

            if (!isset($draw_data)) {
                $line_data['draw_line'] =  Lotto_View::format_line(
                    $line['numbers'],
                    $line['bnumbers'],
                    null,
                    null,
                    null,
                    $slip_additional_data
                );
            } else {
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

                $line_data['draw_line'] = Lotto_View::format_line(
                    $line['numbers'],
                    $line['bnumbers'],
                    $draw_data['numbers'],
                    $draw_data['bnumbers'],
                    $line_type['bextra'],
                    $slip_additional_data,
                    $slip_additional_data_numbers
                );

                if ($line['is_jackpot']) {
                    $line_data['prize_manager'] = Security::htmlentities(_("Jackpot"));
                } elseif ((int)$line['type'] === Helpers_General::LOTTERY_TYPE_DATA_QUICK_PICK) {
                    $line_data['prize_manager'] = Security::htmlentities(_("Quick Pick"));
                } else {
                    $line_prize_manager = Lotto_View::format_currency(
                        $line['prize_manager'],
                        $ticket['manager_currency_code'],
                        true
                    );
                    $line_data['prize_manager'] = Security::htmlentities($line_prize_manager);

                    if (!empty($line['prize_local'])) {
                        $line_prize_lottery = Lotto_View::format_currency(
                            $line['prize_local'],
                            $ticket['lottery_currency_code'],
                            true
                        );
                        $line_prize_other_text = _("Lottery prize currency") .
                            ": " . $line_prize_lottery;

                        if (!empty($line['prize_net_local']) &&
                                $line['prize_local'] != $line['prize_net_local']
                        ) {
                            $line_prize_net_local = Lotto_View::format_currency(
                                $line['prize_net_local'],
                                $ticket['lottery_currency_code'],
                                true
                            );
                            $line_prize_other_text .= "<br>" . _("Lottery prize net currency") .
                                ": " . $line_prize_net_local;
                        }

                        if (!empty($line['prize_net']) &&
                                (string)$ticket['lottery_currency_code'] !== (string)$ticket['user_currency_code']
                        ) {
                            $line_prize_net = Lotto_View::format_currency(
                                $line['prize_net'],
                                $ticket['user_currency_code'],
                                true
                            );
                            $line_prize_other_text .= "<br>" . _("User prize net currency") .
                                ": " . $line_prize_net;
                        }
                        $line_data['other_prizes'] = Security::htmlentities($line_prize_other_text);
                    }
                }

                $line_match = _("none");
                if (isset($line['lottery_type_data_id'])) {
                    $line_match = $line['match_n'];
                    if ($line_type['brange'] > 0) {
                        $line_match .= ' + ' . $line['match_b'];
                    } elseif ($line_type['bextra'] > 0 && $line['match_b'] > 0) {
                        $line_match .= ' + ' . $line_type['bextra'];
                    }

                    $isKeno = in_array((int)$ticket['lottery_id'], Helpers_Lottery::KENO_LOTTERIES_IDS);
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
                    //Security::htmlentities($line_match);

                    $line_data['lottery_type_data_id'] = $line['lottery_type_data_id'];
                }
                $line_data['match'] = Security::htmlentities($line_match);

                $line_payout = "";
                switch ((int)$line['payout']) {
                    case Helpers_General::TICKET_PAYOUT_PENDING:
                        $line_payout = _("Pending");
                        if ((int)$line['status'] === Helpers_General::TICKET_STATUS_WIN &&
                            (int)$line['type'] !== Helpers_General::LOTTERY_TYPE_DATA_QUICK_PICK
                        ) {
                            $payout = false;
                            if ($line['is_jackpot'] == 0) {
                                $confirm_button_text = "&nbsp;";
                                $confirm_button_text .= _("Pay out to user balance");
                                $line_data['confirm_text'] = Security::htmlentities($confirm_button_text);

                                $button_confirm_url = "/tickets/payout/";
                                $button_confirm_url .= $ticket['token'];
                                $button_confirm_url .= "/line/";
                                $button_confirm_url .= $lkey;
                                $button_confirm_url .= Lotto_View::query_vars();
                                $line_data['confirm_url'] = $button_confirm_url;
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
     * @param string $view_template
     * @return int
     */
    public function process_form(string $view_template): int
    {
        $whitelabel = $this->get_whitelabel();
        $token = $this->get_token();
        
        if (empty($whitelabel) || empty($token)) {
            return self::RESULT_INCORRECT_TICKET;
        }
        
        $tickets = Model_Whitelabel_User_Ticket::get_single_with_currencies(
            $whitelabel,
            $token
        );
        
        if ($tickets === null && count($tickets) === 0) {
            return self::RESULT_INCORRECT_TICKET;
        }

        $ticket = $tickets[0];

        $slips = Model_Whitelabel_User_Ticket_Slip::find([
            "where" => [
                "whitelabel_user_ticket_id" => $ticket['id']
            ],
            "order_by" => [
                "id" => "asc"
            ]
        ]);

        Config::load("platform", true);

        $images_dir = Config::get("platform.images.dir");
        $images = [];

        /** @var WhitelabelLotteryRepository $whitelabelLotteryRepository */
        $whitelabelLotteryRepository = Container::get(WhitelabelLotteryRepository::class);
        $whitelabelLottery = $whitelabelLotteryRepository->getOneByLotteryIdForWhitelabel(
            $ticket['lottery_id'],
            $ticket['whitelabel_id']
        );
        if ($slips !== null && $whitelabelLottery->isScanInCrmEnabled) {
            foreach ($slips as $slip) {
                if (!empty($slip->ticket_scan_url)) {
                    $images[] = '/slip/' . $slip->id;
                }
            }

            $images = $this->getGgWorldScanImages($images, $ticket['id']);
        }

        $user = Model_Whitelabel_User::find_by_pk($ticket['whitelabel_user_id']);
        $user = $user === null ? [] : $user->to_array();
        
        $lotteries = Model_Lottery::get_really_all_lotteries_for_whitelabel($whitelabel);
        
        if (!isset($lotteries["__by_id"][$ticket['lottery_id']])) {
            $error_message = "There is a problem with lottery settings. " .
                "No lottery within lotteries list. Lottery ID: " .
                $ticket['lottery_id'] . " " .
                "Ticket ID: " .
                $ticket['id'] . " " .
                "Whitelabel ID: " . $whitelabel['id'];

            $this->fileLoggerService->error(
                $error_message
            );
            
            return self::RESULT_NULL_DATA;
        }
        
        $lottery = $lotteries["__by_id"][$ticket['lottery_id']];
        
        $lottery_types = Model_Lottery_Type::get_lottery_type_for_date($lottery, $ticket['draw_date']);
        
        $transaction = Model_Whitelabel_Transaction::find_by_pk($ticket['whitelabel_transaction_id']);
        $transaction = $transaction === null ? [] : $transaction->to_array();
        
        $ticket_lines = Model_Whitelabel_User_Ticket_Line::get_with_slip_by_ticket_id($ticket['id']);

        $keno_data = Model_Whitelabel_User_Ticket_Keno_Data::find_one_by('whitelabel_user_ticket_id', $ticket['id']);
        $keno_data = $keno_data === null ? [] : $keno_data->to_array();

        $ticket_data = $this->prepare_data(
            $ticket,
            $lottery,
            $transaction,
            $user,
            $keno_data
        );
        
        list(
            $lines_data,
            $ticket_data['is_payout']
        ) = $this->prepare_line_data($ticket, $ticket_lines, $lottery_types);

        if (!$ticket_data['is_payout']) {
            $payout_button_url = "/tickets/paidout/";
            $payout_button_url .= $ticket['token'];
            $payout_button_url .= Lotto_View::query_vars();
            
            $ticket_data['payout_button_url'] = $payout_button_url;
        }
        
        $this->inside = View::forge($view_template);
        
        $this->inside->set("images", $images);
        $this->inside->set("ticket", $ticket);
        $this->inside->set("ticket_data", $ticket_data);

        $this->inside->set("lottery", $lottery);
        $this->inside->set("lines_data", $lines_data);
                
        return self::RESULT_OK;
    }
}
