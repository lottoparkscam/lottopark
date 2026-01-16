<?php

use Fuel\Core\Fuel;
use GuzzleHttp\RequestOptions;

return [
    RequestOptions::TIMEOUT => 2,
    RequestOptions::VERIFY => Fuel::$env !== Fuel::DEVELOPMENT
];
