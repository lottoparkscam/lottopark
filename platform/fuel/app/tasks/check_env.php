<?php

namespace Fuel\Tasks;

use Fuel\Core\Cli;
use Fuel\Core\Fuel;

final class Check_Env
{
    public function run()
    {
        Cli::write('Your environment is: ' . Fuel::$env);
    }
}
