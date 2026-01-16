<?php

/**
 * Mail Helpers_Multidraw
 * @deprecated Helper must be static and cannot be instantiable
 */
class Helpers_Multidraw
{
    /**
     * @var $whitelabel
     */
    private $whitelabel;

    /**
     * Helpers_Multidraw constructor.
     * @param $whitelabel
     */
    public function __construct($whitelabel)
    {
        $this->whitelabel = $whitelabel;
    }

    /**
     * Check if ticket type is multi draw
     * @param $ticket_type
     * @return bool|mixed
     */
    public function check_multidraw($ticket_type)
    {
        // check if ticket type is multi draw
        if ($ticket_type[0] == 2) {
            // get chosen whitelabel multi-draw option
            $option = self::get_whitelabel_option($ticket_type[1]);

            return $option;
        }

        return false;
    }

    /**
     * Get whitelabel multi-draw option
     * @param int $id
     * @return bool|mixed
     */
    public function get_whitelabel_option(int $id)
    {
        $option = Model_Whitelabel_Multidraw_Option::get_whitelabel_option(
            (int) $this->whitelabel['id'],
            $id
        );

        if (empty($option->id)) {
            return false;
        }

        return $option;
    }

    /**
     * @param $multidraw
     * @param $price
     * @return string
     */
    public function calculate($multidraw, $price)
    {
        $i = 4;

        $new_price = round($price * $multidraw['tickets'], $i);

        $discount = round($multidraw['discount'] / 100, $i);
        $discount = round($new_price * $discount, $i);
        $new_price = $new_price - $discount;

        $new_price = round($new_price / $multidraw['tickets'], $i);
        $new_price = round($new_price, 2);

        $final_price = round($new_price * $multidraw['tickets'], $i);

        return $final_price;
    }


    /**
     * @param $multidraw
     * @param $price
     * @return string
     */
    public function calculate_single($multidraw, $price)
    {
        $i = 4;

        $new_price = round($price * $multidraw['tickets'], $i);
        
        $discount = round($multidraw['discount'] / 100, $i);
        $discount = round($new_price * $discount, $i);
        
        $new_price = $new_price - $discount;
        $new_price = round($new_price / $multidraw['tickets'], 2);

        return $new_price;
    }


    /**
     * @param $tickets
     * @param $price
     * @param $lines
     * @return string
     */
    public function calculate_old_price($tickets, $price, $lines)
    {
        $new_price = round($price * $tickets, 2);

        $new_price_with_lines = round($new_price * $lines, 2);

        return $new_price_with_lines;
    }

    /**
     * @param $token
     * @return mixed
     */
    public function get_multidraw($token)
    {
        $multidraw = Model_Multidraw::find_by([
            'token' => $token,
            'whitelabel_id' => $this->whitelabel['id']
        ]);

        if (empty($multidraw)) {
            return false;
        }

        return $multidraw[0];
    }


    /**
     *
     * @param string $token
     * @return mixed
     */
    public function get_multidraw_for_cancellation(string $token)
    {
        $multidraw = Model_Multidraw::find_by([
            'token' => $token,
            'whitelabel_id' => $this->whitelabel['id'],
            'is_finished' => 0
        ]);

        if (empty($multidraw)) {
            return false;
        }

        $transaction = Model_Whitelabel_Transaction::find_by_pk($multidraw[0]->whitelabel_transaction_id);

        if (empty($transaction)) {
            return false;
        }

        if ($transaction->status != 1) {
            return false;
        }

        return $multidraw[0];
    }

