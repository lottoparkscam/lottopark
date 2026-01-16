<?php

/**
 * Description of Forms_Wordpress_Payment_BhartipayTest
 */
class Forms_Wordpress_Payment_BhartipayTest extends Test_Unit
{
    /**
     *
     * @var array
     */
    private $whitelabel = null;
    
    /**
     *
     * @var Model_Whitelabel_Transaction
     */
    private $transaction = null;

    /**
     *
     * @var Model_Whitelabel_Payment_Method
     */
    private $model_whitelabel_payment_method = null;


    /**
     *
     * @var Forms_Wordpress_Payment_Bhartipay
     */
    protected $bhartipay_payment_form = null;


    /**
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->markTestIncomplete('Need rework and removal of dumps.');
        $this->whitelabel = Model_Whitelabel::get_single_by_id(1);
        
        //Mocking transaction
        $transaction = new Model_Whitelabel_Transaction($this->whitelabel);
        $transaction->token = "123456789";
        $transaction->whitelabel_user_id = 1;
        $transaction->payment_method_type = Helpers_General::PAYMENT_TYPE_OTHER;
        $transaction->whitelabel_payment_method_id = 1;
        $transaction->currency_id = 3;
        $transaction->payment_currency_id = 3;
        $transaction->amount_payment = "5.78";
        $this->transaction = $transaction;
        
        //creating object
        $this->bhartipay_payment_form = new Forms_Wordpress_Payment_Bhartipay(
            $this->whitelabel,
            null,
            $this->transaction,
            null
        );
    }
    
    public function test_is_set_whitelabel()
    {
        $this->assertTrue(!empty($this->bhartipay_payment_form->whitelabel));
    }
    
    public function test_is_set_transaction()
    {
        $this->assertTrue(!empty($this->bhartipay_payment_form->transaction));
    }
}
