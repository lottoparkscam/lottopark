<?php

/**
 * @deprecated
 *
 * THIS CLASS IS ONLY TEMPORARY FOR TIME WHEN CURENCIES FEATURE
 * WILL BE APPLIED ON LIVE SERVER OR ON LOCALHOST TO TEST:)
 */
class Controller_Temp extends Controller
{
    private $addresses_permitted = [];

    public function before()
    {
        if (!Lotto_Helper::allow_access("empire")) {
            $error = Request::forge('index/404')->execute();
            echo $error;
            exit();
        }

        // access only for icetique locally and remotelly
        if (!in_array(Lotto_Security::get_IP(), $this->addresses_permitted)) {
            exit("You are not allowed to access this script.");
        }
    }

    /**
     * Insert single row for each whitelabel to whitelabel_default_currency
     * to make possible working on currencies feature
     * It is needed to run only once!
     *
     * @param bool $show_notice
     * @param bool $should_process
     * @return int
     */
    public static function default_currency_update(
        bool $show_notice = true,
        bool $should_process = true
    ): int {
        if (!$should_process) {
            return 0;
        }

        $default_currency_tab = Helpers_Currency::get_mtab_currency();
        $default_currency_id = $default_currency_tab['id'];

        $whitelabels = Model_Whitelabel::find_all();

        if (is_null($whitelabels) || empty($whitelabels)) {
            echo 'There is no whitelabel!<br>';
            return -1;
        }

        $inserted = 0;
        $found = 0;
        foreach ($whitelabels as $key => $whitelabel) {
            $res = Model_Whitelabel_Default_Currency::find_by([
                'whitelabel_id' => $whitelabel['id'],
                'is_default_for_site' => 1
            ], null, null, 1);

            if ($res === null) {
                if (!empty($whitelabel['default_site_currency'])) {
                    $default_currency_id = intval($whitelabel['default_site_currency']);
                }

                $set = [
                    'whitelabel_id' => intval($whitelabel['id']),
                    'currency_id' => $default_currency_id,
                    'is_default_for_site' => 1,
                    'default_deposit_first_box' => 20.00,
                    'default_deposit_second_box' => 50.00,
                    'default_deposit_third_box' => 100.00,
                    'min_purchase_amount' => 1.00,
                    'min_deposit_amount' => 2.00,
                    'min_withdrawal' => 10.00,
                    'max_order_amount' => 1000.00
                ];

                $wl_default_currency = Model_Whitelabel_Default_Currency::forge();

                $wl_default_currency->set($set);
                $wl_default_currency->save();

                if ($show_notice) {
                    echo "Default currency is set successfully for whitelabel ID: " .
                    $whitelabel['id'] . " name: " . $whitelabel['name'] . "<br>";
                }

                $inserted++;
            } else {
                $found++;
            }
        }

        if ($show_notice) {
            echo 'WHITELABELS counted: ' . count($whitelabels) . '<br>';
            echo 'WHITELABELS already with default currency: ' . $found . '<br>';
            echo 'INSERTED DEFAULTS: ' . $inserted . '<br>';
        }

        return $inserted;
    }

    /**
     * Function needed to run to update transaction fields payment_currency_id
     * and amount_payment after update of the system by currencies feature
     * It is made by copy value from currency_id to payment_currency_id
     * and amount to amount_payment.
     * NOTE! This is only for those rows with amount_payment set to null!
     *
     * @param bool $show_notice
     * @param bool $should_process
     * @param bool $process_all
     * @return int
     */
    public static function update_transactions_amount_payment_currencies(
        bool $show_notice = true,
        bool $should_process = true,
        bool $process_all = false
    ): int {
        if (!$should_process) {
            return 0;
        }

        $updated = 0;

        if ($process_all) {
            $whitelabel_transactions_to_update = Model_Whitelabel_Transaction::find_all();
            if (!empty($whitelabel_transactions_to_update)) {
                foreach ($whitelabel_transactions_to_update as $key => $single_transaction) {
                    if (
                        !empty($single_transaction['amount']) &&
                        !empty($single_transaction['currency_id'])
                    ) {
                        $single_transaction->set([
                            'payment_currency_id' => intval($single_transaction['currency_id']),
                            'amount_payment' => floatval($single_transaction['amount'])
                        ]);
                        $single_transaction->save();
                        $updated++;
                    }
                }
            }
        } else {
            $whitelabel_transactions_to_update = Model_Whitelabel_Transaction::find_by([
                'amount_payment' => null
            ]);

            if (!empty($whitelabel_transactions_to_update)) {
                foreach ($whitelabel_transactions_to_update as $key => $single_transaction) {
                    if (
                        !empty($single_transaction['amount']) &&
                        !empty($single_transaction['currency_id'])
                    ) {
                        $single_transaction->set([
                            'payment_currency_id' => intval($single_transaction['currency_id']),
                            'amount_payment' => floatval($single_transaction['amount'])
                        ]);
                        $single_transaction->save();
                        $updated++;
                    }
                }
            }

            $whitelabel_transactions_to_update = Model_Whitelabel_Transaction::find_by([
                'amount_payment' => 0.00
            ]);

            if (!empty($whitelabel_transactions_to_update)) {
                foreach ($whitelabel_transactions_to_update as $key => $single_transaction) {
                    if (
                        !empty($single_transaction['amount']) &&
                        !empty($single_transaction['currency_id'])
                    ) {
                        $single_transaction->set([
                            'payment_currency_id' => intval($single_transaction['currency_id']),
                            'amount_payment' => floatval($single_transaction['amount'])
                        ]);
                        $single_transaction->save();
                        $updated++;
                    }
                }
            }
        }

        if ($show_notice) {
            echo 'WHITELABEL TRANSACTIONS really updated: ' . $updated . '<br>';
        }

        return $updated;
    }

