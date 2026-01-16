<?php

use Services\Logs\FileLoggerService;

class Forms_Admin_Whitelabels_Prepaid_Email extends Forms_Main
{
    const RESULT_NOTHING_TO_SEND = 100;
    
    /**
     *
     * @var array
     */
    private $admin_emails = [
        'tom@whitelotto.com',
        'peter@whitelotto.com',
    ];
    
    /**
     *
     * @var array
     */
    private $whitelabels_under_limit = [];
    private FileLoggerService $fileLoggerService;

    /**
     *
     */
    public function __construct()
    {
        $this->whitelabels_under_limit = Model_Whitelabel_Prepaid::get_whitelabels_under_limit();
        $this->fileLoggerService = Container::get(FileLoggerService::class);
    }

    /**
     *
     * @return array
     */
    private function get_admin_emails(): array
    {
        return $this->admin_emails;
    }
    
    /**
     *
     * @return string
     */
    private function get_whitelabel_domain(): string
    {
        $domain =  Lotto_Helper::getWhitelabelDomainFromUrl();
        return $domain;
    }
    
    /**
     *
     * @return string
     */
    private function get_from_email(): string
    {
        $domain = $this->get_whitelabel_domain();
        $from_email = 'noreply@' . $domain;
        return $from_email;
    }
    
    /**
     *
     * @return string
     */
    private function get_email_title_for_admins(): string
    {
        $title = _("White-Labels with low prepaid balance");
        return $title;
    }
    
    /**
     *
     * @param string $whitelabel_name
     * @return string
     */
    private function get_email_title_for_manager(string $whitelabel_name): string
    {
        $title = $whitelabel_name . ": " . _("Your prepaid balance is getting low!");
        return $title;
    }
    
    /**
     *
     * @return array
     */
    private function get_whitelabels_under_limit(): array
    {
        return $this->whitelabels_under_limit;
    }
    
    /**
     *
     * @param string $current_prepaid
     * @param bool $as_html
     * @return string
     */
    private function get_content_of_email_to_manager(
        string $current_prepaid,
        bool $as_html = true
    ): string {
        $break_tag = '\n\r';
        if ($as_html) {
            $break_tag = '<br>';
        }
        
        $content_of_email_to_manager = _(
            "Warning! Your prepaid amount is very low: "
        );
        $content_of_email_to_manager .= $current_prepaid . ". " . $break_tag;
        $content_of_email_to_manager .= $break_tag . _("Best Regards,") . $break_tag;
        $content_of_email_to_manager .= _("WhiteLotto Team");
        
        return $content_of_email_to_manager;
    }
    
    /**
     *
     * @param array $whitelabels_under_limit
     * @param bool $as_html
     * @return string
     */
    private function get_content_of_email_to_admin(
        array $whitelabels_under_limit,
        bool $as_html = true
    ): string {
        $break_tag = '\n\r';
        if ($as_html) {
            $break_tag = '<br>';
        }
        
        $content_of_email_to_admins = _(
            "List of whitelabels with low prepaid amount: "
        ) . $break_tag;
        
        foreach ($whitelabels_under_limit as $whitelabel) {
            $current_prepaid = Lotto_View::format_currency(
                $whitelabel['prepaid'],
                $whitelabel['manager_currency_code'],
                true
            );
            
            $content_of_email_to_admins .= "- " . $whitelabel['name'] . ": ";
            $content_of_email_to_admins .= $current_prepaid . ";" . $break_tag;
        }
        
        return $content_of_email_to_admins;
    }
    
    /**
     *
     * @return int
     */
    public function send_email_to_admins(): int
    {
        $whitelabels_under_limit = $this->get_whitelabels_under_limit();
        
        if (empty($whitelabels_under_limit)) {
            return self::RESULT_NOTHING_TO_SEND;
        }
        
        $from_email = $this->get_from_email();
        $admin_emails = $this->get_admin_emails();

        $title = $this->get_email_title_for_admins();

        $html_body = $this->get_content_of_email_to_admin($whitelabels_under_limit, true);
        $alt_body = $this->get_content_of_email_to_admin($whitelabels_under_limit, false);

        \Package::load('email');
        $email = Email::forge();
        $email->from($from_email);
        $email->to($admin_emails);
        
        $email->subject($title);
        $email->html_body($html_body);
        $email->alt_body($alt_body);

        try {
            $email->send();
        } catch (\Exception $e) {
            $this->fileLoggerService->error(
                $e->getMessage()
            );
            return self::RESULT_EMAIL_NOT_SENT;
        }

        return self::RESULT_OK;
    }
    
    /**
     *
     * @return int
     */
    public function send_email_to_managers(): int
    {
        $whitelabels_under_limit = $this->get_whitelabels_under_limit();
        
        if (empty($whitelabels_under_limit)) {
            return self::RESULT_NOTHING_TO_SEND;
        }
        
        \Package::load('email');
        $from_email = $this->get_from_email();
        
        $error_count = 0;
        
        foreach ($whitelabels_under_limit as $whitelabel) {
            $whitelabel_email = $whitelabel['email'];
            $title = $this->get_email_title_for_manager($whitelabel['name']);
            
            $current_prepaid = Lotto_View::format_currency(
                $whitelabel['prepaid'],
                $whitelabel['manager_currency_code'],
                true
            );
            
            $html_body = $this->get_content_of_email_to_manager($current_prepaid, true);
            $alt_body = $this->get_content_of_email_to_manager($current_prepaid, false);
                
            $email = Email::forge();
            $email->from($from_email);
            $email->to($whitelabel_email);

            $email->subject($title);
            $email->html_body($html_body);
            $email->alt_body($alt_body);
            
            try {
                $email->send();
            } catch (\Exception $e) {
                $this->fileLoggerService->error(
                    $e->getMessage()
                );
                $error_count++;
            }
        }
        
        if ($error_count > 0) {
            return self::RESULT_EMAIL_NOT_SENT;
        }
        
        return self::RESULT_OK;
    }
}
