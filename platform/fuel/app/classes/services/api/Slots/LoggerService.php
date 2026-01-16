<?php

namespace Services\Api\Slots;

use Carbon\Carbon;
use Models\WhitelabelUser;
use Models\SlotGame;
use Models\SlotLog;
use Models\WhitelabelSlotProvider;

class LoggerService
{
    private int $whitelabelUserId;
    private int $whitelabelSlotProviderId;
    private string $action;
    private array $request;
    private ?int $slotGameId;

    public function configure(
        int $whitelabelUserId,
        int $whitelabelSlotProviderId,
        string $action,
        array $request,
        ?int $slotGameId = null
    ) {
        $this->whitelabelUserId = $whitelabelUserId;
        $this->whitelabelSlotProviderId = $whitelabelSlotProviderId;
        $this->action = $action;
        $this->request = $request;
        $this->slotGameId = $slotGameId;
    }

    public function log(array $response, bool $isError = false)
    {
        $log = new SlotLog();
        $log->whitelabelUserId = $this->whitelabelUserId;
        $log->whitelabelSlotProviderId = $this->whitelabelSlotProviderId;
        $log->slotGameId = $this->slotGameId;
        $log->action = $this->action;
        $log->isError = $isError;
        $log->request = $this->request;
        $log->response = $response;
        $log->createdAt = new Carbon($log->getTimezoneForField('createdAt'));
        $log->save();
    }
}
