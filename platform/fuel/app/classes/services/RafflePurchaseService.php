<?php

namespace Services;

use Model_Whitelabel_User;
use Models\{Raffle, Whitelabel, WhitelabelBonus, WhitelabelRaffleTicket, WhitelabelUser, WhitelabelUserBonus};
use Repositories\{Orm\RaffleRepository, WhitelabelBonusRepository, WhitelabelUserBonusRepository};
use Services\Logs\FileLoggerService;
use Services_Raffle_Ticket;
use Services_Lcs_Raffle_Ticket_Free_Contract as FreeTicketsApi;
use Helpers_App;
use RaffleMailer;
use Container;
use Fuel\Core\Session;
use Wrappers\Db;
use Lotto_Settings;
use InvalidArgumentException;
use BadMethodCallException;
use GuzzleHttp\Exception\ServerException;
use RuntimeException;
use Exception;
use Throwable;

class RafflePurchaseService
{
    public const SCENARIO_FREE_TICKET_WITH_BONUS_WELCOME_REGISTER_WEBSITE = 'free_ticket_with_welcome_bonus_register_website';
    public const SCENARIO_FREE_TICKET_WITH_BONUS_WELCOME_REGISTER_API = 'free_ticket_with_welcome_bonus_register_api';

    /**
     * We don't use type 'open' but possible to introduce it in the future.
     */
    private const RAFFLE_OPEN_TYPE = 'open';
    private const RAFFLE_CLOSED_TYPE = 'closed';

    private string $raffleType = self::RAFFLE_CLOSED_TYPE;
    private string $scenario;

    /** Website Purchase by default */
    private bool $apiPurchase = false;

    private ?Raffle $raffle;
    private ?Whitelabel $whitelabel;
    private ?WhitelabelBonus $bonus;
    private ?WhitelabelUserBonus $userBonus;
    private ?WhitelabelRaffleTicket $ticket;

    private WhitelabelUser $userDao;
    private WhitelabelBonusRepository $whitelabelBonusRepository;
    private WhitelabelUserBonusRepository $whitelabelUserBonusRepository;
    private RaffleRepository $raffleRepository;

    private FreeTicketsApi $freeTicketsApi;
    private Services_Raffle_Ticket $purchaseTicketService;

    private Model_Whitelabel_User|WhitelabelUser $user;
    private FileLoggerService $fileLoggerService;
    private Db $db;

    public function __construct(
        WhitelabelUser $user,
        FreeTicketsApi $freeTicketsApi,
        Services_Raffle_Ticket $purchaseTicketService,
        FileLoggerService $fileLoggerService,
        Db $db,
    ) {
        $this->userDao = $user;
        $this->freeTicketsApi = $freeTicketsApi;
        $this->purchaseTicketService = $purchaseTicketService;
        $this->fileLoggerService = $fileLoggerService;
        $this->db = $db;

        $this->whitelabel = Container::get('whitelabel');
        $this->whitelabelBonusRepository = Container::get(WhitelabelBonusRepository::class);
        $this->whitelabelUserBonusRepository = Container::get(WhitelabelUserBonusRepository::class);
        $this->raffleRepository = Container::get(RaffleRepository::class);
    }

    public function getRaffle(): ?Raffle
    {
        return $this->raffle;
    }

    public function getBonus(): ?WhitelabelBonus
    {
        return $this->bonus;
    }

    /**
     * @throws InvalidArgumentException|Throwable
     */
    public function purchase(string $scenario, int $userId): void
    {
        if ($this->whitelabel === null) {
            throw new InvalidArgumentException('Unable to initialize RafflePurchaseService: Whitelabel is not set.');
        }

        $this->scenario = $scenario;
        $this->user = $this->userDao->get_by_id($userId);
        $this->setIsApi();

        switch ($this->scenario) {
            case self::SCENARIO_FREE_TICKET_WITH_BONUS_WELCOME_REGISTER_WEBSITE:
            case self::SCENARIO_FREE_TICKET_WITH_BONUS_WELCOME_REGISTER_API:
                $this->resolveWelcomeBonusScenario($userId);
                break;
            default:
                $message = sprintf(
                    'Unsupported purchase scenario: %s for User ID: %s',
                    $scenario,
                    $userId
                );

                throw new InvalidArgumentException($message);
        }
    }

