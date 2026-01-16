<?php

final class Base extends Test_Feature
{

    public function test_base(): void
    {
        // create currency and lottery for it
        $result = Test_Factory_Currency::create()
            ->with(Test_Factory_Lottery::class)
            ->get_result();

        $this->assertArrayHasKey('currency', $result);
        $this->assertArrayHasKey('lottery', $result);
        $this->assertArrayHasKey('lottery_source', $result);
    }
}
