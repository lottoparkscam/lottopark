<?php

use DI\Container as Container;
use DI\Factory\RequestedEntry;
use GGLib\Http\GuzzleUriResolver;
use GGLib\NowPayments\EnvironmentDependantUriResolver;
use GGLib\NowPayments\JmsSerializerFactory;
use GGLib\NowPayments\PsrClientGateway;
use GGLib\NowPayments\WebhookValidator;
use GGLib\Zen\EnvironmentBasedUriResolver;
use GGLib\Zen\WebhookIpnValidator;
use GGLib\Gcash\PsrGcashClient;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use Modules\Payments\Astro\AstroCheckoutUrlHandler;
use Modules\Payments\Astro\Client\AstroClientFactory;
use Modules\Payments\Astro\Client\AstroDepositClient;
use Modules\Payments\Astro\Client\AstroSignatureGenerator;
use Modules\Payments\ClientFactoryContract;
use Modules\Payments\Jeton\Client\JetonCheckoutPayClient;
use Modules\Payments\Jeton\Client\JetonClientFactory;
use Modules\Payments\Jeton\Client\JetonStatusCheckClient;
use Modules\Payments\Jeton\JetonCheckoutUrlHandler;
use Modules\Payments\Jeton\JetonTransactionHandler;
use Modules\Payments\PaymentAcceptor;
use Modules\Payments\PaymentAcceptorContract;
use Modules\Payments\PaymentAcceptorDecorator;
use Modules\Payments\PaymentFacadeLocator;
use Modules\Payments\PaymentLogger;
use Modules\Payments\PaymentUrlHelper;
use Modules\Payments\Synchronizer\PaymentsCleaner;
use Modules\Payments\Synchronizer\PaymentsSynchronizer;
use Modules\Payments\Tamspay\TamspayCheckoutUrlHandler;
use Modules\Payments\Trustpayments\TrustpaymentsCheckoutUrlHandler;
use Repositories\CleanerLogRepository;
use Repositories\Orm\TransactionRepository;
use Repositories\SynchronizerLogRepository;
use Services\Logs\FileLoggerService;
use Services\Shared\System;
use Wrappers\Db;
use Wrappers\Decorators\ConfigContract;

