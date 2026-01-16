<?php


use Fuel\Core\Fuel;

class Task_Dev_Reset_User_Password extends Task_Dev_Task
{
    private $user_id;

    protected function __construct(int $user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * @inheritDoc
     */
    public function run(): void
    {
        if (Fuel::$env === Fuel::PRODUCTION){
            exit("Task cannot be executed in production environment.");
        }
        $new_password = \Fuel\Core\Str::random('alnum', 8);
        Model_Whitelabel_User::update_password_by_crm($this->user_id, $new_password);
        echo $new_password . "\n";
        $this->set_result(new Task_Result(true));
    }
}