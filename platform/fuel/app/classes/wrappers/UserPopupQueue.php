<?php


namespace Wrappers;

use Model_Whitelabel_User_Popup_Queue;

/**
 * @codeCoverageIgnore
 */
class UserPopupQueue
{
    public function pushMessage(int $whitelabel_id, int $user_id, string $title, string $content, int $is_promocode = 0): void
    {
        Model_Whitelabel_User_Popup_Queue::push_message(
            $whitelabel_id,
            $user_id,
            $title,
            $content,
            $is_promocode
        );
    }
}
