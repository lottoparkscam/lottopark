<?php


use Repositories\WhitelabelRepository;

class Tests_Feature_Classes_Model_Whitelabel extends Test_Feature
{
    private WhitelabelRepository $whitelabelRepository;

    private Whitelabel $whitelabel;

    public function setUp(): void
    {
        parent::setUp();
        $this->whitelabelRepository = Container::get(WhitelabelRepository::class);
    }

    /** @test  */
    public function cache_should_be_enabled_in_whitelabel_model_by_criteria()
    {
        $whitelabel = $this->get_whitelabel();
        $this->whitelabel = $whitelabel;
        $previousPrepaid = $this->whitelabel->prepaid;

        $this->change_whitelabel_prepaid();
        $updatedWhitelabel = $this->get_whitelabel();

        $this->assertNotEquals($previousPrepaid, $updatedWhitelabel->prepaid);
    }

    private function change_whitelabel_prepaid(): void
    {
        $previousPrepaid = $this->whitelabel->prepaid;
        $this->whitelabel->set([
            'prepaid' => $previousPrepaid + 10
        ]);
        $this->whitelabel->save();
    }

    private function get_whitelabel(): ?Whitelabel
    {
        return  $this->whitelabelRepository->pushCriteria(
            new Model_Orm_Criteria_Where('id', 1)
        )->getOne();
    }
}