    /**
     * This function is for update amount_manager, income_manager, cost_manager,
     * payment_cost_manager, margin_manager in whitelabel_transaction
     * table by use of the values in *_usd columns,
     * manager_site_currency_id from whitelabel
     * and current rate from currency table for each whitelabel
     *
     * @param bool $show_notice
     * @param bool $should_process
     * @return int
     */
    public static function update_transactions_values_manager(
        bool $show_notice = true,
        bool $should_process = true
    ): int {
        if (!$should_process) {
            return 0;
        }

        $whitelabels = Model_Whitelabel::find_all();

        $system_currency_tab = Helpers_Currency::get_mtab_currency(true, "USD");

        $updated = 0;
        $single_updated = 0;

        foreach ($whitelabels as $whitelabel) {
            $manager_currency_tab = Helpers_Currency::get_mtab_currency(
                true,
                "",
                $whitelabel['manager_site_currency_id']
            );

            $transactions = Model_Whitelabel_Transaction::find_by([
                'whitelabel_id' => intval($whitelabel['id'])
            ]);

            if (!empty($transactions)) {
                foreach ($transactions as $transaction_single) {
                    $manager_value = 0.00;
                    $income_value = 0.00;
                    $cost_value = 0.00;
                    $payment_cost_value = 0.00;
                    $margin_value = 0.00;

                    if ((int)$manager_currency_tab['id'] === (int)$transaction_single['currency_id']) {
                        $manager_value = $transaction_single['amount'];
                        $income_value = $transaction_single['income'];
                        $cost_value = $transaction_single['cost'];
                        $payment_cost_value = $transaction_single['payment_cost'];
                        $margin_value = $transaction_single['margin'];
                    } elseif ((int)$manager_currency_tab['id'] === (int)$system_currency_tab['id']) {
                        $manager_value = $transaction_single['amount_usd'];
                        $income_value = $transaction_single['income_usd'];
                        $cost_value = $transaction_single['cost_usd'];
                        $payment_cost_value = $transaction_single['payment_cost_usd'];
                        $margin_value = $transaction_single['margin_usd'];
                    } else {
                        $manager_value = Helpers_Currency::get_single_converted_from_currency(
                            $system_currency_tab,
                            $transaction_single['amount_usd'],
                            $manager_currency_tab
                        );
                        $income_value = Helpers_Currency::get_single_converted_from_currency(
                            $system_currency_tab,
                            $transaction_single['income_usd'],
                            $manager_currency_tab
                        );
                        $cost_value = Helpers_Currency::get_single_converted_from_currency(
                            $system_currency_tab,
                            $transaction_single['cost_usd'],
                            $manager_currency_tab
                        );
                        $payment_cost_value = Helpers_Currency::get_single_converted_from_currency(
                            $system_currency_tab,
                            $transaction_single['payment_cost_usd'],
                            $manager_currency_tab
                        );
                        $margin_value = Helpers_Currency::get_single_converted_from_currency(
                            $system_currency_tab,
                            $transaction_single['margin_usd'],
                            $manager_currency_tab
                        );
                    }

                    $set = [
                        'amount_manager' => floatval($manager_value),
                        'income_manager' => floatval($income_value),
                        'cost_manager' => floatval($cost_value),
                        'payment_cost_manager' => floatval($payment_cost_value),
                        'margin_manager' => floatval($margin_value),
                    ];

                    $transaction_single->set($set);
                    $transaction_single->save();
                    $single_updated++;
                }
            }

            if ($show_notice) {
                echo "Whitelabel ID: " . $whitelabel['id'] . ' Manager currencyID: ' .
                $whitelabel['manager_site_currency_id'] . "<br>";
                echo $manager_currency_tab['rate'] . '<br>';
                echo "Updated: " . $single_updated . "<br>";
            }

            $updated += $single_updated;
            $single_updated = 0;
        }

        return $updated;
    }

    /**
     * This function is for update amount_manager in withdrawals_request
     * table by use of the amount_usd, manager_site_currency_id from whitelabel
     * and current rate from currency table for each whitelabel
     *
     * @param bool $show_notice
     * @param bool $should_process
     * @return int
     */
    public static function update_withdrawals_request_amount_manager(
        bool $show_notice = true,
        bool $should_process = true
    ): int {
        if (!$should_process) {
            return 0;
        }

        $whitelabels = Model_Whitelabel::find_all();

        $system_currency_tab = Helpers_Currency::get_mtab_currency(true, "USD");

        $updated = 0;
        $single_updated = 0;

        foreach ($whitelabels as $whitelabel) {
            $manager_currency_tab = Helpers_Currency::get_mtab_currency(
                true,
                "",
                $whitelabel['manager_site_currency_id']
            );

            $withdrawal_requests = Model_Withdrawal_Request::find_by([
                'whitelabel_id' => intval($whitelabel['id'])
            ]);

            if (!empty($withdrawal_requests)) {
                foreach ($withdrawal_requests as $withdrawal_request) {
                    $manager_value = 0.00;
                    if ((int)$manager_currency_tab['id'] === (int)$withdrawal_request['currency_id']) {
                        $manager_value = $withdrawal_request['amount'];
                    } elseif ((int)$manager_currency_tab['id'] === (int)$system_currency_tab['id']) {
                        $manager_value = $withdrawal_request['amount_usd'];
                    } else {
                        $manager_value = Helpers_Currency::get_single_converted_from_currency(
                            $system_currency_tab,
                            $withdrawal_request['amount_usd'],
                            $manager_currency_tab
                        );
                    }

                    $set = [
                        'amount_manager' => floatval($manager_value)
                    ];

                    $withdrawal_request->set($set);
                    $withdrawal_request->save();
                    $single_updated++;
                }
            }

            if ($show_notice) {
                echo "Whitelabel ID: " . $whitelabel['id'] . ' Manager currencyID: ' .
                $whitelabel['manager_site_currency_id'] . "<br>";
                echo $manager_currency_tab['rate'] . '<br>';
                echo "Updated: " . $single_updated . '<br>';
            }

            $updated += $single_updated;
            $single_updated = 0;
        }

        return $updated;
    }

