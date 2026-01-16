<?php
return [
    /**
     *  When enabled, script will update prize-in-kind & tier's prizes according
     *  related lottery->price.
     *
     *  If disabled, then mail notification will be fired.
     */
    'raffle' => [
        'update_prizes' => $_ENV['SYNC_UPDATE_PRIZES'] ?? false
    ]
];