    /**
     * Logic from wordpress/wp-content/themes/base/template-raffle-ticket-purchase.php
     * @throws Throwable
     */
    public function purchaseTicket(array $numbers): void
    {
        try {
            $this->ticket = $this->purchaseTicketService->purchase(
                $this->whitelabel->id,
                $this->raffle->slug,
                $this->raffleType,
                $numbers,
                $this->user->id
            );
        } catch (Throwable $exception) {
            # used when Numbers validation fails (Services_Raffle_Number_Validator)
            if ($exception instanceof InvalidArgumentException) {
                $this->handleError($exception->getMessage(), $exception, true);
            }
            # used when "Given numbers <%s> has been purchased by someone else. Please select new numbers." is thrown, only.
            if ($exception instanceof BadMethodCallException) {
                $this->handleError($exception->getMessage(), $exception, true);
            }
            # guzzle client exception (catches when any exception is thrown from lcs).
            if ($exception instanceof ServerException && $exception->getCode() == 507) {
                $this->handleError(_('Sorry, but some of your numbers have already been purchased. Please select a new ones and try again.'), $exception, true);
            }
            # unknown error
            if (Helpers_App::is_production_environment() || Helpers_App::is_staging_environment()) {
                $this->handleError(_('Unknown error. Please contact us!'), $exception, true);
            }

            throw $exception;
        }

        $user['balance'] = $this->user->balance;
        $user['bonus_balance'] = $this->user->bonusBalance;
        Lotto_Settings::getInstance()->set('user', $user);

        $this->sendMail();
    }

    /**
     * @throws BadMethodCallException|Throwable
     */
    public function purchaseFreeTicketWithWelcomeBonusRegister(int $userId): void
    {
        $this->setUser($userId);

        $this->verifyAndSetWelcomeBonus(WhitelabelBonus::WELCOME_REGISTER);
        $this->verifyAndSetWelcomeBonusRaffle();
        $numbers = $this->generateNumbers();

        if (empty($numbers)) {
            $message = sprintf(
                'Could not generate numbers for given Welcome Bonus ID: %s, User ID: %s, Raffle: %s',
                $this->bonus->id,
                $this->user->id,
                $this->raffle->name
            );

            throw new BadMethodCallException($message);
        }

        $this->addUserWelcomeBonus();
        $this->purchaseTicket($numbers);
        $this->whitelabelUserBonusRepository->useByUser($this->user->id);
        $this->db->commit_transaction();
    }

    public function setUser(int $userId): void
    {
        $this->user = $this->userDao->get_by_id($userId);
    }

    /**
     * @throws BadMethodCallException|Throwable
     */
    private function verifyAndSetWelcomeBonus(string $bonusType): void
    {
        /** @var WhitelabelBonus $bonus */
        $bonus = $this->whitelabelBonusRepository->findWelcomeBonusRaffleByBonusType($this->whitelabel->id, $bonusType);

        if (!$bonus) {
            throw new RuntimeException(sprintf('No Welcome Bonus found for requested bonus type: "%s".', $bonusType));
        }

        $webDisabled = !$this->apiPurchase && !$bonus->isWebsiteRegistrationAllowed();
        $apiDisabled = $this->apiPurchase && !$bonus->isApiRegistrationAllowed();

        if ($webDisabled || $apiDisabled) {
            throw new RuntimeException(sprintf('Welcome Bonus not allowed for current scenario: "%s".', $this->scenario));
        }

        if ($this->whitelabelUserBonusRepository->isUsedByUser(WhitelabelBonus::WELCOME, $this->user->id)) {
            $message = sprintf(
                'Welcome Bonus ID: %s already used by user ID: %s.',
                $bonus->id,
                $this->user->id
            );

            throw new BadMethodCallException($message);
        }

        $this->bonus = $bonus;
    }

    /**
     * @throws BadMethodCallException|Throwable
     */
    private function verifyAndSetWelcomeBonusRaffle(): void
    {
        /** @var Raffle $raffle */
        $raffle = $this->raffleRepository->getById($this->bonus->registerRaffleId);

        if (!$raffle) {
            $message = sprintf(
                'Could not find valid Raffle for valid Welcome Bonus ID: %s, User ID: %s',
                $this->bonus->id,
                $this->user->id
            );

            throw new Exception($message);
        }

        if (!$raffle || !$raffle->is_enabled) {
            $message = sprintf(
                'Raffle is temporary not playable for valid Welcome Bonus ID: %s, User ID: %s',
                $this->bonus->id,
                $this->user->id
            );

            throw new BadMethodCallException($message);
        }

        if ($raffle->is_sell_temporary_disabled) {
            $message = sprintf(
                'Raffle ticket purchase is closed for valid Welcome Bonus ID: %s, User ID: %s, Raffle: %s',
                $this->bonus->id,
                $this->user->id,
                $raffle->name
            );

            throw new BadMethodCallException($message);
        }

        $this->raffle = $raffle;
    }

