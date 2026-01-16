<?php

namespace Modules\Account;

use Wrappers\UserPopupQueue;

/**
 * Class UserPopupQueueDecorator
 * Add feature to limits pushes by unique key.
 */
class UserPopupQueueDecorator
{
    private array $enqueued = [];
    private bool $once = false;

    private UserPopupQueue $popup;

    public function __construct(UserPopupQueue $popup)
    {
        $this->popup = $popup;
    }

    public function pushMessage(int $whitelabelId, int $userId, string $title, string $content, int $isPromocode = 0): void
    {
        if ($this->once) {
            $enqueued = $this->enqueued;
            $invocationCounts = count(array_filter($this->enqueued, function (string $id) use ($enqueued) {
                return $id === end($enqueued);
            }));

            if ($invocationCounts > 1) {
                return;
            }
        }

        $this->popup->pushMessage(
            $whitelabelId,
            $userId,
            $title,
            $content,
            $isPromocode
        );

        $this->clear();
    }

    /**
     * Allows to limit push message once per unique ID in one app lifecycle.
     *
     * @param string $uniqueQueueId
     * @return $this
     */
    public function once(string $uniqueQueueId): self
    {
        $this->enqueued[] = $uniqueQueueId;
        $this->once = true;
        return $this;
    }

    private function clear(): void
    {
        $this->once = false;
    }
}
