<?php

use Fuel\Core\Controller;
use Fuel\Core\Cookie;
use Fuel\Core\Input;
use Fuel\Core\Pagination;
use Fuel\Core\Presenter;
use Fuel\Core\Request;
use Fuel\Core\Response;
use Fuel\Core\Session;
use Fuel\Core\View;
use Models\WhitelabelAff;
use Repositories\Aff\WhitelabelAffCommissionRepository;
use Repositories\Aff\WhitelabelAffRepository;
use Repositories\Aff\WhitelabelUserAffRepository;
use Repositories\WhitelabelAffCasinoGroupRepository;
use Services\Logs\FileLoggerService;
use Helpers\UrlHelper;
use Helpers\UserHelper;
use Repositories\WhitelabelAffCampaignRepository;
use Repositories\WhitelabelAffSlotCommissionRepository;
use Repositories\WhitelabelSlotProviderRepository;

/**
 * The Welcome Controller.
 *
 * A basic controller example.  Has examples of how to set the
 * response body and status.
 *
 * @package  app
 * @extends  Controller
 */
class Controller_Aff extends Controller
{
    /**
     * Get Trait for date range preparation
     */
    use Traits_Gets_Date;

    /**
     * Reports are in affiliate mode.
     */
    public const AFFILIATE_REPORTS = 0;
    /**
     * Reports are in sub-affiliate mode.
     */
    public const SUBAFFILIATE_REPORTS = 1;

    private bool $is_user = false;
    private array $user = [];
    private View $view;
    private ?bool $isCasino;
    private bool $whitelabelHasCasino;

    private WhitelabelAffRepository $whitelabelAffRepository;
    private WhitelabelUserAffRepository $whitelabelUserAffRepository;
    private WhitelabelAffCommissionRepository $whitelabelAffCommissionRepository;
    private FileLoggerService $fileLoggerService;
    private WhitelabelSlotProviderRepository $whitelabelSlotProviderRepository;
    private WhitelabelAffSlotCommissionRepository $whitelabelAffSlotCommissionRepository;
    private WhitelabelAffCasinoGroupRepository $whitelabelAffCasinoGroupRepository;

    /**
     * Do before processing request.
     * @return void should return nothing.
     */
    public function before()
    {
        $this->fileLoggerService = Container::get(FileLoggerService::class);

        $this->whitelabelAffRepository = Container::get(WhitelabelAffRepository::class);
        $this->whitelabelUserAffRepository = Container::get(WhitelabelUserAffRepository::class);
        $this->whitelabelAffCommissionRepository = Container::get(WhitelabelAffCommissionRepository::class);
        $this->whitelabelSlotProviderRepository = Container::get(WhitelabelSlotProviderRepository::class);
        $this->whitelabelAffSlotCommissionRepository = Container::get(WhitelabelAffSlotCommissionRepository::class);
        $this->whitelabelAffCasinoGroupRepository = Container::get(WhitelabelAffCasinoGroupRepository::class);
        $this->isCasino = $this->isCasino();

        if (file_exists(APPPATH . '/.maintenance')) {
            http_response_code(503);
            exit(file_get_contents(APPPATH . '/.maintenance'));
        }

        if (!Lotto_Helper::allow_access("aff")) {
            $error = Request::forge('index/404')->execute();
            echo $error;
            exit();
        }

        $domain = $_SERVER['HTTP_HOST'];
        $domain = explode('.', $domain);
        if ($domain[0] == "aff") {
            array_shift($domain);
        } else {
            return;
        }
        $domain = implode('.', $domain);

        $whitelabel = Model_Whitelabel::get_by_domain($domain);
        if ($whitelabel == null) {
            $this->fileLoggerService->error(
                "Lack of settings for domain in DB. Name of domain: " . $domain
            );
            exit("There is a problem on server");
        }
        Lotto_Settings::getInstance()->set("whitelabel", $whitelabel);

        if (!empty($whitelabel['language_id'])) {
            $lang_id = $whitelabel['language_id'];
            $lang = Model_Language::find_by_pk($lang_id);
            if ($lang != null) {
                Lotto_Settings::getInstance()->set("locale_default", $lang['code'] . '.utf8');
                putenv('LC_ALL=' . $lang['code'] . '.utf8');
                setlocale(LC_ALL, $lang['code'] . '.utf8');
            }
        } else { //elseif $aff logged in
            // default
            putenv('LC_ALL=en_GB.utf8'); // windows
            setlocale(LC_ALL, 'en_GB.utf8'); // linux
        }
        bindtextdomain("admin", APPPATH . "lang/gettext");
        textdomain("admin");

        if (
            Session::get("aff.remember") === null ||
            (int)Session::get("aff.remember") === 0
        ) {
            Session::set(UserHelper::SHOULD_LOGOUT_AFTER_BROWSER_CLOSE_KEY, true);
        }

        $login = new Forms_Aff_Login($whitelabel);
        $this->is_user = false;

        try {
            $result_check_credentials = $login->check_and_update_credentials();

            switch ($result_check_credentials) {
                case Forms_Aff_Login::RESULT_OK:
                    $this->user = $login->get_user();
                    if (!empty($this->user)) {
                        $this->is_user = true;
                    }
                    break;
                case Forms_Aff_Login::RESULT_AFF_IS_NOT_ACTIVE:
                    // In that case user still can't login in
                    // but is registered and has to confirm
                    // email sent to him/her to make
                    // possible to login in
                    break;
                case Forms_Aff_Login::RESULT_WRONG_CREDENTIALS:
                    // I don't know if and how should I inform user about such problem!
                    throw new Exception("There is a problem with credentials of the user");
                    break;
                case Forms_Aff_Login::RESULT_GO_FURTHER:
                    break;
            }
        } catch (Exception $e) {
            $this->fileLoggerService->error(
                $e->getMessage()
            );
        }

        $currencies = Helpers_Currency::getCurrencies();

        $timezone = "UTC";
        if (!empty($whitelabel['timezone'])) {
            $timezone = $whitelabel['timezone'];
        }
        Lotto_Settings::getInstance()->set("timezone", $timezone);

        Lotto_Settings::getInstance()->set("user", $this->user);
        Lotto_Settings::getInstance()->set("is_user", $this->isAffUserLogged());

        $whitelabel = Container::get('whitelabel');
        $this->whitelabelHasCasino = $whitelabel->hasCasino();

        $this->view = View::forge("aff/index");
        $this->view->set_global("whitelabel", $whitelabel);
        $this->view->set_global("user", $this->user);
        $this->view->set_global('hasCasino', $this->whitelabelHasCasino);

        // Probably that is the best place to process the form of login
        $result_process_form = $login->process_form($this->view);

        switch ($result_process_form) {
            case Forms_Aff_Login::RESULT_OK:
                Response::redirect("/");
                break;
            case Forms_Aff_Login::RESULT_GO_FURTHER:
            case Forms_Aff_Login::RESULT_SECURITY_ERROR:
            case Forms_Aff_Login::RESULT_WRONG_CAPTCHA:
            case Forms_Aff_Login::RESULT_TOO_MANY_ATTEMPTS:
            case Forms_Aff_Login::RESULT_WRONG_CREDENTIALS:
            case Forms_Aff_Login::RESULT_AFF_IS_NOT_ACTIVE:
                break;
            case Forms_Aff_Login::RESULT_DB_ERROR:
                exit("There is a problem on server");
                break;
        }

        if (
            Session::get("aff.remember") === null ||
            (int)Session::get("aff.remember") === 0
        ) {
            Session::set(UserHelper::SHOULD_LOGOUT_AFTER_BROWSER_CLOSE_KEY, true);
        }

        // save request params
        $this->view->set_global("action", $this->request->action);
        $this->view->set_global("params", $this->request->params());
        $this->view->set_global("action_full", $this->get_action_full(
            $this->request->action,
            $this->request->params()
        ));

        // set reports type
        $this->view->set_global("reports_type", $this->get_reports_type());

        // conditionally set all needed data for subaff reports
        $this->set_subaff_data();

        $titles_arr = [
            "payouts" => _("Payouts"),
            "reports" => _("Generate report"),
            "leads" => _("Leads"),
            "ftps" => _("First-Time Purchases"),
            "commissions" => _("Commissions"),
            "subaffiliates" => _("Sub-affiliates"),
            "settings" => _("Edit profile"),
            "payment" => _("Payment settings"),
            "analytics" => _("Google Analytics"),
            "pixel" => _("Facebook Pixel"),
            "index" => $this->isAffUserLogged() ? _("Dashboard") : null
        ];

        $title = null;
        if (isset($titles_arr[$this->request->action])) {
            $title = $titles_arr[$this->request->action];
        }
        $this->view->set_global("title", $title);

        $this->view->header = View::forge("aff/shared/header");
        $navigation_bar = null;
        // render navbar only for logged users.
        if ($this->isAffUserLogged()) {
            $navigation_bar = Presenter::forge("aff/shared/navbar");
        }
        $this->view->navbar = $navigation_bar;
        $this->view->footer = View::forge("aff/shared/footer");

        if ($this->isAffUserNotLogged()) {
            \Helpers\CaptchaHelper::loadCaptchaConfig();
            $this->view->footer->set("captcha", true);
            $this->view->inside = Presenter::forge("aff/signin");
        }
    }

