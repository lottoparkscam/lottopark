<?php

use Fuel\Core\Fuel;

return [
    // guzzle SSL verify
    'options' => [
        'verify' => !(Fuel::$env === Fuel::DEVELOPMENT || Fuel::$env === Fuel::TEST)
    ]
];
