<?php

if (!function_exists('_')) {
    function _(...$args)
    {
        return $args[0];
    }
}

use Classes\Orm\OrmModelInterface;
use Events\NullEventDispatcher;
use Fuel\Core\DB;
use Models\Raffle;
use Models\Lottery;
use Models\Currency;
use Models\RaffleRule;
use Models\Whitelabel;
use Models\RafflePrize;
use Models\RaffleProvider;
use Models\RaffleRuleTier;
use Models\WhitelabelUser;
use Models\WhitelabelRaffle;
use Models\WhitelabelUserGroup;
use Models\WhitelabelRaffleTicket;
use Fuel\Tasks\Factory\Utils\Faker;
use Modules\Account\Reward\PrizeType;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\StreamInterface;
use Models\RaffleRuleTierInKindPrize;
use Models\WhitelabelRaffleTicketLine;
use Psr\Http\Message\ResponseInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Fuel\Core\Autoloader;
use Carbon\Carbon;
use Tests\Unit\BypassFinals;

Autoloader::add_namespace('Fuel\\Tasks\\Factory\\Utils', APPPATH . 'tasks/factory/utils/');
Autoloader::add_classes([
    'Lotto_Helper' => APPPATH . 'classes/test/mock/lottohelper.php',
    'Log' => APPPATH . 'classes/test/mock/logger.php'
]);
BypassFinals::enable();
BypassFinals::setWhitelist([
    '*/repositories/*',
    '*/models/*',
    '*/helpers/*',
    '*/services/*',
    '*/fixtures/*',
    '*/guzzlehttp/psr7/*'
]);

/** Base class for unit tests. */
abstract class Test_Unit extends Test_Base
{
    protected \DI\Container $container;

    public function setUp(): void
    {
        parent::setUp();
        DB::$query_count = 0;
        $this->container = Container::forge(false);
        $this->container->set('whitelabel', $this->get_whitelabel());
        $this->container->set('domain', Faker::forge()->domainName());
        $this->container->set(EventDispatcherInterface::class, new NullEventDispatcher());
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Carbon::setTestNow();
        if (DB::$query_count > 0) {
            $this->markTestIncomplete(
                'This test is invalid, due it attempts to reach external resources (DB in this case). Unit test suppose to work on stubs/mocks instead of real resources!' . PHP_EOL .
                DB::last_query()
            );
        }
        DB::$query_count = 0;
    }

    protected function random_id(): int
    {
        return Faker::forge()->numberBetween(100, 10000);
    }

    protected function random_date(): string
    {
        return Faker::forge()->date('Y-m-d H:i:s');
    }

    protected function get_whitelabel(array $props = []): Whitelabel
    {
        $wl = new Whitelabel();
        $wl->id = $this->random_id();
        $wl->domain = Faker::forge()->domainName();
        $wl->currency = $this->get_currency(['code' => 'EUR']);
        $wl->managerSiteCurrencyId = $wl->currency->id;
        $wl->margin = 11.00;
        return $wl;
    }

    protected function get_raffle(array $props = []): Raffle
    {
        $currency = new Currency();
        $currency->id = $this->random_id();
        $currency->code = 'USD';
        $currency->rate = 1.0;

        $rule = new RaffleRule();
        $rule->id = $this->random_id();
        $rule->line_price = 10;
        $rule->currency_id = $currency->id;
        $rule->currency = $currency;
        $rule->fee = 0.5;
        $rule->max_lines_per_draw = 1000;

        $raffle = new Raffle();
        $raffle->id = $this->random_id();
        $raffle->rules = [$rule];
        $raffle->raffle_rule_id = $rule->id;
        $raffle->currency_id = $currency->id;
        $raffle->currency = $currency;
        $raffle->timezone = 'Europe/Paris';
        $raffle->slug = Faker::forge()->slug();

        $provider = new RaffleProvider();
        $provider->min_bets = 1;
        $provider->max_bets = $raffle->getFirstRule()->max_lines_per_draw;

        $wl_raffle = new WhitelabelRaffle();
        $wl_raffle->whitelabel = $this->get_whitelabel();
        $wl_raffle->whitelabel_id = $wl_raffle->whitelabel->id;
        $wl_raffle->provider = $provider;

        $raffle->whitelabel_raffle = $wl_raffle;

        $this->assign_props($raffle, $props);

        return $raffle;
    }

    protected function get_user(array $props = []): WhitelabelUser
    {
        $currency = new Currency();
        $currency->id = $this->random_id();
        $currency->code = 'EUR';
        $currency->rate = 0.8418;

        $user = new WhitelabelUser();
        $user->id = $this->random_id();
        $user->currency = $currency;
        $user->currency_id = $currency->id;
        $user->timezone = 'Europe/Paris';

        $user->group = new WhitelabelUserGroup([
            'id' => $this->random_id(),
            'whitelabel_id' => $this->random_id(),
            'name' => Faker::forge()->text(5),
            'prize_payout_percent' => Faker::forge()->numberBetween(1, 100),
        ]);


        $this->assign_props($user, $props);

        return $user;
    }

