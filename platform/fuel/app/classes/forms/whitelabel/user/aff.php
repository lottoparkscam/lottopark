<?php

use Fuel\Core\Validation;
use Repositories\Aff\WhitelabelAffRepository;

/**
 * Description of Forms_Whitelabel_User_Aff
 */
class Forms_Whitelabel_User_Aff extends Forms_Main
{
    
    /**
     *
     * @var array
     */
    private $whitelabel = [];
    
    /**
     *
     * @var View
     */
    private $inside = null;

    private WhitelabelAffRepository $whitelabelAffRepository;
    
    /**
     * @param array $whitelabel
     */
    public function __construct($whitelabel = [])
    {
        $this->whitelabel = $whitelabel;
        $this->whitelabelAffRepository = Container::get(WhitelabelAffRepository::class);
    }

    /**
     *
     * @return array
     */
    public function get_whitelabel()
    {
        return $this->whitelabel;
    }

    /**
     *
     * @return View
     */
    public function get_inside()
    {
        return $this->inside;
    }

    /**
     * @return Validation object
     */
    protected function validate_form(): Validation
    {
        $val = Validation::forge();
        
        $val->add("input.aff", _("Affiliate"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('valid_email')
            ->add_rule('max_length', 254);
        
        return $val;
    }
    
    /**
     *
     * @param string $path_to_view
     * @param array $user
     * @param string $rparam
     * @return null
     */
    public function process_form($path_to_view, &$user, &$rparam)
    {
        $whitelabel = $this->get_whitelabel();

        $this->inside = View::forge($path_to_view);
        $this->inside->set("user", $user);
        $this->inside->set("rparam", $rparam);

        $uac = Model_Whitelabel_User_Aff::find_by_whitelabel_user_id($user['id']);

        $this->inside->set("uac", $uac);

        $affId = $uac[0]['whitelabel_aff_id'];
        if (!empty($affId)) {
            $aff = $this->whitelabelAffRepository->findAffiliateById($affId);
            $this->inside->set('affiliateEmail', $aff['email']);
        } else {
            $this->inside->set('affiliateEmail', '');
        }

        if (Input::post("input.aff") === null) {
            return self::RESULT_GO_FURTHER;
        }

        $val = $this->validate_form();

        if ($val->run()) {
            if ($val->validated("input.aff") == 0) {
                if ($uac !== null && count($uac) > 0) {
                    $uac[0]->delete();
                }
            } else{
                if ($uac !== null && count($uac) > 0 && isset($whitelabel) && !empty($whitelabel["id"])) {
                    $uac[0]->delete();
                }

                $affNew = $this->whitelabelAffRepository->findAffiliateByEmail($val->validated("input.aff"), $this->whitelabel['id']);

                if (!empty($affNew['id']) && $affNew['is_deleted'] == 0 && $affNew['is_active'] == 1 && $affNew['is_accepted'] == 1) {
                    $affId = $affNew['id'];

                    $uacn = Model_Whitelabel_User_Aff::forge();
                    $uacn->set([
                        "whitelabel_id" => $whitelabel['id'],
                        "whitelabel_user_id" => $user['id'],
                        "whitelabel_aff_id" => $affId,
                        "whitelabel_aff_medium_id" => null,
                        "whitelabel_aff_campaign_id" => null,
                        "whitelabel_aff_content_id" => null,
                        "is_deleted" => 0,
                        "is_accepted" => 1
                    ]);
                    $uacn->save();
                }
            }
        } else {
            $errors = Lotto_Helper::generate_errors($val->error());
            $this->inside->set("errors", $errors);
            return self::RESULT_WITH_ERRORS;
        }

        return self::RESULT_OK;
    }
}