return [
    /**************************************************************************/
    /* GENERIC                                                                */
    /**************************************************************************/

    # In Payments namespace, we always have to use PaymentConfigDecorator!
    'payments.config' => fn (Container $c) => $c->get(ConfigContract::class),
    'payments.logger' => fn (Container $c) => $c->get(PaymentLogger::class),

    PaymentsSynchronizer::class => fn (Container $c) => new PaymentsSynchronizer(
        $c->get(TransactionRepository::class),
        $c->get(System::class),
        $c->get('payments.config'),
        $c->get(PaymentFacadeLocator::class),
        $c->get(SynchronizerLogRepository::class),
        $c->get(FileLoggerService::class),
    ),

    PaymentsCleaner::class => fn (Container $c) => new PaymentsCleaner(
        $c->get(TransactionRepository::class),
        $c->get('payments.config'),
        $c->get(System::class),
        $c->get(CleanerLogRepository::class),
        $c->get(FileLoggerService::class),
    ),

    'payments.*.facade' => function (Container $c, RequestedEntry $entry) {
        [, $slug,] = explode('.', $entry->getName());
        $slug = ucfirst($slug);
        $class = sprintf('Modules\Payments\%s\%sFacade', $slug, $slug);
        return $c->make($class, ['config' => $c->get('payments.config')]);
    },

    PaymentAcceptorContract::class => fn (Container $c) => $c->get(PaymentAcceptor::class),

    PaymentAcceptorDecorator::class => fn (Container $c) => new PaymentAcceptorDecorator(
        $c->get(TransactionRepository::class),
        $c->get(PaymentAcceptorContract::class),
        $c->get(Db::class),
        $c->get('payments.logger')
    ),

    /**************************************************************************/
    /* Jeton                                                                  */
    /**************************************************************************/

    ClientFactoryContract::class => fn (Container $c) => new JetonClientFactory(),

    # [ client ]
    JetonCheckoutPayClient::class => function (Container $c) {
        return new JetonCheckoutPayClient(
            $c->get(ClientFactoryContract::class),
			$c->get(PaymentLogger::class)
        );
    },

    # [ client ]
    JetonStatusCheckClient::class => function (Container $c) {
        return new JetonStatusCheckClient(
            $c->get(JetonClientFactory::class)
        );
    },

    # [ facade/handler ]
    JetonTransactionHandler::class => function (Container $c) {
        return new JetonTransactionHandler(
            $c->get(TransactionRepository::class),
            $c->get(JetonStatusCheckClient::class),
        );
    },

    # [ facade/handler ]
    JetonCheckoutUrlHandler::class => function (Container $c) {
        return new JetonCheckoutUrlHandler(
            $c->get(JetonCheckoutPayClient::class),
            $c->get(TransactionRepository::class),
            $c->get('payments.logger'),
            $c->get(PaymentUrlHelper::class),
        );
    },

    /**************************************************************************/
    /* Tamspay                                                                */
    /**************************************************************************/

    TamspayCheckoutUrlHandler::class => fn (Container $c) => new TamspayCheckoutUrlHandler(
        $c->get(TransactionRepository::class),
        $c->get('payments.config')
    ),

    /**************************************************************************/
    /* Astro                                                                  */
    /**************************************************************************/

    AstroClientFactory::class => fn (Container $c) => new AstroClientFactory(
        $c->get(AstroSignatureGenerator::class)
    ),

    AstroCheckoutUrlHandler::class => fn (Container $c) => new AstroCheckoutUrlHandler(
        $c->get(AstroDepositClient::class),
        $c->get(TransactionRepository::class),
        $c->get('payments.logger'),
        $c->get(PaymentUrlHelper::class),
        $c->get('payments.config')
    ),

    /**************************************************************************/
    /* Trustpayments                                                          */
    /**************************************************************************/

    TrustpaymentsCheckoutUrlHandler::class => fn (Container $c) => new TrustpaymentsCheckoutUrlHandler(
        $c->get(TransactionRepository::class),
        $c->get('payments.config')
    ),

    /**************************************************************************/
    /* NOWPayments                                                          */
    /**************************************************************************/

    PsrClientGateway::class => function (Container $container, bool $testMode) {
        $client = new Client(
            [
                'timeout' => NowPaymentsSender::CLIENT_TIMEOUT_IN_SECONDS,
                'connect_timeout' => NowPaymentsSender::CLIENT_TIMEOUT_IN_SECONDS,
            ]
        );

        $uriFactory = $container->get(HttpFactory::class);
        $uriResolver = $container->get(GuzzleUriResolver::class);
        $environmentDependantUriResolver = new EnvironmentDependantUriResolver($uriFactory, $uriResolver, $testMode);

        return new PsrClientGateway(
            $client,
            $uriFactory,
            $environmentDependantUriResolver,
            $uriFactory,
            (new JmsSerializerFactory())->createSerializer(),
            (new JmsSerializerFactory())->createSerializer(),
        );
    },
    WebhookValidator::class => fn (Container $container, string $ipnSecretKey) => new WebhookValidator($ipnSecretKey),

    /**************************************************************************/
    /* ZEN                                                          */
    /**************************************************************************/

    \GGLib\Zen\PsrClientGateway::class => function (Container $container, bool $testMode) {
        $client = new Client(
            [
                'timeout' => ZenSender::CLIENT_TIMEOUT_IN_SECONDS,
                'connect_timeout' => ZenSender::CLIENT_TIMEOUT_IN_SECONDS,
            ]
        );

        $uriFactory = $container->get(HttpFactory::class);
        $uriResolver = $container->get(GuzzleUriResolver::class);
        $environmentBasedUriResolver = new EnvironmentBasedUriResolver($uriFactory, $uriResolver, $testMode);

        return new \GGLib\Zen\PsrClientGateway(
            $client,
            $uriFactory,
            $environmentBasedUriResolver,
            $uriFactory,
            (new JmsSerializerFactory())->createSerializer(),
            (new JmsSerializerFactory())->createSerializer(),
        );
    },
    WebhookIpnValidator::class => fn (Container $container, string $merchantIpnSecret) => new WebhookIpnValidator($merchantIpnSecret),

    /**************************************************************************/
    /* Gcash                                                          */
    /**************************************************************************/

    PsrGcashClient::class => function (
        Container $container,
        string $apiClientId,
        string $apiKeySecret,
        bool $testMode
    ) {
        $client = new Client(
            [
                'timeout' => GcashSender::CLIENT_TIMEOUT_IN_SECONDS,
                'connect_timeout' => GcashSender::CLIENT_TIMEOUT_IN_SECONDS,
            ]
        );

        $uriFactory = $container->get(HttpFactory::class);
        $uriResolver = $container->get(GuzzleUriResolver::class);

        $environmentDependantUriResolver = new GGLib\Gcash\EnvironmentBasedUriResolver(
            $uriFactory,
            $uriResolver,
            $testMode
        );

        return new PsrGcashClient(
            $client,
            $uriFactory,
            $environmentDependantUriResolver,
            $uriFactory,
            (new JmsSerializerFactory())->createSerializer(),
            $apiClientId,
            $apiKeySecret
        );
    },
];
