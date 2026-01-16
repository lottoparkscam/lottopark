<?php

/**
 *  This container is being loaded only in Fuel::$env = 'test'
 *  and initialized persistence adapter for all fixtures - to let them
 *  be aware of this package. It have a huge performance factor.
 *  It is recommended to initialize all new fixtures in that way.
 *
 *  Fixtures also accept a Faker implementation in its constructor.
 *  Here is a great place to change default instance if necessary.
 */

use DI\Container as Container;
use Fuel\Core\DB;
use Models\Currency;
use Orm\Model;
use Psr\EventDispatcher\EventDispatcherInterface;
use Stwarog\FuelFixtures\DependencyInjection\Config;
use Stwarog\FuelFixtures\DependencyInjection\ConfigContract;
use Stwarog\FuelFixtures\Fuel\PersistenceContract;
use Stwarog\FuelFixtures\Fuel\UowPersistence;
use Stwarog\Uow\UnitOfWork\UnitOfWork;
use Stwarog\UowFuel\FuelDBAdapter;
use Stwarog\UowFuel\FuelEntityManager;
use Tests\Fixtures\CurrencyFixture;
use Tests\Fixtures\Raffle\RaffleFixture;
use Tests\Fixtures\Raffle\RaffleRuleFixture;
use Tests\Fixtures\Raffle\RaffleRuleTierFixture;
use Tests\Fixtures\Raffle\RaffleTicketFixture;
use Tests\Fixtures\Raffle\RaffleTicketLineFixture;
use Tests\Fixtures\Raffle\WhitelabelRaffleFixture as WlRaffleFixture;
use Tests\Fixtures\Utils\DupesPrevention\FeatureToggle;
use Tests\Fixtures\Utils\DupesPrevention\FixturesEventListener;
use Tests\Fixtures\Utils\DupesPrevention\Matcher;
use Tests\Fixtures\Utils\DupesPrevention\Overridable;
use Tests\Fixtures\WhitelabelFixture;
use Tests\Fixtures\WhitelabelTransactionFixture;
use Tests\Fixtures\WhitelabelUserFixture;

return [
    // Shared persistence adapter
    PersistenceContract::class => fn(Container $c) => $c->get('persistence'),

    ConfigContract::class => fn(Container $c) => new Config(
        $c->get('persistence'),
        null,
        $c->get(EventDispatcherInterface::class)
    ),

    'unsafeConfig' => fn(Container $c) => new Config(
        $c->get('persistence_unsafe'),
        null,
        $c->get(EventDispatcherInterface::class)
    ),

    'persistence' => fn(Container $c) => new UowPersistence(
        new FuelEntityManager(
            new FuelDBAdapter($c->get(DB::class)),
            $c->get(UnitOfWork::class),
            [
                'foreign_key_check' => true,
                'transaction' => false
            ]
        ),
        $c->get(EventDispatcherInterface::class)
    ),

    // Persistence mode with disabled foreign keys check, strongly not recommended using,
    // but sometimes it might be faster than struggling with DB relations.
    'persistence_unsafe' => fn(Container $c) => new UowPersistence(
        new FuelEntityManager(
            new FuelDBAdapter($c->get(DB::class)),
            $c->get(UnitOfWork::class),
            [
                'foreign_key_check' => false,
                'transaction' => false
            ]
        ),
        $c->get(EventDispatcherInterface::class)
    ),

    // Returns list of all fixtures
    'fixtures' => fn(Container $c) => array_filter(
        $c->getKnownEntryNames(),
        function (string $entry) {
            $chunks = explode('\\', $entry);
            $shortClassName = end($chunks);
            return strpos($shortClassName, 'Fixture') !== false;
        }
    ),

    // Fixtures
    WhitelabelFixture::class => fn(Container $c) => WhitelabelFixture::initialize($c->get(ConfigContract::class)),
    RaffleFixture::class => fn(Container $c) => RaffleFixture::initialize($c->get(ConfigContract::class)),
    RaffleRuleFixture::class => fn(Container $c) => RaffleRuleFixture::initialize($c->get(ConfigContract::class)),
    RaffleRuleTierFixture::class => fn(Container $c) => RaffleRuleTierFixture::initialize(
        $c->get(ConfigContract::class)
    ),
    CurrencyFixture::class => fn(Container $c) => CurrencyFixture::initialize($c->get(ConfigContract::class)),
    RaffleTicketLineFixture::class => fn(Container $c) => RaffleTicketLineFixture::initialize($c->get(ConfigContract::class)),
    WhitelabelUserFixture::class => fn(Container $c) => WhitelabelUserFixture::initialize(
        $c->get(ConfigContract::class)
    ),
    WhitelabelTransactionFixture::class => fn(Container $c) => WhitelabelTransactionFixture::initialize(
        $c->get(ConfigContract::class)
    ),

    // Unsafe
    WlRaffleFixture::class => fn(Container $c) => WlRaffleFixture::initialize($c->get('unsafeConfig')),
    RaffleTicketFixture::class => fn(Container $c) => RaffleTicketFixture::initialize($c->get('unsafeConfig')),

    /**************************************************************************/
    /* AVOID DUPES FEATURE TOGGLE                                             */
    /**************************************************************************/

    FeatureToggle::class => fn(Container $c) => new FeatureToggle(),

    /**************************************************************************/
    /* MODELS THAT CAN BE REPLACED                                            */
    /**************************************************************************/

    Overridable::class => fn() => new Overridable([
        Currency::class => function (Currency $new): ?Model {
            return Currency::query()->where('code', $new->code)->get_one();
        }
    ]),

    /**************************************************************************/
    /* PSR EVENT DISPATCHER (SYMFONY)                                         */
    /**************************************************************************/
    EventDispatcherInterface::class => function (Container $c) {

        $symfonyDispatcher = new Symfony\Component\EventDispatcher\EventDispatcher();

        $symfonyDispatcher->addSubscriber(
            new FixturesEventListener(
                $c->get(FeatureToggle::class),
                new Matcher(
                    $c->get(Overridable::class),
                )
            )
        );

        return $symfonyDispatcher;
    },
];