    /**
     * Within this function system will update couple columns for manager
     * within whitelabel_user_tickets table on DB.
     * Those are: amount_manager, prize_manager, prize_net_manager,
     * cost_manager, income_manager, margin_manager
     *
     * @param bool $show_notice
     * @param bool $should_process
     * @return int
     */
    public static function update_tickets_prizes_manager(
        bool $show_notice = true,
        bool $should_process = true
    ): int {
        if (!$should_process) {
            return 0;
        }

        $whitelabels = Model_Whitelabel::find_all();

        if (is_null($whitelabels) || empty($whitelabels)) {
            echo 'There is no whitelabel!<br>';
            return -1;
        }

        $system_currency_tab = Helpers_Currency::get_mtab_currency(true, "USD");

        $updated = 0;
        $single_updated = 0;

        foreach ($whitelabels as $whitelabel) {
            $manager_currency_tab = Helpers_Currency::get_mtab_currency(
                true,
                "",
                $whitelabel['manager_site_currency_id']
            );

            $tickets = Model_Whitelabel_User_Ticket::find_by([
                'whitelabel_id' => intval($whitelabel['id'])
            ]);

            if (!empty($tickets)) {
                foreach ($tickets as $ticket) {
                    $amount_manager = 0.00;
                    $prize_manager = 0.00;
                    $prize_net_manager = 0.00;
                    $cost_manager = 0.00;
                    $income_manager = 0.00;
                    $margin_manager = 0.00;

                    if ((int)$manager_currency_tab['id'] === (int)$ticket['currency_id']) {
                        if (!empty($ticket['amount'])) {
                            $amount_manager = $ticket['amount'];
                        }
                        if (!empty($ticket['prize'])) {
                            $prize_manager = $ticket['prize'];
                        }
                        if (!empty($ticket['prize_net'])) {
                            $prize_net_manager = $ticket['prize_net'];
                        }
                        if (!empty($ticket['cost'])) {
                            $cost_manager = $ticket['cost'];
                        }
                        if (!empty($ticket['income'])) {
                            $income_manager = $ticket['income'];
                        }
                        if (!empty($ticket['margin'])) {
                            $margin_manager = $ticket['margin'];
                        }
                    } elseif ((int)$manager_currency_tab['id'] === (int)$system_currency_tab['id']) {
                        if (!empty($ticket['amount_usd'])) {
                            $amount_manager = $ticket['amount_usd'];
                        }
                        if (!empty($ticket['prize_usd'])) {
                            $prize_manager = $ticket['prize_usd'];
                        }
                        if (!empty($ticket['prize_net_usd'])) {
                            $prize_net_manager = $ticket['prize_net_usd'];
                        }
                        if (!empty($ticket['cost_usd'])) {
                            $cost_manager = $ticket['cost_usd'];
                        }
                        if (!empty($ticket['income_usd'])) {
                            $income_manager = $ticket['income_usd'];
                        }
                        if (!empty($ticket['margin_usd'])) {
                            $margin_manager = $ticket['margin_usd'];
                        }
                    } else {
                        if (!empty($ticket['amount_usd'])) {
                            $amount_manager = Helpers_Currency::get_single_converted_from_currency(
                                $system_currency_tab,
                                $ticket['amount_usd'],
                                $manager_currency_tab
                            );
                        }
                        if (!empty($ticket['prize_usd'])) {
                            $prize_manager = Helpers_Currency::get_single_converted_from_currency(
                                $system_currency_tab,
                                $ticket['prize_usd'],
                                $manager_currency_tab
                            );
                        }
                        if (!empty($ticket['prize_net_usd'])) {
                            $prize_net_manager = Helpers_Currency::get_single_converted_from_currency(
                                $system_currency_tab,
                                $ticket['prize_net_usd'],
                                $manager_currency_tab
                            );
                        }
                        if (!empty($ticket['cost_usd'])) {
                            $cost_manager = Helpers_Currency::get_single_converted_from_currency(
                                $system_currency_tab,
                                $ticket['cost_usd'],
                                $manager_currency_tab
                            );
                        }
                        if (!empty($ticket['income_usd'])) {
                            $income_manager = Helpers_Currency::get_single_converted_from_currency(
                                $system_currency_tab,
                                $ticket['income_usd'],
                                $manager_currency_tab
                            );
                        }
                        if (!empty($ticket['margin_usd'])) {
                            $margin_manager = Helpers_Currency::get_single_converted_from_currency(
                                $system_currency_tab,
                                $ticket['margin_usd'],
                                $manager_currency_tab
                            );
                        }
                    }

                    $set = [
                        'amount_manager' => floatval($amount_manager),
                        'prize_manager' => floatval($prize_manager),
                        'prize_net_manager' => floatval($prize_net_manager),
                        'cost_manager' => floatval($cost_manager),
                        'income_manager' => floatval($income_manager),
                        'margin_manager' => floatval($margin_manager),
                    ];

                    $ticket->set($set);
                    $ticket->save();
                    $single_updated++;
                }
            }

            if ($show_notice) {
                echo "Whitelabel ID: " . $whitelabel['id'] . ' Manager currencyID: ' .
                $whitelabel['manager_site_currency_id'] . "<br>";
                echo $manager_currency_tab['rate'] . '<br>';
                echo "Updated: " . $single_updated . '<br>';
            }

            $updated += $single_updated;
            $single_updated = 0;
        }

        return $updated;
    }

