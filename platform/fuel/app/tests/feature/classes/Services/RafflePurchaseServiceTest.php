<?php

namespace Tests\Unit\Classes\Services;

use Models\{
    Raffle,
    WhitelabelBonus,
    WhitelabelUser,
    WhitelabelUserBonus
};
use Repositories\{
    WhitelabelBonusRepository,
    WhitelabelUserBonusRepository
};
use Tests\Fixtures\{
    Raffle\RaffleFixture,
    WhitelabelBonusFixture,
    WhitelabelUserBonusFixture,
    WhitelabelUserFixture
};
use Services\{
    Logs\FileLoggerService,
    RafflePurchaseService
};
use Services_Raffle_Ticket;
use Services_Lcs_Raffle_Ticket_Free_Contract as FreeTicketsApi;
use Test_Feature;
use PHPUnit\Framework\MockObject\MockObject;
use Wrappers\Db;
use BadMethodCallException;
use RuntimeException;

final class RafflePurchaseServiceTest extends Test_Feature
{
    private WhitelabelUser $user;
    private Raffle $raffle;

    private WhitelabelUserFixture $userFixture;
    private RaffleFixture $raffleFixture;
    private WhitelabelBonusFixture $whitelabelBonusFixture;
    private WhitelabelUserBonusFixture $whitelabelUserBonusFixture;
    private WhitelabelUserBonusRepository $whitelabelUserBonusRepository;

    private MockObject|RafflePurchaseService $rafflePurchaseService;
    private MockObject|WhitelabelBonusRepository|null $whitelabelBonusRepositoryMock = null;

    private string $raffleSlug = 'gg-world-raffle';

    public function setUp(): void
    {
        parent::setUp();

        $this->freeTicketsApiMock = $this->createMock(FreeTicketsApi::class);
        $this->purchaseTicketServiceMock = $this->createMock(Services_Raffle_Ticket::class);
        $this->fileLoggerServiceMock = $this->createMock(FileLoggerService::class);

        $this->userFixture = $this->container->get(WhitelabelUserFixture::class);
        $this->raffleFixture = $this->container->get(RaffleFixture::class);
        $this->whitelabelBonusFixture = $this->container->get(WhitelabelBonusFixture::class);
        $this->whitelabelUserBonusFixture = $this->container->get(WhitelabelUserBonusFixture::class);

        $this->whitelabelUserBonusRepository = $this->container->get(WhitelabelUserBonusRepository::class);

        $this->user = $this->userFixture->with('basic')->createOne();
        $this->raffle = $this->raffleFixture->with(
            RaffleFixture::BASIC,
            RaffleFixture::GGWORLD,
            RaffleFixture::PLAYABLE,
        )->createOne();
    }

    /** @test */
    public function purchase__FreeTicketForRegistrationWithWelcomeBonus__ThrowNoWelcomeBonusFoundForRegisterTypeException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No Welcome Bonus found for requested bonus type: "register".');

        $this->whitelabelBonusRepositoryMock = $this->getMockBuilder(WhitelabelBonusRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findWelcomeBonusRaffleByBonusType'])
            ->getMock();

        $this->mockRafflePurchaseService();

        $this->rafflePurchaseService->purchaseFreeTicketWithWelcomeBonusRegister($this->user->id);
    }

    /** @test */
    public function purchase__FreeTicketForRegistrationWithWelcomeBonus__ThrowWelcomeBonusAlreadyUsedException(): void
    {
        $bonus = $this->whitelabelBonusFixture
            ->with('basic')
            ->withRegisterRaffle(WhitelabelBonus::WELCOME, $this->raffle)
            ->createOne();

        $message = sprintf(
            'Welcome Bonus ID: %s already used by user ID: %s.',
            $bonus->id,
            $this->user->id
        );

        $this->expectExceptionMessage($message);

        $this->whitelabelUserBonusFixture
            ->with('basic')
            ->withType('register')
            ->withLotteryType('raffle')
            ->withUser($this->user, true)
            ->createOne();

        $this->mockRafflePurchaseService();

        $this->rafflePurchaseService->purchaseFreeTicketWithWelcomeBonusRegister($this->user->id);

        $userBonus = $this->findUserBonus();

        $this->assertFalse($userBonus->isUsedByUser($this->user->id));
    }

    /** @test */
    public function purchase__FreeTicketForRegistrationWithWelcomeBonus__RaffleIsTemporaryNotPlayableForValidWelcomeBonus(): void
    {
        $raffleDisabled = $this->raffleFixture
            ->with(
                RaffleFixture::BASIC,
                RaffleFixture::GGWORLD,
            )
            ->withDisabled()
            ->createOne();

        $bonus = $this->whitelabelBonusFixture
            ->with('basic')
            ->withRegisterRaffle(WhitelabelBonus::WELCOME, $raffleDisabled)
            ->createOne();

        $message = sprintf('Raffle is temporary not playable for valid Welcome Bonus ID: %s, User ID: %s', $bonus->id, $this->user->id);

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage($message);
        $this->mockRafflePurchaseService();

        $this->rafflePurchaseService->purchaseFreeTicketWithWelcomeBonusRegister($this->user->id);

        $userBonus = $this->findUserBonus();

        $this->assertNull($userBonus);
    }