    /**
     *
     * @return int
     */
    private function get_user_id(): int
    {
        $user_id = -1;
        if (!empty($this->user)) {
            $user_id = (int)$this->user['id'];
        }

        return $user_id;
    }

    /**
     *
     * Check which type of reports are in use. Based on request parameters.
     * @return int either SUBAFFILIATE_REPORTS or AFFILIATE_REPORTS.
     */
    private function get_reports_type()
    {
        // reports are in subaff mode if action is subafffiliates or parameter action is subaffiliates eg. .../subaffiliates or .../leads/subaffiliates
        $action = "subaffiliates";
        $is_subaff = $this->request->param("action") === $action || $this->request->action === $action;
        return ($is_subaff) ? self::SUBAFFILIATE_REPORTS : self::AFFILIATE_REPORTS;
    }

    private function isReportForSubAffiliates(): bool
    {
        return $this->view->reports_type === self::SUBAFFILIATE_REPORTS;
    }

    private function isReportForAffiliates(): bool
    {
        return $this->view->reports_type === self::AFFILIATE_REPORTS;
    }

    private function isSubAffiliateSelected(): bool
    {
        $inputVal = Input::get('filter.subaff');
        return !empty($inputVal) && $inputVal !== "" && $inputVal !== "a";
    }

    private function affiliateCanCreateSubAffiliates(array $whitelabel): bool
    {
        return (int)$whitelabel["aff_can_create_sub_affiliates"] === 1;
    }

    private function isShowAllOptionSelected(?string $inputValue, bool $skip = false): bool
    {
        if (!$skip) {
            if (!$inputValue) {
                return true;
            }
        }

        return $inputValue === 'a';
    }

    private function getParentId(): ?int
    {
        $parentId = null;
        if ($this->isReportForSubAffiliates()) {
            $parentId = $this->get_user_id();
        }

        return $parentId;
    }

    private function getSubAffiliateIds(int $userId, $isForAffiliates = false): array
    {
        $subAffiliateIds = [];
        if ($this->isSubAffiliateSelected()) {
            $subAffiliateIds = [Input::get('filter.subaff')];
        } else {
            $subAffiliates = $this->whitelabelAffRepository->findSubAffiliateIdsByParentAffiliateId($userId);
            foreach ($subAffiliates as $subAffiliate) {
                $subAffiliateIds[] = $subAffiliate['id'];
            }
            if ($isForAffiliates && $this->isReportForAffiliates()) {
                $subAffiliateIds[] = $userId;
            }
        }

        return $subAffiliateIds;
    }

    private function isAffUserNotLogged(): bool
    {
        return !$this->is_user;
    }

    private function isAffUserLogged(): bool
    {
        return $this->is_user;
    }

    /**
     * This function will conditionally set all data for reports in subaff mode.
     *
     * @return void
     */
    private function set_subaff_data(): void
    {
        $subAffId = null;
        if (!empty($this->user) && $this->isReportForSubAffiliates()) {
            $subaffs = Model_Whitelabel_Aff::fetch_subaffs_active($this->user['id']);
            $subAffId = array_search($this->request->param("id"), array_column($subaffs, "id")) === false ? null : $this->request->param("id");

            $this->view->set_global("subaffs", $subaffs);
        }

        $this->view->set_global("subaff_id", $subAffId);
    }

    /**
     * Get full action - full address of the current request.
     * @param string $action request action.
     * @param array $params request parameters.
     * @return string full action.
     */
    private function get_action_full($action, $params): string
    {
        $action_full = $action;
        foreach ($params as $param) {
            $action_full .= '/' . $param;
        }
        return $action_full;
    }

    /**
     *
     */
    public function action_signout()
    {
        Session::delete("aff");
        Session::set(UserHelper::SHOULD_LOGOUT_AFTER_BROWSER_CLOSE_KEY, true);
        Response::redirect('/');
    }

    /**
     *
     * @return View
     */
    public function action_index()
    {
        if ($this->isAffUserNotLogged()) {
            return Response::forge($this->view);
        }
        $this->view->inside = View::forge("aff/dashboard/index");

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        $aff_group_list_obj = new Forms_Whitelabel_Aff_Group_List($whitelabel);

        $group_data = [];
        if (!empty($this->user) && !is_null($this->user)) {
            $group_data = $aff_group_list_obj->get_group_data($this->user);
        }

        $this->view->inside->set("group_data", $group_data);

        $userCasinoGroupCommissions = [];
        if ($this->whitelabelHasCasino && !is_null($this->user['whitelabel_aff_casino_group_id'])) {
            $userCasinoGroupCommissions = $this->whitelabelAffCasinoGroupRepository
                ->findGroupByWhitelabelIdAndGroupId(
                    $whitelabel['id'],
                    $this->user['whitelabel_aff_casino_group_id']
                );
        } else {
            // default group if no individual group is set
            $userCasinoGroupCommissions['commission_percentage_value_for_tier_1'] =
                $whitelabel['default_casino_commission_percentage_value_for_tier_1'];

            $userCasinoGroupCommissions['commission_percentage_value_for_tier_2'] =
                $whitelabel['default_casino_commission_percentage_value_for_tier_2'];
        }
        $this->view->inside->set('userCasinoGroupCommissions', $userCasinoGroupCommissions);

        $aff_links = new Forms_Aff_Links($whitelabel);

        $token = '';
        if (!empty($this->user) && !is_null($this->user)) {
            $aff_links->process_form($this->view->inside, $this->user);

            $token = $this->user['token'];
        }

        $link_custom = 'https://' . $whitelabel['domain'] .
            '/play/powerball/?ref=' . strtoupper($token);

        $this->view->inside->set("link_custom", $link_custom);

        $this->view->set_global("title", _("Dashboard"));

        return Response::forge($this->view);
    }

    /**
     * @return View
     */
    public function action_sign_up()
    {
        if ($this->isAffUserLogged()) {
            Response::redirect("/");
        }

        $cookieAffName = Helpers_General::COOKIE_AFF_NAME;

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        $isNotSetRef = !Cookie::get($cookieAffName);

        if ((int)$whitelabel['aff_enable_sign_ups'] === 0 && $isNotSetRef) {
            Response::redirect("/");
        }

        $aff_register = new Forms_Aff_Register($whitelabel);
        $aff_register->set_inside_by_presenter("aff/register");
        $result = $aff_register->process_form();

        switch ($result) {
            case Forms_Aff_Register::RESULT_OK:
            case Forms_Aff_Register::RESULT_EMAIL_NOT_SENT:
                Response::redirect("/");
                break;
            case Forms_Aff_Register::RESULT_GO_FURTHER:
            case Forms_Aff_Register::RESULT_SECURITY_ERROR:
            case Forms_Aff_Register::RESULT_WRONG_CAPTCHA:
            case Forms_Aff_Register::RESULT_TOO_MANY_ATTEMPTS:  // I don't know if that option is OK ?
            case Forms_Aff_Register::RESULT_WITH_ERRORS:
                break;
        }

        $inside = $aff_register->get_inside();
        $this->view->inside = $inside;

        return Response::forge($this->view);
    }