    /**
     *
     * @param array $multidraw
     * @return array
     */
    public function calculate_single_cancellation(array $multidraw): array
    {
        $count = $this->count_multidraw_processed_tickets($multidraw);

        $tickets_unprocessed = $count['unprocessed_tickets'];
        $tickets_processed = $count['processed_tickets'];

        $tickets = Model_Whitelabel_User_Ticket::find_by([
            'whitelabel_user_id' => $multidraw['whitelabel_user_id'],
            'whitelabel_id' => $this->whitelabel['id'],
            'multi_draw_id' => $multidraw['id']
        ]);

        $transaction = Model_Whitelabel_Transaction::find_by_pk($tickets[0]['whitelabel_transaction_id']);
        $currency = Model_Currency::find_by_pk($tickets[0]['currency_id']);

        $to_return_tickets = round($multidraw['tickets'] - $tickets_processed, 0);
        $one_ticket_cost = round($multidraw['amount'] / $multidraw['tickets'], 2);

        $to_return_amount = round($one_ticket_cost * $to_return_tickets, 2);

        return [
            'amount' => $to_return_amount,
            'single_ticket_amount' => $one_ticket_cost,
            'processed_tickets' => $tickets_processed,
            'multidraw_tickets' => $multidraw['tickets'],
            'transaction_cost' => $transaction['amount'],
            'tickets' => $to_return_tickets,
            'currency' => $currency['code'],
            'processed_tickets_ids' => $count['processed_tickets_ids'],
        ];
    }

    // ADMIN

    /**
     *
     * @param int $lottery_id
     * @param int $range_from
     *
     * @return array
     */
    public function get_multidraws_for_cancellation(int $lottery_id, int $range_from): array
    {
        $multidraws = Model_Multidraw::get_multidraws_for_cancellation($lottery_id, $range_from);

        $details = $this->calculate_massive_cancellation_for_details($multidraws);

        return $details;
    }

    /**
     *
     * @param array $multidraws
     * @return array
     */
    private function calculate_massive_cancellation_for_details(array $multidraws): array
    {
        $amount = 0;
        $tickets = 0;
        $users = 0;
        $transactions_cost = 0;

        $exists_users = [];

        foreach ($multidraws as $multidraw) {
            $one_ticket_cost = round($multidraw['amount_usd'] / $multidraw['tickets'], 2);
            $to_return_amount = round($one_ticket_cost * $multidraw['unprocessed_tickets'], 2);

            if (!in_array($multidraw['whitelabel_user_id'], $exists_users)) {
                array_push($exists_users, $multidraw['whitelabel_user_id']);
                $users += 1;
            }

            $transactions_cost += $multidraw['amount_usd'];
            $tickets += $multidraw['unprocessed_tickets'];
            $amount += $to_return_amount;
        }

        return [
            'amount' => $amount,
            'tickets' => $tickets,
            'users' => $users,
            'transactions_cost' => $transactions_cost,
            'currency' => 'USD'
        ];
    }

    /**
     *
     * @param string $lottery_name
     * @param type $lottery_id
     * @param type $range_from
     * @return bool
     */
    public function confirm_massive_multidraw_cancellation(
        string $lottery_name,
        $lottery_id,
        $range_from
    ): bool {
        $multidraws = Model_Multidraw::get_multidraws_for_cancellation($lottery_id, $range_from);

        foreach ($multidraws as $multidraw) {
            $one_ticket_cost = round($multidraw['amount'] / $multidraw['tickets'], 2);
            $to_return_amount = round($one_ticket_cost * $multidraw['unprocessed_tickets'], 2);

            $userId = $multidraw['whitelabel_user_id'];
            Model_Whitelabel_User::update_balance([$userId], (string)$to_return_amount);

            Model_Multidraw_Log::add_multidraw_log(
                $multidraw['id'],
                Model_Multidraw_Log::MULTIDRAW_LOG_STATUS_MASSIVE_CANCELLATION,
                'Massive multidraw cancellation, range from '.$range_from.', lottery '.$lottery_name,
                [
                    'range_from' => $range_from,
                    'lottery_name' => $lottery_name,
                    'one_ticket_cost' => $one_ticket_cost,
                    'returned_amount' => $to_return_amount,
                    'user_id' => $multidraw['whitelabel_user_id']
                ]
            );
        }

        return true;
    }

