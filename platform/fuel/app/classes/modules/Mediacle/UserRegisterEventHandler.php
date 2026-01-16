<?php

namespace Modules\Mediacle;

use Modules\Mediacle\ApiRepository\SavePlayerContract;
use Modules\Mediacle\Repositories\PlayerRegistrationDataByIdContract;
use Repositories\Orm\WhitelabelUserRepository;

class UserRegisterEventHandler
{
    private IsPluginEnabledSpecification $isPluginEnabled;
    private WhitelabelUserRepository $registrationRepository;
    private SavePlayerContract $playerSavingRepository;

    public function __construct(
        IsPluginEnabledSpecification $isPluginEnabled,
        PlayerRegistrationDataByIdContract $registrationRepository,
        SavePlayerContract $savePlayerRepository
    ) {
        $this->isPluginEnabled = $isPluginEnabled;
        $this->registrationRepository = $registrationRepository;
        $this->playerSavingRepository = $savePlayerRepository;
    }

    public function handle(array $data): void
    {
        if (!empty(IS_CASINO)) {
            return;
        }

        if (!$this->isPluginEnabled->isSatisfiedBy($data['whitelabel_id'], MediaclePlugin::NAME)) {
            return;
        }

        $userId = $data['user_id'];

        # here we are fetching "User's" data (resolved by DI) from our system,
        # mapped to Mediacle required format (from contract)
        $player = $this->registrationRepository->getPlayerById($userId);

        # this repo will store player data in Mediacle API
        $this->playerSavingRepository->save($player);
    }
}