    /**
     *
     * @return View
     */
    public function action_activation()
    {
        $is_user = Lotto_Settings::getInstance()->get("is_user");
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        $user = Lotto_Settings::getInstance()->get("user");

        if (
            $is_user &&
            ((int)$whitelabel['aff_activation_type'] !== Helpers_General::ACTIVATION_TYPE_OPTIONAL ||
                (int)$user['is_confirmed'] === 1)
        ) {
            Response::redirect('/');
            return;
        }

        $token = $this->param("token");
        $hash = $this->param("hash");

        $aff_register = new Forms_Aff_Register($whitelabel);
        $result = $aff_register->activation($token, $hash);

        switch ($result) {
            case Forms_Aff_Register::RESULT_OK:
            case Forms_Aff_Register::RESULT_ACTIVATION_LINK_EXPIRED:
            case Forms_Aff_Register::RESULT_EMPTY_TOKEN:
            case Forms_Aff_Register::RESULT_EMPTY_HASH:
            case Forms_Aff_Register::RESULT_WRONG_LINK:
            case Forms_Aff_Register::RESULT_ALREADY_ACTIVATED:
                Response::redirect("/");
                break;
        }
    }

    /**
     *
     * @return void
     */
    public function action_resend(): void
    {
        $is_user = Lotto_Settings::getInstance()->get("is_user");
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        $user = Lotto_Settings::getInstance()->get("user");

        if (
            $is_user &&
            ((int)$whitelabel['aff_activation_type'] !== Helpers_General::ACTIVATION_TYPE_OPTIONAL ||
                (int)$user['is_confirmed'] === 1)
        ) {
            Response::redirect('/');
            return;
        }

        $token = $this->param("token");
        $hash = $this->param("hash");

        $aff_register = new Forms_Aff_Register($whitelabel);
        $result = $aff_register->resend_email($token, $hash);

        switch ($result) {
            case Forms_Aff_Register::RESULT_OK:
            case Forms_Aff_Register::RESULT_ACTIVATION:
            case Forms_Aff_Register::RESULT_EMPTY_TOKEN:
            case Forms_Aff_Register::RESULT_EMPTY_HASH:
            case Forms_Aff_Register::RESULT_WRONG_LINK:
            case Forms_Aff_Register::RESULT_EMAIL_NOT_SENT:
                Response::redirect("/");
                break;
        }
    }

    /**
     *
     * @return View
     */
    public function action_payment()
    {
        if ($this->isAffUserNotLogged()) {
            return Response::forge($this->view);
        }

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        $inside = View::forge("aff/settings/payment");

        $methods = Model_Whitelabel_Aff_Withdrawal::get_whitelabel_aff_withdrawals($whitelabel);
        $kmethods = array_values($methods);
        $wmethods = [];
        foreach ($kmethods as $method) {
            $wmethods[$method['withdrawal_id']] = $method;
        }

        $inside->set("methods", $methods);
        if (!empty($this->user)) {
            $inside->set("user", $this->user);

            $data = unserialize($this->user['withdrawal_data']);
            $inside->set("data", $data);
        }

        if (Input::post("input.method") != null) {
            $val = Validation::forge("step1");
            $val->add("input.method", _("Method"))
                ->add_rule("required")
                ->add_rule("trim")
                ->add_rule("is_numeric")
                ->add_rule("numeric_min", 0);

            if ($val->run()) {
                if (
                    isset($wmethods[intval($val->validated("input.method"))]) ||
                    (int)$val->validated("input.method") === 0
                ) {
                    $method = $val->validated('input.method');

                    $fieldset_name = "step2";
                    $validated_form = null;
                    $fields = [];
                    switch ($method) {
                        case Helpers_Withdrawal_Method::WITHDRAWAL_BANK:
                            $bank_withdrawal_method = new Forms_Aff_Withdrawal_Bank($fieldset_name);
                            $validated_form = $bank_withdrawal_method->validate_form();
                            $fields = $bank_withdrawal_method->get_fields();
                            break;
                        case Helpers_Withdrawal_Method::WITHDRAWAL_SKRILL:
                            $skrill_withdrawal_method = new Forms_Aff_Withdrawal_Skrill($fieldset_name);
                            $validated_form = $skrill_withdrawal_method->validate_form();
                            $fields = $skrill_withdrawal_method->get_fields();
                            break;
                        case Helpers_Withdrawal_Method::WITHDRAWAL_NETELLER:
                            $neteller_withdrawal_method = new Forms_Aff_Withdrawal_Neteller($fieldset_name);
                            $validated_form = $neteller_withdrawal_method->validate_form();
                            $fields = $neteller_withdrawal_method->get_fields();
                            break;
                        case Helpers_Withdrawal_Method::WITHDRAWAL_BTC:
                            $btc_withdrawal_method = new Forms_Aff_Withdrawal_Btc($fieldset_name);
                            $validated_form = $btc_withdrawal_method->validate_form();
                            $fields = $btc_withdrawal_method->get_fields();
                            break;
                        case Helpers_Withdrawal_Method::WITHDRAWAL_PAYPAL:
                            $paypal_withdrawal_method = new Forms_Aff_Withdrawal_Paypal($fieldset_name);
                            $validated_form = $paypal_withdrawal_method->validate_form();
                            $fields = $paypal_withdrawal_method->get_fields();
                            break;
                    }

                    if (is_null($validated_form)) {
                        if (!empty($this->user)) {
                            $set = [
                                'whitelabel_aff_withdrawal_id' => null,
                                'withdrawal_data' => null
                            ];

                            $auser = Model_Whitelabel_Aff::find_by_pk($this->user['id']);
                            $auser->set($set);
                            $auser->save();
                        }

                        Session::set_flash("message", ["success", _("Payment settings have been saved!")]);
                        Response::redirect('payment');
                    }

                    if ($validated_form->run()) {
                        $data = [];
                        foreach ($fields as $field) {
                            $field_name = $field;

                            // this should be changed into nested $_POST array...
                            $field_name = str_replace(["nsurname", "ssurname", "bsurname", "btsurname", "ppsurname"], "surname", $field_name);
                            $field_name = str_replace(["nname", "sname", "bname", "btname", "ppname"], "name", $field_name);

                            $data[$field_name] = $validated_form->validated("input." . $field);
                        }

                        $whitelabel_aff_withdrawal_id = null;
                        $withdrawal_data = null;

                        if ((int)$method !== 0) {
                            $withdrawal_methods = Model_Whitelabel_Aff_Withdrawal::find_by_withdrawal_id($method);

                            if (empty($withdrawal_methods)) {
                                $error_message = "Lack of withdrawal payment method of ID: " . $method .
                                    " for whitelabel_id: " . $whitelabel['id'];
                                $this->fileLoggerService->error(
                                    $error_message
                                );
                            } else {
                                $withdrawal_method = $withdrawal_methods[0];
                                $whitelabel_aff_withdrawal_id = (int)$withdrawal_method->id;
                            }

                            $withdrawal_data = serialize($data);
                        }

                        if (!empty($this->user)) {
                            $set = [
                                'whitelabel_aff_withdrawal_id' => $whitelabel_aff_withdrawal_id,
                                'withdrawal_data' => $withdrawal_data
                            ];

                            $auser = Model_Whitelabel_Aff::find_by_pk($this->user['id']);
                            $auser->set($set);
                            $auser->save();
                        }

                        Session::set_flash("message", ["success", _("Payment settings have been saved!")]);
                        Response::redirect('payment');
                    } else {
                        $errors = Lotto_Helper::generate_errors($validated_form->error());
                        $inside->set("errors", $errors);
                    }
                }
            } else {
                $errors = Lotto_Helper::generate_errors($val->error());
                $inside->set("errors", $errors);
            }
            $fields = [];
        }

        $inside->set('whitelabel', $whitelabel);
        $this->view->inside = $inside;

        return Response::forge($this->view);
    }

