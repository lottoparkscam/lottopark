<?php

use DI\Container as Container;
use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use Doctrine\Inflector\Language;
use Email\Email;
use Fuel\Core\Config as FuelConfig;
use Fuel\Core\DB;
use Fuel\Core\Fuel;
use Fuel\Core\Package;
use Fuel\Core\Str;
use GuzzleHttp\Client;
use Modules\Account\Balance\BalanceContract;
use Modules\Account\Balance\RegularBalance;
use Modules\View\ViewRenderer;
use Modules\View\ViewRendererContract;
use Repositories\WhitelabelRepository;
use Services\Logs\FileLoggerService;
use Services\MailerService;
use Services\Shared\Logger\InMemoryLogger;
use Services\Shared\Logger\LoggerContract;
use Services\Shared\System;
use Stwarog\UowFuel\FuelEntityManager;
use Wrappers\Config as ConfigWrapper;
use Wrappers\Decorators\Config as ConfigDecorator;
use Wrappers\Decorators\ConfigContract;

return [
    'env' => Fuel::$env,
    'date' => new DateTimeImmutable(),

    System::class => function (Container $c) {
        return new System(
            $c->get('env'),
            $c->get('date'),
            $c->get('url_data'),
            $c->get('whitelabel'),
        );
    },

    Client::class => function (Container $container, ConfigContract $configContract) {
        return new Client([
            'verify' => $configContract->get('GuzzleSSL.options.verify'),
        ]);
    },

    FuelEntityManager::class => function (Container $c) {
        return FuelEntityManager::forge($c->get(DB::class));
    },

    /**************************************************************************/
    /* WEB IDENTITY                                                           */
    /**************************************************************************/

    'domain' => function () {
        if (defined('WORDPRESS_INSIDE_FUEL_DOMAIN')) {
            return preg_replace(['/^www\./'], '', WORDPRESS_INSIDE_FUEL_DOMAIN, 1);
        }

        return Lotto_Helper::getWhitelabelDomainFromUrl();
    },

    // base_url comes from config.php. It is commented out and Fuel resolves it automatically
    'url_data' => fn (Container $c) => parse_url($c->get(ConfigContract::class)->get('base_url')),

    'whitelabel' => function (Container $c) {
        return $c->get(WhitelabelRepository::class)->findOneByDomain($c->get('domain') ?? '');
    },

    'theme' => function (Container $c) {
        return $c->get('whitelabel')->theme;
    },
    
    /**************************************************************************/
    /* LOGGER                                                                 */
    /**************************************************************************/

    LoggerContract::class => fn () => new InMemoryLogger(),

    /**************************************************************************/
    /* USER BALANCE                                                           */
    /**************************************************************************/

    BalanceContract::class => fn (Container $c) => $c->get(RegularBalance::class),

    /**************************************************************************/
    /* VIEW RENDERER                                                          */
    /**************************************************************************/

    ViewRendererContract::class => fn (Container $c) => $c->get(ViewRenderer::class),

    /**************************************************************************/
    /* CONFIG                                                                 */
    /**************************************************************************/

    # config decoration
    ConfigContract::class => fn () => new ConfigDecorator(new ConfigWrapper()),

    ConfigWrapper::class => function (Container $c) {
        if (Str::starts_with($c->get('env'), 'prod') === false) {
            echo 'Attempted to load Fuel core config in some class. It is recommended to use ConfigContract. Check container-config.php';
        }
        return new ConfigWrapper();
    },

    FuelConfig::class => function (Container $c) {
        if (Str::starts_with($c->get('env'), 'prod') === false) {
            echo 'Attempted to load Fuel core config in some class. It is recommended to use ConfigContract. Check container-config.php';
        }
        return new FuelConfig();
    },

    /**************************************************************************/
    /* MAILER                                                                 */
    /**************************************************************************/
    MailerService::class => function (Container $c) {
        Package::load('email');
        return new MailerService(
            $c->get(FileLoggerService::class),
            Email::forge()
        );
    },

    /**************************************************************************/
    /* DOCTRINE INFLECTOR                                                     */
    /**************************************************************************/
    Inflector::class => fn (Container $c) => InflectorFactory::createForLanguage(Language::ENGLISH)->build(),

    /**************************************************************************/
    /* WP SEEDERS                                                             */
    /**************************************************************************/

    'wpseeders' => function (Container $c) {
        // todo st: Investigation required for tagging services in Container
        // https://php-di.org/doc/php-definitions.html
        $seedersNameSpace = 'Fuel\Tasks\Seeders\Wordpress\\';
        $seedersPath = __DIR__ . '/../tasks/seeders/wordpress/*.php';
        $orderedFileNames = [];
        $fullFileNames = glob($seedersPath);
        $fileNames = array_map(fn (string $fullPath) => $baseName = basename($fullPath, '.php'), $fullFileNames);
        asort($fileNames);
        $classNames = array_map(
            function (string $fullPath) {
                $baseName = basename($fullPath, '.php');
                $chunks = explode('_', $baseName);
                return $nameWithoutNumberPrefix = count($chunks) === 2 ? $chunks[1] : $chunks[0];
            },
            $fileNames
        );
        $isNotAbstract = fn (string $class) => strpos($class, 'Abstract') === false;
        $entries = array_map(
            fn (string $class) => $isNotAbstract($class) ? $c->get($seedersNameSpace . $class) : null,
            $classNames
        );
        $entries = array_values(array_filter($entries));

        return $entries;
    },
];
