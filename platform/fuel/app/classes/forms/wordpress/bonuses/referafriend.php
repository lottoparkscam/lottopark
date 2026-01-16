<?php

use Forms\Wordpress\Forms_Wordpress_Email;
use Services\Logs\FileLoggerService;
use Helpers\Wordpress\LanguageHelper;

/**
 * Description of Forms_Wordpress_Bonuses_Welcome
 */
class Forms_Wordpress_Bonuses_Referafriend extends Forms_Main
{
    /**
     *
     * @var array
     */
    private $whitelabel;
    private FileLoggerService $fileLoggerService;

    /**
     *
     * @var Model_Whitelabel_User
     */
    private $user;
    
    /**
     *
     * @var array
     */
    private $lottery = [];
    
    /**
     *
     * @var string
     */
    private $total_purchases_manager;
    
    /**
     *
     * @var Model_Whitelabel
     */
    private $bonus;

    const REFER_TO_BONUS_MAIL_TEMPLATE = "refer-to-bonus";
    const REFER_BY_BONUS_MAIL_TEMPLATE = "refer-by-bonus";

    /**
     *
     * @param array $whitelabel
     * @param Model_Whitelabel_User $user
     * @param string $total_purchases_manager
     */
    public function __construct(
        array $whitelabel,
        Model_Whitelabel_User $user,
        string $total_purchases_manager
    ) {
        $this->whitelabel = $whitelabel;
        $this->user = $user;
        $this->total_purchases_manager = $total_purchases_manager;
        $this->fileLoggerService = Container::get(FileLoggerService::class);
    }
    
    /**
     *
     * @param Forms_Wordpress_Bonuses_Ticket_Ticket $ticket
     * @return type
     */
    private function create_ticket(
        Forms_Wordpress_Bonuses_Ticket_Ticket $ticket
    ):? Model_Whitelabel_User_Ticket {
        $ticket->process_form();
        
        $lottery_type = $ticket->get_lottery_type();
        $new_bonus_ticket = $ticket->get_new_bonus_ticket();
        
        $new_bonus_ticket_line = new Forms_Wordpress_Bonuses_Ticket_Line(
            $this->lottery,
            $lottery_type,
            $new_bonus_ticket
        );
        $new_bonus_ticket_line->process_form();
        
        Lotto_Helper::create_slips_for_ticket($new_bonus_ticket);

        $notification_draw = new Helpers_Notifications_Draw();
        $notification_draw->new_record([$new_bonus_ticket]);
        
        return $new_bonus_ticket;
    }
    
