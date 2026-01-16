<?php

use Fuel\Core\Str;

final class Task_Dev_Generate_Users extends Task_Dev_Task
{
    /**
     * How many users should be generated.
     * @var int
     */
    private $users_count;

    /**
     * Login prefix of generated users.
     * @var string
     */
    private $login_prefix;

    /**de
     * Whitelabel of generated users
     * @var int
     */
    private $whitelabel_id;

    public function __construct(string $login_prefix, int $users_count, int $whitelabel_id)
    {
        parent::__construct();

        $this->login_prefix = $login_prefix;
        $this->users_count = $users_count;
        $this->whitelabel_id = $whitelabel_id;
    }

    public function run(): void
    {
        for ($userIndex = 0; $userIndex < $this->users_count; $userIndex++) {
            $factory = Test_Factory_Whitelabel_User::create([
                'whitelabel_id' => $this->whitelabel_id,
                'login' => $this->login_prefix . ($userIndex+1),
            ]);
            $factory->get_result();
        }
    }
}