    /**
     * Within this function system will update couple columns for manager
     * within whitelabel_user_tickets table on DB.
     * Those are: amount_manager, prize_manager, prize_net_manager,
     * cost_manager, income_manager, margin_manager
     *
     * @param bool $show_notice
     * @param bool $should_process
     * @return int
     */
    public static function update_tickets_amount_payment(
        bool $show_notice = true,
        bool $should_process = true
    ): int {
        if (!$should_process) {
            return 0;
        }

        $whitelabels = Model_Whitelabel::find_all();

        if (is_null($whitelabels) || empty($whitelabels)) {
            echo 'There is no whitelabel!<br>';
            return -1;
        }

        $system_currency_tab = Helpers_Currency::get_mtab_currency(true, "USD");

        $updated = 0;
        $single_updated = 0;

        foreach ($whitelabels as $whitelabel) {
            $manager_currency_tab = Helpers_Currency::get_mtab_currency(
                true,
                "",
                $whitelabel['manager_site_currency_id']
            );

            $tickets = Model_Whitelabel_User_Ticket::find_by([
                'whitelabel_id' => intval($whitelabel['id']),
                'amount_payment' => 0.00
            ]);

            if (!empty($tickets)) {
                foreach ($tickets as $ticket) {
                    $amount_payment = 0.00;

                    if ((int)$ticket['currency_id'] === (int)$manager_currency_tab['id']) {
                        if (!empty($ticket['amount'])) {
                            $amount_payment = $ticket['amount'];
                        }
                    } elseif ((int)$ticket['currency_id'] === (int)$system_currency_tab['id']) {
                        if (!empty($ticket['amount_usd'])) {
                            $amount_payment = $ticket['amount_usd'];
                        }
                    } else {
                        if (!empty($ticket['amount_usd'])) {
                            $ticket_currency_tab = Helpers_Currency::get_mtab_currency(
                                true,
                                "",
                                $ticket['currency_id']
                            );

                            $amount_payment = Helpers_Currency::get_single_converted_from_currency(
                                $system_currency_tab,
                                $ticket['amount_usd'],
                                $ticket_currency_tab
                            );
                        }
                    }

                    $set = [
                        'amount_payment' => floatval($amount_payment),
                    ];

                    $ticket->set($set);
                    $ticket->save();
                    $single_updated++;
                }
            }

            if ($show_notice) {
                echo "Whitelabel ID: " . $whitelabel['id'] . ' Manager currencyID: ' .
                $whitelabel['manager_site_currency_id'] . "<br>";
                echo $manager_currency_tab['rate'] . '<br>';
                echo "Updated: " . $single_updated . '<br>';
            }

            $updated += $single_updated;
            $single_updated = 0;
        }

        return $updated;
    }

    /**
     * This function is for update all whitelabel_user_ticket_line rows in
     * different columns for manager prizes.
     * Those columns are: amount_manager, prize_manager,
     * prize_net_manager, uncovered_prize_manager
     *
     * @param bool $show_notice
     * @param bool $should_process
     * @return int
     */
    public static function update_ticket_lines_prizes_manager(
        bool $show_notice = true,
        bool $should_process = true
    ): int {
        if (!$should_process) {
            return 0;
        }

        $whitelabels = Model_Whitelabel::find_all();

        if (is_null($whitelabels) || empty($whitelabels)) {
            echo 'There is no whitelabel!<br>';
            return -1;
        }

        $system_currency_tab = Helpers_Currency::get_mtab_currency(true, "USD");

        $updated = 0;
        $single_updated = 0;

        foreach ($whitelabels as $whitelabel) {
            $manager_currency_tab = Helpers_Currency::get_mtab_currency(
                true,
                "",
                $whitelabel['manager_site_currency_id']
            );

            $ticket_lines = Model_Whitelabel_User_Ticket_Line::get_all_for_whitelabel_with_currencies($whitelabel);

            if (!empty($ticket_lines)) {
                foreach ($ticket_lines as $ticket_line) {
                    $amount_manager = 0.00;
                    $prize_manager = 0.00;
                    $prize_net_manager = 0.00;
                    $uncovered_prize_manager = 0.00;

                    if ((int)$manager_currency_tab['id'] === (int)$ticket_line['user_currency_id']) {
                        if (!empty($ticket_line['amount'])) {
                            $amount_manager = $ticket_line['amount'];
                        }
                        if (!empty($ticket_line['prize'])) {
                            $prize_manager = $ticket_line['prize'];
                        }
                        if (!empty($ticket_line['prize_net'])) {
                            $prize_net_manager = $ticket_line['prize_net'];
                        }
                        if (!empty($ticket_line['uncovered_prize'])) {
                            $uncovered_prize_manager = $ticket_line['uncovered_prize'];
                        }
                    } elseif ((int)$manager_currency_tab['id'] === (int)$ticket_line['lottery_currency_id']) {
                        if (!empty($ticket_line['amount_local'])) {
                            $amount_manager = $ticket_line['amount_local'];
                        }
                        if (!empty($ticket_line['prize_local'])) {
                            $prize_manager = $ticket_line['prize_local'];
                        }
                        if (!empty($ticket_line['prize_net_local'])) {
                            $prize_net_manager = $ticket_line['prize_net_local'];
                        }
                        if (!empty($ticket_line['uncovered_prize_local'])) {
                            $uncovered_prize_manager = $ticket_line['uncovered_prize_local'];
                        }
                    } elseif ((int)$manager_currency_tab['id'] === (int)$system_currency_tab['id']) {
                        if (!empty($ticket_line['amount_usd'])) {
                            $amount_manager = $ticket_line['amount_usd'];
                        }
                        if (!empty($ticket_line['prize_usd'])) {
                            $prize_manager = $ticket_line['prize_usd'];
                        }
                        if (!empty($ticket_line['prize_net_usd'])) {
                            $prize_net_manager = $ticket_line['prize_net_usd'];
                        }
                        if (!empty($ticket_line['uncovered_prize_usd'])) {
                            $uncovered_prize_manager = $ticket_line['uncovered_prize_usd'];
                        }
                    } else {
                        if (!empty($ticket_line['amount_usd'])) {
                            $amount_manager = Helpers_Currency::get_single_converted_from_currency(
                                $system_currency_tab,
                                $ticket_line['amount_usd'],
                                $manager_currency_tab
                            );
                        }
                        if (!empty($ticket_line['prize_usd'])) {
                            $prize_manager = Helpers_Currency::get_single_converted_from_currency(
                                $system_currency_tab,
                                $ticket_line['prize_usd'],
                                $manager_currency_tab
                            );
                        }
                        if (!empty($ticket_line['prize_net_usd'])) {
                            $prize_net_manager = Helpers_Currency::get_single_converted_from_currency(
                                $system_currency_tab,
                                $ticket_line['prize_net_usd'],
                                $manager_currency_tab
                            );
                        }
                        if (!empty($ticket_line['uncovered_prize_usd'])) {
                            $uncovered_prize_manager = Helpers_Currency::get_single_converted_from_currency(
                                $system_currency_tab,
                                $ticket_line['uncovered_prize_usd'],
                                $manager_currency_tab
                            );
                        }
                    }

                    $set = [
                        'amount_manager' => floatval($amount_manager),
                        'prize_manager' => floatval($prize_manager),
                        'prize_net_manager' => floatval($prize_net_manager),
                        'uncovered_prize_manager' => floatval($uncovered_prize_manager),
                    ];

                    $result = Model_Whitelabel_User_Ticket_Line::update_manager_values_by_line_id(
                        $set,
                        $ticket_line['id']
                    );

                    if (!is_null($result)) {
                        $single_updated++;
                    }
                }
            }

            if ($show_notice) {
                echo "Whitelabel ID: " . $whitelabel['id'] . ' Manager currencyID: ' .
                $whitelabel['manager_site_currency_id'] . "<br>";
                echo $manager_currency_tab['rate'] . '<br>';
                echo "Updated: " . $single_updated . '<br>';
            }

            $updated += $single_updated;
            $single_updated = 0;
        }

        return $updated;
    }

