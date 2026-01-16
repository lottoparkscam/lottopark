<?php

use Repositories\WhitelabelOAuthClientRepository;
use Services\Logs\FileLoggerService;
use Services\OAuth2Server\ServerFactory;
use Services\OAuth2Server\WhitelabelUserOAuth2Service;

return [
    WhitelabelUserOAuth2Service::class => function (Container $container) {
        $serverFactory = $container->get(ServerFactory::class);

        return new WhitelabelUserOAuth2Service(
            $serverFactory->create(),
            $container->get(WhitelabelOAuthClientRepository::class),
            $container->get(FileLoggerService::class)
        );
    }
];
