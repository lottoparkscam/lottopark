<?php

use Services\LotteryAdditionalDataService;
use Forms\Wordpress\Forms_Wordpress_Email;
use Services\Logs\FileLoggerService;

class Helpers_Mail
{
    /**
     * @var $whitelabel
     */
    private $whitelabel;

    /**
     * @var $user
     */
    private $user;

    /**
     * Helpers_Mail constructor.
     * @param $whitelabel
     */
    public function __construct($whitelabel, $user)
    {
        $this->whitelabel = $whitelabel;
        $this->user = $user;
    }

    /**
     * Send email helper
     * @param $email_address
     * @param $title
     * @param $html_body
     * @param $alt_body
     */
    private function send_email($email_address, $title, $html_body, $alt_body)
    {
        if (!$this->user['is_confirmed']) {
            return;
        }
        $fileLoggerService = Container::get(FileLoggerService::class);

        \Package::load('email');

        $email = Email::forge();
        $email->from('noreply+'.time().'@'. $this->whitelabel['domain'], $this->whitelabel['name']);
        $email->to($email_address);

        $email->subject($title);
        $email->html_body($html_body);
        $email->alt_body($alt_body);

        try {
            $email->send();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }
    }

    /**
     * Deposit success email
     * @param $email
     * @param $lang
     * @param $email_data
     */
    public function send_deposit_success_email($email, $lang, $email_data)
    {
        if (!defined('WPINC')) {
            return false;
        }

        $create_email = new Forms_Wordpress_Email($this->whitelabel);
        $email_template = $create_email->get_email('deposit-success', $lang, $email_data);
        
        $this->send_email($email, $email_template['title'], $email_template['body_html'], $email_template['alt_body']);
    }

    /**
     * Deposit failure
     * @param $email
     * @param $lang
     * @param $email_data
     */
    public function send_deposit_failure_email($email, $lang, $email_data)
    {
        $create_email = new Forms_Wordpress_Email($this->whitelabel);
        $email_template = $create_email->get_email('deposit-failure', $lang, $email_data);

        $this->send_email($email, $email_template['title'], $email_template['body_html'], $email_template['alt_body']);
    }

    /**
     * Ticket failure
     * @param $email
     * @param $lang
     * @param $email_data
     */
    public function send_ticket_failure_email($email, $lang, $email_data)
    {
        $create_email = new Forms_Wordpress_Email($this->whitelabel);
        $email_template = $create_email->get_email('ticket-failure', $lang, $email_data);

        $this->send_email($email, $email_template['title'], $email_template['body_html'], $email_template['alt_body']);
    }

