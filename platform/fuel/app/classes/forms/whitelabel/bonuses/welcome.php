<?php

use Models\{
    Whitelabel,
    WhitelabelBonus
};
use Repositories\{
    Orm\RaffleRepository,
    WhitelabelBonusRepository
};
use Fuel\Core\{
    Input,
    Presenter,
    Validation
};

/**
 * Description of Forms_Whitelabel_Bonuses_Welcome
 */
class Forms_Whitelabel_Bonuses_Welcome extends Forms_Whitelabel_Bonuses_Main
{
    private int $bonusId;

    private int $source;

    private ?array $lotteries = [];

    private ?array $raffles = [];
    
    private Whitelabel $whitelabel;

    private ?WhitelabelBonus $whitelabelBonus = null;

    private WhitelabelBonusRepository $whitelabelBonusRepository;

    private RaffleRepository $raffleRepository;

    private Presenter_Whitelabel_Bonuses_Welcome|Presenter $inside;

    private array $bonusValue = [
        'purchase_lottery_id' => null,
        'register_lottery_id' => null,
        'purchase_raffle_id' => null,
        'register_raffle_id' => null,
    ];

    /**
     *
     * @param int $source
     * @param array $whitelabel
     */
    public function __construct(int $source, array $whitelabel)
    {
        $this->source = $source;
        $this->whitelabel = Container::get('whitelabel');
        $this->bonusId = Forms_Whitelabel_Bonuses_Main::BONUS_WELCOME;

        $pathToView = '';

        if ($this->source !== Helpers_General::SOURCE_ADMIN) {
            $pathToView = 'whitelabel/bonuses/welcome';
        }
        
        $this->inside = Presenter::forge($pathToView);
        $this->whitelabelBonusRepository = Container::get(WhitelabelBonusRepository::class);
        $this->raffleRepository = Container::get(RaffleRepository::class);
    }

    public function get_source(): int
    {
        return $this->source;
    }

    public function get_whitelabel(): Whitelabel
    {
        return $this->whitelabel;
    }

    public function get_inside(): Presenter_Whitelabel_Bonuses_Welcome
    {
        return $this->inside;
    }

    public function get_list_of_lotteries(): array
    {
        $this->lotteries = Model_Lottery::get_all_lotteries_for_whitelabel_short(
            $this->whitelabel->id
        );

        return $this->lotteries;
    }

    public function getListOfRaffles(): array
    {
        $this->raffles = $this->raffleRepository->getAllRafflesForWhitelabelShort(
            $this->whitelabel->id
        );

        return $this->raffles;
    }

    public function get_max_lottery_id(): int
    {
        $max_lottery_id = 0;

        $last_key_id = 0;
        if (!empty($this->lotteries)) {
            $last_key_id = count($this->lotteries) - 1;
            $max_lottery_id = (int)$this->lotteries[$last_key_id]['id'];
        }

        return $max_lottery_id;
    }

    public function process_form(): int
    {
        $this->whitelabelBonus = $this->whitelabelBonusRepository->findByBonusId(
            $this->whitelabel->id,
            $this->bonusId
        );

        $this->setWhitelabelBonusValues();

        $this->inside->set('lotteries', $this->get_list_of_lotteries());
        $this->inside->set('raffles', $this->getListOfRaffles());

        if (Input::post() == null) {
            return self::RESULT_GO_FURTHER;
        }

        $validatedForm = $this->validate_form();

        if (!$validatedForm->run()) {
            $errors = Lotto_Helper::generate_errors($validatedForm->error());
            $this->inside->set('errors', $errors);

            return self::RESULT_WITH_ERRORS;
        }

        $this->setBonusValue($validatedForm);
        $this->addRegistrationTypes($validatedForm);

        // Check if currently exist and delete
        // If not exist leave it
        if (!$this->isLotterySet()) {
            $this->deleteBonus();
            $message = _('Welcome bonus successfully deleted!');

        } else {    // If not exist create new, if exists change it
            $this->saveBonus();
            $message = ('Welcome bonus successfully set!');
        }

        Session::set_flash('message', [
            'success', $message
        ]);

        return self::RESULT_OK;
    }

    public function validateBonusRegisterType(string $val): string|false
    {
        if (!empty($val)) {
            $registerWebsite = Input::post('input.register_website');
            $registerApi = Input::post('input.register_api');

            if (!$registerWebsite && !$registerApi) {
                return false;
            }
        }

        return $val;
    }

    public function validateLotteryType(string $val): string|false
    {
        $parts = explode('_', $val); // eg. lottery_5 | raffle_1

        if(!empty($parts[0])){
            if(!in_array($parts[0], [WhitelabelBonus::WELCOME_TYPE_LOTTERY, WhitelabelBonus::WELCOME_TYPE_RAFFLE])) {
                return false;
            }
        }

        return $parts[1] ?? $parts[0];
    }

