<?php

namespace Tests\Feature\Helpers\Raffle;

use Tests\Fixtures\{
    CurrencyFixture,
    Raffle\RaffleFixture,
    Raffle\RaffleTicketFixture,
    WhitelabelTransactionFixture,
    WhitelabelFixture,
};
use Models\{
    WhitelabelTransaction,
    WhitelabelRaffleTicket
};
use Services\{
    Logs\FileLoggerService,
    MailerService
};
use Fuel\Core\{
    Package,
    Cache
};
use LanguageHelper;
use RaffleMailer;
use Test_Feature;

final class RaffleMailerTest extends Test_Feature
{
    private const DOMAIN = 'lottopark.loc';
    private const ACCOUNT_URL_EN = 'https://' . self::DOMAIN . '/account/';
    private const ACCOUNT_URL_PL = 'https://' . self::DOMAIN . '/pl/konto/';

    private const TICKET_DATE = '2021-07-26 09:54:43';

    private WhitelabelRaffleTicket $ticket;
    private ?WhitelabelTransaction $transaction = null;

    private CurrencyFixture $currencyFixture;
    private WhitelabelFixture $whitelabelFixture;
    private WhitelabelTransactionFixture $transactionFixture;
    private RaffleFixture $raffleFixture;
    private RaffleTicketFixture $ticketFixture;

    private MailerService $mailerService;
    private FileLoggerService $fileLoggerService;

    public function setUp(): void
    {
        parent::setUp();

        Package::load('email');

        $this->mailerService = $this->createConfiguredMock(MailerService::class, ['sendPublic' => true]);
        $this->fileLoggerService = $this->createMock(FileLoggerService::class);

        $this->whitelabelFixture = $this->container->get(WhitelabelFixture::class);
        $this->transactionFixture = $this->container->get(WhitelabelTransactionFixture::class);
        $this->currencyFixture = $this->container->get(CurrencyFixture::class);
        $this->raffleFixture = $this->container->get(RaffleFixture::class);
        $this->ticketFixture = $this->container->get(RaffleTicketFixture::class);

        $this->transaction = $this->transactionFixture
            ->with('basic')
            ->makeOne(['date' => self::TICKET_DATE]);

        $whitelabel = $this->whitelabelFixture
            ->makeOne([
                'name' => 'LottoPark',
                'domain' => self::DOMAIN,
                'theme' => 'lottopark',
            ]);

        $whitelabel->id = 1;

        $currency = $this->currencyFixture
            ->with(CurrencyFixture::USD)
            ->makeOne();

        $raffle = $this->raffleFixture
            ->with(RaffleFixture::BASIC)
            ->makeOne(['name' => 'Test Raffle']);

        $this->ticketFixture
            ->with(RaffleTicketFixture::USER)
            ->withWhitelabel($whitelabel)
            ->withCurrency($currency)
            ->withRaffle($raffle);
    }

    /**
     * @test
     * @dataProvider transactionAndLanguageDataProvider
     */
    public function generatePurchaseEmail_OneTicket_PreparesCorrectEmailData(callable $addTransaction, array $languageData): void
    {
        $addTransaction($this->ticketFixture);

        $this->ticket = $this->ticketFixture
            ->withNumbers([10])
            ->makeOne([
                'amount' => 2.00,
                'created_at' => self::TICKET_DATE
            ]);

        $this->ticket->user->language->code = $languageData['languageCode'];

        if ($languageData['languageCode'] === 'pl_PL') {
            $this->setPermalinksCachePolish();
        }

        $expectedMailTitle = 'LottoPark - Ticket purchased successfully';
        $expectedMailContent = file_get_contents($languageData['testRafflePurchaseEmailBody']);

        $raffleMailer = new RaffleMailer($this->mailerService, $this->fileLoggerService);

        $emailData = $raffleMailer->generatePurchaseEmail($this->ticket);

        $this->assertEquals($expectedMailTitle, $emailData['title']);
        $this->assertEquals(preg_replace('/\s/', "", $expectedMailContent), preg_replace('/\s/', "", $emailData['body_html']));
        $this->assertStringContainsString($languageData['myAccountUrl'], $emailData['body_html']);
    }