    /**
     * New Ticket email
     * @param $email
     * @param $lang
     * @param $email_data
     * @param $tickets
     */
    public function send_ticket_email($email, $lang, $email_data, $tickets)
    {
        if (!defined('WPINC')) {
            return false;
        }

        $fileLoggerService = Container::get(FileLoggerService::class);

        $lotteries = Model_Lottery::get_really_all_lotteries_for_whitelabel($this->whitelabel);

        // Prepare data
        $saved_multidraws = [];
        foreach ($tickets as $id => $ticket) {
            if (!empty($ticket['multi_draw_id']) && in_array($ticket['multi_draw_id'], $saved_multidraws)) {
                continue;
            }

            if (!empty($ticket['multi_draw_id'])) {
                array_push($saved_multidraws, $ticket['multi_draw_id']);
            }

            if (!isset($lotteries["__by_id"][$ticket['lottery_id']])) {
                $error_message = "There is a problem with lottery settings. " .
                    "No lottery within lotteries list. Lottery ID: " .
                    $ticket['lottery_id'] . " " .
                    "Ticket ID: " .
                    $ticket['id'] . " " .
                    "Whitelabel ID: " . $this->whitelabel['id'];

                $fileLoggerService->error(
                    $error_message
                );
                
                continue;
            }
            
            $lottery = $lotteries['__by_id'][$ticket['lottery_id']];
            $ticketId = $ticket['id'];
            $lines = Model_Whitelabel_User_Ticket_Line::get_lines_by_ticket_id($ticketId);
            $email_data['tickets'][$id]['token'] = $this->whitelabel['prefix'] . 'T' . $ticket['token'];
            $email_data['tickets'][$id]['lottery_name'] = $lottery['name'];
            $email_data['tickets'][$id]['draw_date'] = $ticket['draw_date'];
            $email_data['tickets'][$id]['date'] = $ticket['date'];
            $email_data['tickets'][$id]['amount'] = $ticket['amount'];
            $email_data['tickets'][$id]['multi_draw_id'] = $ticket['multi_draw_id'];
            $email_data['tickets'][$id]['multi_draw_tickets'] = $ticket['multi_draw_tickets'];
            $email_data['tickets'][$id]['multi_draw_amount'] = $ticket['multi_draw_amount'];
            $email_data['tickets'][$id]['numbers'] = [];
            $email_data['tickets'][$id]['timezone'] = $lottery['timezone'];

            /** @var LotteryAdditionalDataService $lotteryAdditionalDataService */
            $lotteryAdditionalDataService = Container::get(LotteryAdditionalDataService::class);
            foreach ($lines as $id2 => $line) {
                $extraBall = $lotteryAdditionalDataService->getExtraBall($line['additional_data']);
                array_push($email_data['tickets'][$id]['numbers'], [
                    'numbers' => $line['numbers'],
                    'bnumbers' => $line['bnumbers'] . $extraBall,
                ]);
            }
        }

        $create_email = new Forms_Wordpress_Email($this->whitelabel);
        $email_template = $create_email->get_email('ticket-buy', $lang, $email_data);

        $this->send_email($email, $email_template['title'], $email_template['body_html'], $email_template['alt_body']);
    }


    /**
     * Ticket failure
     * @param $email
     * @param $lang
     * @param $email_data
     */
    public function send_user_draw_notification_email($email, $lang, $email_data)
    {
        $create_email = new Forms_Wordpress_Email($this->whitelabel);
        $email_template = $create_email->get_email('draw-notification', $lang, $email_data);

        $this->send_email($email, $email_template['title'], $email_template['body_html'], $email_template['alt_body']);
    }


    /**
     * Welcome email
     * @param $email
     * @param $lang
     * @param $email_data
     */
    public function send_welcome_email($sent_welcome_mail, $email, $lang, $email_data)
    {
        // If welcome mail was already sent, ignore it
        if ($sent_welcome_mail == 1) {
            return false;
        }

        $this->user->sent_welcome_mail = 1;
        $this->user->save();

        $create_email = new Forms_Wordpress_Email($this->whitelabel);
        $email_template = $create_email->get_email('welcome-mail', $lang, $email_data);

        $this->send_email($email, $email_template['title'], $email_template['body_html'], $email_template['alt_body']);
    }

    /**
    * Send multi-draw notification
    * @param $email
    * @param $lang
    * @param $email_data
    */
    public function send_multidraw_notification($email, $lang, $email_data)
    {
        $create_email = new Forms_Wordpress_Email($this->whitelabel);
        $email_template = $create_email->get_email('multidraw-notification', $lang, $email_data);

        $this->send_email($email, $email_template['title'], $email_template['body_html'], $email_template['alt_body']);
    }

    /**
     *
     * @param $email
     * @param $lang
     * @param $email_data
     */
    public function send_promo_code_free_ticket_email($email, $lang, $email_data)
    {
        $create_email = new Forms_Wordpress_Email($this->whitelabel);
        $email_template = $create_email->get_email('promo-code-bonus', $lang, $email_data);

        $this->send_email($email, $email_template['title'], $email_template['body_html'], $email_template['alt_body']);
    }

    public static function send_emergency_email(string $title, string $body, array $recipients = []): void
    {
        if (empty($recipients)) {
            Config::load("lotteries", true);
            $recipients = Config::get("lotteries.emergency_emails");
        }

        Package::load('email');
        $email = Email::forge();
        $email->from('noreply@' . Lotto_Helper::getWhitelabelDomainFromUrl(), 'Lotto Emergency');
        $email->to($recipients);
        $email->subject($title);
        $email->body($body);
        try {
            $email->send();
        } catch (Exception $e) {
        }
    }
}
