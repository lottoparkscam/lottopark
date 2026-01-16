<?php

namespace Fuel\Tasks;

use Container;
use Helpers\CaseHelper;

class Alert_Listener
{
    public function run(string $name): void
    {
        $name = ucfirst($name);
        $classname = "Task\Alert\\{$name}Listener";
        $task = Container::get($classname);
        $task->run();
    }
}
