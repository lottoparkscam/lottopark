<?php

use Fuel\Core\Validation;
use Forms\Wordpress\Forms_Wordpress_Email;
use Services\Logs\FileLoggerService;

/**
 * NOTE THAT WELCOME BONUS HAS BEEN EXTENDED BY MORE THAN ONE BONUS TYPE AND LOTTERY TYPE
 * Forms_Wordpress_Bonuses_Welcome CURRENTLY USED FOR FIRST PURCHASE LOTTERY ONLY
 */
class Forms_Wordpress_Bonuses_Welcome extends Forms_Main
{
    private FileLoggerService $fileLoggerService;

    const RESULT_NO_BONUS = 100;

    /**
     *
     * @var bool
     */
    private $should_be_in_transaction = false;

    /**
     *
     * @var array
     */
    private $whitelabel = [];

    /**
     *
     * @var array
     */
    private $user = [];

    /**
     *
     * @var null|array
     */
    private $bonus_for_whitelabel = null;

    /**
     *
     * @var null|Model_Whitelabel_User_Ticket
     */
    private $whitelabel_user_ticket = null;
    
    /**
     *
     * @var null|Model_Whitelabel_Transaction
     */
    private $transaction = null;

    /**
     *
     * @param array $whitelabel
     * @param array $user Could be null
     * @param bool $should_be_in_transaction Default false
     * @param null|Model_Whitelabel_Transaction Default null
     */
    public function __construct(
        array $whitelabel,
        array $user = null,
        bool $should_be_in_transaction = false,
        ?Model_Whitelabel_Transaction $transaction = null
    ) {
        $this->whitelabel = $whitelabel;
        $this->user = $user;
        $this->should_be_in_transaction = $should_be_in_transaction;
        $this->transaction = $transaction;
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
     * @return array
     */
    public function get_user(): ?array
    {
        return $this->user;
    }

    /**
     *
     * @return \Model_Whitelabel_Transaction|null
     */
    public function get_transaction():? Model_Whitelabel_Transaction
    {
        return $this->transaction;
    }
    
    /**
     *
     * @return array
     */
    public function get_bonus_for_whitelabel(): ?array
    {
        $this->bonus_for_whitelabel = Model_Whitelabel_Bonus::get_bonus_with_lottery_name_and_timezone(
            $this->whitelabel['id'],
            Forms_Whitelabel_Bonuses_Main::BONUS_WELCOME
        );

        return $this->bonus_for_whitelabel;
    }

    /**
     *
     * @return string
     */
    public function get_lottery_name(): string
    {
        $lottery_name = '';
        if (!empty($this->bonus_for_whitelabel) &&
                !empty($this->bonus_for_whitelabel['lottery_name'])
        ) {
            $lottery_name = $this->bonus_for_whitelabel['lottery_name'];
        }

        return $lottery_name;
    }

    /**
     *
     * @param array $bonus_for_whitelabel
     * @return array
     */
    public function get_lottery(array $bonus_for_whitelabel): ?array
    {
        if (empty($bonus_for_whitelabel)) {
            return null;
        }

        $purchase_lottery_id = (int) $bonus_for_whitelabel['purchase_lottery_id'];

        $lottery = Model_Lottery::get_single_row_by_id($purchase_lottery_id);
        return $lottery;
    }

    /**
     *
     * @param array $user
     * @return string
     */
    public function get_wlang_code(array $user): string
    {
        if (empty($user) || empty($user['language_id'])) {
            return "";
        }
        
        $language_model = Model_Language::find_by_pk($user['language_id']);
        
        if ($language_model === null) {
            return "";
        }
        
        return $language_model->code;
    }
    
    /**
     *
     * @return void
     */
    public function send_mail(): void
    {
        if (!defined('WPINC')) {
            return;
        }
        $user = $this->get_user();

        if (empty($user)) {
            return ;
        }
        
        $wlang_code = $this->get_wlang_code($user);
        
        if (empty($wlang_code)) {
            return ;
        }

        $from = 'noreply+' . time() . '@' . $this->whitelabel['domain'];
        $user_email = $user['email'];

        \Package::load('email');
        $email = Email::forge();
        $email->from($from, $this->whitelabel['name']);
        $email->to($user_email);

        $email_data = $this->get_email_data();

        $create_email = new Forms_Wordpress_Email($this->whitelabel);
        $email_template = $create_email->get_email(
            'welcome-bonus',
            $wlang_code,
            $email_data
        );

        $email->subject($email_template['title']);
        $email->html_body($email_template['body_html']);
        $email->alt_body($email_template['alt_body']);

        try {
            $email->send();
        } catch (\Exception $e) {
            $this->fileLoggerService->error(
                $e->getMessage()
            );
        }
    }

    /**
     *
     * @return \Model_Whitelabel_User_Ticket|null
     */
    public function get_new_bonus_ticket(): ?Model_Whitelabel_User_Ticket
    {
        return $this->whitelabel_user_ticket;
    }

    /**
     *
     * @return string
     */
    public function get_ticket_full_token(): string
    {
        $ticket = $this->get_new_bonus_ticket();

        $full_ticket_token = "";
        if (!empty($ticket)) {
            $full_ticket_token = $this->whitelabel['prefix'] .
                'T' .
                $ticket['token'];
        }

        return $full_ticket_token;
    }

    /**
     *
     * @return array
     */
    public function get_email_data(): ?array
    {
        if (empty($this->get_new_bonus_ticket())) {
            return [];
        }

        $lottery_name = $this->get_lottery_name();
        $lottery_timezone = $this->get_lottery_timezone();
        $user = $this->get_user();
        $user_timezone = $user ? $user['timezone'] : null;

        $email_data = [
            'lottery_name' => $lottery_name,
            'user_timezone' => $user_timezone
        ];

        $lines = Model_Whitelabel_User_Ticket_Line::get_lines_by_ticket_id(
            $this->whitelabel_user_ticket->id
        );

        $full_token = $this->get_ticket_full_token();

        $email_data['tickets'][0]['token'] = $full_token;
        $email_data['tickets'][0]['lottery_name'] = $lottery_name;
        $email_data['tickets'][0]['draw_date'] = $this->whitelabel_user_ticket->draw_date;
        $email_data['tickets'][0]['date'] = date("Y-m-d H:i:s");
        $email_data['tickets'][0]['numbers'] = [];
        $email_data['tickets'][0]['timezone'] = $lottery_timezone;

        foreach ($lines as $id2 => $line) {
            array_push($email_data['tickets'][0]['numbers'], [
                'numbers' => $line['numbers'],
                'bnumbers' => $line['bnumbers']
            ]);
        }

        return $email_data;
    }

    /**
     *
     * @return int
     */
    public function process_form(): int
    {
        $bonus_for_whitelabel = $this->get_bonus_for_whitelabel();
        if (empty($bonus_for_whitelabel)) {
            return self::RESULT_NO_BONUS;
        }

        $lottery = $this->get_lottery($bonus_for_whitelabel);
        if (empty($lottery)) {
            return self::RESULT_NO_BONUS;
        }
        
        $user = $this->get_user();

        if (empty($user)) {
            return self::RESULT_WITH_ERRORS;
        }

        try {
            if ($this->should_be_in_transaction) {
                DB::start_transaction();
            }

            $bonus_ticket = new Forms_Wordpress_Bonuses_Ticket_Ticket(
                $this->whitelabel,
                $user,
                $lottery
            );
            $result_ticket = $bonus_ticket->process_form();

            if ($result_ticket !== Forms_Wordpress_Bonuses_Ticket_Ticket::RESULT_OK) {
                $message = "There is something wrong with DB. " .
                    "No bonus ticket added for whitelabel ID: " .
                    $this->whitelabel['id'] .
                    " and user ID: " . $user['id'];
                throw new \Exception($message);
            }

            $lottery_type = $bonus_ticket->get_lottery_type();

            $this->whitelabel_user_ticket = $bonus_ticket->get_new_bonus_ticket();

            $new_bonus_ticket_line = new Forms_Wordpress_Bonuses_Ticket_Line(
                $lottery,
                $lottery_type,
                $this->whitelabel_user_ticket
            );
            $result_ticket_line = $new_bonus_ticket_line->process_form();

            if ($result_ticket_line !== Forms_Wordpress_Bonuses_Ticket_Line::RESULT_OK) {
                $message = "There is something wrong with DB. " .
                    "No bonus ticket line added for whitelabel ID: " .
                    $this->whitelabel['id'] .
                    " and user ID: " . $user['id'];
                throw new \Exception($message);
            }

            Lotto_Helper::create_slips_for_ticket($this->whitelabel_user_ticket);

            $notification_draw = new Helpers_Notifications_Draw();
            $notification_draw->new_record([$this->whitelabel_user_ticket]);
            
            Model_Whitelabel_User_Popup_Queue::push_message(
                $this->whitelabel['id'],
                $user['id'],
                _('You have received a bonus!'),
                _('You have received a free ticket with your first order!')
            );

            if ($this->should_be_in_transaction) {
                DB::commit_transaction();
            }
            if ($user['is_confirmed']) {
                $this->send_mail();
            }
        } catch (\Exception $e) {
            if ($this->should_be_in_transaction) {
                DB::rollback_transaction();
            }

            $this->fileLoggerService->error(
                $e->getMessage()
            );
            return self::RESULT_WITH_ERRORS;
        }

        return self::RESULT_OK;
    }

    private function get_lottery_timezone(): string
    {
        $lottery_timezone = '';
        if (!empty($this->bonus_for_whitelabel) &&
            !empty($this->bonus_for_whitelabel['lottery_timezone'])
        ) {
            $lottery_timezone = $this->bonus_for_whitelabel['lottery_timezone'];
        }

        return $lottery_timezone;
    }
}
