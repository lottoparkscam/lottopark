<?php

return [
    "details" => [
        "name" => $_ENV['SLACK_NAME'],
        "timeout" => $_ENV['SLACK_TIMEOUT']
    ],
    'syncLogs' => isset($_ENV['SYNC_LOGS_ENABLED']) ? $_ENV['SYNC_LOGS_ENABLED'] : true
];