    /**
     * This function is for update all whitelabel_user_ticket_line rows in
     * different columns for manager prizes.
     * Those columns are: amount_manager, prize_manager,
     * prize_net_manager, uncovered_prize_manager
     *
     * @param bool $show_notice
     * @param bool $should_process
     * @return int
     */
    public static function update_ticket_lines_amount_payment(
        bool $show_notice = true,
        bool $should_process = true
    ): int {
        if (!$should_process) {
            return 0;
        }

        $whitelabels = Model_Whitelabel::find_all();

        if (is_null($whitelabels) || empty($whitelabels)) {
            echo 'There is no whitelabel!<br>';
            return -1;
        }

        $system_currency_tab = Helpers_Currency::get_mtab_currency(true, "USD");

        $updated = 0;
        $single_updated = 0;

        foreach ($whitelabels as $whitelabel) {
            $manager_currency_tab = Helpers_Currency::get_mtab_currency(
                true,
                "",
                $whitelabel['manager_site_currency_id']
            );

            $ticket_lines = Model_Whitelabel_User_Ticket_Line::get_all_for_whitelabel_with_currencies($whitelabel);

            if (!empty($ticket_lines)) {
                foreach ($ticket_lines as $ticket_line) {
                    if ((float)$ticket_line['amount_payment'] !== 0.00) {
                        continue;
                    }

                    $amount_payment = 0.00;

                    if ((int)$ticket_line['user_currency_id'] === (int)$manager_currency_tab['id']) {
                        if (!empty($ticket_line['amount'])) {
                            $amount_payment = $ticket_line['amount'];
                        }
                    } elseif ((int)$ticket_line['user_currency_id'] === (int)$ticket_line['lottery_currency_id']) {
                        if (!empty($ticket_line['amount_local'])) {
                            $amount_payment = $ticket_line['amount_local'];
                        }
                    } elseif ((int)$ticket_line['user_currency_id'] === (int)$system_currency_tab['id']) {
                        if (!empty($ticket_line['amount_usd'])) {
                            $amount_payment = $ticket_line['amount_usd'];
                        }
                    } else {
                        if (!empty($ticket_line['amount_usd'])) {
                            $payment_currency_tab = Helpers_Currency::get_mtab_currency(
                                true,
                                "",
                                $ticket_line['user_currency_id']
                            );

                            $amount_payment = Helpers_Currency::get_single_converted_from_currency(
                                $system_currency_tab,
                                $ticket_line['amount_usd'],
                                $payment_currency_tab
                            );
                        }
                    }

                    $set = [
                        'amount_payment' => floatval($amount_payment),
                    ];

                    $result = Model_Whitelabel_User_Ticket_Line::update_amount_payment_by_line_id(
                        $set,
                        $ticket_line['id']
                    );

                    if (!is_null($result)) {
                        $single_updated++;
                    }
                }
            }

            if ($show_notice) {
                echo "Whitelabel ID: " . $whitelabel['id'] . ' Manager currencyID: ' .
                $whitelabel['manager_site_currency_id'] . "<br>";
                echo $manager_currency_tab['rate'] . '<br>';
                echo "Updated: " . $single_updated . '<br>';
            }

            $updated += $single_updated;
            $single_updated = 0;
        }

        return $updated;
    }