    public function get_usd_currency(): Currency
    {
        $currency = new Currency();
        $currency->id = 1;
        $currency->code = 'USD';
        $currency->rate = 1.0;
        return $currency;
    }

    public function get_currency(array $props = []): Currency
    {
        $currency = new Currency();
        $currency->id = 1;
        $currency->code = 'USD';
        $currency->rate = 1.0;
        $this->assign_props($currency, $props);
        return $currency;
    }

    protected function get_ticket(array $props = [], bool $isBonus = false): WhitelabelRaffleTicket
    {
        $amountType = 'amount';
        if ($isBonus) {
            $amountType = 'bonus_amount';
        }
        $ticket = new WhitelabelRaffleTicket();

        $ticket->id = $this->random_id();

        $ticket->uuid = Faker::forge()->uuid();

        $ticket->user = $this->get_user();
        $ticket->whitelabel_user_id = $ticket->user->id;

        $ticket->whitelabel = $this->get_whitelabel();
        $ticket->whitelabel_id = $ticket->whitelabel->id;

        $ticket->$amountType = rand(1, 100);
        $ticket->currency = $this->get_usd_currency();
        $ticket->currency_id = $ticket->currency->id;
        $ticket->raffle = $this->get_raffle();
        $ticket->raffle_id = $ticket->raffle->id;
        $ticket->rule = $ticket->raffle->getFirstRule();
        $ticket->raffle_rule_id = $ticket->raffle->getFirstRule()->id;

        $ticket->transaction = $this->container->get(Factory_Orm_Transaction::class)->build(false);

        $ticket->lines = array_map(function (int $number) use ($amountType, $ticket) {
            $new_prize = function (WhitelabelRaffleTicketLine $line) use ($amountType, $ticket) {
                $prize = new RafflePrize();
                $prize->per_user = rand(10, 1000);
                $prize->currency = $this->get_usd_currency();
                $prize->currency_id = $prize->currency->id;
                $ticket->prize += $prize->per_user;
                $line->$amountType = $prize->per_user;
                return $prize;
            };

            $line = new WhitelabelRaffleTicketLine();
            $line->id = $this->random_id();
            $line->number = $number;
            $line->status = rand(0, 2);
            $line->$amountType = $line->status === Helpers_General::TICKET_STATUS_WIN ? rand(1, 10) : 0;
            $line->raffle_prize = $line->status === Helpers_General::TICKET_STATUS_WIN ? $new_prize($line) : null;
            $line->ticket = $ticket;
            return $line;
        }, [rand(0, 10), rand(10, 20), rand(20, 30), rand(30, 40), rand(40, 50)]);

        return $ticket;
    }

    private function assign_props(OrmModelInterface $model, array $props = []): void
    {
        if (empty($props)) {
            return;
        }
        foreach ($props as $prop => $value) {
            $model->$prop = $value;
        }
    }

    protected function calculate_currency(float $value, string $to_currency): float
    {
        switch ($to_currency) {
            case 'EUR': $divider = 0.8; break;
            case 'USD': $divider = 1; break;
            case 'PLN': $divider = 0.25; break;
            default:
                throw new InvalidArgumentException(sprintf('%s currency not supported in base test', $to_currency));
        }

        return $value / $divider;
    }

    protected function get_line(string $prize_in_kind_type = PrizeType::TICKET, ?WhitelabelUser $user = null): WhitelabelRaffleTicketLine
    {
        $ticket = $this->get_ticket();
        $ticket->user = $user ?? $this->get_user();
        $line = new WhitelabelRaffleTicketLine();
        $tier = new RaffleRuleTier();
        $tier_prize_in_kind = new RaffleRuleTierInKindPrize();
        $tier_prize_in_kind->type = $prize_in_kind_type;
        $tier->tier_prize_in_kind = $tier_prize_in_kind;
        $raffle_prize = new RafflePrize();
        $raffle_prize->tier = $tier;
        $line->raffle_prize = $raffle_prize;
        $line->ticket = $ticket;
        return $line;
    }

    protected function get_lottery(array $props = []): Lottery
    {
        $lottery = new Lottery($props);
        $lottery->is_enabled = (bool)rand(0, 1);
        $lottery->name = Faker::forge()->name();
        $lottery->slug = Faker::forge()->slug();
        $lottery->price = 10;
        return $lottery;
    }

    protected function mock_lcs_response(array $data): Services_Lcs_Client_Response
    {
        /** @var ResponseInterface|MockObject $response */
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn(json_encode($data));
        return new Services_Lcs_Client_Response($response);
    }

    protected function mockResponse(array $data): ResponseInterface
    {
        $stream = $this->createMock(StreamInterface::class);

        $stream->method('getContents')->willReturn(json_encode($data));
        
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        return $response;
    }

    protected function setFakeCarbon(string $date, ?string $timezone = null): void
    {
        // we have to set default timezone otherwise Carbon::now() with timezone converts date from UTC
        if (!empty($timezone)) {
            date_default_timezone_set($timezone);
        }

        Carbon::setTestNow(Carbon::parse($date, $timezone));
    }
}