    /**
     *
     * @return bool
     */
    public function process_form(): bool
    {
        $this->bonus = Model_Whitelabel_Bonus::get_bonus_with_lottery_name_and_timezone(
            $this->whitelabel['id'],
            Forms_Whitelabel_Bonuses_Main::BONUS_REFER_A_FRIEND
        );
        
        if (!empty($this->bonus) &&
            $this->total_purchases_manager >= $this->bonus['min_total_purchase']
        ) {
            $this->lottery = Model_Lottery::get_single_row_by_id($this->bonus['lottery_id']);
            
            $bonus_ticket_form = new Forms_Wordpress_Bonuses_Ticket_Ticket(
                $this->whitelabel,
                $this->user->to_array(),
                $this->lottery
            );
            $this->bonus_ticket = $this->create_ticket($bonus_ticket_form);
            
            $pnl_update_query = DB::query(
                "UPDATE whitelabel_user 
                SET pnl_manager = COALESCE(pnl_manager, 0) + :amount, 
                total_net_income_manager = COALESCE(total_net_income_manager, 0) + :amount, 
                last_update = NOW()
                WHERE whitelabel_user.id = :user_id"
            );
            $pnl_update_query->param(":amount", $this->bonus_ticket->income_manager);
            $pnl_update_query->param(":user_id", $this->user->id);

            $pnl_update_query->execute();
            
            Model_Whitelabel_User_Popup_Queue::push_message(
                $this->whitelabel['id'],
                $this->user->id,
                _('You have received a bonus!') ?? 'You have received a bonus!',
                _('You have received a free ticket, because you have registered using your friend\'s link!') ?? 'You have received a free ticket, because you have registered using your friend\'s link!'
            );
            
            if ($this->user->is_confirmed) {
                $this->send_mail(
                    self::REFER_TO_BONUS_MAIL_TEMPLATE,
                    $this->user->to_array(),
                    $this->bonus_ticket
                );
            }
            
            $refer_user = Model_Whitelabel_User::find_one_by([
                'id' => $this->user->referrer_id,
                'whitelabel_id' => $this->whitelabel['id'],
                'is_active' => 1,
                'is_deleted' => 0
            ]);
            
            if (!empty($refer_user)) {
                $refer_ticket_form = new Forms_Wordpress_Bonuses_Ticket_Ticket(
                    $this->whitelabel,
                    $refer_user->to_array(),
                    $this->lottery
                );
                $refer_ticket = $this->create_ticket($refer_ticket_form);
                Model_Whitelabel_Refer_Statistics::add_free_tickets($refer_user->id);
                
                $pnl_update_query = DB::query(
                    "UPDATE whitelabel_user 
                    SET pnl_manager = COALESCE(pnl_manager, 0) + :amount, 
                    total_net_income_manager = COALESCE(total_net_income_manager, 0) + :amount, 
                    last_update = NOW()
                    WHERE whitelabel_user.id = :user_id"
                );
                $pnl_update_query->param(":amount", $refer_ticket->income_manager);
                $pnl_update_query->param(":user_id", $refer_user->id);
    
                $pnl_update_query->execute();
                
                Model_Whitelabel_User_Popup_Queue::push_message(
                    $this->whitelabel['id'],
                    $refer_user->id,
                    _('You have received a bonus!') ?? 'You have received a bonus!',
                    _('Somebody used your link to register and you have received a free ticket!') ?? 'Somebody used your link to register and you have received a free ticket!'
                );
                
                if ($refer_user->is_confirmed) {
                    $this->send_mail(
                        self::REFER_BY_BONUS_MAIL_TEMPLATE,
                        $refer_user->to_array(),
                        $refer_ticket
                    );
                }
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     *
     * @param Model_Whitelabel_User_Ticket $ticket
     * @return array|null
     */
    public function get_email_data(Model_Whitelabel_User_Ticket $ticket):? array
    {
        $email_data = [
            'lottery_name' => $this->lottery['name']
        ];
        
        $lines = Model_Whitelabel_User_Ticket_Line::get_lines_by_ticket_id(
            $ticket->id
        );
        
        $full_token = $this->get_ticket_full_token($ticket);

        $email_data['tickets'][0]['token'] = $full_token;
        $email_data['tickets'][0]['lottery_name'] = $this->lottery['name'];
        $email_data['tickets'][0]['draw_date'] = $ticket->draw_date;
        $email_data['tickets'][0]['date'] = date("Y-m-d H:i:s");
        $email_data['tickets'][0]['numbers'] = [];

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
     * @param Model_Whitelabel_User_Ticket $ticket
     * @return string
     */
    public function get_ticket_full_token(Model_Whitelabel_User_Ticket $ticket): string
    {
        $full_ticket_token = "";
        if (!empty($ticket)) {
            $full_ticket_token = $this->whitelabel['prefix'] .
                'T' .
                $ticket->token;
        }
        
        return $full_ticket_token;
    }
    
    /**
     *
     * @param string $template_name
     * @param array $user
     * @param Model_Whitelabel_User_Ticket $ticket
     * @return void
     */
    public function send_mail(
        string $template_name,
        array $user,
        Model_Whitelabel_User_Ticket $ticket
    ): void {
        $wlang = LanguageHelper::getCurrentWhitelabelLanguage();
        
        if (empty($user)) {
            return;
        }
        
        $from = 'noreply+'.time().'@' . Lotto_Helper::getWhitelabelDomainFromUrl();
        
        \Package::load('email');
        $email = Email::forge();
        $email->from($from, $this->whitelabel['name']);
        $email->to($user['email']);
        
        $email_data = $this->get_email_data($ticket);
        
        $create_email = new Forms_Wordpress_Email($this->whitelabel);
        $email_template = $create_email->get_email(
            $template_name,
            $wlang['code'],
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
}
