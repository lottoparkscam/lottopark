<?php

use DI\Container as Container;
use Fuel\Core\Config as FuelConfig;
use GGLib\Http\GuzzleUriResolver;
use GGLib\Lcs\Client\Http\HttpLcsClient;
use GGLib\Lcs\Client\Http\Nonce\MicroTimeNonceGenerator;
use GGLib\Lcs\Client\Http\Signature\HmacSha512SignatureGenerator;
use GGLib\Serialization\SimpleArrayBasedJsonSerializer;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use Services\LcsService;
use Wrappers\Decorators\ConfigContract;

return [
    /**************************************************************************/
    /* Jeton                                                                  */
    /**************************************************************************/

    # free numbers
    Services_Lcs_Raffle_Ticket_Free_Contract::class => function (Container $c) {
        if (FuelConfig::get('mock_lcs')) {
            return new Services_Lcs_Raffle_Ticket_Free_Mock();
        }
        return new Services_Lcs_Raffle_Ticket_Free_Api($c->get(Services_Lcs_Auth_Resolver::class));
    },

    # taken numbers
    Services_Lcs_Raffle_Ticket_Taken_Contract::class => function (Container $c) {
        if (FuelConfig::get('mock_lcs')) {
            return new Services_Lcs_Raffle_Ticket_Taken_Mock();
        }
        return new Services_Lcs_Raffle_Ticket_Taken_Api($c->get(Services_Lcs_Auth_Resolver::class));
    },

    # tickets data
    Services_Lcs_Raffle_Ticket_Get_Contract::class => function (Container $c) {
        if (FuelConfig::get('mock_lcs')) {
            return new Services_Lcs_Raffle_Ticket_Get_Mock();
        }
        return new Services_Lcs_Raffle_Ticket_Get_Api($c->get(Services_Lcs_Auth_Resolver::class));
    },

    # tickets store
    Services_Lcs_Raffle_Ticket_Store_Contract::class => function (Container $c) {
        if (FuelConfig::get('mock_lcs')) {
            return new Services_Lcs_Raffle_Ticket_Store_Mock();
        }
        return new Services_Lcs_Raffle_Ticket_Store_Api($c->get(Services_Lcs_Auth_Resolver::class));
    },

    # tickets buy
    Services_Lcs_Raffle_Buy_Ticket_Contract::class => function (Container $c) {
        if (FuelConfig::get('mock_lcs')) {
            return new Services_Lcs_Raffle_Buy_Ticket_Mock();
        }
        return new Services_Lcs_Raffle_Buy_Ticket_Api($c->get(Services_Lcs_Auth_Resolver::class));
    },

    # draws data
    Services_Lcs_Draws_Contract::class => function (Container $c) {
        if (FuelConfig::get('mock_lcs')) {
            return new Services_Lcs_Draws_Mock();
        }
        return new Services_Lcs_Draws_Api($c->get(Services_Lcs_Auth_Resolver::class));
    },
    HmacSha512SignatureGenerator::class => function (Container $container, ConfigContract $configContract) {
        return new HmacSha512SignatureGenerator($configContract->get('lottery_central_server.sale_point.secret'));
    },
    HttpLcsClient::class => function (Container $container, HttpFactory $httpFactory, ConfigContract $configContract) {
        $factory = $httpFactory;
            $client = new Client([
                'timeout' => LcsService::TIMEOUT_IN_SECONDS,
                'connect_timeout' => LcsService::TIMEOUT_IN_SECONDS
            ]);
        return new HttpLcsClient(
            $client,
            $factory,
            $factory,
            $container->get(MicroTimeNonceGenerator::class),
            $container->get(SimpleArrayBasedJsonSerializer::class),
            $container->get(SimpleArrayBasedJsonSerializer::class),
            $container->get(HmacSha512SignatureGenerator::class),
            $factory,
            $container->get(GuzzleUriResolver::class),
            $configContract->get('lottery_central_server.url.base'),
            $configContract->get('lottery_central_server.sale_point.key')
        );
    }
];
