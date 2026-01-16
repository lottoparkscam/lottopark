<?php

use DI\Container as Container;
use Modules\Mediacle\ApiRepository\MediacleRepository;
use Modules\Mediacle\ApiRepository\SavePlayerContract;
use Modules\Mediacle\Repositories\PlayerRegistrationDataByIdContract;
use Modules\Mediacle\Repositories\SalesDataByDateContract;
use Repositories\Orm\TransactionRepository;
use Repositories\Orm\WhitelabelUserRepository;

return [
    /*
     *  Wrappers for our internal services, around Mediacle contracts
     */
    PlayerRegistrationDataByIdContract::class => fn (Container $c) => $c->get(WhitelabelUserRepository::class),
    SalesDataByDateContract::class => fn (Container $c) => $c->get(TransactionRepository::class),

    /*
     *  External api integrations
     */
    SavePlayerContract::class => fn (Container $c) => $c->get(MediacleRepository::class),
];
