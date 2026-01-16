<?php

/**
 * Description of Tests_Unit_Forms_Aff_Register
 */
class Tests_Unit_Forms_Aff_Register extends Test_Unit
{
    private $aff_register = null;

    private $whitelabel = null;

    private $new_aff_email_to_set = 'newaff@local.pl';
    
    private $new_aff_login_to_set = 'newafflogin';
    
    /**
     *
     * @return void
     */
    public function setUp(): void
    {
        $domain = "lottopark.loc";
        $this->whitelabel = Model_Whitelabel::get_by_domain($domain);
        
        $this->aff_register = new Forms_Aff_Register($this->whitelabel);
        
        $this->aff_register->set_new_aff_email($this->new_aff_email_to_set);
        
        $this->aff_register->set_new_aff_login($this->new_aff_login_to_set);
    }
    
    /**
     * @test
     */
    public function is_whitelabel_is_fine()
    {
        $whitelabel = $this->aff_register->get_whitelabel();
        $whitelabel_id = $whitelabel['id'];
        parent::assertEquals(1, $whitelabel_id);
    }
    
    /**
     *
     */
    public function is_activation_type_different_than_none()
    {
        $aff_activation_type_to_process = $this->aff_register->aff_activation_type_to_process();
        
        $whitelabel_aff_activation_type = (int)$this->whitelabel['aff_activation_type'];
        
        if ($whitelabel_aff_activation_type !== Helpers_General::ACTIVATION_TYPE_NONE) {
            parent::assertEquals(true, $aff_activation_type_to_process);
        }
    }
    
    /**
     * @test
     */
    public function is_presenter_aff_register_set()
    {
        $this->aff_register->set_inside_by_presenter("aff/register");
        $aff_presenter_aff_register = $this->aff_register->get_inside();
        
        parent::assertInstanceOf(Presenter_Aff_Register::class, $aff_presenter_aff_register);
    }
    
    /**
     * @test
     */
    public function is_content_email_of_registration_not_empty()
    {
        $as_html = true;
        $email_of_registrtion_content = $this->aff_register->get_content_of_registration_email($as_html);
        
        parent::assertFalse(empty($email_of_registrtion_content));
    }
    
    
    /**
     * @test
     */
    public function is_whitelabel_email_set()
    {
        $whitelabel_email = $this->whitelabel['email'];
        
        $aff_register_whitelabel_email = $this->aff_register->get_whitelabel_email();
        
        parent::assertEquals($whitelabel_email, $aff_register_whitelabel_email);
    }
    
    /**
     * @test
     */
    public function is_whitelabel_name_not_empty()
    {
        $whitelabel_name = $this->whitelabel['name'];
        
        $aff_register_whitelabel_name = $this->aff_register->get_whitelabel_name();
        
        parent::assertEquals($whitelabel_name, $aff_register_whitelabel_name);
    }
        
    /**
     * @test
     */
    public function is_email_title_to_manager_not_empty()
    {
        $email_title_to_manager = $this->aff_register->get_email_title_to_manager();
        parent::assertFalse(empty($email_title_to_manager));
    }
    
    /**
     * @test
     */
    public function is_from_email_set()
    {
        $email_from = $this->aff_register->get_from_email();
        parent::assertFalse(empty($email_from));
    }
    
    /**
     * @test
     */
    public function check_new_aff_email_is_set()
    {
        $new_aff_email = $this->aff_register->get_new_aff_email();
        parent::assertEquals($this->new_aff_email_to_set, $new_aff_email);
    }
    
    /**
     * @test
     */
    public function check_new_aff_login_is_set()
    {
        $new_aff_login = $this->aff_register->get_new_aff_login();
        parent::assertEquals($this->new_aff_login_to_set, $new_aff_login);
    }
    
    /**
     * @test
     */
    public function is_content_email_to_manager_not_empty()
    {
        $as_html = true;
        $email_to_manager_content = $this->aff_register->get_content_of_email_to_manager($as_html);
        parent::assertFalse(empty($email_to_manager_content));
    }
    
    /**
     * @test
     */
    public function is_manager_link_set()
    {
        $whitelabel_domain = $this->whitelabel['domain'];
        $link_to_manager_to_check = 'https://manager.' . $whitelabel_domain . '/';
        $link_to_manager = $this->aff_register->get_link_to_manager();
        
        parent::assertEquals($link_to_manager_to_check, $link_to_manager);
    }
    
    /**
     *
     */
    public function send_email_to_manager_with_success()
    {
//        $email_sent = $this->aff_register->send_email_to_manager();
//
//        parent::assertEquals($email_sent, Forms_Aff_Register::RESULT_EMAIL_SENT);
    }
}
