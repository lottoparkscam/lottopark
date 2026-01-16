<?php

return [
    "images" => [
        "dir" => "/data/scans" // not really used now (was used for imvalap provider)
    ],
    "admin" => [
        "subdomain" => $_ENV['ADMIN_SUBDOMAIN'],
    ],
    "ip" => [
        "whitelist" => explode(",", $_ENV['IP_WHITELIST']),
    ],
    "iptest" => [
        /*
        Samples from old config:

            ; Poland
            ;ip=5.149.160.10
            ; Belgium
            ;ip=5.23.128.1
            ; England
            ;ip=5.62.6.2
            ; For Skrill notifications
            ;ip=91.208.28.0
            ;ip=93.191.174.0
            ;ip=193.105.47.0
            ;ip=195.69.173.0
            ; For TPay notifications
            ;ip=195.149.229.109
            ;ip=148.251.96.163
            ;ip=178.32.201.77
            ;ip=46.248.167.59
            ;ip=46.29.19.106
            ; For Sofort notifications
            ;ip=193.104.32.100
        */
        //"ip" => $_ENV['IP_TEST'],
    ],
    "log" => [
        "min_log_level" => $_ENV['MIN_LOG_LEVEL'],
        "min_low_log_level" => $_ENV['MIN_LOG_LOW_LEVEL'],
    ],
];