    /**
     * @return View
     */
    public function action_settings()
    {
        if ($this->isAffUserNotLogged()) {
            return Response::forge($this->view);
        }

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        $user = [];
        if (!empty($this->user)) {
            $user = $this->user;
        }

        if ($this->param("action") == "edit") {
            $inside = View::forge("aff/settings/edit");

            $this->view->inside = $inside;

            $inside->set("user", $user);

            $countries = Lotto_Helper::get_localized_country_list();
            $languages = Model_Language::get_all_languages();
            $pcountries = Lotto_Helper::filter_phone_countries($countries);
            $timezones = Lotto_Helper::get_timezone_list();

            $prefixes = Lotto_Helper::get_telephone_prefix_list();

            if (null !== Input::post("input.name")) {
                $val = Validation::forge();

                $val->add("input.login", _("Login"))
                    ->add_rule('trim')
                    ->add_rule("required")
                    ->add_rule('min_length', 3)
                    ->add_rule('max_length', 30)
                    ->add_rule('valid_string', ['alpha', 'numeric', 'dashes']);

                $val->add("input.name", _("First Name"))
                    ->add_rule('trim')
                    ->add_rule('min_length', 3)
                    ->add_rule('max_length', 100)
                    ->add_rule('valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'utf8']);

                $val->add("input.company", _("Company"))
                    ->add_rule('trim')
                    ->add_rule('min_length', 3)
                    ->add_rule('max_length', 100)
                    ->add_rule('valid_string', ['alpha', 'numeric', 'specials', 'dashes', 'dots', 'spaces', 'utf8']);

                $val->add("input.surname", _("Last Name"))
                    ->add_rule('trim')
                    ->add_rule('min_length', 3)
                    ->add_rule('max_length', 100)
                    ->add_rule('valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'utf8']);

                $val->add("input.city", _("City"))
                    ->add_rule('trim')
                    ->add_rule('min_length', 3)
                    ->add_rule('max_length', 100)
                    ->add_rule('valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'utf8']);

                $val->add("input.zip", _("Postal/ZIP Code"))
                    ->add_rule('trim')
                    ->add_rule('max_length', 20)
                    ->add_rule('valid_string', ['alpha', 'numeric', 'dashes', 'spaces']);

                $val->add("input.state", _("Region"))
                    ->add_rule('trim')
                    ->add_rule('valid_string', ['alpha', 'numeric', 'dashes']);

                $val->add("input.address", _("Address #1"))
                    ->add_rule('trim')
                    ->add_rule('min_length', 3)
                    ->add_rule('max_length', 100)
                    ->add_rule('valid_string', ['alpha', 'specials', 'numeric', 'dashes', 'spaces', 'dots', 'commas', 'forwardslashes', 'utf8']);

                $val->add("input.address_2", _("Address #2"))
                    ->add_rule('trim')
                    ->add_rule('min_length', 3)
                    ->add_rule('max_length', 100)
                    ->add_rule('valid_string', ['alpha', 'specials', 'numeric', 'dashes', 'spaces', 'dots', 'commas', 'forwardslashes', 'utf8']);

                $val->add("input.phone", _("Phone"))
                    ->add_rule('trim')
                    ->add_rule('min_length', 3)
                    ->add_rule('max_length', 100)
                    ->add_rule('valid_string', ['numeric', 'dashes', 'spaces']);

                $val->add("input.country", _("Country"))
                    ->add_rule('trim')
                    ->add_rule('exact_length', 2)
                    ->add_rule('valid_string', ['alpha']);

                $val->add("input.prefix", _("Phone"))
                    ->add_rule('trim')
                    ->add_rule('valid_string', ['alpha', 'numeric', 'dashes']);

                $val->add("input.timezone", _("Time Zone"))
                    ->add_rule('trim')
                    ->add_rule('valid_string', ['alpha', 'forwardslashes', 'dashes']);

                $val->add("input.birthdate", _("Birthdate"))
                    ->add_rule('trim')
                    ->add_rule('valid_string', ['numeric', 'forwardslashes']);

                if ($val->run()) {
                    $user_id = $this->get_user_id();

                    $auser = Model_Whitelabel_Aff::find_by_pk($user_id);

                    $result = Model_Whitelabel_Aff::get_count_for_whitelabel(
                        $whitelabel,
                        $val->validated("input.email"),
                        $val->validated("input.login"),
                        $auser['id']
                    );

                    if (is_null($result)) {
                        Session::set_flash("message", ["danger", _("There is something wrong with DB!")]);
                        Response::redirect("settings");
                    }

                    $aff_count = $result[0]['count'];

                    if ($aff_count == 0) {
                        if (
                            $val->validated('input.country') === "" ||
                            isset($countries[$val->validated('input.country')])
                        ) {
                            if (
                                $val->validated('input.state') == "" ||
                                ($val->validated('input.country') !== "" &&
                                    Lotto_Helper::check_region($val->validated("input.state"), $val->validated("input.country")))
                            ) {
                                list(
                                    $is_date_ok,
                                    $date_time
                                ) = Helpers_General::validate_birthday($val->validated('input.birthdate'), "m/d/Y");

                                if ($is_date_ok) {
                                    if (
                                        empty($val->validated('input.timezone')) ||
                                        isset($timezones[$val->validated('input.timezone')])
                                    ) {
                                        $errors = [];
                                        list(
                                            $phone,
                                            $phone_country,
                                            $phone_validation_errors
                                        ) = Helpers_General::validate_phonenumber(
                                            $val->validated('input.phone'),
                                            $val->validated("input.prefix"),
                                            $pcountries
                                        );

                                        if (!empty($phone_validation_errors)) {
                                            // should be single key
                                            $key = key($phone_validation_errors);
                                            $key_modified = "input." . $key;
                                            $errors = [
                                                $key_modified => $phone_validation_errors[$key]
                                            ];
                                            $inside->set("errors", $errors);
                                        }

                                        if (count($errors) == 0) {
                                            $auser->set([
                                                'login' => $val->validated("input.login"),
                                                'company' => $val->validated('input.company'),
                                                'name' => $val->validated('input.name'),
                                                'surname' => $val->validated('input.surname'),
                                                'city' => $val->validated('input.city'),
                                                'zip' => $val->validated('input.zip'),
                                                'state' => $val->validated('input.state') !== null ? $val->validated('input.state') : '',
                                                'phone' => $phone,
                                                'phone_country' => $phone_country,
                                                'address_1' => $val->validated('input.address'),
                                                'address_2' => $val->validated('input.address_2'),
                                                'country' => $val->validated('input.country'),
                                                'birthdate' => $date_time !== null ? $date_time->format('Y-m-d') : null,
                                                'timezone' => $val->validated('input.timezone'),
                                            ]);
                                            $auser->save();

                                            Session::set_flash("message", ["success", _("Your details has been saved!")]);
                                            Response::redirect("settings");
                                        }
                                    } else {
                                        $errors = ['input.timezone' => _("Wrong timezone!")];
                                        $inside->set("errors", $errors);
                                    }
                                } else {
                                    $errors = ['input.birthdate' => _("Wrong birthdate!")];
                                    $inside->set("errors", $errors);
                                }
                            } else {
                                $errors = ['input.region' => _("Wrong region!")];
                                $inside->set("errors", $errors);
                            }
                        } else {
                            $errors = ['input.country' => _("Wrong country!")];
                            $inside->set("errors", $errors);
                        }
                    } else {
                        $errors = ['input.login' => _("This login is already in use by another affiliate!")];
                        $inside->set("errors", $errors);
                    }
                } else {
                    $errors = Lotto_Helper::generate_errors($val->error());
                    $inside->set("errors", $errors);
                }
            }

            $inside->set("prefixes", $prefixes);
            $inside->set("pcountries", $pcountries);
            $inside->set("countries", $countries);
            $inside->set("timezones", $timezones);
            $inside->set("languages", $languages);

            $inside->set("user", $user);
        } elseif ($this->param("action") == "password") {
            $inside = View::forge("aff/settings/password");
            if (null !== Input::post("input.password")) {
                $user_id = $this->get_user_id();

                $auser = Model_Whitelabel_Aff::find_by_pk($user_id);

                $val = Validation::forge();
                $val->add("input.password", _("New password"))
                    ->add_rule("trim")
                    ->add_rule("required")
                    ->add_rule('min_length', 6);

                if ($val->run()) {
                    $newsalt = Lotto_Security::generate_salt();
                    $newhash = Lotto_Security::generate_hash(
                        $val->validated('input.password'),
                        $newsalt
                    );

                    $auser->set([
                        'salt' => $newsalt,
                        'hash' => $newhash
                    ]);
                    $auser->save();

                    Session::set("aff.hash", $newhash);

                    Session::set_flash("message", ["success", _("Your password has been changed!")]);
                    Response::redirect("aff/settings");
                } else {
                    $errors = Lotto_Helper::generate_errors($val->error());
                    $inside->set("errors", $errors);
                }
            }

            $inside->set("user", $user);

            $this->view->inside = $inside;
        } else {
            $inside = View::forge("aff/settings/profile");

            $this->view->inside = $inside;
            $countries = Lotto_Helper::get_localized_country_list();
            $languages = Model_Language::get_all_languages();
            $pcountries = Lotto_Helper::filter_phone_countries($countries);

            $prefixes = Lotto_Helper::get_telephone_prefix_list();

            $inside->set("prefixes", $prefixes);
            $inside->set("pcountries", $pcountries);
            $inside->set("countries", $countries);
            $timezones = Lotto_Helper::get_timezone_list();
            $inside->set("timezones", $timezones);
            $inside->set("languages", $languages);

            $inside->set("user", $user);
        }

        return Response::forge($this->view);
    }

    /**
     * @return ?bool
     * null = all
     * true = casino
     * false = lotteries
     */
    private function isCasino(): ?bool
    {
        $campaignTypeInput = Input::get('filter.campaignType');

        if (in_array($campaignTypeInput, ['all', 'a'], true)) {
            return null;
        }

        $isCasino = $campaignTypeInput === 'casino';

        return $isCasino;
    }

    /**
     *
     * @param array $mediums
     * @param array $campaigns
     * @param array $contents
     * @return array
     */
    private function createFiltersForNewModels(
        array $mediums = null,
        array $campaigns = null,
        array $contents = null
    ): array {
        $columns = [];
        $joins = [];
        $mediumInput = Input::get("filter.medium");
        $campaignInput = Input::get("filter.campaign");
        $contentInput = Input::get("filter.content");

        $mediumPrefix = 'LEFT';
        if (!$this->isShowAllOptionSelected($mediumInput, true) && isset($mediums[$mediumInput])) {
            $columns[] = [
                'medium',
                'whitelabel_aff_medium_id',
                $mediums[$mediumInput]['id']
            ];
            $mediumPrefix = 'INNER';
        }
        $joins['medium'] = $mediumPrefix;

        $campaignPrefix = 'LEFT';
        if (!$this->isShowAllOptionSelected($campaignInput, true) && isset($campaigns[$campaignInput])) {
            $columns[] = [
                'campaign',
                'whitelabel_aff_campaign_id',
                $campaigns[Input::get("filter.campaign")]['id']
            ];
            $campaignPrefix = 'INNER';
        }

        $joins['campaign'] = $campaignPrefix;

        $contentPrefix = 'LEFT';
        if (!$this->isShowAllOptionSelected($contentInput, true) && isset($contents[$contentInput])) {
            $columns[] = [
                'content',
                'whitelabel_aff_content_id',
                $contents[$contentInput]['id']
            ];
            $contentPrefix = 'INNER';
        }
        $joins['content'] = $contentPrefix;

        return [
            'joins' => $joins,
            'columns' => $columns
        ];
    }

    /**
     * @deprecated
     *
     * @param array $mediums
     * @param array $campaigns
     * @param array $contents
     * @return array
     */
    private function create_filters(
        array $mediums = null,
        array $campaigns = null,
        array $contents = null
    ): array {
        $add = [];
        $params = [];
        $joins = [];

        // For all mediums should be LEFT
        $medium_prefix = "LEFT ";
        if (
            Input::get("filter.medium") !== "a" &&
            isset($mediums[Input::get("filter.medium")])
        ) {
            $add[] = " AND whitelabel_aff_medium_id = :medium";
            $params[] = [
                ":medium",
                $mediums[Input::get("filter.medium")]["id"]
            ];
            $medium_prefix = "INNER ";
        }
        $joins["medium"] = $medium_prefix;

        // For all campaigns should be LEFT
        $campaign_prefix = "LEFT ";
        if (
            Input::get("filter.campaign") !== "a" &&
            isset($campaigns[Input::get("filter.campaign")])
        ) {
            $add[] = " AND whitelabel_aff_campaign_id = :campaign";
            $params[] = [
                ":campaign",
                $campaigns[Input::get("filter.campaign")]["id"]
            ];
            $campaign_prefix = "INNER ";
        }

        $campaignType = Input::get('filter.campaignType');
        $issetSourceFilter = $campaignType !== 'all' && !empty(Input::get('filter.campaignType')) && is_bool($this->isCasino);
        if ($issetSourceFilter && $campaignType === 'casino') {
            $add[] = " AND is_casino = :isCasino";
            $params[] = [
                ":isCasino",
                $this->isCasino
            ];
            $campaign_prefix = "INNER ";
        }

        $joins["campaign"] = $campaign_prefix;

        // For all contents should be LEFT
        $content_prefix = "LEFT ";
        if (
            Input::get("filter.content") !== "a" &&
            isset($contents[Input::get("filter.content")])
        ) {
            $add[] = " AND whitelabel_aff_content_id = :content";
            $params[] = [
                ":content",
                $contents[Input::get("filter.content")]["id"]
            ];
            $content_prefix = "INNER ";
        }
        $joins["content"] = $content_prefix;

        return [
            "add" => $add,
            "params" => $params,
            "joins" => $joins
        ];
    }


    /**
     * Get proper pagination url for report type.
     * @param string $action url action.
     * return string pagination url;
     */
    private function get_pagination_url($action)
    {
        return ($this->isReportForAffiliates()) ?
            "/$action?" . http_build_query(Input::get()) :
            "/$action/subaffiliates/{$this->view->subaff_id}?" . http_build_query(Input::get());
    }

    /**
     * Get proper aff id for reports.
     */
    private function get_aff_id(): ?int
    {
        if ($this->isReportForAffiliates()) {
            return $this->get_user_id();
        }

        $inputSubAffiliateValue = Input::get("filter.subaff");
        $isSubAffIdValid = !$this->isShowAllOptionSelected($inputSubAffiliateValue) &&
            array_search($inputSubAffiliateValue, array_column($this->view->subaffs, "id")) !== false;

        if ($isSubAffIdValid) {
            $this->view->subaff_id = $inputSubAffiliateValue;
        }

        return $this->view->subaff_id;
    }

    /**
     * Prepare leads view.
     * @return View prepared leads view.
     */
    public function action_leads()
    {
        // check if user is logged in
        if ($this->isAffUserNotLogged()) {
            return Response::forge($this->view);
        }

        // create inside view
        $inside = Presenter::forge("aff/reports/leads");

        $userId = $this->get_user_id();
        $parentId = $this->getParentId();

        // set mediums, campaigns and contents into view
        $mediums = Model_Whitelabel_Aff_Medium::fetch_mediums($userId);
        /** @var WhitelabelAffCampaignRepository $repo */
        $repo = Container::get(WhitelabelAffCampaignRepository::class);
        $campaigns = $repo->getCampaigns($userId, $this->isCasino);

        $contents = Model_Whitelabel_Aff_Content::fetch_contents($userId);
        $inside->set("mediums", $mediums);
        $inside->set("campaigns", $campaigns);
        $inside->set("contents", $contents);

        // prepare filters
        $filters = $this->create_filters($mediums, $campaigns, $contents);

        // set countries and currencies
        $countries = Lotto_Helper::get_localized_country_list();
        $inside->set("countries", $countries);
        $inside->set("currencies", Lotto_Settings::getInstance()->get("currencies"));

        $leadsCount = Model_Whitelabel_User_Aff::fetch_count_for_leads(
            $filters["add"],
            $filters["params"],
            $userId,
            $parentId
        );

        // create config and forge pagination
        $config = [
            'pagination_url' => $this->get_pagination_url("leads"),
            'total_items' => $leadsCount,
            'per_page' => 25,
            'uri_segment' => 'page'
        ];
        $pagination = Pagination::forge('affspagination', $config);

        $leads = Model_Whitelabel_User_Aff::fetch_leads(
            $filters["add"],
            $filters["params"],
            $filters["joins"],
            $pagination,
            $userId,
            $parentId
        );

        $inside->set("regcount", $leads);
        $inside->set("pages", $pagination);

        // set content view into global view
        $this->view->inside = $inside;

        // forge and return view
        return Response::forge($this->view);
    }

    /**
     *
     * @return View
     */
    public function action_analytics()
    {
        if ($this->isAffUserNotLogged()) {
            return Response::forge($this->view);
        }
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        if (empty($whitelabel['analytics'])) {
            return View::forge("index/404");
        }

        $inside = View::forge("aff/settings/analytics");
        if (null !== Input::post("input.gaid")) {
            $user_id = $this->get_user_id();
            $auser = Model_Whitelabel_Aff::find_by_pk($user_id);

            $val = Validation::forge();
            $val->add("input.gaid", _("Google Analytics Tracking ID"))
                ->add_rule("trim")
                ->add_rule('valid_string', ['alpha', 'numeric', 'dashes']);

            if ($val->run()) {
                $auser->set([
                    'analytics' => $val->validated("input.gaid")
                ]);
                $auser->save();

                Session::set_flash("message", ["success", _("Google Analytics Tracking ID saved!")]);
                Response::redirect("aff/analytics");
            } else {
                $errors = Lotto_Helper::generate_errors($val->error());
                $inside->set("errors", $errors);
            }
        }

        $user = [];
        if (!empty($this->user)) {
            $user = $this->user;
        }

        $inside->set("user", $user);

        $this->view->inside = $inside;
        return Response::forge($this->view);
    }

    /**
     *
     * @return View
     */
    public function action_fbpixel()
    {
        if ($this->isAffUserNotLogged()) {
            return Response::forge($this->view);
        }

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        if (empty($whitelabel['fb_pixel'])) {
            return View::forge("index/404");
        }
        $inside = View::forge("aff/settings/fbpixel");

        if (null !== Input::post("input.fbpixel")) {
            $user_id = $this->get_user_id();
            $auser = Model_Whitelabel_Aff::find_by_pk($user_id);

            $val = Validation::forge();
            $val->add("input.fbpixel", _("Facebook Pixel ID"))
                ->add_rule("trim")
                ->add_rule('valid_string', ['alpha', 'numeric', 'dashes']);

            $val->add("input.fbmatch", _("Activate Advanced Matching"))
                ->add_rule("trim")
                ->add_rule("match_value", 1);

            if ($val->run()) {
                $auser->set([
                    'fb_pixel' => $val->validated("input.fbpixel"),
                    "fb_pixel_match" => $val->validated("input.fbmatch") == 1 ? 1 : 0
                ]);

                $auser->save();

                Session::set_flash("message", ["success", _("Facebook Pixel ID saved!")]);
                Response::redirect("aff/fbpixel");
            } else {
                $errors = Lotto_Helper::generate_errors($val->error());
                $inside->set("errors", $errors);
            }
        }

        $user = [];
        if (!empty($this->user)) {
            $user = $this->user;
        }

        $inside->set("user", $user);

        $this->view->inside = $inside;
        return Response::forge($this->view);
    }

    /**
     * Prepare view aff/reports/commissions.
     * @return View prepared view.
     */
    public function action_casinoCommissions()
    {
        // check if user is logged in
        if ($this->isAffUserNotLogged()) {
            return Response::forge($this->view);
        }

        // create inside view
        $inside = Presenter::forge("aff/reports/CasinoCommissions");

        $affId = $this->get_aff_id();
        $userId = $this->get_user_id();
        $parentId = $this->getParentId();
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        // set mediums, campaigns and contents into view
        $mediums = Model_Whitelabel_Aff_Medium::fetch_mediums($userId);

        /** @var WhitelabelAffCampaignRepository $whitelabelAffCampaignRepository */
        $whitelabelAffCampaignRepository = Container::get(WhitelabelAffCampaignRepository::class);
        $campaigns = $whitelabelAffCampaignRepository->getCampaigns($userId, $this->isCasino);

        $contents = Model_Whitelabel_Aff_Content::fetch_contents($userId);
        $inside->set("mediums", $mediums);
        $inside->set("campaigns", $campaigns);
        $inside->set("contents", $contents);

        // prepare filters
        $filters = $this->createFiltersForNewModels($mediums, $campaigns, $contents);

        // set countries and currencies into view
        $countries = Lotto_Helper::get_localized_country_list();
        $inside->set("countries", $countries);
        $inside->set("currencies", Lotto_Settings::getInstance()->get("currencies"));

        $isSubAffiliateTab = $this->isReportForSubAffiliates();

        $commissionCount = $this->whitelabelAffSlotCommissionRepository->getCasinoCommissionCount(
            $filters['columns'],
            $filters['joins'],
            null,
            $parentId,
            $affId,
            $isSubAffiliateTab,
            null,
            null,
            $whitelabel['id'],
            'count'
        );

        // create config and forge pagination
        $config = [
            'pagination_url' => $this->get_pagination_url("commissions"),
            'total_items' => $commissionCount,
            'per_page' => 25,
            'uri_segment' => 'page'
        ];

        $pagination = Pagination::forge('affspagination', $config);

        $casinoCommissions = $this->whitelabelAffSlotCommissionRepository->findCasinoCommissions(
            $filters['columns'],
            $filters['joins'],
            $pagination,
            $parentId,
            $affId,
            $isSubAffiliateTab,
            null,
            null,
            $whitelabel['id']
        );

        $inside->set("casinoCommissions", $casinoCommissions);
        $inside->set("pages", $pagination);

        // set content view into global view
        $this->view->inside = $inside;

        // forge and return view
        return Response::forge($this->view);
    }

    /**
     * Prepare view aff/reports/commissions.
     * @return View prepared view.
     */
    public function action_commissions()
    {
        // check if user is logged in
        if ($this->isAffUserNotLogged()) {
            return Response::forge($this->view);
        }

        // create inside view
        $inside = Presenter::forge("aff/reports/commissions");

        $affId = $this->get_aff_id();
        $userId = $this->get_user_id();
        $parentId = $this->getParentId();
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        // set mediums, campaigns and contents into view
        $mediums = Model_Whitelabel_Aff_Medium::fetch_mediums($userId);

        /** @var WhitelabelAffCampaignRepository $whitelabelAffCampaignRepository */
        $whitelabelAffCampaignRepository = Container::get(WhitelabelAffCampaignRepository::class);
        $campaigns = $whitelabelAffCampaignRepository->getCampaigns($userId, $this->isCasino);

        $contents = Model_Whitelabel_Aff_Content::fetch_contents($userId);
        $inside->set("mediums", $mediums);
        $inside->set("campaigns", $campaigns);
        $inside->set("contents", $contents);

        // prepare filters
        $filters = $this->createFiltersForNewModels($mediums, $campaigns, $contents);

        // set countries and currencies into view
        $countries = Lotto_Helper::get_localized_country_list();
        $inside->set("countries", $countries);
        $inside->set("currencies", Lotto_Settings::getInstance()->get("currencies"));

        $isSubAffiliateTab = $this->isReportForSubAffiliates();

        $commissionCount = $this->whitelabelAffCommissionRepository->getCommissionCount(
            $filters['columns'],
            $filters['joins'],
            null,
            $parentId,
            $affId,
            $isSubAffiliateTab,
            null,
            null,
            $whitelabel['id'],
            'count'
        );

        // create config and forge pagination
        $config = [
            'pagination_url' => $this->get_pagination_url("commissions"),
            'total_items' => $commissionCount,
            'per_page' => 25,
            'uri_segment' => 'page'
        ];

        $pagination = Pagination::forge('affspagination', $config);

        $commissions = $this->whitelabelAffCommissionRepository->findCommissions(
            $filters['columns'],
            $filters['joins'],
            $pagination,
            $parentId,
            $affId,
            $isSubAffiliateTab,
            null,
            null,
            $whitelabel['id']
        );

        $inside->set("commissions", $commissions);
        $inside->set("pages", $pagination);

        // set content view into global view
        $this->view->inside = $inside;

        // forge and return view
        return Response::forge($this->view);
    }

    /**
     *
     * @return View
     */
    public function action_payouts()
    {
        if ($this->isAffUserNotLogged()) {
            return Response::forge($this->view);
        }

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        $user = null;
        if (!empty($this->user)) {
            $user = $this->user;
        }

        $aff_payouts_obj = new Forms_Whitelabel_Aff_Payouts(
            $whitelabel,
            Helpers_General::SOURCE_AFF,
            $user
        );
        $view_template = "aff/reports/payouts";
        $aff_payouts_obj->process_form($view_template);

        $inside = $aff_payouts_obj->get_inside();
        $this->view->inside = $inside;

        $this->view->set_global("title", _("Payouts"));
        return Response::forge($this->view);
    }

    /**
     * Prepare view for aff/reports/ftps.
     * @return View prepared view.
     */
    public function action_ftps()
    {
        // check if user is logged in
        if ($this->isAffUserNotLogged()) {
            return Response::forge($this->view);
        }

        // create inside view
        $inside = Presenter::forge("aff/reports/ftps");

        $userId = $this->get_user_id();
        $parentId = $this->getParentId();

        // set mediums, campaigns and contents into view
        $mediums = Model_Whitelabel_Aff_Medium::fetch_mediums($userId);
        /** @var WhitelabelAffCampaignRepository $whitelabelAffCampaignRepository */
        $whitelabelAffCampaignRepository = Container::get(WhitelabelAffCampaignRepository::class);
        $campaigns = $whitelabelAffCampaignRepository->getCampaigns($userId, $this->isCasino);

        $contents = Model_Whitelabel_Aff_Content::fetch_contents($userId);
        $inside->set("mediums", $mediums);
        $inside->set("campaigns", $campaigns);
        $inside->set("contents", $contents);

        // prepare filters
        $filters = $this->create_filters($mediums, $campaigns, $contents);

        // set countries and currencies
        $countries = Lotto_Helper::get_localized_country_list();
        $inside->set("countries", $countries);
        $inside->set("currencies", Lotto_Settings::getInstance()->get("currencies"));

        $count = Model_Whitelabel_User_Aff::fetch_count_for_ftps(
            $filters['add'],
            $filters['params'],
            $userId,
            $parentId
        );

        // create config and forge pagination
        $config = [
            'pagination_url' => $this->get_pagination_url("ftps"),
            'total_items' => $count,
            'per_page' => 25,
            'uri_segment' => 'page'
        ];
        $pagination = Pagination::forge('affspagination', $config);

        $ftpcount = Model_Whitelabel_User_Aff::fetch_ftp(
            $filters['add'],
            $filters['params'],
            $filters['joins'],
            $pagination,
            $userId,
            $parentId
        );

        $inside->set("ftpcount", $ftpcount);
        $inside->set("pages", $pagination);

        // set content view into global view
        $this->view->inside = $inside;

        // forge and return view
        return Response::forge($this->view);
    }

    private function prepareFiltersForSubAffiliate(): array
    {
        $filters = [];
        if (Input::get("filter.id") != null) {
            $filters[] = ['token', '%' . Input::get("filter.id") . '%'];
        }
        if (Input::get("filter.email") != null) {
            $filters[] = ['email', '%' . Input::get("filter.email") . '%'];
        }
        if (Input::get("filter.login") != null) {
            $filters[] = ['login', '%' . Input::get("filter.login") . '%'];
        }
        if (Input::get("filter.language") != null && Input::get("filter.language") != "a") {
            $filters[] = ['language_id', intval(Input::get("filter.language"))];
        }
        if (Input::get("filter.country") != null && Input::get("filter.country") != "a") {
            $filters[] = ['country', '%' . Input::get("filter.country") . '%'];
        }
        if (Input::get("filter.name") != null) {
            $filters[] = ['name', '%' . Input::get("filter.name") . '%'];
        }
        if (Input::get("filter.surname") != null) {
            $filters[] = ['surname', '%' . Input::get("filter.surname") . '%'];
        }

        return $filters;
    }

    /**
     * This function handle action subaffiliates/details, prepare and create view for them.
     * @return view prepared view.
     */
    private function subactionSubAffiliatesDetails()
    {
        // user is logged in, proceed
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        // create inside view
        $inside = Presenter::forge("aff/reports/details");

        // fetch details
        $details = Model_Whitelabel_Aff::fetch_subaffiliate_details($this->view->subaff_id);

        // save details to view
        $inside->set("details", $details);

        $inside->set("whitelabel", $whitelabel);

        // set countries
        $inside->set("countries", Lotto_Helper::get_localized_country_list());

        // set content view into global view
        $this->view->inside = $inside;

        // forge and return view
        return Response::forge($this->view);
    }

    /**
     * This function handle action subaffiliates/create, prepare and create view for them.
     */
    private function subactionSubAffiliatesCreateView(): Response
    {
        // user is logged in, proceed
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        // whitelabel cannot create sub-aff
        if (!$this->affiliateCanCreateSubAffiliates($whitelabel)) {
            Response::redirect("/subaffiliates");
        }

        // create inside view
        $inside = Presenter::forge("aff/new");

        // set content view into global view
        $this->view->inside = $inside;

        // forge and return view
        return Response::forge($this->view);
    }

    /**
     * @deprecated new validators: https://gginternational.slite.com/app/channels/4mtH3PN_5R/notes/tBx1Qwuasx
     */
    private function storeSubAffiliateValidation(): Validation
    {
        $validation = Validation::forge();

        $validation->add("input.login", _('Login'))
            ->add_rule('trim')
            ->add_rule("required")
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 30)
            ->add_rule('valid_string', ['alpha', 'numeric', 'dashes']);

        $validation->add("input.email", _("E-mail"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_email");

        $validation->add("input.password", _("Password"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule('min_length', 6);

        return $validation;
    }

    /**
     * @throws Exception when input data from form is invalid
     */
    private function subAffiliatePrepareAndInsert(
        WhitelabelAff $affUser,
        Validation $validatedForm,
        array $whitelabel
    ): WhitelabelAff {
        $newSalt = Lotto_Security::generate_salt();
        $hash = Lotto_Security::generate_hash(
            $validatedForm->validated("input.password"),
            $newSalt
        );

        $token = Lotto_Security::generate_aff_token($whitelabel['id']);
        $subAffiliateToken = Lotto_Security::generate_aff_token($whitelabel['id']);

        // null is default group id
        $whitelabelAffDefaultGroupId = null;

        $defaultLangId = Helpers_General::get_default_language_id();

        // it is the affiliate id of the logged in user
        $whitelabelAffParentId = $affUser['id'];

        // insert new affiliate into database
        try {
            $newAffiliate = $this->whitelabelAffRepository->insert(
                $whitelabel,
                $whitelabelAffParentId,
                $token,
                $subAffiliateToken,
                $defaultLangId,
                $whitelabelAffDefaultGroupId,
                true,
                true,
                true,
                $validatedForm->validated("input.login"),
                $validatedForm->validated("input.email"),
                $hash,
                $newSalt
            );
        } catch (Throwable $t) {
            throw new Exception('Invalid input data format');
        }

        return $newAffiliate;
    }

    private function subActionSubAffiliatesStore()
    {
        // user is logged in, proceed
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        // create inside view
        $inside = Presenter::forge("aff/new");

        // whitelabel cannot create sub-aff
        if (!$this->affiliateCanCreateSubAffiliates($whitelabel)) {
            Response::redirect("/subaffiliates");
        }

        // validation store request
        $validatedForm = $this->storeSubaffiliateValidation();

        // set content view into global view
        $this->view->inside = $inside;

        if (!$validatedForm->run()) {
            $errors = Lotto_Helper::generate_errors($validatedForm->error());
            $this->view->inside->set("errors", $errors);

            return Response::forge($this->view);
        }

        $result = Model_Whitelabel_Aff::get_count_for_whitelabel(
            $whitelabel,
            $validatedForm->validated("input.email"),
            $validatedForm->validated("input.login")
        );

        if (is_null($result)) {
            Session::set_flash("message", ["danger", _("There is something wrong with DB!")]);
            return Response::forge($this->view);
        }

        $affCount = $result[0]['count'];

        if ((int)$affCount === 0) {
            $affUser = $this->whitelabelAffRepository->findAffiliateById($this->user['id']);

            try {
                $this->subAffiliatePrepareAndInsert($affUser, $validatedForm, $whitelabel);

                Session::set_flash("message", ["success", _("Your sub-affiliate has been created!")]);
                Response::redirect("/subaffiliates");
            } catch (Exception $exception) {
                Session::set_flash("message", ["danger", $exception->getMessage()]);
            }
        } else {
            Session::set_flash("message", ["danger", _("The email address and/or login you have provided are already in use.")]);
            return Response::forge($this->view);
        }

        // forge and return view
        return Response::forge($this->view);
    }

    /**
     * This function handle action subaffiliates and prepare and create view.
     * @return view prepared view.
     */
    public function action_subaffiliates()
    {
        // check if user is logged in
        if ($this->isAffUserNotLogged()) {
            return Response::forge($this->view);
        }

        // user is logged in, proceed
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        switch ($this->param("subaction")) {
            case 'details':
                return $this->subactionSubAffiliatesDetails();
            case 'create':
                return $this->subactionSubAffiliatesCreateView();
            case 'store':
                return $this->subactionSubAffiliatesStore();
            default:
                break;
        }

        // create inside view
        $inside = Presenter::forge("aff/reports/subaffiliates");

        // prepare filters
        $filters = $this->prepareFiltersForSubAffiliate();

        // fetch languages, countries, groups and set them to view. They will be used in filters.
        $languages = Model_Language::get_all_languages();
        $countries = Lotto_Helper::get_localized_country_list();
        $groups = Model_Whitelabel_Aff_Group::get_whitelabel_groups($whitelabel);
        $inside->set("languages", $languages);
        $inside->set("countries", $countries);
        $inside->set("groups", $groups);

        $user_id = $this->get_user_id();

        $subAffiliatesCount = $this->whitelabelAffRepository
            ->countSubAffiliatesByParentId($user_id, $filters);

        // create config and forge pagination
        $config = [
            'pagination_url' => '/subaffiliates?' . http_build_query(Input::get()),
            'total_items' => $subAffiliatesCount,
            'per_page' => 15,
            'uri_segment' => 'page'
        ];
        $pagination = Pagination::forge('affspagination', $config);

        // fetch subaffiliates with filters
        $subAffiliates = $this->whitelabelAffRepository
            ->findSubAffiliatesByParentId($user_id, $pagination, $filters);

        // set subaffs and pagination to view
        $inside->set("subaffiliates", $subAffiliates);
        $inside->set("pages", $pagination);

        // generating a sub affiliate link
        $subAffiliateLink = 'https://' . $whitelabel['domain'] . '/?ref=' . strtoupper($this->user['sub_affiliate_token']);
        $casinoSubAffiliateLink = UrlHelper::changeAbsoluteUrlToCasinoUrl($subAffiliateLink, true);
        $inside->set("subaffiliateLink", $subAffiliateLink);
        $inside->set("casinoSubaffiliateLink", $casinoSubAffiliateLink);

        $affiliateCanCreateSubAffiliates = $this->affiliateCanCreateSubAffiliates($whitelabel);
        $inside->set("affiliateCanCreateSubAffiliates", $affiliateCanCreateSubAffiliates);

        // set content view into global view
        $this->view->inside = $inside;

        // forge and return view
        return Response::forge($this->view);
    }

    /**
     * Prepare data for and render aff/reports/index view.
     * @return View prepared view.
     */
    public function action_reports()
    {
        // check if user is logged in
        if ($this->isAffUserNotLogged()) {
            return Response::forge($this->view);
        }

        // create inside view
        $inside = Presenter::forge("aff/reports/index");

        $userId = $this->get_user_id();
        $affId = $this->get_aff_id();
        $parentId = $this->getParentId();
        $subAffiliateIds = $this->getSubAffiliateIds($userId, $this->isReportForAffiliates());
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        // set mediums, campaigns and contents into view
        $mediums = Model_Whitelabel_Aff_Medium::fetch_mediums($userId);

        /** @var WhitelabelAffCampaignRepository $whitelabelAffCampaignRepository */
        $whitelabelAffCampaignRepository = Container::get(WhitelabelAffCampaignRepository::class);
        $campaigns = $whitelabelAffCampaignRepository->getCampaigns($userId, $this->isCasino);

        $contents = Model_Whitelabel_Aff_Content::fetch_contents($userId);
        $inside->set("mediums", $mediums);
        $inside->set("campaigns", $campaigns);
        $inside->set("contents", $contents);

        // check if range_start is valid
        if (Input::get("filter.range_start") !== null) {
            // prepare filters
            $filters = $this->create_filters($mediums, $campaigns, $contents);

            // set countries and currencies
            $countries = Lotto_Helper::get_localized_country_list();
            $inside->set("countries", $countries);
            $inside->set("currencies", Lotto_Settings::getInstance()->get("currencies"));

            // get date ranges from Trait
            $dates = $this->prepare_dates();

            // set dates into view
            $inside->set("date_start", $dates['date_start']);
            $inside->set("date_end", $dates['date_end']);

            // add dates to global params
            $filters["params"][] = [":date_start", $dates["date_start"]];
            $filters["params"][] = [":date_end", $dates["date_end"]];

            // fetch clicks
            $clicks = Model_Whitelabel_Aff_Click::fetch_clicks(
                $filters["add"],
                $filters["params"],
                $affId,
                $parentId
            );

            // set clicks to view
            $inside->set("clicks", $clicks);

            $leads = Model_Whitelabel_User_Aff::fetch_leads_for_reports(
                $filters["add"],
                $filters["params"],
                $filters["joins"],
                $subAffiliateIds,
                $parentId
            );

            $inside->set("regcount", $leads);

            $ftpCount = Model_Whitelabel_User_Aff::fetch_ftp_for_reports(
                $filters["add"],
                $filters["params"],
                $filters["joins"],
                $subAffiliateIds,
                $parentId
            );

            $inside->set("ftpcount", $ftpCount);

            $ftdCount = Model_Whitelabel_User_Aff::fetch_ftd_for_reports(
                $filters["add"],
                $filters["params"],
                $filters["joins"],
                $subAffiliateIds,
                $parentId
            );
            $inside->set('ftdCount', $ftdCount);

            $isSubAffiliateTab = $this->isReportForSubAffiliates();
            $filtersNew = $this->createFiltersForNewModels($mediums, $campaigns, $contents);

            $commissions = $this->whitelabelAffCommissionRepository->findCommissions(
                $filtersNew['columns'],
                $filtersNew['joins'],
                null,
                $parentId,
                $affId,
                $isSubAffiliateTab,
                $dates["date_start"],
                $dates["date_end"],
                $whitelabel['id'],
            );

            $casinoCommissions = $this->whitelabelAffSlotCommissionRepository->findCasinoCommissions(
                $filtersNew['columns'],
                $filtersNew['joins'],
                null,
                $parentId,
                $affId,
                $isSubAffiliateTab,
                null,
                null,
                $whitelabel['id']
            );

            // calculate total commissions
            $totalLotteryCommissionManager = 0;
            $totalCasinoCommissionManager = 0;
            foreach ($commissions as $commission) {
                $totalLotteryCommissionManager = round($totalLotteryCommissionManager + $commission['commission_manager'], 2);
            }

            foreach ($casinoCommissions as $commission) {
                $totalCasinoCommissionManager = round($totalCasinoCommissionManager + $commission['daily_commission_usd'], 2);
            }

            $totalCommissionManager = round($totalLotteryCommissionManager + $totalCasinoCommissionManager, 2);

            // set total commissions and commissions into view
            $inside->set('totalLotteryCommission', $totalLotteryCommissionManager);
            $inside->set('totalCasinoCommission', $totalCasinoCommissionManager);
            $inside->set('totalCommission', $totalCommissionManager);
            $inside->set('commissions', $commissions);
            $inside->set('casinoCommissions', $casinoCommissions);
        }

        // set content view into global view
        $this->view->inside = $inside;

        // forge and return view
        return Response::forge($this->view);
    }

    /**
     *
     * @return View
     */
    public function action_banners()
    {
        if ($this->isAffUserNotLogged()) {
            return Response::forge($this->view);
        }

        $this->view->inside = View::forge("aff/banners/index");

        // Get all lotteries and languages available for whitelabel
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        $lotteries = Model_Lottery::get_all_lotteries_for_whitelabel($whitelabel);
        $whitelabel_languages = Model_Whitelabel_Language::get_whitelabel_languages($whitelabel);

        $this->view->inside->set("whitelabel", $whitelabel);
        $this->view->inside->set("langs", $whitelabel_languages);
        $this->view->inside->set("lotteries", $lotteries);

        // Get sizes and sort them
        $sizes = Banners_Create::$allowed_methods;
        // sort($sizes);

        $this->view->inside->set("sizes", $sizes);

        $this->view->set_global("title", _("Banners"));

        // Process form
        $aff_links = new Forms_Aff_Links($whitelabel);

        if (!empty($this->user) && !is_null($this->user)) {
            $aff_links->process_banner_form($this->view->inside, $this->user);
        }

        return Response::forge($this->view);
    }

    /**
     *
     * @return View
     */
    public function action_widgets()
    {
        if ($this->isAffUserNotLogged()) {
            return Response::forge($this->view);
        }

        $this->view->inside = View::forge("aff/banners/widgets");

        // Get all lotteries and languages available for whitelabel
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        $lotteries = Model_Lottery::get_all_lotteries_for_whitelabel($whitelabel);
        $whitelabel_languages = Model_Whitelabel_Language::get_whitelabel_languages($whitelabel);

        $this->view->inside->set("whitelabel", $whitelabel);
        $this->view->inside->set("langs", $whitelabel_languages);
        $this->view->inside->set("lotteries", $lotteries);

        // Get widget types and sort them
        $types = Banners_Widgets::$allowed_methods;

        $this->view->inside->set("types", $types);

        $this->view->set_global("title", _("Widgets"));

        // Process form
        $aff_links = new Forms_Aff_Links($whitelabel);

        if (!empty($this->user)) {
            $this->view->inside->set("aff", $this->user);
            $aff_links->process_widgets_form($this->view->inside, $this->user);
        }

        return Response::forge($this->view);
    }
}