    /**
     * This function is for update payouts, in fact amount_manager
     * within whitelabel_aff_payout.
     * It is needed to run only once, but it is save to run more
     *
     * @param bool $show_notice
     * @param bool $should_process
     * @return int
     */
    public static function whitelabel_aff_payout(
        bool $show_notice = true,
        bool $should_process = true
    ): int {
        if (!$should_process) {
            return 0;
        }

        $whitelabels = Model_Whitelabel::find_all();

        if (is_null($whitelabels) || empty($whitelabels)) {
            echo 'There is no whitelabel!<br>';
            return -1;
        }

        $system_currency_tab = Helpers_Currency::get_mtab_currency(true, "USD");

        $updated = 0;
        $single_updated = 0;

        foreach ($whitelabels as $whitelabel) {
            $manager_currency_tab = Helpers_Currency::get_mtab_currency(
                true,
                "",
                $whitelabel['manager_site_currency_id']
            );

            $payouts = Model_Whitelabel_Aff_Payout::find_by([
                "whitelabel_id" => $whitelabel['id']
            ]);

            if (empty($payouts)) {
                continue;
            }

            foreach ($payouts as $payout) {
                $amount_manager = 0.00;

                if ((int)$manager_currency_tab['id'] === (int)$payout['currency_id']) {
                    $amount_manager = $payout['amount'];
                } elseif ((int)$manager_currency_tab['id'] === (int)$system_currency_tab['id']) {
                    $amount_manager = $payout['amount_usd'];
                } else {
                    $amount_manager = Helpers_Currency::get_single_converted_from_currency(
                        $system_currency_tab,
                        $payout['amount_usd'],
                        $manager_currency_tab
                    );
                }

                $set = [
                    'amount_manager' => $amount_manager
                ];

                $payout->set($set);
                $payout->save();

                $single_updated++;
            }

            if ($show_notice) {
                echo "Whitelabel ID: " . $whitelabel['id'] . ' Manager currencyID: ' .
                $whitelabel['manager_site_currency_id'] . "<br>";
                echo $manager_currency_tab['rate'] . '<br>';
                echo "Updated: " . $single_updated . '<br>';
            }

            $updated += $single_updated;
            $single_updated = 0;
        }

        return $updated;
    }

    /**
     * Function for update whitelabel_aff_commission table by
     * amount_manager value based on manager_curreny pulled from whitelabel table
     *
     * @param bool $show_notice
     * @param bool $should_process
     * @return int
     */
    public static function update_aff_commission_manager(
        bool $show_notice = true,
        bool $should_process = true
    ): int {
        if (!$should_process) {
            return 0;
        }

        $whitelabel_aff_commissions = Model_Whitelabel_Aff_Commission::find_all();

        if (is_null($whitelabel_aff_commissions) || empty($whitelabel_aff_commissions)) {
            echo 'There is no rows within whitelabel_aff_commissions!<br>';
            return -1;
        }

        // In USD
        $system_currency_tab = Helpers_Currency::get_mtab_currency(true, "USD");

        $updated = 0;
        foreach ($whitelabel_aff_commissions as $single_commission) {
            $manager_currency_temp = Model_Whitelabel_Aff::fetch_manager_curr_for_user(
                $single_commission->whitelabel_aff_id
            );

            if (empty($manager_currency_temp)) {
                continue;
            }

            $manager_currency_db = $manager_currency_temp[0];

            $manager_currency_tab = [
                'id' => $manager_currency_db['manager_currency_id'],
                'code' => $manager_currency_db['manager_currency_code'],
                'rate' => $manager_currency_db['manager_currency_rate']
            ];

            $transaction_temp = Model_Whitelabel_Transaction::find_by_pk($single_commission->whitelabel_transaction_id);

            if (empty($transaction_temp)) {
                continue;
            }

            $payment_currency_tab = Helpers_Currency::get_mtab_currency(
                true,
                "",
                $transaction_temp->payment_currency_id
            );

            $commission_manager = 0.00;
            if ((int)$manager_currency_tab['id'] === (int)$single_commission->currency_id) {
                $commission_manager = $single_commission->commission;
            } elseif ((int)$manager_currency_tab['id'] === (int)$system_currency_tab['id']) {
                $commission_manager = $single_commission->commission_usd;
            } else {
                $commission_manager = Helpers_Currency::get_recalculated_to_given_currency(
                    $single_commission->commission_usd,
                    $system_currency_tab,
                    $manager_currency_tab['code']
                );
            }

            $commission_payment = 0.00;
            if ((int)$payment_currency_tab['id'] === (int)$single_commission->currency_id) {
                $commission_payment = $single_commission->commission;
            } elseif ((int)$payment_currency_tab['id'] === (int)$system_currency_tab['id']) {
                $commission_payment = $single_commission->commission_usd;
            } else {
                $commission_payment = Helpers_Currency::get_recalculated_to_given_currency(
                    $single_commission->commission_usd,
                    $system_currency_tab,
                    $payment_currency_tab['code']
                );
            }

            $set = [
                'currency_id' => $transaction_temp->currency_id,
                'payment_currency_id' => $transaction_temp->payment_currency_id,
                'commission_payment' => $commission_payment,
                'commission_manager' => $commission_manager
            ];

            $single_commission->set($set);
            $single_commission->save();

            if ($show_notice) {
                echo "Whitelabel Aff Commission ID: " . $single_commission->id;
                echo ' Manager currencyID: ' . $manager_currency_tab['id'] . "<br>";
            }

            $updated++;
        }

        if ($show_notice) {
            echo "Updated: " . $updated . '<br>';
        }

        return $updated;
    }