    protected function validate_form(): Validation
    {
        $validation = Validation::forge();

        $max_id_of_lotteries = $this->get_max_lottery_id();

        $validation->add('input.lottery_purchase', _('Lottery Purchase'))
            ->add_rule('trim')
            ->add_rule('required')
            ->add_rule([$this, 'validateLotteryType'])
            ->add_rule('is_numeric')
            ->add_rule('numeric_min', 0)
            ->add_rule('numeric_max', $max_id_of_lotteries);

        $validation->add('input.lottery_register', _('Lottery Register'))
            ->add_rule('trim')
            ->add_rule('required')
            ->add_rule([$this, 'validateLotteryType'])
            ->add_rule([$this, 'validateBonusRegisterType'])
            ->add_rule('is_numeric')
            ->add_rule('numeric_min', 0)
            ->add_rule('numeric_max', $max_id_of_lotteries)
            ->set_error_message(
                Forms_Whitelabel_Bonuses_Welcome::class . ':validateBonusRegisterType',
                'At least one registration option is required.'
            );

        $validation->add('input.register_website', _('Website Sign Up'))
            ->add_rule('trim')
            ->add_rule('match_collection', [0,1]);

        $validation->add('input.register_api', _('API Sign Up'))
            ->add_rule('trim')
            ->add_rule('match_collection', [0,1]);

        return $validation;
    }

    private function deleteBonus(): void
    {
        if (!empty($this->whitelabelBonus)) {
            $this->whitelabelBonus->delete();
        }
    }
    
    private function saveBonus(): void
    {
        if($this->whitelabelBonus !== null){
            $this->whitelabelBonus->set(array_merge([
                'whitelabel_id' => $this->whitelabel->id,
                'bonus_id' => $this->bonusId
            ], $this->bonusValue));

            $this->whitelabelBonus->save();

            return;
        }

        $this->whitelabelBonusRepository->insert($this->whitelabel->id, $this->bonusId, $this->bonusValue);
    }

    private function setBonusValue(Validation $validatedForm): void
    {
        foreach ([WhitelabelBonus:: WELCOME_PURCHASE, WhitelabelBonus:: WELCOME_REGISTER] as $bonusType) {
            foreach ([WhitelabelBonus:: WELCOME_TYPE_LOTTERY, WhitelabelBonus:: WELCOME_TYPE_RAFFLE] as $lotteryType) {
                $fieldName = "{$bonusType}_{$lotteryType}_id";

                $this->bonusValue[$fieldName] = $this->getInputValue($validatedForm, $bonusType, $lotteryType);
            }
        }
    }

    private function addRegistrationTypes(Validation $validatedForm): void
    {
        $registerLotteryId = $this->getInputValue($validatedForm, WhitelabelBonus:: WELCOME_REGISTER, WhitelabelBonus:: WELCOME_TYPE_LOTTERY);
        $registerRaffleId = $this->getInputValue($validatedForm, WhitelabelBonus:: WELCOME_REGISTER, WhitelabelBonus:: WELCOME_TYPE_RAFFLE);

        $this->bonusValue['register_website'] = 0;
        $this->bonusValue['register_api'] = 0;

        if ($registerLotteryId || $registerRaffleId) {
            $this->bonusValue['register_website'] = (int) $validatedForm->validated('input.register_website');
            $this->bonusValue['register_api'] = (int) $validatedForm->validated('input.register_api');
        }
    }

    private function getInputValue(Validation $validatedForm, string $bonusType, string $lotteryType): ?string
    {
        $field = 'input.lottery_' . $bonusType;

        $id = $validatedForm->validated($field);
        $parts = explode('_', Input::post($field)); // eg. lottery_5 | raffle_1

        if(empty($parts[1])){
            return null;
        }

        if($parts[1] === $id && $parts[0] === $lotteryType){
            return $id;
        }

        return null;
    }

    private function setWhitelabelBonusValues(): void
    {
        // In the case that there is already some lottery set
        $edit = [];

        if (!empty($this->whitelabelBonus)) {

            $purchaseId = $this->whitelabelBonus->getPurchaseId();
            $registerId = $this->whitelabelBonus->getRegisterId();

            $editPurchase = $purchaseId ? [
                $this->whitelabelBonus->getPurchaseLotteryType() => $purchaseId,
            ] : null;

            $editRegister = $registerId ? [
                $this->whitelabelBonus->getRegisterLotteryType() => $registerId,
            ] : null;

            $edit = [
                WhitelabelBonus::WELCOME_PURCHASE => $editPurchase,
                WhitelabelBonus::WELCOME_REGISTER => $editRegister,
                'register_website' => $this->whitelabelBonus->registerWebsite,
                'register_api' => $this->whitelabelBonus->registerApi,
            ];
        }

        $this->inside->set('edit', $edit);
    }

    private function isLotterySet(): bool
    {
        return !empty(array_filter(array_unique(array_values($this->bonusValue))));
    }
}
