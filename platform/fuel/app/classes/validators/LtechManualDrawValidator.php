<?php

namespace Validators;

use Carbon\Carbon;
use Exception;
use Fuel\Core\Input;
use Helpers_Time;
use Lotto_Security;
use Models\AdminUser;
use Models\Lottery;
use Models\LotteryTypeData;
use Repositories\AdminUserRepository;
use Repositories\LotteryRepository;
use Repositories\LotteryTypeDataRepository;
use Repositories\Orm\CurrencyRepository;
use Validators\Rules\Amount;
use Validators\Rules\CurrencyCode;
use Validators\Rules\Date;
use Validators\Rules\LotteryAdditionalNumber;
use Validators\Rules\Password;
use Validators\Rules\Slug;
use Validators\Rules\Timezone;

class LtechManualDrawValidator extends Validator
{
    protected static string $method = Validator::JSON;
    private LotteryRepository $lotteryRepository;
    private AdminUserRepository $adminUserRepository;
    private LotteryTypeDataRepository $lotteryTypeDataRepository;
    private CurrencyRepository $currencyRepository;

    public function __construct(
        LotteryRepository $lotteryRepository,
        AdminUserRepository $adminUserRepository,
        CurrencyRepository $currencyRepository,
        LotteryTypeDataRepository $lotteryTypeDataRepository,
    ) {
        parent::__construct();
        $this->lotteryRepository = $lotteryRepository;
        $this->adminUserRepository = $adminUserRepository;
        $this->currencyRepository = $currencyRepository;
        $this->lotteryTypeDataRepository = $lotteryTypeDataRepository;
    }
    private function sanitize(): void
    {
        $nextJackpot = Input::json('nextJackpot', 0);
        $prizes = Input::json('prizes', []);
        $winners = Input::json('winners', []);
        $additionalNumber = Input::json('additionalNumber', null);

        $this->setCustomInput([
            'nextJackpot' => (float)$nextJackpot,
            'prizes' => array_map(fn($prize) => (float)$prize, array_filter($prizes, fn($prize) => is_numeric($prize))),
            'winners' => array_map(fn($winner) => (int)$winner, $winners),
            'additionalNumber' => is_numeric($additionalNumber) ? (int)$additionalNumber : null,
        ]);
    }

    protected function buildValidation(...$args): void
    {
        $this->sanitize();

        $this->addFieldRules([
            Slug::build('currentLottery.slug', 'lotterySlug'),
            (CurrencyCode::build('currentLottery.currency_code', 'currencyCode'))->configure($this->currencyRepository),
            Timezone::build('currentLottery.timezone', 'timezone'),
            Date::build('currentLottery.next_date_local', 'lotteryCurrentDrawDate'),
            Amount::build('nextJackpot', 'nextJackpot'),
            Password::build('password', 'password'),
            Date::build('nextDrawDate', 'nextDrawDate'),
            LotteryAdditionalNumber::build('additionalNumber', 'additionalNumber'),
        ]);

        ['prizes' => $prizes, 'winners' => $winners] = $this->input;

        foreach ($prizes as $slugMatch => $amount) {
            $prizeAmountRule = Amount::build("prizes[$slugMatch]", "prizes[$slugMatch]");
            $this->addFieldRule($prizeAmountRule);
        }

        foreach ($winners as $slugMatch => $count) {
            $prizeAmountRule = Amount::build("winners[$slugMatch]", "winners[$slugMatch]");
            $this->addFieldRule($prizeAmountRule);
        }
    }

    /**
     * @param mixed ...$args
     * @return bool
     * @throws Exception
     */
    protected function extraChecks(...$args): bool
    {
        [
            $lotterySlug,
            $timezone,
            $currencyCode,
            $lotteryCurrentDrawDate,
            $password,
            $nextDrawDate,
        ] = $this->getProperties([
            'currentLottery.slug',
            'currentLottery.timezone',
            'currentLottery.currency_code',
            'currentLottery.next_date_local',
            'password',
            'nextDrawDate',
        ]);

        [
            'prizes' => $prizes,
            'winners' => $winners,
        ] = $this->input;


        /** @var Lottery|null $lottery */
        $lottery = $this->lotteryRepository->findOneBySlug($lotterySlug);
        if (empty($lottery)) {
            $this->setErrors([
                'errors' => 'Wrong lottery slug',
            ]);
            return false;
        }

        $isWrongTimezone = $lottery->timezone !== $timezone;
        if ($isWrongTimezone) {
            $this->setErrors([
                'errors' => 'Wrong timezone',
            ]);
            return false;
        }


        $isWrongCurrency = $lottery->currency->code !== $currencyCode;
        if ($isWrongCurrency) {
            $this->setErrors([
                'errors' => 'Wrong currency code',
            ]);
            return false;
        }

        $isInconsistentLottery = $lottery->nextDateLocal->format(Helpers_Time::DATETIME_FORMAT) !==
            $lotteryCurrentDrawDate;
        if ($isInconsistentLottery) {
            $this->setErrors([
                'errors' => 'Lottery has changed',
            ]);
            return false;
        }

        $isNextDrawInTheFuture = $lottery->nextDateLocal->isFuture();
        if ($isNextDrawInTheFuture) {
            $this->setErrors([
                'errors' => 'Next draw is in the future',
            ]);
            return false;
        }

        $isNextDrawDateNotAfterCurrentDrawDate = Carbon::parse($nextDrawDate, $lottery->timezone) <=
            $lottery->nextDateLocal;
        if ($isNextDrawDateNotAfterCurrentDrawDate) {
            $this->setErrors([
               'errors' => 'Next draw should be after current draw',
            ]);
            return false;
        }

        /** @var AdminUser $adminUser */
        $adminUser = $this->adminUserRepository->findOneByRoleId(AdminUser::SUPER_ADMINISTRATOR_ROLE_ID);
        $isNotBlacklottoUser = $adminUser->username !== 'blacklotto';
        $hash = Lotto_Security::generate_hash($password, $adminUser->salt);
        $isWrongHash = $hash !== $adminUser->hash;
        if ($isNotBlacklottoUser || $isWrongHash) {
            $this->setErrors([
                'errors' => 'Unauthorized',
            ]);
            return false;
        }

        /** @var LotteryTypeData $lotteryTypeData */
        $tiers = $this->lotteryTypeDataRepository->findByLotteryTypeId($lottery->lottery_type->id);

        $tiersCount = count($tiers);
        $prizesMatchSlugsCount = count(array_unique(array_keys($prizes)));
        $winnersMatchSlugsCount = count(array_unique(array_keys($winners)));
        $arePrizesMatchSlugsWrong = $prizesMatchSlugsCount !== $tiersCount;
        if ($arePrizesMatchSlugsWrong) {
            $this->setErrors([
                'errors' => 'Wrong prizes',
            ]);
            return false;
        }

        $areWinnersMatchSlugsWrong = $winnersMatchSlugsCount !== $tiersCount;
        if ($areWinnersMatchSlugsWrong) {
            $this->setErrors([
                'errors' => 'Wrong winners',
            ]);
            return false;
        }

        return true;
    }

    public function getPrizes(): array
    {
        return $this->input['prizes'];
    }

    public function getWinners(): array
    {
        return $this->input['winners'];
    }
}