    /**
     * @test
     * @dataProvider transactionDataProvider
     */
    public function generatePurchaseEmail_MultipleTickets_PreparesCorrectEmailData(callable $addTransaction): void
    {
        $addTransaction($this->ticketFixture);

        $this->ticket = $this->ticketFixture
            ->withNumbers([10, 35, 101])
            ->makeOne([
                'amount' => 30.00,
                'created_at' => self::TICKET_DATE
            ]);

        $expectedMailTitle = 'LottoPark - Ticket purchased successfully';
        $expectedMailContent = file_get_contents(__DIR__ . '/testMultipleTicketsRafflePurchaseEmailBody.html');

        $raffleMailer = new RaffleMailer($this->mailerService, $this->fileLoggerService);
        $emailData = $raffleMailer->generatePurchaseEmail($this->ticket);

        $this->assertEquals($expectedMailTitle, $emailData['title']);
        $this->assertEquals(preg_replace('/\s/', "", $expectedMailContent), preg_replace('/\s/', "", $emailData['body_html']));
        $this->assertStringContainsString(self::ACCOUNT_URL_EN . 'tickets/', $emailData['body_html']);
    }

    /** @test */
    public function sendPurchaseEmail_MailerDoesNotWork_EmailDoesNotSend(): void
    {
        $this->ticket = $this->ticketFixture
            ->withNumbers([10])
            ->makeOne([
                'amount' => 2.00,
                'created_at' => self::TICKET_DATE
            ]);

        $mailerServiceNotWorking = $this->createConfiguredMock(MailerService::class, ['sendPublic' => false]);

        $raffleMailer = new RaffleMailer($mailerServiceNotWorking, $this->fileLoggerService);
        $isEmailSent = $raffleMailer->sendPurchaseEmail($this->ticket);
        $this->assertFalse($isEmailSent);
    }

    public function transactionDataProvider(): array
    {
        return [
            'transaction' => [$this->raffleTicketFixtureTransaction()],
            'no transaction' => [$this->raffleTicketFixtureTransaction(false)]
        ];
    }

    public function transactionAndLanguageDataProvider(): array
    {
        $getLanguageData = function (string $languageCode): array {
            $languageData = [
                'en_GB' => [
                    'languageCode' => 'en_GB',
                    'myAccountUrl' => self::ACCOUNT_URL_EN . 'tickets/',
                    'testRafflePurchaseEmailBody' => __DIR__ . '/testRafflePurchaseEmailBody.html'
                ],
                'pl_PL' => [
                    'languageCode' => 'pl_PL',
                    'myAccountUrl' => self::ACCOUNT_URL_PL . 'tickets/',
                    'testRafflePurchaseEmailBody' => __DIR__ . '/testRafflePurchaseEmailBodyPL.html'
                ]
            ];

            if (isset($languageData[$languageCode])) {
                return $languageData[$languageCode];
            }

            return $languageData['en_GB'];
        };

        return [
            'transaction en_GB' => [$this->raffleTicketFixtureTransaction(), $getLanguageData('en_GB')],
            'no transaction en_GB' => [$this->raffleTicketFixtureTransaction(false), $getLanguageData('en_GB')],
            'transaction pl_PL' => [$this->raffleTicketFixtureTransaction(), $getLanguageData('pl_PL')],
            'no transaction pl_PL' => [$this->raffleTicketFixtureTransaction(false), $getLanguageData('pl_PL')]
        ];
    }

    private function raffleTicketFixtureTransaction(bool $withTransaction = true): callable
    {
        if ($withTransaction) {
            return function (RaffleTicketFixture $ticketFixture): void {
                if ($this->transaction) {
                    $ticketFixture->withTransaction($this->transaction);
                }
            };
        }

        return function (RaffleTicketFixture $ticketFixture): void {
        };
    }

    private function setPermalinksCachePolish(): void
    {
        $prefix = 'get_permalinks_by_slug';
        $domain = str_replace('.', '_', self::DOMAIN);
        $language = LanguageHelper::getLanguageCodeFromLocale('pl_PL');

        $cacheName = "wordpress_{$prefix}_{$domain}_{$language}";

        $cache = [
            'page' => [
                'account' => self::ACCOUNT_URL_PL
            ]
        ];

        Cache::set($cacheName, $cache);
    }
}
