<?php

return [
    'pagerDuty' => [
        'pageStatus' => $_ENV['PAGER_DUTY_PAGE_STATUS_ROUTING_KEY'],
        'unprocessedTickets' => $_ENV['PAGER_DUTY_UNPROCESSED_TICKETS_ROUTING_KEY'],
        'unpurchasedLCSTickets' => $_ENV['PAGER_DUTY_UNPURCHASED_LCS_TICKETS_ROUTING_KEY'],
        'unpurchasedTickets' => $_ENV['PAGER_DUTY_UNPURCHASED_TICKETS_ROUTING_KEY'],
        'outdatedLottery' => $_ENV['PAGER_DUTY_OUTDATED_LOTTERY_ROUTING_KEY'],
        'unpaidoutTickets' => $_ENV['PAGER_DUTY_UNPAIDOUT_TICKETS_ROUTING_KEY'],
    ]
];
