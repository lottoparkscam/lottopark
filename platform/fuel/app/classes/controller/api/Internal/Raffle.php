<?php

use Abstracts\Controllers\Internal\AbstractPublicController;
use Fuel\Core\Input;
use Fuel\Core\Response;
use Helpers\SanitizerHelper;
use Helpers\UserHelper;
use Models\RaffleRuleTier;
use Modules\Account\Reward\PrizeType;
use Repositories\Orm\RaffleRepository;
use Repositories\WhitelabelRaffleRepository;
use Services\Logs\FileLoggerService;
use Helpers\Wordpress\LanguageHelper;
use Fuel\Core\Security;

/**
 * @property WhitelabelRaffleRepository $whitelabelRaffleRepository
 */
class Controller_Api_Internal_Raffle extends AbstractPublicController
{
    private RaffleRepository $raffleRepository;
    private Services_Lcs_Raffle_Ticket_Free_Contract $availableTicketsApi;
    private FileLoggerService $logger;
    private Services_Currency_Calc $currencyCalculationService;

    public function before()
    {
        parent::before();
        $this->raffleRepository = Container::get(RaffleRepository::class);
        $this->availableTicketsApi = Container::get(Services_Lcs_Raffle_Ticket_Free_Contract::class);
        $this->logger = Container::get(FileLoggerService::class);
        $this->currencyCalculationService = Container::get(Services_Currency_Calc::class);
        $this->whitelabelRaffleRepository = Container::get(WhitelabelRaffleRepository::class);
    }

    public function get_index(): Response
    {
        $slug = SanitizerHelper::sanitizeSlug(Input::get('slug'));
        $raffle = $this->raffleRepository->findOneBySlug($slug);

        if (empty($raffle)) {
            return $this->returnResponse([], 404);
        }

        $poolIsSoldOut = $raffle->maxBets === $raffle->drawLinesCount;
        $isRaffleEnabled = (bool)$raffle->is_turned_on;
        if (!$isRaffleEnabled) {
            return $this->returnResponse([
                'name' => $raffle->name,
                'slug' => $raffle->slug,
                'isEnabled' => false,
                'amountOfAvailableNumbers' => 0,
            ]);
        }

        try {
            $availableTicketsResponse = $this->availableTicketsApi->request($raffle->slug);
            $availableTickets = array_values($availableTicketsResponse->get_data());
            $wholePull = range(1, $raffle->maxBets);
            $takenNumbers = !$poolIsSoldOut ?
                array_values(array_diff($wholePull, $availableTickets)) :
                $wholePull;

            $availableTicketsBody = $availableTicketsResponse->get_body();
            $amountOfAvailableNumbers = count($availableTicketsBody['data']['free_numbers']) ?? 0;
        } catch (Throwable $exception) {
            $takenNumbers = [];
            $amountOfAvailableNumbers = 0;
            $this->logger->error('Cannot get free numbers from LCS');
        }

        $isPrizeInTickets = function (RaffleRuleTier $tier) {
            $tier_prize_in_kind = $tier->tier_prize_in_kind;
            return !empty($tier_prize_in_kind) && $tier_prize_in_kind->type === PrizeType::TICKET;
        };

        $isPrizeInKind = function (RaffleRuleTier $tier) {
            $tier_prize_in_kind = $tier->tier_prize_in_kind;
            return !empty($tier_prize_in_kind);
        };

        # It was assumed that first tier is always main prize (suppose to be), then we check it it prize in kind
        $mainPrizeTier = array_filter($raffle->getFirstRule()->tiers, function (RaffleRuleTier $tier) {
            return $tier->is_main_prize;
        });
        /** @var RaffleRuleTier $mainPrizeTier */
        $mainPrizeTier = reset($mainPrizeTier);

        $prize = match (true) {
            $isPrizeInTickets($mainPrizeTier) => $mainPrizeTier->tier_prize_in_kind->name,
            $isPrizeInKind($mainPrizeTier) => $mainPrizeTier->tier_prize_in_kind->name . '(' .
                Lotto_View::format_currency(
                    $mainPrizeTier->tier_prize_in_kind->per_user,
                    $raffle->currency->code
                ) . ')',
            default => Lotto_View::format_currency($raffle->main_prize, $raffle->currency->code),
        };

        $usersCurrencyCode = Helpers_Currency::getUserCurrencyTable()['code'];
        $linePrice = $raffle->getFirstRule()->line_price_with_fee;
        $linePriceCalculated = $this->currencyCalculationService->convert_to_any(
            $linePrice,
            $raffle->getFirstRule()->currency->code,
            $usersCurrencyCode
        );
        $linePriceFormatted = Lotto_View::format_currency($linePriceCalculated, $usersCurrencyCode, true);
        $defaultPlaySummary = Lotto_View::format_currency(0, $usersCurrencyCode, true);

        $user = UserHelper::getUser();
        $isUserLogged = UserHelper::isUserLogged();
        $usersBonusBalanceAmount = $isUserLogged ? $user->bonusBalance : 0.0;

        $jsCurrencyFormat = LanguageHelper::getCurrentWhitelabelLanguage()['js_currency_format'];

        $whitelabelRaffle = $this->whitelabelRaffleRepository->findOneByRaffleIdForCurrentWhitelabel($raffle->id);
        $iEnabled = !empty($whitelabelRaffle) &&
            $raffle->isEnabled &&
            $whitelabelRaffle->isEnabled;
        $isSellEnabled = $raffle->isSellEnabled && !$raffle->isSellLimitationEnabled;

        $csrfToken = Security::fetch_token();

        return $this->returnResponse([
            'name' => $raffle->name,
            'slug' => $raffle->slug,
            'isEnabled' => $iEnabled,
            'takenNumbers' => $takenNumbers,
            'isSellEnabled' => $isSellEnabled,
            'amountOfAvailableNumbers' => $amountOfAvailableNumbers,
            'prize' => $prize,
            'linePrice' => $linePrice,
            'linePriceInUsersCurrency' => $linePriceCalculated,
            'linePriceFormatted' => $linePriceFormatted,
            'defaultPlaySummary' => $defaultPlaySummary,
            'poolIsSoldOut' => $poolIsSoldOut,
            'soldOutText' => _('Lottery is temporary not playable due prizes calculation.'),
            'isUserLogged' => $isUserLogged,
            'usersCurrencyCode' => $usersCurrencyCode,
            'usersBonusBalanceAmount' => $usersBonusBalanceAmount,
            'jsCurrencyFormat' => $jsCurrencyFormat,
            'csrfToken' => $csrfToken,
        ]);
    }
}
