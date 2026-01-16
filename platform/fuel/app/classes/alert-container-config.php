<?php

use DI\Container as Container;
use Services\Alert\AlertProviderInterface;
use Services\Alert\SlackProvider;

return [
    AlertProviderInterface::class => fn (Container $c) => $c->get(SlackProvider::class),
];