    /**
     * This function is for update whitelabel default values for aff groups
     *
     * @param bool $show_notice
     * @param bool $should_process
     * @return int
     */
    public static function update_def_groups_values(
        bool $show_notice = true,
        bool $should_process = true
    ): int {
        if (!$should_process) {
            return 0;
        }

        $whitelabels = Model_Whitelabel::find_all();

        if (is_null($whitelabels) || empty($whitelabels)) {
            echo 'There is no whitelabels!<br>';
            return -1;
        }

        // In EUR
        $default_currency_tab = Helpers_Currency::get_mtab_currency();

        $updated = 0;

        foreach ($whitelabels as $whitelabel) {
            $manager_currency_tab = Helpers_Currency::get_mtab_currency(
                true,
                "",
                $whitelabel['manager_site_currency_id']
            );

            $set = [];

            if (!empty($whitelabel['def_commission_value'])) {
                $set["def_commission_value_manager"] = $whitelabel['def_commission_value'];
            }

            if (!empty($whitelabel['def_commission_value_2'])) {
                $set["def_commission_value_2_manager"] = $whitelabel['def_commission_value_2'];
            }

            if (!empty($whitelabel['def_ftp_commission_value'])) {
                $value_to_save = $whitelabel['def_ftp_commission_value'];

                if ($default_currency_tab['code'] !== $manager_currency_tab['code']) {
                    $value_to_save = Helpers_Currency::get_recalculated_to_given_currency(
                        $whitelabel['def_ftp_commission_value'],
                        $default_currency_tab,
                        $manager_currency_tab['code']
                    );
                }

                $set["def_ftp_commission_value_manager"] = $value_to_save;
            }

            if (!empty($whitelabel['def_ftp_commission_value_2'])) {
                $value_to_save = $whitelabel['def_ftp_commission_value_2'];

                if ($default_currency_tab['code'] !== $manager_currency_tab['code']) {
                    $value_to_save = Helpers_Currency::get_recalculated_to_given_currency(
                        $whitelabel['def_ftp_commission_value_2'],
                        $default_currency_tab,
                        $manager_currency_tab['code']
                    );
                }

                $set["def_ftp_commission_value_2_manager"] = $value_to_save;
            }

            if (!empty($set)) {
                $whitelabel->set($set);
                $whitelabel->save();

                $updated++;

                if ($show_notice) {
                    echo "<br>WhitelabelID: " . $whitelabel['id'] . "<br><br>";
                }
            }
        }

        if ($show_notice) {
            echo "Updated: " . $updated . '<br>';
        }

        return $updated;
    }

    /**
     * This function is for update values for aff groups (other than default
     * group for whitelabel)
     *
     * @param bool $show_notice
     * @param bool $should_process
     * @return int
     */
    public static function update_groups_values(
        bool $show_notice = true,
        bool $should_process = true
    ): int {
        if (!$should_process) {
            return 0;
        }

        $whitelabel_aff_groups = Model_Whitelabel_Aff_Group::find_all();

        if (is_null($whitelabel_aff_groups) || empty($whitelabel_aff_groups)) {
            echo 'There is no rows within whitelabel_aff_groups!<br>';
            return -1;
        }

        $default_currency_tab = Helpers_Currency::get_mtab_currency();

        $updated = 0;

        foreach ($whitelabel_aff_groups as $whitelabel_aff_group) {
            $whitelabel = Model_Whitelabel::find_by_pk($whitelabel_aff_group['whitelabel_id']);

            if (empty($whitelabel)) {
                continue;
            }

            $manager_currency_tab = Helpers_Currency::get_mtab_currency(
                true,
                "",
                $whitelabel['manager_site_currency_id']
            );

            $set = [];

            if (!empty($whitelabel_aff_group['commission_value'])) {
                $set["commission_value_manager"] = $whitelabel_aff_group['commission_value'];
            }

            if (!empty($whitelabel_aff_group['commission_value_2'])) {
                $set["commission_value_2_manager"] = $whitelabel_aff_group['commission_value_2'];
            }

            if (!empty($whitelabel_aff_group['ftp_commission_value'])) {
                $value_to_save = $whitelabel_aff_group['ftp_commission_value'];

                if ($default_currency_tab['code'] !== $manager_currency_tab['code']) {
                    $value_to_save = Helpers_Currency::get_recalculated_to_given_currency(
                        $whitelabel_aff_group['ftp_commission_value'],
                        $default_currency_tab,
                        $manager_currency_tab['code']
                    );
                }

                $set["ftp_commission_value_manager"] = $value_to_save;
            }

            if (!empty($whitelabel_aff_group['ftp_commission_value_2'])) {
                $value_to_save = $whitelabel_aff_group['ftp_commission_value_2'];

                if ($default_currency_tab['code'] !== $manager_currency_tab['code']) {
                    $value_to_save = Helpers_Currency::get_recalculated_to_given_currency(
                        $whitelabel_aff_group['ftp_commission_value_2'],
                        $default_currency_tab,
                        $manager_currency_tab['code']
                    );
                }

                $set["ftp_commission_value_2_manager"] = $value_to_save;
            }

            if (!empty($set)) {
                $whitelabel_aff_group->set($set);
                $whitelabel_aff_group->save();

                $updated++;

                if ($show_notice) {
                    var_dump($set);

                    echo "<br>WhitelabelID: " . $whitelabel['id'] . "<br><br>";
                    echo "<br>Whitelabel Aff GroupID: " . $whitelabel_aff_group['id'] . "<br><br>";
                }
            }
        }

        if ($show_notice) {
            echo "Updated: " . $updated . '<br>';
        }

        return $updated;
    }

