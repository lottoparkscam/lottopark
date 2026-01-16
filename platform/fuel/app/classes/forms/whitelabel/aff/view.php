<?php

use Fuel\Core\Presenter;
use Fuel\Core\View;
use Helpers\AffGroupHelper;
use Repositories\Aff\WhitelabelAffRepository;
use Repositories\WhitelabelAffCasinoGroupRepository;

/**
 * @deprecated
 * Description of Forms_Whitelabel_Aff_View
 */
class Forms_Whitelabel_Aff_View extends Forms_Main
{
    private array $whitelabel;
    private Presenter $inside;
    private array $countries;
    private array $languages;

    private WhitelabelAffCasinoGroupRepository $whitelabelAffCasinoGroupRepository;

    public function __construct(
        array $whitelabel,
        array $countries,
        array $languages
    ) {
        $this->whitelabel = $whitelabel;
        $this->countries = $countries;
        $this->languages = $languages;
        $this->whitelabelAffCasinoGroupRepository = Container::get(WhitelabelAffCasinoGroupRepository::class);
    }

    /**
     *
     * @return array
     */
    public function get_whitelabel()
    {
        return $this->whitelabel;
    }

    public function get_inside(): Presenter
    {
        return $this->inside;
    }

    private function prepareCasinoGroups(array $casinoGroups): Generator
    {
        foreach ($casinoGroups as $casinoGroup) {
            yield $casinoGroup['id'] => $casinoGroup;
        }
    }
    
    /**
     *
     * @param string $token
     * @return int
     */
    public function process_form(string $token): int
    {
        $whitelabel = $this->get_whitelabel();
        
        $users = Model_Whitelabel_Aff::find_by([
            "whitelabel_id" => $whitelabel['id'],
            "token" => $token
        ]);
        
        if (!($users !== null &&
            count($users) > 0 &&
            (int)$users[0]->whitelabel_id === (int)$whitelabel['id'] &&
            (int)$users[0]->is_deleted === 0 &&
            (int)$users[0]->is_accepted === 1 &&
            (((int)$whitelabel['aff_activation_type'] !== Helpers_General::ACTIVATION_TYPE_REQUIRED &&
                (int)$users[0]->is_active === 1) ||
                ((int)$whitelabel['aff_activation_type'] === Helpers_General::ACTIVATION_TYPE_REQUIRED &&
                    (int)$users[0]->is_active === 1 &&
                    (int)$users[0]->is_confirmed === 1)))
        ) {
            Session::set_flash("message", ["danger", _("Wrong affiliate.")]);
            return self::RESULT_WRONG_AFF;
        }
        
        $user = $users[0];

        $timezones = Lotto_Helper::get_timezone_list();

        $affiliateIds = [$user['whitelabel_aff_parent_id']];
        $whitelabelAffRepository = Container::get(WhitelabelAffRepository::class);
        $affiliatesDetails = $whitelabelAffRepository->getAffiliatesDetailsByIds($affiliateIds, $whitelabel['id']);

        $affiliatesDetailsFormatted = [];
        foreach ($affiliatesDetails as $affiliate) {
            $affiliatesDetailsFormatted[$affiliate['id']] = $affiliate;
        }

        $withdrawal_data = unserialize($user['withdrawal_data']);

        $lotteryGroups = Model_Whitelabel_Aff_Group::get_whitelabel_groups($whitelabel);
        $prepareCasinoGroups = AffGroupHelper::prepareCasinoGroups(
            $this->whitelabelAffCasinoGroupRepository->getGroupsByWhitelabelId($whitelabel['id'])
        );
        $casinoGroups = iterator_to_array($prepareCasinoGroups);

        $this->inside = Presenter::forge("whitelabel/affs/view");
        $this->inside->set('lotteryGroups', $lotteryGroups);
        $this->inside->set('casinoGroups', $casinoGroups);
        $this->inside->set("rallaffs", $affiliatesDetailsFormatted);
        $this->inside->set("user", $user);
        $this->inside->set("countries", $this->countries);
        $this->inside->set("timezones", $timezones);
        $this->inside->set("languages", $this->languages);
        $this->inside->set("currencies", Lotto_Settings::getInstance()->get("currencies"));
        $this->inside->set("withdrawal_data", $withdrawal_data);

        return self::RESULT_OK;
    }
}
