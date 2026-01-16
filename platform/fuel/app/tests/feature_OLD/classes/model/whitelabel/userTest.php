<?php


use Fuel\Core\DB;
use Repositories\Orm\WhitelabelUserRepository;

class Tests_Feature_Classes_Model_Whitelabel_User extends Test_Feature
{
    private WhitelabelUserRepository $whitelabelUserRepository;

    private WhitelabelUser $whitelabelUser;

    public function setUp(): void
    {
        parent::setUp();
        $this->whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);
    }

    /** @test  */
    public function cache_should_be_disabled_in_user_model_by_criteria()
    {
        $whitelabelUser = $this->get_user();
        $this->whitelabelUser = $whitelabelUser;
        $previousBalance = $this->whitelabelUser->balance;

        $this->change_user_balance();
        $updatedWhitelabelUser = $this->get_user();

        $this->assertNotEquals($previousBalance, $updatedWhitelabelUser->balance);
    }

    private function change_user_balance(): void
    {
        $previousBalance = $this->whitelabelUser->balance;
        $this->whitelabelUser->set([
            'balance' => $previousBalance + 10
        ]);
        $this->whitelabelUser->save();
    }

    private function get_user(): ?WhitelabelUser
    {
        return  $this->whitelabelUserRepository->pushCriteria(
            new Model_Orm_Criteria_Where('id', 1)
        )->getOne();
    }
}