    /**
     * @throws BadMethodCallException|Throwable
     */
    public function generateNumbers(int $count = 1): array
    {
        $availableNumbers = $this->getAvailableTicketNumbersForRaffle($this->raffle->slug);

        if (empty($availableNumbers)) {
            $message = sprintf(
                'No available numbers for valid Welcome Bonus ID: %s, User ID: %s, Raffle: %s',
                $this->bonus->id,
                $this->user->id,
                $this->raffle->name
            );

            throw new BadMethodCallException($message);
        }

        $linesCount = $this->raffle->max_bets;
        $availableNumbersCount = sizeof($availableNumbers);

        if ($linesCount >= $this->raffle->getFirstRule()->max_lines_per_draw) {
            $linesCount = $availableNumbersCount;
        }

        if ($linesCount >= $count) {
            $linesCount = $count;
        }

        shuffle($availableNumbers);

        return array_slice($availableNumbers, 0, $linesCount);
    }

    private function getAvailableTicketNumbersForRaffle(string $raffleSlug): array
    {
        $response = $this->freeTicketsApi->request($raffleSlug, $this->raffleType);

        return $response->get_data();
    }

    private function sendMail(): void
    {
        try {
            /** @var MailerService $mailerService */
            $mailerService = Container::get(MailerService::class);
            $raffleMailer = new RaffleMailer($mailerService, $this->fileLoggerService);
            $raffleMailer->sendPurchaseEmail($this->ticket);
        } catch (Throwable $exception) {
            $this->fileLoggerService->error(
                'Raffle ticket with token ' . $this->ticket->get_prefixed_token_attribute() . ' has been purchased, but we couldn\'t send confirmation email. ' .
                'Detailed message: ' . $exception->getMessage()
            );
        }
    }

    private function resolveWelcomeBonusScenario(int $userId): void
    {
        try {
            $this->purchaseFreeTicketWithWelcomeBonusRegister($userId);
        } catch (RuntimeException $exception) {
            if (!str_contains($exception->getMessage(), 'Welcome Bonus')) {
                throw $exception;
            }
        } catch (BadMethodCallException $exception) {
            if (str_contains($exception->getMessage(), 'Welcome Bonus')) {
                $this->handleError($exception->getMessage(), $exception);
            } else {
                throw $exception;
            }
        }
    }

    private function handleError(string $message, ?Throwable $exception = null, bool $setFlash = false, bool $throwException = false): void
    {
        $file = !empty($exception) ? $exception->getFile() : __FILE__;
        $line = !empty($exception) ? $exception->getLine() : __LINE__;
        $code = !empty($exception) ? $exception->getCode() : 0;
        $detailedMessageForLogs = !empty($exception) ? $exception->getMessage() : $message;
        $detailedMessageForLogs .= $file . ':' . $line;

        $isNotBalanceError = !str_contains($detailedMessageForLogs, 'Your balance is too low to proceed');
        $isNotPurchasedNumbersError = !str_contains($detailedMessageForLogs, 'has been purchased by someone else');
        $shouldLogInfo = $isNotBalanceError && $isNotPurchasedNumbersError;

        // Raffle on LCS is used by many clients
        // It can happen when two people buy the same numbers in the same time
        // Only the first one will be bought and the second will receive below error response
        $isNotTakenNumbersError = !str_contains($detailedMessageForLogs, 'Failed to insert numbers into raffle closed pool. Most likely another request has already inserted');
        $shouldLogError = $code >= 500 && $isNotTakenNumbersError;
        if ($shouldLogError) {
            $this->fileLoggerService->error($detailedMessageForLogs);
        } else if ($shouldLogInfo) {
            $this->fileLoggerService->info($detailedMessageForLogs);
        }

        if ($setFlash) {
            Session::set_flash('error', $message);
        }

        if ($throwException) {
            throw $exception;
        }
    }

    private function addUserWelcomeBonus(): void
    {
        $this->db->start_transaction();

        $this->userBonus = $this->whitelabelUserBonusRepository->insert(
            WhitelabelBonus::WELCOME,
            WhitelabelUserBonus::TYPE_REGISTER,
            WhitelabelUserBonus::TYPE_RAFFLE,
            $this->user->id
        );

        $this->purchaseTicketService->addUserBonus($this->userBonus);
    }

    private function setIsApi(): void
    {
        if ($this->scenario === self::SCENARIO_FREE_TICKET_WITH_BONUS_WELCOME_REGISTER_API) {
            $this->apiPurchase = true;
        }
    }
}
