<?php

namespace Tests\Feature\Wordpress\Wp_Content\Themes\Base\Box;

use Fuel\Core\Fuel;
use Fuel\Tasks\Seeders\Faireum_Raffle_Two_Closed;
use Fuel\Tasks\Seeders\Faireum_Raffle_Two_Closed_Prize_In_Kind;
use Fuel\Tasks\Seeders\Faireum_Replacement;
use Models\Raffle;
use Models\WhitelabelRaffleTicket;
use Repositories\Orm\RaffleRepository;
use Test_Feature;
use Tests\Fixtures\Raffle\RaffleTicketFixture;
use Lotto_Settings;
use Models\RafflePrize;
use Models\WhitelabelRaffleTicketLine;

/**
 * @covers wordpress/wp-content/themes/base/box/raffle-myaccount_tickets.php
 * @property WhitelabelRaffleTicketLine[] $tickets
 */
final class RaffleMyAccountTicketsDetailsTest extends Test_Feature
{
    private RaffleRepository $raffleRepository;
    private RaffleTicketFixture $raffleTicketFixture;
    private Raffle $raffle;
    private array $tickets;

    public function setUp(): void
    {
        parent::setUp();
        global $wp;

        /** @var RaffleRepository $raffleRepository */
        $this->raffleRepository = $this->container->get(RaffleRepository::class);
        /** @var RaffleTicketFixture $raffleTicketFixture */
        $this->raffleTicketFixture = $this->container->get(RaffleTicketFixture::class);

        $raffle = $this->raffleRepository->findOneBySlug(Raffle::FAIREUM_RAFFLE_SLUG);

        if (empty($raffle)) {
            Fuel::$env = Fuel::DEVELOPMENT;
            $faireumRaffleSeeders = [
                $this->container->get(Faireum_Raffle_Two_Closed::class),
                $this->container->get(Faireum_Replacement::class),
                $this->container->get(Faireum_Raffle_Two_Closed_Prize_In_Kind::class),
            ];
            foreach ($faireumRaffleSeeders as $faireumRaffleSeeder) {
                $faireumRaffleSeeder->execute();
            }
            Fuel::$env = Fuel::TEST;
        }

        $this->raffle = $this->raffleRepository->findOneBySlug(Raffle::FAIREUM_RAFFLE_SLUG);
        $this->tickets = $this->raffleTicketFixture->generateTickets($this->raffle, 'won', -1, 3);
        $this->tickets[0]->transaction->amount = 10;
        $this->tickets[0]->status = WhitelabelRaffleTicket::STATUS_WIN;
        $this->tickets[0]->currency_id = $this->raffle->currency_id;
        $this->tickets[0]->flush_cache();
        $this->tickets[0]->save();

        if (!function_exists('lotto_platform_get_permalink_by_slug')) {
            require_once(APPPATH . 'tests/feature/helpers/raffle/PermalinkBySlug.php');
        }

        $wp = new class ()
        {
            public array $query_vars = [];
        };
        $wp->query_vars['id'] = (int) $this->tickets[0]->token;
    }

    /** @test */
    public function getPreparedPrizeIsFaireumRaffleShouldCountCorrectlyAndDetectOthersPrizes(): void
    {
        $tierPrizeId = 0;
        /** @var WhitelabelRaffleTicketLine $line */
        foreach ($this->tickets[0]->lines as $lineId => $line) {
            // mock specific prizes
            if ($tierPrizeId === 0) {
                $tierPrizeId = $this->tickets[0]->lines[$lineId]->raffle_prize->tier->tier_prize->id;
            } else {
                $prize = RafflePrize::find($tierPrizeId);
                $line->raffle_prize_id = $prize->id;
                $line->raffle_prize = $prize;
                $tierPrizeId = 0;
            }

            // change names to test 2x prize
            $shouldChangeName = $lineId === array_key_last($this->tickets[0]->lines);
            if ($shouldChangeName) {
                $this->tickets[0]->lines[$lineId]->raffle_prize->tier->tier_prize->tier->tier_prize_in_kind->name = '1 x pc';
            } else {
                $this->tickets[0]->lines[$lineId]->raffle_prize->tier->tier_prize->tier->tier_prize_in_kind->name = '1 x laptop';
            }
        }

        $transactionToken = $this->tickets[0]->transaction->token;
        $transactionPrefix = $this->tickets[0]->whitelabel->prefix . 'P';
        $transactionTokenPrefixed = $transactionPrefix . $transactionToken;
        $this->tickets[0]->save();
        Lotto_Settings::getInstance()->set('whitelabel', $this->tickets[0]->whitelabel->to_array());
        Lotto_Settings::getInstance()->set('user', $this->tickets[0]->user->to_array());
        // get html output from site
        ob_start();
        require_once($_ENV['WP_PATH'] . '/wp-content/themes/base/box/raffle-myaccount_tickets_details.php');
        $content = ob_get_contents();
        ob_end_clean();

        $this->assertStringContainsString('Prize in kinds:', $content);
        $expectedUrl = "transactions/details/$transactionToken";
        $this->assertStringContainsString('<a href="' . $expectedUrl . '">' . $transactionTokenPrefixed . '</a>', $content);
        $this->assertStringContainsString('/play-raffle/faireum-raffle', $content);
        $this->assertStringContainsString('transactions-status transactions-status-1', $content);
        $this->assertStringContainsString('1 x pc', $content);
        $this->assertStringContainsString('2 x laptop', $content);
    }
}