    /**
     *
     * @param int $multidraw_id
     * @param string $draw_date
     * @return void
     */
    public function check_multidraw_ltech_confirmation(int $multidraw_id, string $draw_date): void
    {
        $multidraw = Model_Multidraw::find_by_pk($multidraw_id);
        $multidraw->current_draw = $draw_date;
        if ($draw_date == $multidraw->valid_to_draw) {
            $multidraw->is_finished = 1;
        }
        $multidraw->save();
    }

    /**
     *
     * @param array $multidraw
     * @return array
     */
    public function count_multidraw_processed_tickets(array $multidraw): array
    {
        $processed_tickets = Model_Whitelabel_User_Ticket::get_processed_tickets_for_multidraw($multidraw['id']);
        $count_processed_tickets = count($processed_tickets);

        $unprocessed_tickets = round($multidraw['tickets'] - $count_processed_tickets);

        $processed_tickets_ids = [];
        foreach ($processed_tickets as $id => $row) {
            $processed_tickets_ids[] = $row['id'];
        }

        return [
            'processed_tickets' => $count_processed_tickets,
            'unprocessed_tickets' => $unprocessed_tickets,
            'processed_tickets_ids' => $processed_tickets_ids
        ];
    }

    /**
     * @param $lottery
     * @param $old_date
     * @param $new_date
     * @return void
     */
    public static function update_current_draw_date($lottery, $old_date, $new_date): void
    {
        if (is_null($old_date)) {
            return;
        }
        $sql = DB::query("UPDATE multi_draw md
            LEFT JOIN whitelabel_transaction wt
            ON wt.id = md.whitelabel_transaction_id
            SET current_draw = :currentdraw
            WHERE wt.status = 1
            AND lottery_id = :lottery
            AND is_finished = 0
            AND is_cancelled = 0
            AND current_draw = :olddraw");

        $sql->param(":currentdraw", $new_date->format(Helpers_Time::DATETIME_FORMAT));
        $sql->param(":lottery", $lottery['id']);
        $sql->param(":olddraw", $old_date->format(Helpers_Time::DATETIME_FORMAT));
        $sql->execute();
    }

    /**
     * @param $lottery
     * @param $new_date
     * @return void
     */
    public static function mark_as_finished($lottery, $new_date): void
    {
        $sql = DB::query("UPDATE multi_draw md
            LEFT JOIN whitelabel_transaction wt
            ON wt.id = md.whitelabel_transaction_id
            SET is_finished = 1
            WHERE wt.status = 1
            AND lottery_id = :lottery
            AND is_finished = 0
            AND is_cancelled = 0
            AND current_draw = :newdraw
            AND valid_to_draw = :newdraw");

        $sql->param(":newdraw", $new_date->format(Helpers_Time::DATETIME_FORMAT));
        $sql->param(":lottery", $lottery['id']);
        $sql->execute();
    }

    /**
     * @param array $notification
     * @return void
     */
    public static function send_notification(array $notification): void
    {
        $languages = Model_Language::get_all_languages();
        $whitelabel = Model_Whitelabel::get_single_by_id($notification['whitelabel_id']);
        $user = Model_Whitelabel_User::find_by_pk($notification['whitelabel_user_id']);
        $lotteries = Model_Lottery::get_all_lotteries();

        $lottery = $lotteries['__by_id'][$notification['lottery_id']];

        $date = $notification['valid_to_draw'];

        // Prepare email content
        $email_data = [
            'link' => 'https://'.$whitelabel['domain'].'/play/'.$lottery['slug'].'/',
            'lottery_name' => $lottery['name'],
            'draws' => $notification['tickets'],
            'last_date' => Lotto_View::format_date($date, IntlDateFormatter::LONG, IntlDateFormatter::SHORT, $lottery['timezone'], false)
        ];

        $language_code = $languages[$user->language_id]["code"];

        $email_helper = new Helpers_Mail($whitelabel, $user);

        $email_helper->send_multidraw_notification(
            $user->email,
            $language_code,
            $email_data
        );

        $multidraw = Model_Multidraw::find_by_pk($notification['id']);
        $multidraw->is_notification_sent = 1;
        $multidraw->save();
    }
}