    /** @test */
    public function purchase__FreeTicketForRegistrationWithWelcomeBonus__RaffleTicketPurchaseIsClosedForValidWelcomeBonus(): void
    {
        $raffleTemporaryDisabled = $this->raffleFixture
            ->with(
                RaffleFixture::GGWORLD,
                RaffleFixture::TEMPORARY_DISABLED,
            )
            ->createOne();

        $bonus = $this->whitelabelBonusFixture
            ->with('basic')
            ->withRegisterRaffle(WhitelabelBonus::WELCOME, $raffleTemporaryDisabled)
            ->createOne();

        $message = sprintf(
            'Raffle ticket purchase is closed for valid Welcome Bonus ID: %s, User ID: %s, Raffle: %s',
            $bonus->id,
            $this->user->id,
            $raffleTemporaryDisabled->name,
        );

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage($message);
        $this->mockRafflePurchaseService();

        $this->rafflePurchaseService->purchaseFreeTicketWithWelcomeBonusRegister($this->user->id);

        $userBonus = $this->findUserBonus();

        $this->assertNull($userBonus);
    }

    /** @test */
    public function purchase__FreeTicketForRegistrationWithWelcomeBonus__CouldNotGenerateNumbersForGivenWelcomeBonus(): void
    {
        $bonus = $this->whitelabelBonusFixture
            ->with('basic')
            ->withRegisterRaffle(WhitelabelBonus::WELCOME, $this->raffle)
            ->createOne();

        $message = sprintf(
            'Could not generate numbers for given Welcome Bonus ID: %s, User ID: %s, Raffle: %s',
            $bonus->id,
            $this->user->id,
            $this->raffle->name
        );

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage($message);
        $this->mockRafflePurchaseService([]);

        $this->rafflePurchaseService->purchaseFreeTicketWithWelcomeBonusRegister($this->user->id);

        $userBonus = $this->findUserBonus();

        $this->assertNull($userBonus);
    }

    /** @test */
    public function purchase__FreeTicketForRegistrationWithWelcomeBonus__ReturnValidBonusAndRaffle(): void
    {
        $this->whitelabelBonusFixture
            ->with('basic')
            ->withRegisterRaffle(WhitelabelBonus::WELCOME, $this->raffle)
            ->createOne();

        $this->mockRafflePurchaseService();

        $this->rafflePurchaseService->purchaseFreeTicketWithWelcomeBonusRegister($this->user->id);

        $bonus = $this->rafflePurchaseService->getBonus();
        $raffle = $this->rafflePurchaseService->getRaffle();
        $userBonus = $this->findUserBonus();

        $this->assertNotNull($bonus);
        $this->assertSame($bonus->registerRaffleId, $raffle->id);
        $this->assertSame($this->raffleSlug, $raffle->slug);
        $this->assertTrue($userBonus->isUsedByUser($this->user->id));
    }

    /** @test */
    public function purchase__FreeTicketForRegistrationWithWelcomeBonus__CannotBeReusedByUser(): void
    {
        $bonus = $this->whitelabelBonusFixture
            ->with('basic')
            ->withRegisterRaffle(WhitelabelBonus::WELCOME, $this->raffle)
            ->createOne();

        $message = sprintf(
            'Welcome Bonus ID: %s already used by user ID: %s.',
            $bonus->id,
            $this->user->id
        );

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage($message);
        $this->mockRafflePurchaseService();

        $this->rafflePurchaseService->purchaseFreeTicketWithWelcomeBonusRegister($this->user->id);

        $userBonusPurchase1 = $this->findUserBonus();
        $this->assertTrue($userBonusPurchase1->isUsedByUser($this->user->id));

        $this->rafflePurchaseService->purchaseFreeTicketWithWelcomeBonusRegister($this->user->id);
    }

    private function mockRafflePurchaseService(array $numbers = [1]): void
    {
        if ($this->whitelabelBonusRepositoryMock) {
            $this->container->set(WhitelabelBonusRepository::class, $this->whitelabelBonusRepositoryMock);
        }

        $this->rafflePurchaseService = $this->getMockBuilder(RafflePurchaseService::class)
            ->setConstructorArgs([
                $this->container->get(WhitelabelUser::class),
                $this->freeTicketsApiMock,
                $this->purchaseTicketServiceMock,
                $this->fileLoggerServiceMock,
                $this->container->get(Db::class)
            ])
            ->onlyMethods(['purchaseTicket', 'generateNumbers'])
            ->getMock();

        $this->rafflePurchaseService->method('generateNumbers')->willReturn($numbers);

        if ($this->whitelabelBonusRepositoryMock) {
            $this->whitelabelBonusRepositoryMock
                ->method('findWelcomeBonusRaffleByBonusType')
                ->willReturn(null);
        }
    }

    private function findUserBonus(): ?WhitelabelUserBonus
    {
        return $this->whitelabelUserBonusRepository->findTypeRegisterRaffleByUserId($this->user->id);
    }
}
