<?php

namespace Task\Alert;

use Container;
use Services\Alert\AlertService;

abstract class AbstractAlertListener
{
    public const TYPE_PAGE_STATUS = 'pageStatus';
    public const TYPE_UNPROCESSED_TICKETS = 'unprocessedTickets';
    public const TYPE_UNPURCHASED_LCS_TICKETS = 'unpurchasedLCSTickets';
    public const TYPE_UNPURCHASED_TICKETS = 'unpurchasedTickets';
    public const TYPE_OUTDATED_LOTTERY = 'outdatedLottery';
    public const TYPE_UNPAIDOUT_TICKETS = 'unpaidoutTickets';
    public const TYPE_WORDPRESS_PAGES = 'wordpressPages';
    public const TYPE_MISSING_RAFFLE_DRAW = 'missingRaffleDraw';
    public const TYPE_INCORRECT_NEXT_DRAW = 'incorrectNextDraw';
    public const TYPE_NEXT_DRAW_LISTENER = 'nextDraw';

    private AlertService $alertService;

    protected string $message;
    protected string $type;
    protected string $slackChannelName = 'health-check';

    /** If this method return true, notification will send to AlertProvider */
    abstract public function shouldSendAlert();

    public function __construct()
    {
        $this->alertService = Container::get(AlertService::class);
    }

    public function run(): void
    {
        if ($this->shouldSendAlert()) {
            $this->alertService->send($this->message, $this->type, $this->slackChannelName);
        }
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }
}