    /**
     * Function for update currency and min_purchase based on payment currency
     * from Model_Whitelabel_Payment_Method
     *
     * @param bool $show_notice
     * @param bool $should_process
     * @return int
     */
    public static function update_payment_method_currency(
        bool $show_notice = true,
        bool $should_process = true
    ): int {
        if (!$should_process) {
            return 0;
        }

        $payment_method_currencies = Model_Payment_Method_Currency::find_all();

        if (is_null($payment_method_currencies) || empty($payment_method_currencies)) {
            echo 'There is no rows within payment_method_currencies!<br>';
            return -1;
        }

        $i = 0;
        $updated = 0;

        foreach ($payment_method_currencies as $payment_method_currency) {
            $i++;
            $wl_payment_method = Model_Whitelabel_Payment_Method::find_by_pk($payment_method_currency->whitelabel_payment_method_id);

            if (empty($wl_payment_method)) {
                continue;
            }

            if ((int) $wl_payment_method['payment_currency_id'] !== (int) $payment_method_currency->currency_id) {
                $old_currency_tab = Helpers_Currency::get_mtab_currency(
                    false,
                    null,
                    (int) $payment_method_currency->currency_id
                );

                $new_currency_tab = Helpers_Currency::get_mtab_currency(
                    false,
                    null,
                    (int) $wl_payment_method['payment_currency_id']
                );

                $min_purchase_val = Helpers_Currency::get_recalculated_to_given_currency(
                    $payment_method_currency->min_purchase,
                    $old_currency_tab,
                    $new_currency_tab['code'],
                    2
                );

                $payment_method_currency->set([
                    "currency_id" => $wl_payment_method['payment_currency_id'],
                    "min_purchase" => $min_purchase_val,
                ]);
                $payment_method_currency->save();

                $updated++;
            }
        }

        if ($show_notice) {
            echo "Potential number of rows to update: " . $i . "<br>";
            echo "Updated: " . $updated . '<br>';
        }

        return $updated;
    }

    /**
     * Function for add default currencies to DB for each payment method
     * for each whitelabel
     * Based on payment_method_currency (which is not needed any more)
     * copy some data (mainly min_purchase values from each row) to new
     * table whitelabel_payment_method_currency (when there is mapping for
     * whitelabel_payment_method by currency_id)
     *
     * @param bool $show_notice
     * @param bool $should_process
     * @return int
     */
    public static function update_whitelabel_payment_method_currencies(
        bool $show_notice = true,
        bool $should_process = true
    ): int {
        if (!$should_process) {
            return 0;
        }

        $inserted = 0;

        $default_currency_tab = Helpers_Currency::get_mtab_currency();

        // Only for TPAY case there is different default currency (PLN)
        $pln_currency_tab = Helpers_Currency::get_mtab_currency(false, "PLN");
        $tpay_payment_tab = Model_Payment_Method::find_by([
            "name" => "tpay.com"
        ]);
        $tpay_payment = $tpay_payment_tab[0];

        $whitelabel_payment_methods = Model_Whitelabel_Payment_Method::find_all();

        foreach ($whitelabel_payment_methods as $key => $single_payment_method) {
            $currency_id_in = null;
            if ((int)$single_payment_method->payment_method_id === (int)$tpay_payment->id) {
                $currency_id_in = $pln_currency_tab['id'];
            } else {
                $currency_id_in = (int)$default_currency_tab['id'];
            }

            // Try to check if there is already a row in whitelabel_payment_method_currency
            // with given currency
            $count = Model_Whitelabel_Payment_Method_Currency::fetch_count_by_whitelabel_payment_method_id(
                (int)$single_payment_method->id,
                $currency_id_in
            );

            if ($count > 0) {
                continue;
            }

            $payment_method_currency = null;
            $min_purchase = '0.00';
            $payment_method_currencies = Model_Payment_Method_Currency::find_by([
                "whitelabel_payment_method_id" => (int)$single_payment_method->id
            ]);

            // Try to find proper value of the min_purchase
            // if currency within payment_method_currency is different than
            // within prepared new whitelabel_payment_method_currency
            if (!empty($payment_method_currencies[0])) {
                $payment_method_currency = $payment_method_currencies[0];

                $payment_method_currency_id = (int)$payment_method_currency->currency_id;
                $min_purchase_value = $payment_method_currency->min_purchase;

                if ((int)$payment_method_currency_id !== (int)$currency_id_in) {
                    $old_currency_tab = Helpers_Currency::get_mtab_currency(
                        false,
                        null,
                        (int) $payment_method_currency_id
                    );

                    $new_currency_tab = Helpers_Currency::get_mtab_currency(
                        false,
                        null,
                        (int) $currency_id_in
                    );

                    $min_purchase = Helpers_Currency::get_recalculated_to_given_currency(
                        $min_purchase_value,
                        $old_currency_tab,
                        $new_currency_tab['code'],
                        2
                    );
                } else {
                    $min_purchase = $min_purchase_value;
                }
            }

            $set = [
                'whitelabel_payment_method_id' => (int)$single_payment_method->id,
                'currency_id' => $currency_id_in,
                'is_default' => 1,
                'is_enabled' => 1,
                'min_purchase' => (float)$min_purchase
            ];

            if ($show_notice) {
                echo "Add currency " . $default_currency_tab['code'] .
                    " to whitelabel_payment_method_id: " .
                    $single_payment_method->id . "<br>";
            }

            $whitelabel_payment_method_currency = Model_Whitelabel_Payment_Method_Currency::forge();
            $whitelabel_payment_method_currency->set($set);
            $whitelabel_payment_method_currency->save();

            $inserted++;
        }

        if ($show_notice) {
            echo "<br>Inserted: " . $inserted . "<br>";
        }

        return $inserted;
    }
}
