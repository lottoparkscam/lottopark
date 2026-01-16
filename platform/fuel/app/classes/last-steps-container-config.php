<?php

use Carbon\Carbon;
use Hybridauth\Adapter\OAuth2;
use Repositories\Orm\WhitelabelUserRepository;
use Repositories\WhitelabelSocialApiRepository;
use Repositories\WhitelabelUserSocialRepository;
use Services\Auth\WordpressLoginService;
use Services\CartService;
use Services\Logs\FileLoggerService;
use Services\RedirectService;
use Services\SocialMediaConnect\ActivationService;
use Services\SocialMediaConnect\ConfirmMailerService;
use Services\SocialMediaConnect\ConnectService;
use Services\SocialMediaConnect\FormService;
use Services\SocialMediaConnect\LastStepsService;
use Services\SocialMediaConnect\SessionService;

return [
    LastStepsService::class => function (Container $container, OAuth2 $socialAdapter) {
        return new LastStepsService(
            $container->get(ConnectService::class),
            $container->get(FileLoggerService::class),
            $socialAdapter,
            $container->get(WhitelabelUserRepository::class),
            $container->get(WhitelabelUserSocialRepository::class),
            $container->get(RedirectService::class),
            $container->get(ConfirmMailerService::class),
            $container->get(ActivationService::class),
            $container->get(Carbon::class),
            $container->get(WhitelabelSocialApiRepository::class),
            $container->get(WordpressLoginService::class),
            $container->get(SessionService::class),
            $container->get(FormService::class),
            $container->get(CartService::class),
        );
    }
];