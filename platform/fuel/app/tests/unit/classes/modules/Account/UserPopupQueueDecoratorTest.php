<?php

namespace Unit\Modules\Account;

use Modules\Account\UserPopupQueueDecorator;
use Test_Unit;
use Wrappers\UserPopupQueue;

class UserPopupQueueDecoratorTest extends Test_Unit
{
    private UserPopupQueue $popup;
    private UserPopupQueueDecorator $decorator;

    public function setUp(): void
    {
        parent::setUp();
        $this->popup = $this->createMock(UserPopupQueue::class);
        $this->decorator = new UserPopupQueueDecorator($this->popup);
    }

    /** @test */
    public function once__when_limited_invocation__skips(): void
    {
        $id = '1-2';
        $id2 = '1-2-3';
        $this->popup->expects($this->exactly(2))->method('pushMessage');

        $this->decorator->once($id)->pushMessage(1, 1, 'ads', 'asd');
        $this->decorator->once($id)->pushMessage(1, 1, 'ads', 'asd');
        $this->decorator->once($id2)->pushMessage(1, 1, 'ads', 'asd');
    }

    /** @test */
    public function pushMessage__once_disabled__pushes_n_times(): void
    {
        $id = '1-2';
        $this->popup->expects($this->exactly(4))->method('pushMessage');

        $this->decorator->pushMessage(1, 1, 'ads', 'asd');
        $this->decorator->pushMessage(1, 1, 'ads', 'asd');
        $this->decorator->pushMessage(1, 1, 'ads', 'asd');

        $this->decorator->once($id)->pushMessage(1, 1, 'ads', 'asd');
        $this->decorator->once($id)->pushMessage(1, 1, 'ads', 'asd');
    }
}
