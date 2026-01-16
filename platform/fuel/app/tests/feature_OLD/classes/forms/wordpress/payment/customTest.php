<?php

/**
 * Description of Forms_Wordpress_Payment_CustomTest
 */
class Forms_Wordpress_Payment_CustomTest extends Test_Unit
{

    /**
     * @var Forms_
     */
    protected $object;

    /**
     * In that case I want to have possibility to test add/subtract features
     * in transaction without any further result on DB.
     *
     * @var bool
     */
    protected $in_transaction = true;

    /**
     *
     * @var int
     */
    protected $start_auto_increment_ticket = 1;

    /**
     *
     * @var int
     */
    protected $start_auto_increment_ticket_line = 1;

    /**
     *
     * @var array
     */
    private $whitelabel = null;

    /**
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->markTestIncomplete('Need rework and removal of dumps.');
        parent::setUp();

        if ($this->in_transaction) {
            DB::start_transaction();

            $query = "SELECT 
                COALESCE(MAX(id) + 1, 1) AS current_auto_increment 
                FROM whitelabel_user_ticket";
            $result_tickets = DB::query($query)->execute();
            $this->start_auto_increment_ticket = $result_tickets[0]['current_auto_increment'];

            $query = "SELECT 
                COALESCE(MAX(id) + 1, 1) AS current_auto_increment_t_line 
                FROM whitelabel_user_ticket_line";
            $result_tickets_lines = DB::query($query)->execute();
            $this->start_auto_increment_ticket_line = $result_tickets_lines[0]['current_auto_increment_t_line'];
        }

        $this->whitelabel = Model_Whitelabel::get_single_by_id(1);

        $user_id = 1;
        $user = Model_Whitelabel_User::get_user_with_currencies_by_id_and_whitelabel(
            $user_id,
            $this->whitelabel
        );

        $this->object = new Forms_Wordpress_Payment_Custom();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    public function tearDown(): void
    {
        parent::tearDown();

        if ($this->in_transaction) {
            DB::rollback_transaction();

            $query = "ALTER TABLE whitelabel_user_ticket AUTO_INCREMENT = " . $this->start_auto_increment_ticket;
            DB::query($query)->execute();

            $query = "ALTER TABLE whitelabel_user_ticket_line AUTO_INCREMENT = " . $this->start_auto_increment_ticket_line;
            DB::query($query)->execute();
        }
    }

    /**
     * @test
     */
    public function is_payment_url_set()
    {
        $payment_url = $this->object->get_payment_url();
        var_dump($payment_url);
        parent::assertFalse(is_null($payment_url));
    }
}
