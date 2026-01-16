<?php

/**
 * Mail helper
 */
class Helpers_Notifications_Draw
{
    /**
     * @var $whitelabel
     */
    private $whitelabel;

    /**
     * Helpers_Mail constructor.
     * @param $whitelabel
     */
    public function __construct()
    {
    }

    /**
     * Send email notification about new draw
     */
    public function send_emails()
    {
        $users_list = Model_Whitelabel_Notifications_Draw::get_users_draw_notification();

        foreach ($users_list as $id => $row) {
            // If email is empty or is not confirmed, mark notification as sent
            if (empty($row['email']) || !$row['is_confirmed']) {
                $this->mark_as_sent($row['id']);

                continue;
            }

            $whitelabel = Model_Whitelabel::get_single_by_id($row['whitelabel_id']);


            // Send email to user
            $email_data = [
                'lottery_name' => $row['name'],
                'lottery_timezone' => $row['timezone'],
                'user_timezone' => $row['user_timezone'],
                'draw_date' => $row['date_local'],
                'numbers' => $row['numbers'],
                'bnumbers' => $row['bnumbers'],
                'link' => 'https://'.$whitelabel['domain'].'/account/tickets/'
            ];

            $email_helper = new Helpers_Mail($whitelabel, $row);
            $email_helper->send_user_draw_notification_email($row['email'], $row['code'], $email_data);

            // Mark email as sent
            $this->mark_as_sent($row['id']);
        }
    }

    /**
     * Mark notification email as sent
     * @param $id
     */
    private function mark_as_sent($id)
    {
        $notification_email = Model_Whitelabel_Notifications_Draw::find_by_pk($id);
        $notification_email->is_email_sent = 1;
        $notification_email->save();
    }

    /**
     * Add new record to user_draw_notification
     * @param $tickets
     */
    public function new_record($tickets)
    {
        foreach ($tickets as $id => $ticket) {
            $notification_record = Model_Whitelabel_Notifications_Draw::get_draw_notification_record($ticket['whitelabel_user_id'], $ticket['lottery_id'], $ticket['draw_date']);

            if (empty($notification_record['id'])) {
                Model_Whitelabel_Notifications_Draw::insert_draw_notification_record($ticket['whitelabel_user_id'], $ticket['lottery_id'], $ticket['draw_date']);
            }
        }
    }

    /**
     * Set draw id to draw email notifications list
     * @param $lottery_id
     * @param $lottery_draw_date
     * @param $lottery_draw_id
     */
    public function update_draw_notification_emails($lottery_id, $lottery_draw_date, $lottery_draw_id)
    {
        Model_Whitelabel_Notifications_Draw::update_draw_notification_emails($lottery_id, $lottery_draw_date, $lottery_draw_id);
    }
}