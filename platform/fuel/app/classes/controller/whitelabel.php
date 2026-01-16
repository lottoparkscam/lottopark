<?php

use Fuel\Core\Controller;
use Fuel\Core\Presenter;
use Fuel\Core\Session;
use Fuel\Core\Response;
use Fuel\Core\View;
use Services\AffiliateGroupService;
use Services\Logs\FileLoggerService;
use Fuel\Core\Input;
use Fuel\Core\Pagination;
use Services\AffCasinoCommissionService;
use Repositories\WhitelabelAffSlotCommissionRepository;
use Repositories\WhitelabelAffCasinoGroupRepository;
use Forms\Wordpress\Forms_Wordpress_Email;
use Helpers\UserHelper;
use Repositories\MiniGamePromoCodeRepository;
use Repositories\MiniGameRepository;
use Repositories\MiniGameUserPromoCodeRepository;
use Services\MiniGame\MiniGamePromoCodeService;

/**
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel
 * @version    1.8
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2016 Fuel Development Team
 * @link       http://fuelphp.com
 */

/**
 * The Welcome Controller.
 *
 * A basic controller example.  Has examples of how to set the
 * response body and status.
 *
 * @package  app
 * @extends  Controller
 */
class Controller_Whitelabel extends Controller
{
    /**
     *
     * Get Trait for CSV Export
     */
    use Traits_Gets_Csv;

    /**
     * Get Trait for date range preparation
     */
    use Traits_Gets_Date;

    /**
     * The basic welcome message
     *
     * @access  public
     * @return  Response
     */
    protected $view;

    /**
     *
     * @var bool
     */
    protected $is_user;

    /**
     *
     * @var array
     */
    protected $settings;

    /**
     * This is only for preparation - at this moment it does mean noting
     *
     * @var array
     */
    private $user = null;

    private FileLoggerService $fileLoggerService;
    private AffCasinoCommissionService $affCasinoCommissionService;
    private WhitelabelAffSlotCommissionRepository $whitelabelAffSlotCommissionRepository;
    private WhitelabelAffCasinoGroupRepository $whitelabelAffCasinoGroupRepository;
    private AffiliateGroupService $affiliateGroupService;
    private MiniGamePromoCodeRepository $miniGamePromoCodeRepository;
    private MiniGameRepository $miniGameRepository;
    private MiniGamePromoCodeService $miniGamePromoCodeService;
    private MiniGameUserPromoCodeRepository $miniGameUserPromoCodeRepository;

    /**
     *
     * @return type
     */
    public function before()
    {
        if (file_exists(APPPATH . '/.maintenance')) {
            http_response_code(503);
            exit(file_get_contents(APPPATH . '/.maintenance'));
        }

        if (!Lotto_Helper::allow_access("manager")) {
            $error = Request::forge('index/404')->execute();
            echo $error;
            exit();
        }

        $this->fileLoggerService = Container::get(FileLoggerService::class);
        $this->affCasinoCommissionService = Container::get(AffCasinoCommissionService::class);
        $this->whitelabelAffSlotCommissionRepository = Container::get(WhitelabelAffSlotCommissionRepository::class);
        $this->whitelabelAffCasinoGroupRepository = Container::get(WhitelabelAffCasinoGroupRepository::class);
        $this->affiliateGroupService = Container::get(AffiliateGroupService::class);
        $this->miniGamePromoCodeRepository = Container::get(MiniGamePromoCodeRepository::class);
        $this->miniGameRepository = Container::get(MiniGameRepository::class);
        $this->miniGamePromoCodeService = Container::get(MiniGamePromoCodeService::class);
        $this->miniGameUserPromoCodeRepository = Container::get(MiniGameUserPromoCodeRepository::class);

        $domain = $_SERVER['HTTP_HOST'];
        $domain = explode('.', $domain);
        if ($domain[0] == "www") {
            array_shift($domain);
        }
        array_shift($domain);
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
                bindtextdomain("admin", APPPATH . "lang/gettext");
                textdomain("admin");
                Config::set("language", substr($lang['code'], 0, 2));
            }
        }

        $currencies = Helpers_Currency::getCurrencies();

        $user = null;

        $timezone = "UTC";
        if (!empty($whitelabel['timezone'])) {
            $timezone = $whitelabel['timezone'];
        }
        Lotto_Settings::getInstance()->set("timezone", $timezone);

        $this->is_user = false;
        $this->view = View::forge("whitelabel/index");
        $this->view->set_global("whitelabel", $whitelabel);

        $login = new Forms_Login("whitelabel");
        $result = $login->process_form($this->view, $whitelabel);

        switch ($result) {
            case Forms_Login::RESULT_OK:
                $source = Session::get("source");
                if ($source != 'admin') {
                    Model_Whitelabel::update_last_login_and_last_active($whitelabel['id']);
                    Lotto_Helper::clear_cache(['model_whitelabel']);
                }
                Response::redirect("/");
                break;
            case Forms_Login::RESULT_GO_FURTHER:
            case Forms_Login::RESULT_SECURITY_ERROR:
            case Forms_Login::RESULT_TOO_MANY_ATTEMPTS:
            case Forms_Login::RESULT_WRONG_CREDENTIALS:
            case Forms_Login::RESULT_EMPTY_SALT:
                break;
        }

        $source = Session::get("source");
        if (isset($source)) {
            $this->set_session_user($source);
        }

        $this->view->set_global("action", $this->request->action);
        $this->view->set_global("params", $this->request->params());
        $this->view->set_global("is_user", $this->is_user);

        $titles_arr = [
            "users" => _("Active users"),
            "inactive" => _("Inactive users"),
            "deleted" => _("Deleted users"),
            "transactions" => _("Purchases"),
            "deposits" => _("Deposits"),
            "withdrawals" => _("Withdrawals"),
            "tickets" => _("Tickets"),
            "winners" => _("Winners"),
            "reports" => _("Generate report"),
            "index" => $this->is_user ? _("Dashboard") : null
        ];

        $title = null;
        if (isset($titles_arr[$this->request->action])) {
            $title = $titles_arr[$this->request->action];
        }

        $this->view->set_global("title", $title);

        $this->view->header = View::forge("whitelabel/shared/header");
        $this->view->footer = View::forge("whitelabel/shared/footer");

        if (!$this->is_user) {
            $this->view->inside = View::forge("whitelabel/signin/index");
        } elseif ($source != 'admin') {
            Model_Whitelabel::update_last_active($whitelabel['id']);
            Lotto_Helper::clear_cache(['model_whitelabel']);
        }
    }

    /**
     *
     * @return Response
     */
    public function action_signout(): Response
    {
        if (!empty(Session::get("whitelabel"))) {
            Session::delete("whitelabel");
        } elseif (!empty(Session::get("admin"))) {
            Session::delete("source");
            Session::delete("admin");
        }

        Session::set(UserHelper::SHOULD_LOGOUT_AFTER_BROWSER_CLOSE_KEY, true);
        Response::redirect('/');
    }

    /**
     *
     * @return void
     * @param string $source
     */
    private function set_session_user(string $source): void
    {
        $source_name = $source . '.' . 'name';
        $source_hash = $source . '.' . 'hash';
        $source_remember = $source . '.' . 'remember';

        $hashed = false;
        if ($source == 'admin') {
            $hashed = Model_Setting::check_admin_credentials_hashed(
                Session::get($source_name),
                Session::get($source_hash)
            );
        } else {
            $hashed = Lotto_Security::check_whitelabel_credentials_hashed(
                Session::get($source_name),
                Session::get($source_hash)
            );
        }

        if (
            Session::get($source_remember) === null ||
            (int)Session::get($source_remember) === 0
        ) {
            Session::set(UserHelper::SHOULD_LOGOUT_AFTER_BROWSER_CLOSE_KEY, true);
        }

        if (
            !empty(Session::get($source_name)) &&
            !empty(Session::get($source_hash)) &&
            $hashed
        ) {
            $this->is_user = true;
        }
    }

    /**
     *
     * @return void
     */
    public function action_ajaxpassword(): void
    {
        echo bin2hex(Lotto_Security::get_random_pseudobytes(6));
        exit();
    }

    /**
     *
     * @return Response
     */
    public function action_prepaid(): Response
    {
        if (!$this->is_user) {
            return Response::forge($this->view);
        }

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        if (Helpers_Whitelabel::is_V1($whitelabel['type'])) {
            return Response::forge($this->view);
        }

        $path_to_view = "whitelabel/prepaid/list";
        $request_page = $this->request->param("page");

        $prepaid_list = new Forms_Admin_Whitelabels_Prepaid_List(
            Helpers_General::SOURCE_WHITELABEL,
            $whitelabel,
            $request_page
        );
        $prepaid_list->set_inside_by_path_to_view($path_to_view);
        $result = $prepaid_list->process_form();

        switch ($result) {
            case Forms_Admin_Whitelabels_Prepaid_List::RESULT_OK:
                $inside = $prepaid_list->get_inside();
                $this->view->inside = $inside;
                break;
            case Forms_Admin_Whitelabels_Prepaid_List::RESULT_NULL_DATA:
                $this->fileLoggerService->error(
                    "There is a problem with pull data of whitelabels prepaids. Received null."
                );
                exit('Bad request');
                break;
        }

        $this->view->set_global("title", _("Prepaid transactions"));
        return Response::forge($this->view);
    }

    /**
     *
     * @param array $whitelabel
     * @return void
     */
    private function account_password($whitelabel): void
    {
        $account_password = new Forms_Whitelabel_Account_Password($whitelabel);
        $result = $account_password->process_form();

        switch ($result) {
            case Forms_Whitelabel_Account_Password::RESULT_OK:
                Response::redirect("account");
                break;
            case Forms_Whitelabel_Account_Password::RESULT_GO_FURTHER:
            case Forms_Whitelabel_Account_Password::RESULT_WITH_ERRORS:
            default:
                $inside = $account_password->get_inside();
                $this->view->inside = $inside;
                break;
        }
    }

    /**
     *
     * @param array $whitelabel
     * @param bool $edit_manager_currency
     * @return void
     */
    private function account_edit($whitelabel, $edit_manager_currency = false): void
    {
        $account_edit = new Forms_Whitelabel_Account_Edit(
            $whitelabel,
            true,
            $edit_manager_currency
        );
        $result = $account_edit->process_form();

        switch ($result) {
            case Forms_Whitelabel_Account_Edit::RESULT_OK:
                Response::redirect("account");
                break;
            case Forms_Whitelabel_Account_Edit::RESULT_GO_FURTHER:
            case Forms_Whitelabel_Account_Edit::RESULT_WITH_ERRORS:
            default:
                $inside = $account_edit->get_inside();
                $this->view->inside = $inside;
                break;
        }
    }

    /**
     *
     * @param array $whitelabel
     * @param bool $edit_manager_currency
     * @return void
     */
    private function account_view($whitelabel, $edit_manager_currency = false): void
    {
        $inside = View::forge("whitelabel/settings/account");

        $inside->set("whitelabel", $whitelabel);
        $timezones = Lotto_Helper::get_timezone_list();
        $inside->set("timezones", $timezones);
        $languages = Model_Language::get_all_languages();
        $inside->set("languages", $languages);

        $inside->set("edit_manager_currency", $edit_manager_currency);

        $this->view->inside = $inside;
    }

    /**
     *
     * @return Response
     */
    public function action_account(): Response
    {
        if (!$this->is_user) {
            return Response::forge($this->view);
        }
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        $edit_manager_currency = false;

        switch ($this->param("action")) {
            case "password":
                $this->account_password($whitelabel);
                break;
            case "edit":
                $this->account_edit($whitelabel, $edit_manager_currency);
                break;
            default:
                $this->account_view($whitelabel, $edit_manager_currency);
                break;
        }

        $this->view->set_global("title", _("Account settings"));
        return Response::forge($this->view);
    }

    /**
     *
     * @return void
     */
    public function action_slip(): void
    {
        $id = $this->param("image");
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        $slip = Model_Whitelabel_User_Ticket_Slip::get_ticket_id_for_slip($id);

        if (
            $slip !== null && count($slip) == 1 &&
            $whitelabel['id'] == $slip[0]['whitelabel_id']
        ) {
            header('Content-Type: image/jpeg');
            echo file_get_contents($slip[0]['ticket_scan_url']);
            exit();
        }
        http_response_code(404);
        exit();
    }

    /**
     *
     * @param array $whitelabel
     * @param string $rparam
     * @return void
     */
    private function aff_delete(array $whitelabel, string $rparam): void
    {
        $token = (string) $this->param("id");
        $affs = Model_Whitelabel_Aff::find_by([
            "whitelabel_id" => $whitelabel['id'],
            "token" => $token
        ]);

        if (
            $affs !== null &&
                count($affs) > 0 &&
                (int)$affs[0]->whitelabel_id === (int)$whitelabel['id'] &&
                (int)$affs[0]->is_deleted === 0
        ) {
            $aff = $affs[0];
            $aff_set = [
                "is_deleted" => 1,
                'date_delete' => DB::expr("NOW()")
            ];
            $aff->set($aff_set);
            $aff->save();

            $result = Model_Whitelabel_Aff::delete_parent_aff($aff);

            Session::set_flash("message", ["success", _("Affiliate has been deleted!")]);
        } else {
            Session::set_flash("message", ["danger", _("Wrong affiliate.")]);
        }

        $rparam = $rparam == "affs" ? "" : $rparam;
        Response::redirect("affs/" . $rparam . Lotto_View::query_vars());
    }

    /**
     *
     * @param array $whitelabel
     * @param string $rparam
     * @return void
     */
    private function aff_confirm(array $whitelabel, string $rparam): void
    {
        $token = (string) $this->param("id");
        $affs = Model_Whitelabel_Aff::find_by([
            "whitelabel_id" => $whitelabel['id'],
            "token" => $token
        ]);

        if (
            $affs !== null &&
                count($affs) > 0 &&
                (int)$affs[0]->whitelabel_id === (int)$whitelabel['id'] &&
                (int)$affs[0]->is_deleted === 0 &&
                (int)$affs[0]->is_confirmed === 0 &&
                (int)$affs[0]->is_active === 1 &&
                (int)$whitelabel['aff_activation_type'] !== Helpers_General::ACTIVATION_TYPE_REQUIRED
        ) {
            $aff = $affs[0];
            $aff_set = [
                "is_confirmed" => 1
            ];
            $aff->set($aff_set);
            $aff->save();

            Session::set_flash("message", ["success", _("Affiliate e-mail address has been confirmed!")]);
        } else {
            Session::set_flash("message", ["danger", _("Wrong affiliate.")]);
        }
        Response::redirect("affs/" . $rparam . Lotto_View::query_vars());
    }

    /**
     *
     * @param array $whitelabel
     * @return void
     */
    private function aff_password(array $whitelabel): void
    {
        $aff_password = new Forms_Whitelabel_Aff_Password($whitelabel);
        $token = (string) $this->param("id");
        $result = $aff_password->process_form($token);
        switch ($result) {
            case Forms_Whitelabel_Aff_Password::RESULT_OK:
                $user = $aff_password->get_user_aff();
                Response::redirect("affs/list/view/" . $user->token . Lotto_View::query_vars());
                break;
            case Forms_Whitelabel_Aff_Password::RESULT_WRONG_AFF:
                Response::redirect("affs");
                break;
            case Forms_Whitelabel_Aff_Password::RESULT_WITH_ERRORS:
                $inside = $aff_password->get_inside();
                $this->view->inside = $inside;
                break;
        }
    }

    /**
     *
     * @param array $whitelabel
     * @return void
     */
    private function aff_email(array $whitelabel): void
    {
        $aff_email = new Forms_Whitelabel_Aff_Email($whitelabel);
        $token = $this->param("id");
        $result = $aff_email->process_form($token);
        switch ($result) {
            case Forms_Whitelabel_Aff_Email::RESULT_OK:
                $user = $aff_email->get_user_aff();
                Response::redirect("affs/list/view/" . $user->token . Lotto_View::query_vars());
                break;
            case Forms_Whitelabel_Aff_Email::RESULT_WRONG_AFF:
                Response::redirect("affs");
                break;
            case Forms_Whitelabel_Aff_Email::RESULT_WITH_ERRORS:
                $inside = $aff_email->get_inside();
                $this->view->inside = $inside;
                break;
        }
    }

    /**
     *
     * @param array $whitelabel
     * @return void
     */
    private function aff_new(array $whitelabel): void
    {
        $aff_new = new Forms_Whitelabel_Aff_New($whitelabel);
        $result = $aff_new->process_form();
        switch ($result) {
            case Forms_Whitelabel_Aff_New::RESULT_OK:
                Response::redirect("affs" . Lotto_View::query_vars());
                break;
            case Forms_Whitelabel_Aff_New::RESULT_WITH_ERRORS:
                $inside = $aff_new->get_inside();
                $this->view->inside = $inside;
                break;
        }
    }

    /**
     *
     * @param array $whitelabel
     * @param string $rparam
     * @return void
     */
    private function aff_group_delete(array $whitelabel, string $rparam): void
    {
        $id = intval($this->param("id"));

        $delete = Model_Whitelabel_Aff_Group::find([
            "where" => [
                "whitelabel_id" => $whitelabel['id']
            ],
            "order_by" => [
                "name" => "ASC"
            ],
            "limit" => 1,
            "offset" => $id - 1
        ]);

        if (
            $delete !== null &&
                count($delete) > 0 &&
                (int)$delete[0]->whitelabel_id === (int)$whitelabel['id']
        ) {
            $delete = $delete[0];
            $delete->delete();
            Session::set_flash("message", ["success", _("Affiliate group has been deleted!")]);
            Lotto_Helper::clear_cache("whitelabel_aff_group.wlgroups." . $whitelabel['id']);
        } else {
            Session::set_flash("message", ["danger", _("Wrong affiliate group!")]);
        }

        Response::redirect("affs/" . $rparam . Lotto_View::query_vars());
    }

    /**
     *
     * @param array $whitelabel
     * @param array $countries
     * @return void
     */
    private function aff_ftps(
        array $whitelabel,
        array $countries
    ): void {
        $aff_first_time_purchase = new Forms_Whitelabel_Aff_Ftps($whitelabel, $countries);
        $aff_first_time_purchase->process_form();
        $inside = $aff_first_time_purchase->get_inside();
        $this->view->inside = $inside;
    }

    /**
     *
     * @param array $whitelabel
     * @param array $countries
     * @return void
     */
    private function aff_ftps_export(
        array $whitelabel,
        array $countries
    ): void {
        $aff_first_time_purchase = new Forms_Whitelabel_Aff_Ftps($whitelabel, $countries);
        $aff_first_time_purchase->process_form_export();
    }

    /**
     *
     * @param array $whitelabel
     * @param array $countries
     * @return void
     */
    private function aff_commissions(array $whitelabel, array $countries): void
    {
        $aff_commissions = new Forms_Whitelabel_Aff_Commissions(
            $whitelabel,
            $countries
        );
        $aff_commissions->process_form();
        $inside = $aff_commissions->get_inside();
        $this->view->inside = $inside;
    }

    private function affCasinoCommissions(array $whitelabel, array $countries): void
    {
        $whitelabelId = $whitelabel['id'];

        $dates = [];
        if (!empty(Input::get('filter.range_start'))) {
            $dates = $this->prepare_dates();
        }

        [$filters, $pagination] = $this->affCasinoCommissionService
            ->prepareAffCasinoCommissionsData($whitelabelId, $dates);

        $casinoCommissions = $this->whitelabelAffSlotCommissionRepository->findCasinoCommissionsByReport(
            $filters,
            $pagination,
            $whitelabelId,
            $dates['date_start'] ?? null,
            $dates['date_end'] ?? null,
        );

        $inside = Presenter::forge('whitelabel/affs/reports/CasinoCommissions');
        $inside->set('countries', $countries);
        $inside->set('pages', $pagination);
        $inside->set('casinoCommissions', $casinoCommissions);

        $this->view->inside = $inside;
    }

    /**
     *
     * @param array $whitelabel
     * @param string $rparam
     * @param array $countries
     */
    private function aff_commissions_accept(
        array $whitelabel,
        string $rparam,
        array $countries
    ) {
        $aff_commissions = new Forms_Whitelabel_Aff_Commissions(
            $whitelabel,
            $countries
        );
        $token = $this->param("id");
        $aff_commissions->process_accept($token);

        Response::redirect("affs/" . $rparam . Lotto_View::query_vars());
    }

    /**
     *
     * @param array $whitelabel
     * @param string $rparam
     * @param array $countries
     */
    private function aff_commissions_delete(
        array $whitelabel,
        string $rparam,
        array $countries
    ) {
        $aff_commissions = new Forms_Whitelabel_Aff_Commissions(
            $whitelabel,
            $countries
        );
        $token = $this->param("id");
        $aff_commissions->process_delete($token);

        Response::redirect("affs/" . $rparam . Lotto_View::query_vars());
    }

    /**
     *
     * @param array $whitelabel
     * @param array $countries
     * @return void
     */
    private function aff_commissions_export(array $whitelabel, array $countries): void
    {
        $aff_commissions = new Forms_Whitelabel_Aff_Commissions(
            $whitelabel,
            $countries
        );
        $aff_commissions->process_export();
    }

    /**
     *
     * @param array $whitelabel
     * @param array $countries
     * @return void
     */
    private function aff_reports(array $whitelabel, array $countries): void
    {
        $aff_reports = new Forms_Whitelabel_Aff_Reports($whitelabel, $countries);
        $aff_reports->process_form();
        $inside = $aff_reports->get_inside();
        $this->view->inside = $inside;
    }

    /**
     *
     * @param array $whitelabel
     * @return void
     */
    private function aff_payouts(array $whitelabel): void
    {
        $aff_payouts = new Forms_Whitelabel_Aff_Payouts(
            $whitelabel,
            Helpers_General::SOURCE_WHITELABEL
        );

        // This is also path to Presenter!
        $view_template = "whitelabel/affs/reports/payouts";
        $aff_payouts->process_form($view_template);
        $inside = $aff_payouts->get_inside();
        $this->view->inside = $inside;
    }

    /**
     *
     * @param array $whitelabel
     * @param string $rparam
     * @return void
     */
    private function aff_payouts_accept(array $whitelabel, string $rparam): void
    {
        $aff_payouts = new Forms_Whitelabel_Aff_Payouts(
            $whitelabel,
            Helpers_General::SOURCE_WHITELABEL
        );
        $payout_id = $this->param("id");
        $aff_payouts->process_accept($payout_id);
        Response::redirect("affs/" . $rparam . Lotto_View::query_vars());
    }

    /**
     *
     * @param array $whitelabel
     * @param string $rparam
     * @return void
     */
    private function aff_leads(array $whitelabel, string $rparam): void
    {
        $aff_leads = new Forms_Whitelabel_Aff_Leads($whitelabel, $rparam);
        $aff_leads->process_form();
        $inside = $aff_leads->get_inside();
        $this->view->inside = $inside;
    }

    /**
     *
     * @param array $whitelabel
     * @param string $rparam
     */
    private function aff_leads_export(array $whitelabel, string $rparam): void
    {
        $aff_leads = new Forms_Whitelabel_Aff_Leads(
            $whitelabel,
            $rparam
        );
        $aff_leads->process_export();
    }

    /**
     *
     * @param array $whitelabel
     * @param string $rparam
     */
    private function aff_leads_accept(array $whitelabel, string $rparam): void
    {
        $aff_leads = new Forms_Whitelabel_Aff_Leads(
            $whitelabel,
            $rparam
        );
        $user_id = $this->param("id");
        $aff_leads->process_accept($user_id);

        Response::redirect("affs/" . $rparam . Lotto_View::query_vars());
    }

    /**
     *
     * @param array $whitelabel
     * @param string $rparam
     */
    private function aff_leads_delete(array $whitelabel, string $rparam): void
    {
        $aff_leads = new Forms_Whitelabel_Aff_Leads($whitelabel);
        $user_id = $this->param("id");
        $aff_leads->process_delete($user_id);
        Response::redirect("affs/" . $rparam . Lotto_View::query_vars());
    }

    /**
     *
     * @param array $whitelabel
     * @param string $rparam
     */
    private function aff_group_add_edit(array $whitelabel, string $rparam)
    {
        $aff_group_edit = new Forms_Whitelabel_Aff_Group_Edit($whitelabel);
        $action_name = $this->param("action");
        $token = "";
        if (!is_null($this->param("id"))) {
            $token = $this->param("id");
        }
        $result = $aff_group_edit->process_form($action_name, $token);
        switch ($result) {
            case Forms_Whitelabel_Aff_Group_Edit::RESULT_OK:
                Response::redirect('/affs/lottery-groups');
                break;
            case Forms_Whitelabel_Aff_Group_Edit::RESULT_WRONG_AFF_GROUP:
                Response::redirect("affs/" . $rparam . Lotto_View::query_vars());
                break;
            case Forms_Whitelabel_Aff_Group_Edit::RESULT_WITH_ERRORS:
                $inside = $aff_group_edit->get_inside();
                $this->view->inside = $inside;
                break;
        }
    }

    /**
     *
     * @param array $whitelabel
     */
    private function aff_group_list(array $whitelabel)
    {
        $aff_group_list = new Forms_Whitelabel_Aff_Group_List($whitelabel);
        $view_template = "whitelabel/affs/groups";
        $aff_group_list->process_form($view_template);
        $inside = $aff_group_list->get_inside();
        $this->view->inside = $inside;
    }

    /**
     *
     * @param array $whitelabel
     */
    private function aff_settings(array $whitelabel): void
    {
        $aff_settings = new Forms_Whitelabel_Aff_Settings($whitelabel);
        $result = $aff_settings->process_form();
        switch ($result) {
            case Forms_Whitelabel_Aff_Settings::RESULT_OK:
                Response::redirect("affs/settings");
                break;
            case Forms_Whitelabel_Aff_Settings::RESULT_WITH_ERRORS:
                $inside = $aff_settings->get_inside();
                $this->view->inside = $inside;
                break;
        }
    }

    /**
     *
     * @param array $whitelabel
     * @param array $countries
     * @return void
     */
    private function aff_edit(array $whitelabel, array $countries): void
    {
        $aff_edit = new Forms_Whitelabel_Aff_Edit($whitelabel, $countries);
        $token = $this->param("id");
        $result = $aff_edit->process_form($token);
        switch ($result) {
            case Forms_Whitelabel_Aff_Edit::RESULT_OK:
                $user = $aff_edit->get_user_aff();
                Response::redirect("affs/list/view/" . $user->token . Lotto_View::query_vars());
                break;
            case Forms_Whitelabel_Aff_Edit::RESULT_DB_ERROR:
                Response::redirect("affs");
                break;
            case Forms_Whitelabel_Aff_Edit::RESULT_WITH_ERRORS:
                $inside = $aff_edit->get_inside();
                $this->view->inside = $inside;
                break;
        }
    }

    /**
     *
     * @param array $whitelabel
     * @param array $countries
     * @param array $languages
     * @return void
     */
    private function aff_view(
        array $whitelabel,
        array $countries,
        array $languages
    ): void {
        $aff_view = new Forms_Whitelabel_Aff_View(
            $whitelabel,
            $countries,
            $languages
        );
        $token = $this->param("id");
        $result = $aff_view->process_form($token);

        switch ($result) {
            case Forms_Whitelabel_Aff_View::RESULT_OK:
                $inside = $aff_view->get_inside();
                $this->view->inside = $inside;
                break;
            case Forms_Whitelabel_Aff_View::RESULT_WRONG_AFF:
                Response::redirect("affs");
                break;
        }
    }

    /**
     *
     * @param array $whitelabel
     * @return void
     */
    private function aff_activate(array $whitelabel): void
    {
        $token = (string) $this->param("id");

        $affs = Model_Whitelabel_Aff::find_by([
            "whitelabel_id" => $whitelabel['id'],
            "token" => $token
        ]);

        if (
            $affs !== null &&
                count($affs) > 0 &&
                (int)$affs[0]->whitelabel_id === (int)$whitelabel['id'] &&
                (int)$affs[0]->is_deleted === 0 &&
                ((int)$affs[0]->is_active === 0 ||
                    ((int)$whitelabel['aff_activation_type'] === Helpers_General::ACTIVATION_TYPE_REQUIRED &&
                        (int)$affs[0]->is_confirmed === 0))
        ) {
            $aff = $affs[0];

            $aff_set = [
                "is_active" => 1,
                "is_confirmed" => 1
            ];
            $aff->set($aff_set);

            if ((int)$whitelabel['aff_auto_accept'] === 1) {
                $aff_set_accepted = [
                    "is_accepted" => 1
                ];
                $aff->set($aff_set_accepted);
            }

            $aff->save();

            Session::set_flash("message", ["success", _("Affiliate has been activated and confirmed!")]);
        } else {
            Session::set_flash("message", ["danger", _("Wrong affiliate.")]);
        }

        Response::redirect("affs/inactive" . Lotto_View::query_vars());
    }

    /**
     *
     * @param array $whitelabel
     * @return void
     */
    private function aff_accept(array $whitelabel): void
    {
        $token = (string) $this->param("id");

        $affs = Model_Whitelabel_Aff::find_by([
            "whitelabel_id" => $whitelabel['id'],
            "token" => $token
        ]);

        if (
            $affs !== null &&
                count($affs) > 0 &&
                (int)$affs[0]->whitelabel_id === (int)$whitelabel['id'] &&
                (int)$affs[0]->is_deleted === 0 &&
                (int)$affs[0]->is_accepted === 0 &&
                (((int)$whitelabel['aff_activation_type'] !== Helpers_General::ACTIVATION_TYPE_REQUIRED &&
                    (int)$affs[0]->is_active === 1) ||
                    ((int)$whitelabel['aff_activation_type'] === Helpers_General::ACTIVATION_TYPE_REQUIRED &&
                        (int)$affs[0]->is_confirmed === 1 &&
                        (int)$affs[0]->is_active === 1))
        ) {
            $aff = $affs[0];

            $aff_set = [
                "is_accepted" => 1
            ];

            $aff->set($aff_set);
            $aff->save();

            Session::set_flash("message", ["success", _("Affiliate has been accepted!")]);
        } else {
            Session::set_flash("message", ["danger", _("Wrong affiliate.")]);
        }

        Response::redirect("affs/notaccepted" . Lotto_View::query_vars());
    }

    /**
     *
     * @param array $whitelabel
     * @return void
     */
    private function aff_restore(array $whitelabel): void
    {
        $token = (string) $this->param("id");

        $affs = Model_Whitelabel_Aff::find_by([
            "whitelabel_id" => $whitelabel['id'],
            "token" => $token
        ]);

        if (
            $affs !== null &&
                count($affs) > 0 &&
                (int)$affs[0]->whitelabel_id === (int)$whitelabel['id'] &&
                (int)$affs[0]->is_deleted === 0
        ) {
            $aff = $affs[0];
            $result = Model_Whitelabel_Aff::get_count_for_whitelabel(
                $whitelabel,
                $aff->email
            );

            if (is_null($result)) {
                Session::set_flash("message", ["danger", _("There is something wrong with DB!")]);
                Response::redirect("affs/deleted" . Lotto_View::query_vars());
            }

            $aff_count = $result[0]['count'];

            if ((int)$aff_count === 0) {
                $aff_set = [
                    "is_deleted" => 0
                ];
                $aff->set($aff_set);
                $aff->save();

                Session::set_flash("message", ["success", _("Affiliate has been restored!")]);
            } else {
                Session::set_flash("message", [
                    "danger",
                    _(
                        "Affiliate cannot be restored, because an " .
                        "active affiliate with this e-mail address exists."
                    )
                ]);
            }
        } else {
            Session::set_flash("message", ["danger", _("Wrong affiliate.")]);
        }

        Response::redirect("affs/deleted" . Lotto_View::query_vars());
    }

    /**
     *
     * @param array $whitelabel
     * @param string $rparam
     * @param array $languages
     * @param array $countries
     * @return void
     */
    private function aff_list(
        array $whitelabel,
        string $rparam,
        array $languages,
        array $countries
    ): void {
        $aff_list = new Forms_Whitelabel_Aff_List(
            $whitelabel,
            $rparam,
            $countries,
            $languages
        );
        $aff_list->process_form();
        $inside = $aff_list->get_inside();
        $this->view->inside = $inside;
    }

    /**
     *
     * @param array $whitelabel
     * @param string $rparam
     * @param array $languages
     * @param array $countries
     * @return void
     */
    private function aff_list_export(
        array $whitelabel,
        string $rparam,
        array $languages,
        array $countries
    ): void {
        $aff_list = new Forms_Whitelabel_Aff_List(
            $whitelabel,
            $rparam,
            $countries,
            $languages
        );
        $aff_list->process_form_export();
    }

    private function prepareCasinoGroupView(?string $action, array $whitelabel)
    {
        $groupId = (int)$this->param('id');
        switch ($action) {
            case 'create':
                $renderedView = View::forge('whitelabel/affs/CasinoGroups/Create');
                break;
            case 'store':
                $isNotGroupCreated = !$this->affiliateGroupService->createCommissionGroup(
                    $whitelabel['id']
                );

                if ($isNotGroupCreated) {
                    $errors = $this->affiliateGroupService->getErrors();
                    $errorMessage = $errors[array_key_first($errors)];

                    Session::set_flash('message', ['danger',
                        $errorMessage
                    ]);

                    Response::redirect('/affs/casino-groups/create');
                }

                Session::set_flash('message', ['success',
                    _('Successfully created!')
                ]);

                Response::redirect('/affs/casino-groups');
                break;
            case 'edit':
                $renderedView = Presenter::forge('whitelabel/affs/CasinoGroups/Edit');
                $isNotDefaultGroup = $groupId !== AffiliateGroupService::DEFAULT_CASINO_AFF_GROUP_ID;
                if ($isNotDefaultGroup) {
                    $casinoGroup = $this->whitelabelAffCasinoGroupRepository->findGroupByWhitelabelIdAndGroupId(
                        $whitelabel['id'],
                        $groupId
                    );
                    $renderedView->set('casinoGroup', $casinoGroup);
                }
                break;
            case 'update':
                $isNotGroupUpdated = !$this->affiliateGroupService->updateCommissionValuesForGroup(
                    $groupId,
                    $whitelabel['id']
                );
                if ($isNotGroupUpdated) {
                    $errors = $this->affiliateGroupService->getErrors();
                    $errorMessage = $errors[array_key_first($errors)];

                    Session::set_flash('message', ['danger',
                        $errorMessage
                    ]);

                    Response::redirect('/affs/casino-groups/edit/' . $groupId);
                }

                // we clean so that the data in the view is up-to-date
                Lotto_Helper::clear_cache([
                    'model_whitelabel.bydomain.' . str_replace('.', '-', $whitelabel['domain'])
                ]);

                Session::set_flash('message', ['success',
                    _('Successfully updated!')
                ]);

                Response::redirect('/affs/casino-groups');
                break;
            default:
                $casinoGroups = $this->whitelabelAffCasinoGroupRepository
                    ->findBy('whitelabel_id', $whitelabel['id']);
                $renderedView = Presenter::forge('whitelabel/affs/CasinoGroups/Index');
                $renderedView->set('casinoGroups', $casinoGroups);
                break;
        }

        $renderedView->set('id', $groupId);
        $this->view->inside = $renderedView;
    }

    /**
     *
     * @return Response
     */
    public function action_affs(): Response
    {
        $rparam = $this->param("rparam");

        if (!$this->is_user) {
            return Response::forge($this->view);
        }
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        $countries = Lotto_Helper::get_localized_country_list();
        $languages = Model_Language::get_all_languages();

        // I have commented that because it is not used - maybe in the future!
        //$count_deleted = 0;
        //$accepted = 1;

        $link = "/affs/list";
        if ($rparam == "deleted") {
            //$count_deleted = 1;
            $link = "/affs/deleted";
        } elseif ($rparam == "notaccepted") {
            //$accepted = 0;
            $link = "/affs/notaccepted";
        } elseif ($rparam == "inactive") {
            $link = "/affs/inactive";
        }

        switch ($rparam) {
            case "list":
            case "deleted":
            case "notaccepted":
            case "inactive":
                switch ($this->param("action")) {
                    case "confirm":
                        $this->aff_confirm($whitelabel, $rparam);
                        break;
                    case "password":
                        $this->aff_password($whitelabel);
                        break;
                    case "email":
                        $this->aff_email($whitelabel);
                        break;
                    case "new":
                        $this->aff_new($whitelabel);
                        break;
                    case "delete":
                        $this->aff_delete($whitelabel, $rparam);
                        break;
                    case "edit":
                        $this->aff_edit($whitelabel, $countries);
                        break;
                    case "view":
                        $this->aff_view($whitelabel, $countries, $languages);
                        break;
                    case "activate":
                        $this->aff_activate($whitelabel);
                        break;
                    case "accept":
                        $this->aff_accept($whitelabel);
                        break;
                    case "restore":
                        $this->aff_restore($whitelabel);
                        break;
                    case "export":
                        $this->aff_list_export($whitelabel, $rparam, $languages, $countries);
                        break;
                    default:
                        $this->aff_list($whitelabel, $rparam, $languages, $countries);
                        break;
                }
                break;
            case "lottery-groups":
                switch ($this->param("action")) {
                    case "new":
                    case "edit":
                        $this->aff_group_add_edit($whitelabel, $rparam);
                        break;
                    case "delete":
                        $this->aff_group_delete($whitelabel, $rparam);
                        break;
                    default:
                        $this->aff_group_list($whitelabel);
                        break;
                }
                break;
            case "casino-groups":
                $this->prepareCasinoGroupView(
                    $this->param('action'),
                    $whitelabel,
                );
                break;
            case "leads":
                switch ($this->param("action")) {
                    case "export":
                        $this->aff_leads_export($whitelabel, $rparam);
                        break;
                    case "accept":
                        $this->aff_leads_accept($whitelabel, $rparam);
                        break;
                    case "delete":
                        $this->aff_leads_delete($whitelabel, $rparam);
                        break;
                    default:
                        $this->aff_leads($whitelabel, $rparam);
                        break;
                }
                break;
            case "commissions":
                switch ($this->param("action")) {
                    case "accept":
                        $this->aff_commissions_accept($whitelabel, $rparam, $countries);
                        break;
                    case "delete":
                        $this->aff_commissions_delete($whitelabel, $rparam, $countries);
                        break;
                    case "export":
                        $this->aff_commissions_export($whitelabel, $countries);
                        break;
                    default:
                        $this->aff_commissions($whitelabel, $countries);
                        break;
                }
                break;
            case "casinoCommissions":
                switch ($this->param("action")) {
                    default:
                        $this->affCasinoCommissions($whitelabel, $countries);
                        break;
                }
                break;
            case "ftps":
                switch ($this->param("action")) {
                    case "export":
                        $this->aff_ftps_export($whitelabel, $countries);
                        break;
                    default:
                        $this->aff_ftps($whitelabel, $countries);
                        break;
                }
                break;
            case "reports":
                $this->aff_reports($whitelabel, $countries);
                break;
            case "payouts":
                switch ($this->param("action")) {
                    case "accept":
                        $this->aff_payouts_accept($whitelabel, $rparam);
                        break;
                    default:
                        $this->aff_payouts($whitelabel);
                        break;
                }
                break;
            case "settings":
                $this->aff_settings($whitelabel);
                break;
            case "banners":
                $this->aff_banners_generator($whitelabel);
                break;
            case "widgets":
                $this->aff_widgets_generator($whitelabel);
                break;
            default:
                $rparam = "";
                $this->aff_list($whitelabel, $rparam, $languages, $countries);
                break;
        }

        $this->view->set_global("link", $link);
        $this->view->set_global("rparam", $rparam);
        $this->view->set_global("title", _("Affiliates"));

        $all_add = " AND is_accepted = 1 AND is_active = 1";
        if ((int)$whitelabel['aff_activation_type'] === Helpers_General::ACTIVATION_TYPE_REQUIRED) {
            $all_add .= " AND is_confirmed = 1";
        }
        $count_all = Model_Whitelabel_Aff::count_by_whitelabel(
            $whitelabel,
            0,
            $all_add
        );
        // Don't know what exactly should happened
        if (is_null($count_all)) {
            ;
        }

        $not_accepted_add = " AND is_accepted = 0 AND is_active = 1";
        $count_not_accepted = Model_Whitelabel_Aff::count_by_whitelabel(
            $whitelabel,
            0,
            $not_accepted_add
        );
        // Don't know what exactly should happened
        if (is_null($count_not_accepted)) {
            ;
        }

        $inactive_add = " AND (is_active = 0";
        if ((int)$whitelabel['aff_activation_type'] === Helpers_General::ACTIVATION_TYPE_REQUIRED) {
            $inactive_add .= " OR is_confirmed = 0";
        }
        $inactive_add .= ")";
        $count_inactive = Model_Whitelabel_Aff::count_by_whitelabel(
            $whitelabel,
            0,
            $inactive_add
        );

        // Don't know what exactly should happened
        if (is_null($count_inactive)) {
            ;
        }

        $deleted_add = "";
        $count_deleted = Model_Whitelabel_Aff::count_by_whitelabel(
            $whitelabel,
            1,
            $deleted_add
        );

        // Don't know what exactly should happened
        if (is_null($count_deleted)) {
            ;
        }

        $this->view->set_global("active_cnt", $count_all);
        $this->view->set_global("notaccepted_cnt", $count_not_accepted);
        $this->view->set_global("inactive_cnt", $count_inactive);
        $this->view->set_global("deleted_cnt", $count_deleted);

        return Response::forge($this->view);
    }

    /**
     *
     * @return void
     */
    public function action_insurance(): void
    {
        try {
            $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
            $lotteries = Model_Lottery::get_all_lotteries_for_whitelabel($whitelabel);

            if (!isset($lotteries['__by_id'][Input::post("lottery")])) {
                exit();
            }

            if (
                !is_numeric(Input::post("jackpot")) ||
                !is_numeric(Input::post("tiers")) ||
                !is_numeric(Input::post("volume"))
            ) {
                exit();
            }

            $lottery = $lotteries['__by_id'][Input::post("lottery")];
            $currencies = Helpers_Currency::getCurrencies();
            $currency_map = [];

            foreach ($currencies as $item) {
                $currency_map[$item['code']] = $item;
            }

            $lottery['current_jackpot'] = Input::post("jackpot");

            $currency_rate = $currency_map[$lottery['currency']]['rate'];
            $multiplier = round(1 / $currency_rate, Helpers_Currency::RATE_SCALE);
            $jackpot_multi = round($lottery['current_jackpot'] * $multiplier, 2);

            $lottery['current_jackpot_usd'] = $jackpot_multi;

            $result = Lotto_Helper::get_price($lottery, 1, Input::post("tiers"), Input::post("volume"));

            echo json_encode($result);
            exit();
        } catch (Exception $e) {
            echo "0";
            exit();
        }
    }

    /**
     *
     * @param array $whitelabel
     * @return void
     */
    private function lottery_settings_edit(array $whitelabel): void
    {
        $edit_id = intval($this->param("id"));

        $lottery_settings_edit = new Forms_Whitelabel_Lottery_Settings_Edit($whitelabel);
        $lottery_settings_edit->process_form($edit_id);

        $inside = $lottery_settings_edit->get_inside();
        $this->view->inside = $inside;
    }

    /**
     *
     * @param array $whitelabel
     * @return void
     */
    private function lottery_settings_list(array $whitelabel): void
    {
        $lottery_settings_list = new Forms_Whitelabel_Lottery_Settings_List($whitelabel);
        $lottery_settings_list->process_form();

        $inside = $lottery_settings_list->get_inside();
        $this->view->inside = $inside;
    }

    /**
     *
     * @return Response
     */
    public function action_lotterysettings(): Response
    {
        if (!$this->is_user) {
            return Response::forge($this->view);
        }

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        switch ($this->param("action")) {
            case "edit":
                $this->lottery_settings_edit($whitelabel);
                break;
            default:
                $this->lottery_settings_list($whitelabel);
                break;
        }

        $this->view->set_global("title", _("Lottery settings"));

        return Response::forge($this->view);
    }

    /**
     * Handle request \blocked_countries\add Should be called on confirmation from \blocked_countries\new
     * @param array $whitelabel current whitelabel model in form of array.
     * @return Presenter|View Created and prepared presenter or view. Note: it has to return inside view.
     */
    private function blocked_countries_add(array $whitelabel)
    {
        // validate received input
        $countries = Lotto_Helper::get_localized_country_list(); // get countries for validation and new view on errors.
        $valid = Validator_Whitelabel_Settings_Blocked_Add::validate(array_keys($countries)); // pass codes (keys) to validator
        if (!$valid) {
            // invalid input - set errors and render _new view
            Session::set_flash("message", ["danger", _("Sorry, we don't have such country in database.")]);
            return $this->blocked_countries_new($countries)
                ->set('errors', ['input.code' => 'input.code']); // exist for has-error;
        }

        // input is valid - add new country
        $country = new Model_Whitelabel_Blocked_Country([
            'whitelabel_id' => $whitelabel['id'],
            'code' => Input::post('input.code')
        ]);

        // save with failure handling
        $success = $country->save_safe();
        if (!$success) {
            // save failed - set errors and render _new view
            Session::set_flash("message", ["danger", _("Database error - unable to save country. Make sure you do not enter duplicate or try again in a while.")]);
            return $this->blocked_countries_new($countries);
        }

        // success - set success message, and redirect to base
        Session::set_flash("message", ["success", _("Country added.")]);
        Response::redirect('blocked_countries');
    }

    /**
     * Handle request \blocked_countries\delete\:code
     * @param array $whitelabel current whitelabel model in form of array.
     */
    private function blocked_countries_delete(array $whitelabel)
    {
        // get code from param
        $code = (string)$this->param('code');

        // get user type
        $source = Session::get("source");

        // get model instance for current whitelabel and specified code
        $blocked_country = Model_Whitelabel_Blocked_Country::by_whitelabel_code(
            (int)$whitelabel['id'],
            $code
        );

        // check if model was found
        if (!($blocked_country instanceof Model_Whitelabel_Blocked_Country)) {
            // error - model was not found
            Session::set_flash("message", ["danger", _("Unable to delete country. Please try again using delete button.")]);
            return;
        }

        // check if this country is deletable or if user is admin
        if (!boolval($blocked_country->is_deletable) && $source !== 'admin') {
            // error - country is not deletable
            Session::set_flash("message", ["danger", _("Unable to delete country. This country is not deletable.")]);
            return;
        }

        // delete model and set success message
        $blocked_country->delete(); // TODO: observe this, I suspect it may throw, when db failed. We could catch it in Model_Model.
        Session::set_flash("message", ["success", _("Country deleted.")]);
    }

    /**
     * Handle request \blocked_countries\deletable\:code
     * @param array $whitelabel current whitelabel model in form of array.
     */
    private function blocked_countries_toggle_deletable(array $whitelabel)
    {
        // get code from param
        $code = (string)$this->param('code');

        // get user type
        $source = Session::get("source");

        // get model instance for current whitelabel and specified code
        $blocked_country = Model_Whitelabel_Blocked_Country::by_whitelabel_code(
            (int)$whitelabel['id'],
            $code
        );

        // check if model was found
        if (!($blocked_country instanceof Model_Whitelabel_Blocked_Country)) {
            // error - model was not found
            Session::set_flash("message", ["danger", _("Unable to change deletable. Please try again.")]);
            return;
        }

        // check if user is admin
        if ($source !== 'admin') {
            // error - country is not deletable
            Session::set_flash("message", ["danger", _("Unable to delete country.")]);
            return;
        }

        //Toggle deletable
        $blocked_country->set([
            'is_deletable' => !$blocked_country->is_deletable
        ]);

        // save with failure handling
        $success = $blocked_country->save_safe();
        if (!$success) {
            // save failed - set errors and render _new view
            Session::set_flash("message", ["danger", _("Database error - unable to change country.")]);
            return;
        }

        // success - set success message, and redirect to base
        Session::set_flash("message", ["success", _("Country changed.")]);
        Response::redirect('blocked_countries');
    }

    /**
     * Handle request \blocked_countries\new
     * @param array $countries optional countries, if you have them in hand.
     * @return Presenter|View Created and prepared presenter or view. Note: it has to return inside view.
     */
    private function blocked_countries_new(array $countries = null)
    {
        return Presenter::forge("whitelabel/settings/blocked/new")
            ->set('countries', $countries ?: Lotto_Helper::get_localized_country_list()); // use countries if provided, otherwise fetch them
    }

    /**
     * Create inside view for blocked countries, based on possible sub-actions.
     * @return Presenter|View Created and prepared presenter or view. Note: it has to return inside view.
     */
    private function blocked_countries_create_inside()
    {
        // get current whitelabel (user)
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        // get user type
        $source = Session::get("source");

        // return proper view|presenter for subaction
        switch ($this->param('subaction')) {
            case 'new': // new inside view with form.
                return $this->blocked_countries_new();
            case 'delete': // in case of delete there will be some operations and redirect to default blocked_countries (purge of request address).
                $this->blocked_countries_delete($whitelabel);
                break;
            case 'deletable':
                $this->blocked_countries_toggle_deletable($whitelabel);
                break;
            case 'add': // on error render of new view, on success redirect to default blocked_countries.
                return $this->blocked_countries_add($whitelabel); // NOTE: on success exit script here
            default: // undefined and base (blocked_countries)
                // forge, set variables and return prepared view
                return Presenter::forge("whitelabel/settings/blocked/countries")
                    ->set('countries', Lotto_Helper::get_localized_country_list())
                    ->set('is_admin', $source === 'admin')
                    ->set('blocked_countries', Model_Whitelabel_Blocked_Country::by_whitelabel_sort_code($whitelabel['id']) ?: []); // NOTE: convert null to empty array.
        }

        // for those, who don't return inside view - redirect to site with default address
        Response::redirect('blocked_countries');
    }

    /**
     * Handle request \blocked_countries
     * @return Response Created and prepared presenter or view. Note: it has to return top view.
     */
    public function action_blocked_countries(): Response
    {
        // check if user is logged in
        if (!$this->is_user) {
            return Response::forge($this->view); // user not logged forge basic view (unprepared top view).
        }

        // forge inside (current) view (|presenter)
        $inside = $this->blocked_countries_create_inside();

        // set current view as top view attribute
        $this->view->inside = $inside;

        // set title in top view
        $this->view->set_global("title", _("Blocked countries")); // TODO: aside from this line (or rather concrete title) all controller functions have these core actions - it could be extracted.
        // return prepared top view.
        return Response::forge($this->view);
    }

    /**
     *
     * @return Response
     */
    public function action_analytics(): Response
    {
        if (!$this->is_user) {
            return Response::forge($this->view);
        }
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        $inside = View::forge("whitelabel/settings/analytics");
        if (null !== Input::post("input.gtmid")) {
            $val = Validation::forge();

            $val->add("input.gtmid", _("GTM Tracking ID"))
                ->add_rule("trim")
                ->add_rule('valid_string', ['alpha', 'numeric', 'dashes']);

            $val->add("input.gtmid_casino", _("Casino GTM Tracking ID"))
                ->add_rule("trim")
                ->add_rule('valid_string', ['alpha', 'numeric', 'dashes']);

            if ($val->run()) {
                $awl = Model_Whitelabel::find_by_pk($whitelabel['id']);
                $awl->set([
                    "analytics" => $val->validated("input.gtmid"),
                    "analytics_casino" => $val->validated("input.gtmid_casino")
                ]);
                $awl->save();
                Lotto_Helper::clear_cache();
                Session::set_flash("message", ["success", _("GTM have been saved!")]);
                Response::redirect("analytics");
            } else {
                $errors = Lotto_Helper::generate_errors($val->error());
                $inside->set("errors", $errors);
            }
        }
        $inside->set("whitelabel", $whitelabel);
        $this->view->inside = $inside;

        $this->view->set_global("title", _("GTM"));
        return Response::forge($this->view);
    }

    /**
     *
     * @return Response
     */
    public function action_fbpixel(): Response
    {
        if (!$this->is_user) {
            return Response::forge($this->view);
        }
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        $inside = View::forge("whitelabel/settings/fbpixel");
        if (null !== Input::post("input.fbpixel")) {
            $val = Validation::forge();

            $val->add("input.fbpixel", _("Facebook Pixel ID"))
                ->add_rule("trim")
                ->add_rule('valid_string', ['numeric']);

            $val->add("input.fbmatch", _("Activate Advanced Matching"))
                ->add_rule("trim")
                ->add_rule("match_value", 1);

            if ($val->run()) {
                $awl = Model_Whitelabel::find_by_pk($whitelabel['id']);
                $awl->set([
                    "fb_pixel" => $val->validated("input.fbpixel"),
                    "fb_pixel_match" => $val->validated("input.fbmatch") == 1 ? 1 : 0
                ]);
                $awl->save();
                Lotto_Helper::clear_cache(["model_whitelabel.bydomain." . str_replace('.', '-', $whitelabel['domain'])]);
                Session::set_flash("message", ["success", _("Facebook Pixel settings have been saved!")]);
                Response::redirect("fbpixel");
            } else {
                $errors = Lotto_Helper::generate_errors($val->error());
                $inside->set("errors", $errors);
            }
        }
        $inside->set("whitelabel", $whitelabel);
        $this->view->inside = $inside;

        $this->view->set_global("title", _("Facebook Pixel"));
        return Response::forge($this->view);
    }

    /**
     *
     * @return Request The new request object
     */
    public function action_settings(): Response
    {
        if (!$this->is_user) {
            return Response::forge($this->view);
        }
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        switch ($this->param("action")) {
            default:
                $this->settings_view($whitelabel);
                break;
        }

        $this->view->set_global("title", _("Site settings"));

        return Response::forge($this->view);
    }

    /**
     *
     * @param array $whitelabel
     * @return void
     */
    private function settings_view($whitelabel): void
    {
        $path_to_view = "whitelabel/settings/index";
        $settings = new Forms_Whitelabel_Settings($whitelabel, true, $path_to_view);

        $redirect_path = "settings";
        $result = $settings->process_form();

        switch ($result) {
            case Forms_Whitelabel_Settings::RESULT_OK:
                Response::redirect($redirect_path);
                break;
            case Forms_Whitelabel_Settings::RESULT_GO_FURTHER:
            case Forms_Whitelabel_Settings::RESULT_WITH_ERRORS:
            default:
                $inside = $settings->get_inside();
                $this->view->inside = $inside;
                break;
        }
    }

    /**
     *
     * @return Request The new request object
     */
    public function action_settings_currency(): Response
    {
        if (!$this->is_user) {
            return Response::forge($this->view);
        }

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        switch ($this->param("action")) {
            case "new":
                $this->settings_currency_add($whitelabel);
                break;
            case 'edit':
                $this->settings_currency_edit($whitelabel);
                break;
            case "delete":
                $this->settings_currency_delete();
                break;
            default:
                $this->settings_currency_list($whitelabel);
                break;
        }

        $this->view->set_global("title", _("Currency settings"));

        return Response::forge($this->view);
    }

    /**
     * @param array $whitelabel
     * @return void
     */
    private function settings_currency_add(array $whitelabel): void
    {
        $path_to_view = "whitelabel/settings/currency/edit";
        $redirect_path = "settings_currency";
        $settings_currency_add = new Forms_Whitelabel_Settings_Currency_Edit(
            Helpers_General::SOURCE_WHITELABEL,
            $whitelabel,
            true,
            $path_to_view,
            $redirect_path
        );
        $settings_currency_add->process_form();

        $inside = $settings_currency_add->get_inside();
        $this->view->inside = $inside;
    }

    /**
     * @param array $whitelabel
     * @return void
     */
    private function settings_currency_edit(array $whitelabel): void
    {
        if (empty($this->param("id"))) {
            return;
        }

        $edit_id = intval($this->param("id"));

        $path_to_view = "whitelabel/settings/currency/edit";
        $redirect_path = "settings_currency";
        $settings_currency_edit = new Forms_Whitelabel_Settings_Currency_Edit(
            Helpers_General::SOURCE_WHITELABEL,
            $whitelabel,
            true,
            $path_to_view,
            $redirect_path,
            $edit_id
        );
        $settings_currency_edit->process_form();

        $inside = $settings_currency_edit->get_inside();

        $this->view->inside = $inside;
    }

    /**
     *
     * @return void
     */
    private function settings_currency_delete(): void
    {
        if (empty($this->param("id"))) {
            return;
        }

        $result = Model_Whitelabel_Country_Currency::delete_row_by_default_currency_id($this->param("id"));

        if (!(!empty($result) && $result == 1 || empty($result))) {
            Session::set_flash("message", ["danger", _("There is a problem with database! Please contact us.")]);
        } else {
            $result = Model_Whitelabel_Default_Currency::delete_row($this->param("id"));

            if (!empty($result) && $result == 1) {
                Session::set_flash("message", ["success", _("Record successfully removed!")]);
            } else {
                Session::set_flash("message", ["danger", _("There is a problem with database! Please contact us.")]);
            }
        }

        Response::redirect("settings_currency");
    }

    /**
     *
     * @param array $whitelabel
     * @return void
     */
    private function settings_currency_list(array $whitelabel): void
    {
        $path_to_view = "whitelabel/settings/currency/list";
        $redirect_path = "settings_currency";
        $settings_currency_list = new Forms_Whitelabel_Settings_Currency_List(
            Helpers_General::SOURCE_WHITELABEL,
            $whitelabel,
            true,
            $path_to_view,
            $redirect_path
        );
        $settings_currency_list->process_form();

        $inside = $settings_currency_list->get_inside();
        $this->view->inside = $inside;
    }

    /**
     *
     * @return Request The new request object
     */
    public function action_settings_country_currency(): Response
    {
        if (!$this->is_user) {
            return Response::forge($this->view);
        }

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        switch ($this->param("action")) {
            case 'new':
                $this->settings_country_currency_add($whitelabel);
                break;
            case 'edit':
                $this->settings_country_currency_edit($whitelabel);
                break;
            case "delete":
                $this->settings_country_currency_delete();
                break;
            default:
                $this->settings_country_currency_list($whitelabel);
                break;
        }

        $this->view->set_global("title", _("Currency settings"));

        return Response::forge($this->view);
    }

    /**
     *
     * @param array $whitelabel
     * @return void
     */
    private function settings_country_currency_add(array $whitelabel): void
    {
        $path_to_view = "whitelabel/settings/country/currency/edit";
        $redirect_path = "settings_country_currency";
        $country_currency_add = new Forms_Whitelabel_Settings_Country_Currency_Edit(
            $whitelabel,
            true,
            $path_to_view,
            $redirect_path
        );
        $country_currency_add->process_form();

        $inside = $country_currency_add->get_inside();
        $this->view->inside = $inside;
    }

    /**
     *
     * @param array $whitelabel
     * @return void
     */
    private function settings_country_currency_edit(array $whitelabel): void
    {
        if (empty($this->param("id"))) {
            return;
        }

        $edit_id = intval($this->param("id"));

        $path_to_view = "whitelabel/settings/country/currency/edit";
        $redirect_path = "settings_country_currency";
        $country_currency_edit = new Forms_Whitelabel_Settings_Country_Currency_Edit(
            $whitelabel,
            true,
            $path_to_view,
            $redirect_path,
            $edit_id
        );
        $country_currency_edit->process_form();

        $inside = $country_currency_edit->get_inside();
        $this->view->inside = $inside;
    }

    /**
     *
     * @return void
     */
    private function settings_country_currency_delete(): void
    {
        if (empty($this->param("id"))) {
            return;
        }

        $result = Model_Whitelabel_Country_Currency::delete_row($this->param("id"));

        if (!empty($result) && $result == 1) {
            Session::set_flash("message", ["success", _("Record successfully removed!")]);
        } else {
            Session::set_flash("message", ["danger", _("There is a problem with database! Please contact us.")]);
        }

        Response::redirect("settings_country_currency");
    }

    /**
     *
     * @param array $whitelabel
     * @return void
     */
    private function settings_country_currency_list(array $whitelabel): void
    {
        $path_to_view = "whitelabel/settings/country/currency/list";
        $redirect_path = "settings_country_currency";
        $settings_country_currency_list = new Forms_Whitelabel_Settings_Country_Currency_List(
            $whitelabel,
            true,
            $path_to_view,
            $redirect_path
        );
        $settings_country_currency_list->process_form();

        $inside = $settings_country_currency_list->get_inside();
        $this->view->inside = $inside;
    }

    /**
     *
     * @return Response
     */
    public function action_reports(): Response
    {
        if (!$this->is_user) {
            return Response::forge($this->view);
        }

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        $reports = new Forms_Whitelabel_Reports(
            Helpers_General::SOURCE_WHITELABEL,
            $whitelabel
        );
        $reports->process_form();

        $inside = $reports->get_inside();
        $this->view->inside = $inside;

        $this->view->set_global("title", _("Generate report"));

        return Response::forge($this->view);
    }

    /**
     *
     * @return Response
     */
    public function action_winners(): Response
    {
        if (!$this->is_user) {
            return Response::forge($this->view);
        }
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        // At this moment those are empty
        // Maybe in short future results will be filtered
        $add = [];
        $params = [];

        $count = Model_Whitelabel_Lottery_Draw::count_for_whitelabel_filtered(
            $add,
            $params,
            $whitelabel['id']
        );

        $config = [
            'pagination_url' => '/winners', //.'?'.http_build_query(Input::get()),
            'total_items' => $count,
            'per_page' => 10,
            'uri_segment' => 'page'
        ];
        $pagination = Pagination::forge('winnerspagination', $config);

        $winners = Model_Whitelabel_Lottery_Draw::get_winners_for_whitelabel(
            $add,
            $params,
            $pagination,
            $whitelabel['id']
        );

        $inside = Presenter::forge("whitelabel/reports/index");
        $inside->set("pages", $pagination);
        $inside->set("winners", $winners);
        $this->view->inside = $inside;

        return Response::forge($this->view);
    }

    /**
     *
     * @param array $whitelabel
     * @return void
     */
    private function tickets_payout(array $whitelabel = []): void
    {
        if (empty($whitelabel)) {
            Response::redirect('tickets');
        }

        $token = (int) $this->param("id");
        $offset = (int) $this->param("sid");

        $ticket_payout = new Forms_Whitelabel_Ticket_Payout(
            $token,
            $whitelabel,
            $offset                 // I cant find where exactly this param is set
            // but I left that as it was
        );
        $result = $ticket_payout->process_form();

        switch ($result) {
            case Forms_Whitelabel_Ticket_Payout::RESULT_OK:
                $flash_message = _(
                    "Line prize has been paid out to " .
                    "the user account balance!"
                );
                Session::set_flash("message", ["success", $flash_message]);
                break;
            case Forms_Whitelabel_Ticket_Payout::RESULT_DB_ERROR:
                exit('Bad request');
                break;
            case Forms_Whitelabel_Ticket_Payout::RESULT_WITH_ERRORS:
                Session::set_flash("message", ["danger", _("Incorrect line!")]);
                break;
        }

        $redirect_url = $ticket_payout->get_redirect_url();
        Response::redirect($redirect_url);
    }

    /**
     *
     * @param array $whitelabel
     * @return void
     */
    private function tickets_paidout(array $whitelabel = []): void
    {
        if (empty($whitelabel)) {
            Response::redirect('tickets');
        }

        $token = (int) $this->param("id");

        $ticket_paidout = new Forms_Whitelabel_Ticket_Paidout(
            $token,
            $whitelabel
        );
        $result = $ticket_paidout->process_form();

        switch ($result) {
            case Forms_Whitelabel_Ticket_Paidout::RESULT_OK:
                $flash_message = _("The ticket has been marked as paid out!");
                Session::set_flash("message", ["success", $flash_message]);
                // no break
            case Forms_Whitelabel_Ticket_Paidout::RESULT_PAIDOUT_PARTIALLY:
                $flash_message = _(
                    "The ticket has been marked as paid out, " .
                    "however there are still some lines that will have to " .
                    "be paid automatically!"
                );
                Session::set_flash("message", ["success", $flash_message]);
                break;
            case Forms_Whitelabel_Ticket_Paidout::RESULT_INCORRECT_TICKET:
                Session::set_flash("message", ["danger", _("Incorrect ticket!")]);
                break;
        }

        $ticket_view_url = $ticket_paidout->get_ticket_view_url();
        Response::redirect($ticket_view_url);
    }

    /**
     *
     * @param array $whitelabel
     * @return void
     */
    private function tickets_view(array $whitelabel = []): void
    {
        if (empty($whitelabel)) {
            Response::redirect('tickets');
        }

        $view_template = "whitelabel/tickets/view";

        $token = (int) $this->param("id");

        $ticket_view = new Forms_Whitelabel_Ticket_View(
            $token,
            $whitelabel
        );
        $result = $ticket_view->process_form($view_template);

        switch ($result) {
            case Forms_Whitelabel_Ticket_View::RESULT_OK:
                $inside = $ticket_view->get_inside();
                $this->view->inside = $inside;
                break;
            case Forms_Whitelabel_Ticket_View::RESULT_INCORRECT_TICKET:
                Session::set_flash("message", ["danger", _("Incorrect ticket!")]);
                Response::redirect("tickets");
                break;
            case Forms_Whitelabel_Ticket_View::RESULT_NULL_DATA:
                Session::set_flash("message", ["danger", _("Incorrect ticket!")]);
                Response::redirect("tickets");
                break;
        }
    }

    /**
     *
     * @param array $whitelabel
     * @return void
     */
    private function tickets_list(array $whitelabel = []): void
    {
        if (empty($whitelabel)) {
            Response::redirect('tickets');
        }

        $view_template = "whitelabel/tickets/index";
        $ticket_list = new Forms_Whitelabel_Ticket_List(
            Helpers_General::SOURCE_WHITELABEL,
            $whitelabel
        );
        $result = $ticket_list->process_form($view_template);

        switch ($result) {
            case Forms_Whitelabel_Ticket_List::RESULT_OK:
                $inside = $ticket_list->get_inside();
                $this->view->inside = $inside;
                break;
            case Forms_Whitelabel_Ticket_List::RESULT_NULL_COUNTED:
                exit('Bad request');
        }
    }

    /**
     *
     * @return Response
     */
    public function action_tickets(): Response
    {
        if (!$this->is_user) {
            return Response::forge($this->view);
        }

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        switch ($this->param("action")) {
            case "payout":
                $this->tickets_payout($whitelabel);
                break;
            case "paidout":
                $this->tickets_paidout($whitelabel);
                break;
            case "view":
                $this->tickets_view($whitelabel);
                break;
            default:
                $this->tickets_list($whitelabel);
                break;
        }

        $tcount = Model_Whitelabel_User_Ticket::count_for_whitelabel_paid(
            Helpers_General::TICKET_PAID,
            $whitelabel
        );

        $mtcount = Model_Whitelabel_User_Ticket::count_for_whitelabel_multidraw(
            Helpers_General::TICKET_PAID,
            $whitelabel
        );

        $this->view->set_global("tcount", $tcount);
        $this->view->set_global("mtcount", $mtcount);

        return Response::forge($this->view);
    }

    /**
     *
     * @param array $whitelabel
     * @return void
     */
    private function multidraw_tickets_list(array $whitelabel = []): void
    {
        $view_template = "whitelabel/tickets/multidraw/index";
        $ticket_list = new Forms_Whitelabel_Ticket_Multidrawlist(
            Helpers_General::SOURCE_WHITELABEL,
            $whitelabel
        );
        $result = $ticket_list->process_form($view_template);

        switch ($result) {
            case Forms_Whitelabel_Ticket_Multidrawlist::RESULT_OK:
                $inside = $ticket_list->get_inside();
                $this->view->inside = $inside;
                break;
            case Forms_Whitelabel_Ticket_Multidrawlist::RESULT_NULL_COUNTED:
                exit('Bad request');
        }
    }


    /**
     *
     * @return Response
     */
    public function action_multidraw_tickets(): Response
    {
        if (!$this->is_user) {
            return Response::forge($this->view);
        }

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        switch ($this->param("action")) {
            case "cancellation":
                $this->multidraw_cancellation($whitelabel);
                break;
            case "paidout":
                $this->tickets_paidout($whitelabel);
                break;
            case "view":
                $this->tickets_view($whitelabel);
                break;
            default:
                $this->multidraw_tickets_list($whitelabel);
                break;
        }

        $tcount = Model_Whitelabel_User_Ticket::count_for_whitelabel_paid(
            Helpers_General::TICKET_PAID,
            $whitelabel
        );

        $mtcount = Model_Whitelabel_User_Ticket::count_for_whitelabel_multidraw(
            Helpers_General::TICKET_PAID,
            $whitelabel
        );

        $this->view->set_global("tcount", $tcount);
        $this->view->set_global("mtcount", $mtcount);

        return Response::forge($this->view);
    }

    /**
     *
     * @param array $whitelabel
     * @return void
     */
    private function multidraw_cancellation(array $whitelabel = []): void
    {
        if (empty($whitelabel)) {
            Response::redirect('multidraw_tickets');
        }

        $token = (string) $this->param("id");

        //  exit;
        $view_template = "whitelabel/tickets/multidraw/cancellation";
        $ticket_view = new Forms_Whitelabel_Multidraw_Cancellation(
            $token,
            $whitelabel
        );
        $ticket_view->process_form($view_template, $this->param("subaction"));

        $inside = $ticket_view->get_inside();
        $this->view->inside = $inside;
    }

    /**
     *
     * @param array $whitelabel
     * @return void
     */
    private function withdrawal_view(array $whitelabel = []): void
    {
        if (empty($whitelabel)) {
            Response::redirect("withdrawals" . Lotto_View::query_vars());
        }

        $view_template = "whitelabel/transactions/withdrawal";

        $token = (string) $this->param("id");

        $withdrawal_view = new Forms_Whitelabel_Withdrawal_View(
            $token,
            $whitelabel
        );
        $result = $withdrawal_view->process_form($view_template);

        switch ($result) {
            case Forms_Whitelabel_Withdrawal_View::RESULT_OK:
                $inside = $withdrawal_view->get_inside();
                $this->view->inside = $inside;
                break;
            case Forms_Whitelabel_Withdrawal_View::RESULT_INCORRECT_WITHDRAWAL:
                Session::set_flash("message", ["danger", _("Incorrect withdrawal!")]);
                Response::redirect("withdrawals" . Lotto_View::query_vars());
        }
    }


    /**
     *
     * @param array $whitelabel
     * @return void
     */
    private function withdrawal_list(array $whitelabel = []): void
    {
        if (empty($whitelabel)) {
            Response::redirect("withdrawals" . Lotto_View::query_vars());
        }

        $view_template = "whitelabel/transactions/withdrawals";
        $withdrawal_list = new Forms_Whitelabel_Withdrawal_List(
            Helpers_General::SOURCE_WHITELABEL,
            $whitelabel
        );
        $result = $withdrawal_list->process_form($view_template);

        switch ($result) {
            case Forms_Whitelabel_Withdrawal_List::RESULT_OK:
                $inside = $withdrawal_list->get_inside();
                $this->view->inside = $inside;
                break;
            case Forms_Whitelabel_Withdrawal_List::RESULT_NULL_DATA:
            case Forms_Whitelabel_Withdrawal_List::RESULT_NULL_COUNTED:
                exit("There is a problem on server");
        }
    }

    /**
     *
     * @return Response
     */
    public function action_withdrawals(): Response
    {
        if (!$this->is_user) {
            return Response::forge($this->view);
        }

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        switch ((string)$this->param("action")) {
            case "view":
                $this->withdrawal_view($whitelabel);
                break;
            default:
                $this->withdrawal_list($whitelabel);
                break;
        }

        $wcount = Model_Withdrawal_Request::count_for_whitelabel($whitelabel);
        $this->view->set_global("wcount", $wcount);

        $pcount = Model_Whitelabel_Transaction::count_for_whitelabel(
            Helpers_General::TYPE_TRANSACTION_PURCHASE,
            $whitelabel
        );
        $this->view->set_global("pcount", $pcount);

        $dcount = Model_Whitelabel_Transaction::count_for_whitelabel(
            Helpers_General::TYPE_TRANSACTION_DEPOSIT,
            $whitelabel
        );
        $this->view->set_global("dcount", $dcount);

        return Response::forge($this->view);
    }

    /**
     *
     * @param array $whitelabel
     * @param array $whitelabel_payment_methods_indexed list of methods where key is from 0 not by id of method
     * @param array $language_methods
     * @param int $whitelabel_payment_method_index index value needed for pull data from $kmethods
     * @return void
     */
    private function payment_method_order_down(
        array $whitelabel,
        array $whitelabel_payment_methods_indexed,
        array $language_methods,
        int $whitelabel_payment_method_index
    ): void {
        $whitelabel_payment_method_id = $whitelabel_payment_methods_indexed[$whitelabel_payment_method_index]['id'];

        $whitelabel_payment_method = Model_Whitelabel_Payment_Method::find_by_pk($whitelabel_payment_method_id);

        if (
            $whitelabel_payment_method !== null &&
            (int)$whitelabel_payment_method->whitelabel_id === (int)$whitelabel['id'] &&
            $whitelabel_payment_method->order < $language_methods[$whitelabel_payment_method['language_id']]
        ) {
            $whitelabel_payment_method_later = Model_Whitelabel_Payment_Method::find_by([
                "whitelabel_id" => $whitelabel['id'],
                "language_id" => $whitelabel_payment_method['language_id'],
                "order" => $whitelabel_payment_method['order'] + 1
            ]);

            if (
                $whitelabel_payment_method_later !== null &&
                count($whitelabel_payment_method_later) > 0
            ) {
                $whitelabel_payment_method_later[0]->set([
                    "order" => $whitelabel_payment_method_later[0]->order - 1
                ]);
                $whitelabel_payment_method_later[0]->save();
            }

            $whitelabel_payment_method->set([
                "order" => $whitelabel_payment_method->order + 1
            ]);
            $whitelabel_payment_method->save();

            Lotto_Helper::clear_cache('model_whitelabel_payment_method.paymentmethods.' . $whitelabel['id']);

            Session::set_flash("message", ["success", _("Payment method order has been changed!")]);
            Response::redirect('paymentmethods' . Lotto_View::query_vars());
        }
    }

    /**
     *
     * @param array $whitelabel
     * @param array $whitelabel_payment_methods_indexed list of methods where key is from 0 not by id of method
     * @param int $whitelabel_payment_method_index index value needed for pull data from $kmethods
     * @return void
     */
    private function payment_method_order_up(
        array $whitelabel,
        array $whitelabel_payment_methods_indexed,
        int $whitelabel_payment_method_index
    ): void {
        $whitelabel_payment_method_id = $whitelabel_payment_methods_indexed[$whitelabel_payment_method_index]['id'];

        $whitelabel_payment_method = Model_Whitelabel_Payment_Method::find_by_pk($whitelabel_payment_method_id);

        if (
            $whitelabel_payment_method !== null &&
            (int)$whitelabel_payment_method->whitelabel_id === (int)$whitelabel['id'] &&
            $whitelabel_payment_method->order > 1
        ) {
            $whitelabel_payment_method_later = Model_Whitelabel_Payment_Method::find_by([
                "whitelabel_id" => $whitelabel['id'],
                "language_id" => $whitelabel_payment_method['language_id'],
                "order" => $whitelabel_payment_method['order'] - 1
            ]);

            if (
                $whitelabel_payment_method_later !== null &&
                count($whitelabel_payment_method_later)
            ) {
                $whitelabel_payment_method_later[0]->set([
                    "order" => $whitelabel_payment_method_later[0]->order + 1
                ]);
                $whitelabel_payment_method_later[0]->save();
            }

            $whitelabel_payment_method->set([
                "order" => $whitelabel_payment_method->order - 1
            ]);
            $whitelabel_payment_method->save();

            Lotto_Helper::clear_cache('model_whitelabel_payment_method.paymentmethods.' . $whitelabel['id']);

            Session::set_flash("message", ["success", _("Payment method order has been changed!")]);
            Response::redirect('paymentmethods' . Lotto_View::query_vars());
        }
    }

    /**
     *
     * @param array $whitelabel
     * @param array $whitelabel_payment_methods_with_currency
     * @param array $whitelabel_payment_methods_indexed list of methods where key is from 0 not by id of method
     * @param array $currencies_indexed_by_id
     * @param array $languages
     * @param array $languages_indexed_by_id
     * @return void
     */
    private function payment_method_add_edit(
        array $whitelabel,
        array $whitelabel_payment_methods_with_currency,
        array $whitelabel_payment_methods_indexed,
        array $currencies_indexed_by_id,
        array $languages,
        array $languages_indexed_by_id
    ): void {
        $token = null;
        if (!empty($this->param("id"))) {
            $token = intval($this->param("id"));
        }
        $action = $this->param("action");

        $template_path = "whitelabel/settings/payments/edit";

        $payments_edit = new Forms_Whitelabel_Payment_Method_Edit(
            Helpers_General::SOURCE_WHITELABEL,
            $whitelabel,
            $whitelabel_payment_methods_with_currency,
            $whitelabel_payment_methods_indexed,
            $currencies_indexed_by_id,
            $languages,
            $languages_indexed_by_id
        );
        $result = $payments_edit->process_form($action, $template_path, $token);

        switch ($result) {
            case Forms_Whitelabel_Payment_Method_Edit::RESULT_OK:
                Lotto_Helper::clear_cache('model_whitelabel_payment_method.paymentmethods.' . $whitelabel['id']);
                Session::set_flash("message", ["success", _("Payment method has been saved!")]);
                Response::redirect('paymentmethods' . Lotto_View::query_vars());
                break;
            case Forms_Whitelabel_Payment_Method_Edit::RESULT_WRONG_PAYMENT_METHOD:
                Session::set_flash("message", ["danger", _("Wrong payment method!")]);
                Response::redirect("paymentmethods" . Lotto_View::query_vars());
                break;
            case Forms_Whitelabel_Payment_Method_Edit::RESULT_WITH_ERRORS:
                $inside = $payments_edit->get_inside();
                $this->view->inside = $inside;
                break;
        }
    }

    /**
     *
     * @param array $whitelabel_payment_methods_with_currency
     * @param array $language_methods
     * @param array $languages_indexed_by_id
     * @param array $currencies_indexed_by_id
     * @return void
     */
    private function payment_method_list(
        array $whitelabel_payment_methods_with_currency,
        array $language_methods,
        array $languages_indexed_by_id,
        array $currencies_indexed_by_id
    ): void {
        $inside = Presenter::forge("whitelabel/settings/payments/list");
        $inside->set("methods", $whitelabel_payment_methods_with_currency);
        $inside->set("lang_methods", $language_methods);
        $inside->set("langs", $languages_indexed_by_id);
        $inside->set("currencies", $currencies_indexed_by_id);
        $inside->set("source", Helpers_General::SOURCE_WHITELABEL);

        $this->view->inside = $inside;
    }

    /**
     *
     * @param array $whitelabel
     * @param array $whitelabel_payment_methods_indexed list of methods where key is from 0 not by id of method
     * @param int $whitelabel_payment_method_index index value needed for pull data from $kmethods
     * @return void
     */
    private function payment_method_currency_list(
        array $whitelabel,
        array $whitelabel_payment_methods_indexed,
        int $whitelabel_payment_method_index
    ): void {
        $template_path = "whitelabel/settings/payments/currency/list";

        $payment_method_currency_list = new Forms_Whitelabel_Payment_Method_Currency_List(
            Helpers_General::SOURCE_WHITELABEL,
            $whitelabel,
            $whitelabel_payment_methods_indexed
        );

        $result = $payment_method_currency_list->process_form(
            $whitelabel_payment_method_index,
            $template_path
        );

        switch ($result) {
            case Forms_Whitelabel_Payment_Method_Currency_List::RESULT_OK:
                $inside = $payment_method_currency_list->get_inside();
                $this->view->inside = $inside;
                break;
            case Forms_Whitelabel_Payment_Method_Currency_List::RESULT_WRONG_PAYMENT_METHOD:
                Session::set_flash("message", ["danger", _("Incorrect payment method!")]);
                Response::redirect('paymentmethods' . Lotto_View::query_vars());
                break;
        }
    }

    /**
     *
     * @param array $whitelabel
     * @param array $whitelabel_payment_methods_indexed list of methods where key is from 0 not by id of method
     * @param int $whitelabel_payment_method_index index value needed for pull data from $kmethods
     * @param array $currencies_indexed_by_id
     * @return void
     */
    private function payment_method_currency_add_edit(
        array $whitelabel,
        array $whitelabel_payment_methods_indexed,
        int $whitelabel_payment_method_index,
        array $currencies_indexed_by_id
    ): void {
        $edit_id = null;
        if (!empty($this->param("edit_id"))) {
            $edit_id = $this->param("edit_id");
        }

        $template_path = "whitelabel/settings/payments/currency/edit";

        $payment_method_currency_edit = new Forms_Whitelabel_Payment_Method_Currency_Edit(
            Helpers_General::SOURCE_WHITELABEL,
            $whitelabel,
            $whitelabel_payment_methods_indexed,
            $currencies_indexed_by_id
        );

        $result = $payment_method_currency_edit->process_form(
            $whitelabel_payment_method_index,
            $template_path,
            $edit_id
        );

        switch ($result) {
            case Forms_Whitelabel_Payment_Method_Currency_Edit::RESULT_OK:
                // Because it is previously decreased
                $current_whitelabel_payment_method_index = $whitelabel_payment_method_index + 1;
                $url_redirect = 'paymentmethods/currency/' .
                    $current_whitelabel_payment_method_index .
                    '/list/' .
                    Lotto_View::query_vars();
                Session::set_flash("message", ["success", _("Currency to payment method has been saved!")]);
                Response::redirect($url_redirect);
                break;
            case Forms_Whitelabel_Payment_Method_Currency_Edit::RESULT_WITH_ERRORS:
                $inside = $payment_method_currency_edit->get_inside();
                $this->view->inside = $inside;
                break;
            case Forms_Whitelabel_Payment_Method_Currency_Edit::RESULT_WRONG_PAYMENT_METHOD:
                Session::set_flash("message", ["danger", _("Incorrect payment method!")]);
                Response::redirect('paymentmethods' . Lotto_View::query_vars());
                break;
        }
    }

    /**
     *
     * @param array $whitelabel
     * @param array $whitelabel_payment_methods_indexed list of methods where key is from 0 not by id of method
     * @param int $whitelabel_payment_method_index index value needed for pull data from $kmethods
     * @return void
     */
    private function payment_method_currency_delete(
        array $whitelabel,
        array $whitelabel_payment_methods_indexed,
        int $whitelabel_payment_method_index
    ): void {
        $edit_id = null;
        if (!empty($this->param("edit_id"))) {
            $edit_id = $this->param("edit_id");
        }

        $payment_method_currency_delete = new Forms_Whitelabel_Payment_Method_Currency_Delete(
            Helpers_General::SOURCE_WHITELABEL,
            $whitelabel
        );

        $whitelabel_payment_method_id = $whitelabel_payment_methods_indexed[$whitelabel_payment_method_index]['id'];

        $result = $payment_method_currency_delete->process_form(
            $whitelabel_payment_method_id,
            $edit_id
        );

        switch ($result) {
            case Forms_Whitelabel_Payment_Method_Currency_Delete::RESULT_OK:
                $current_whitelabel_payment_method_index = $whitelabel_payment_method_index + 1;
                $url_redirect = 'paymentmethods/currency/' .
                    $current_whitelabel_payment_method_index .
                    '/list/' .
                    Lotto_View::query_vars();
                Session::set_flash("message", ["success", _("Currency to payment method has been deleted!")]);
                Response::redirect($url_redirect);
                break;
            case Forms_Whitelabel_Payment_Method_Currency_Delete::RESULT_WRONG_PAYMENT_METHOD:
                Session::set_flash("message", ["danger", _("Incorrect payment method!")]);
                Response::redirect('paymentmethods' . Lotto_View::query_vars());
                break;
            case Forms_Whitelabel_Payment_Method_Currency_Delete::RESULT_WRONG_ID_GIVEN:
                Session::set_flash("message", ["danger", _("Wrong ID given!")]);
                Response::redirect('paymentmethods' . Lotto_View::query_vars());
                break;
        }
    }

    /**
     *
     * @param array $whitelabel
     * @param array $whitelabel_payment_methods_indexed list of methods where key is from 0 not by id of method
     * @param int $whitelabel_payment_method_index index value needed for pull data from $kmethods
     * @return void
     */
    private function payment_method_customize_list(
        array $whitelabel,
        array $whitelabel_payment_methods_indexed,
        int $whitelabel_payment_method_index
    ): void {
        $payment_method_customize_list = new Forms_Whitelabel_Payment_Method_Customize_List(
            Helpers_General::SOURCE_WHITELABEL,
            $whitelabel,
            $whitelabel_payment_methods_indexed
        );

        $result = $payment_method_customize_list->process_form($whitelabel_payment_method_index);

        switch ($result) {
            case Forms_Whitelabel_Payment_Method_Customize_List::RESULT_OK:
                $inside = $payment_method_customize_list->get_inside();
                $this->view->inside = $inside;
                break;
            case Forms_Whitelabel_Payment_Method_Customize_List::RESULT_WRONG_PAYMENT_METHOD:
                Session::set_flash("message", ["danger", _("Incorrect payment method!")]);
                Response::redirect('paymentmethods' . Lotto_View::query_vars());
                break;
        }
    }

    /**
     *
     * @param array $whitelabel
     * @param array $whitelabel_payment_methods_indexed
     * @param int $whitelabel_payment_method_index
     * @return void
     */
    private function payment_method_customize_add_edit(
        array $whitelabel,
        array $whitelabel_payment_methods_indexed,
        int $whitelabel_payment_method_index
    ): void {
        $edit_id = null;
        if (!empty($this->param("edit_id"))) {
            $edit_id = $this->param("edit_id");
        }

        $whitelabel_payment_method_id = $whitelabel_payment_methods_indexed[$whitelabel_payment_method_index]['id'];
        $action = $this->param("action");

        $payment_method_customize = new Forms_Whitelabel_Payment_Method_Customize_Edit(
            Helpers_General::SOURCE_WHITELABEL,
            $whitelabel,
            $whitelabel_payment_methods_indexed
        );

        $result = $payment_method_customize->process_form(
            $action,
            $whitelabel_payment_method_id,
            $edit_id,
            $whitelabel_payment_method_index
        );

        switch ($result) {
            case Forms_Whitelabel_Payment_Method_Customize_Edit::RESULT_OK:
                $current_whitelabel_payment_method_index = $whitelabel_payment_method_index + 1;
                $url_redirect = 'paymentmethods/customize/' .
                    $current_whitelabel_payment_method_index .
                    '/list/' .
                    Lotto_View::query_vars();
                Session::set_flash("message", ["success", _("Customization has been saved!")]);
                Response::redirect($url_redirect);
                break;
            case Forms_Whitelabel_Payment_Method_Customize_Edit::RESULT_NO_FREE_LANGUAGES:
                $current_whitelabel_payment_method_index = $whitelabel_payment_method_index + 1;
                $url_redirect = 'paymentmethods/customize/' .
                    $current_whitelabel_payment_method_index .
                    '/list/' .
                    Lotto_View::query_vars();
                Response::redirect($url_redirect);
                break;
            case Forms_Whitelabel_Payment_Method_Customize_Edit::RESULT_WRONG_PAYMENT_METHOD:
                Session::set_flash("message", ["danger", _("Incorrect payment method!")]);
                Response::redirect('paymentmethods' . Lotto_View::query_vars());
                break;
            case Forms_Whitelabel_Payment_Method_Customize_Edit::RESULT_WITH_ERRORS:
            case Forms_Whitelabel_Payment_Method_Customize_Edit::RESULT_GO_FURTHER:
                $inside = $payment_method_customize->get_inside();
                $this->view->inside = $inside;
                break;
        }
    }

    /**
     *
     * @param array $whitelabel
     * @param array $whitelabel_payment_methods_indexed list of methods where key is from 0 not by id of method
     * @param int $whitelabel_payment_method_index index value needed for pull data from $kmethods
     * @return void
     */
    private function payment_method_customize_delete(
        array $whitelabel,
        array $whitelabel_payment_methods_indexed,
        int $whitelabel_payment_method_index
    ): void {
        $edit_id = null;
        if (!empty($this->param("edit_id"))) {
            $edit_id = $this->param("edit_id");
        }

        $payment_method_customize_delete = new Forms_Whitelabel_Payment_Method_Customize_Delete(
            Helpers_General::SOURCE_WHITELABEL,
            $whitelabel
        );

        $whitelabel_payment_method_id = $whitelabel_payment_methods_indexed[$whitelabel_payment_method_index]['id'];

        $result = $payment_method_customize_delete->process_form(
            $whitelabel_payment_method_id,
            $edit_id
        );

        switch ($result) {
            case Forms_Whitelabel_Payment_Method_Customize_Delete::RESULT_OK:
                $current_whitelabel_payment_method_index = $whitelabel_payment_method_index + 1;
                $url_redirect = 'paymentmethods/customize/' .
                    $current_whitelabel_payment_method_index .
                    '/list/' .
                    Lotto_View::query_vars();
                Session::set_flash("message", ["success", _("Customization row to payment method has been deleted!")]);
                Response::redirect($url_redirect);
                break;
            case Forms_Whitelabel_Payment_Method_Customize_Delete::RESULT_WRONG_PAYMENT_METHOD:
                Session::set_flash("message", ["danger", _("Incorrect payment method!")]);
                Response::redirect('paymentmethods' . Lotto_View::query_vars());
                break;
            case Forms_Whitelabel_Payment_Method_Customize_Delete::RESULT_WRONG_ID_GIVEN:
                Session::set_flash("message", ["danger", _("Wrong ID given!")]);
                Response::redirect('paymentmethods' . Lotto_View::query_vars());
                break;
            case Forms_Whitelabel_Payment_Method_Customize_Delete::RESULT_WITH_ERRORS:
                Session::set_flash("message", ["danger", _("There is a problem with database! Please contact us.")]);
                Response::redirect('paymentmethods' . Lotto_View::query_vars());
        }
    }

    /**
     *
     * @return Response
     */
    public function action_paymentmethods(): Response
    {
        if (!$this->is_user) {
            return Response::forge($this->view);
        }

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        $whitelabel_languages = Model_Whitelabel_Language::get_whitelabel_languages($whitelabel);

        $languages_indexed_by_id = [];
        foreach ($whitelabel_languages as $whitelabel_language) {
            $languages_indexed_by_id[$whitelabel_language['id']] = $whitelabel_language;
        }

        $user_currency = [];
        $whitelabel_payment_methods_without_currency = Model_Whitelabel_Payment_Method::get_payment_methods_for_whitelabel($whitelabel);
        $whitelabel_payment_methods_with_currency = Helpers_Currency::get_whitelabel_payment_methods_with_currency(
            $whitelabel,
            $whitelabel_payment_methods_without_currency,
            $user_currency
        );

        $language_methods = [];
        foreach ($whitelabel_payment_methods_with_currency as $whitelabel_payment_method_with_currency) {
            if (!isset($language_methods[$whitelabel_payment_method_with_currency['language_id']])) {
                $language_methods[$whitelabel_payment_method_with_currency['language_id']] = 0;
            }
            $language_methods[$whitelabel_payment_method_with_currency['language_id']]++;
        }

        $currencies = Helpers_Currency::getCurrencies();
        $currencies_indexed_by_id = [];
        foreach ($currencies as $currency) {
            $currencies_indexed_by_id[$currency['id']] = $currency['code'];
        }
        asort($currencies_indexed_by_id);

        $whitelabel_payment_methods_indexed = array_values($whitelabel_payment_methods_with_currency);

        $title = _("Payment methods");

        $go_to_default = false;

        // index value needed for pull data from $kmethods which is map
        // of methods pulled from DB, but mapped from int value 0 not by
        // id of the method within DB
        $whitelabel_payment_method_index = -1;
        if (
            !is_null($this->param("id")) &&
            (int)$this->param("id") > 0
        ) {
            $whitelabel_payment_method_index = intval($this->param("id")) - 1;
        }

        switch ($this->param("action")) {
            case "orderdown":
                if (isset($whitelabel_payment_methods_indexed[$whitelabel_payment_method_index])) {
                    $this->payment_method_order_down(
                        $whitelabel,
                        $whitelabel_payment_methods_indexed,
                        $language_methods,
                        $whitelabel_payment_method_index
                    );
                } else {
                    $go_to_default = true;
                }
                break;
            case "orderup":
                if (isset($whitelabel_payment_methods_indexed[$whitelabel_payment_method_index])) {
                    $this->payment_method_order_up(
                        $whitelabel,
                        $whitelabel_payment_methods_indexed,
                        $whitelabel_payment_method_index
                    );
                } else {
                    $go_to_default = true;
                }
                break;
            case "new":
            case "edit":
                Helpers_Whitelabel::check_permission($whitelabel);
                $this->payment_method_add_edit(
                    $whitelabel,
                    $whitelabel_payment_methods_with_currency,
                    $whitelabel_payment_methods_indexed,
                    $currencies_indexed_by_id,
                    $whitelabel_languages,
                    $languages_indexed_by_id
                );
                break;
            case "currency":
                if (isset($whitelabel_payment_methods_indexed[$whitelabel_payment_method_index])) {
                    $payment_method_name = $whitelabel_payment_methods_indexed[$whitelabel_payment_method_index]['name'];

                    switch ($this->param("subaction")) {
                        case "list":
                            $title = _("Currencies for " . $payment_method_name);
                            $this->payment_method_currency_list(
                                $whitelabel,
                                $whitelabel_payment_methods_indexed,
                                $whitelabel_payment_method_index
                            );
                            break;
                        case "new":
                        case "edit":
                            Helpers_Whitelabel::check_permission($whitelabel);
                            $title = _("Currencies for " . $payment_method_name);
                            $this->payment_method_currency_add_edit(
                                $whitelabel,
                                $whitelabel_payment_methods_indexed,
                                $whitelabel_payment_method_index,
                                $currencies_indexed_by_id
                            );
                            break;
                        case "delete":
                            Helpers_Whitelabel::check_permission($whitelabel);
                            $this->payment_method_currency_delete(
                                $whitelabel,
                                $whitelabel_payment_methods_indexed,
                                $whitelabel_payment_method_index
                            );
                            break;
                        default:
                            $go_to_default = true;
                            break;
                    }
                } else {
                    $go_to_default = true;
                }
                break;
            case "customize":
                if (isset($whitelabel_payment_methods_indexed[$whitelabel_payment_method_index])) {
                    $payment_method_name = $whitelabel_payment_methods_indexed[$whitelabel_payment_method_index]['name'];
                    $title = _("Customize for " . $payment_method_name);

                    switch ($this->param("subaction")) {
                        case "list":
                            $this->payment_method_customize_list(
                                $whitelabel,
                                $whitelabel_payment_methods_indexed,
                                $whitelabel_payment_method_index
                            );
                            break;
                        case "new":
                        case "edit":
                            $this->payment_method_customize_add_edit(
                                $whitelabel,
                                $whitelabel_payment_methods_indexed,
                                $whitelabel_payment_method_index
                            );
                            break;
                        case "delete":
                            $this->payment_method_customize_delete(
                                $whitelabel,
                                $whitelabel_payment_methods_indexed,
                                $whitelabel_payment_method_index
                            );
                            break;
                        default:
                            $go_to_default = true;
                            break;
                    }
                } else {
                    $go_to_default = true;
                }
                break;
            default:
                $go_to_default = true;
                break;
        }

        if ($go_to_default) {
            $this->payment_method_list(
                $whitelabel_payment_methods_with_currency,
                $language_methods,
                $languages_indexed_by_id,
                $currencies_indexed_by_id
            );
        }

        $this->view->set_global("title", $title);

        return Response::forge($this->view);
    }

    /**
     * TODO: (if it will be working in the future)
     * At this moment this is not fully functional because there is no such functionality
     * such as currencies for cc payment
     *
     * @return Response
     */
    public function action_ccsettings(): Response
    {
        if (!$this->is_user) {
            return Response::forge($this->view);
        }

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        Helpers_Whitelabel::check_permission($whitelabel);

        $methods = Model_Whitelabel_CC_Method::get_cc_methods_for_whitelabel($whitelabel);
        $smethods = Lotto_Helper::get_cc_gateways();
        $cmethods = [];
        foreach ($methods as $method) {
            if (!isset($cmethods[$method['method']])) {
                $cmethods[$method['method']] = 0;
            }
            $cmethods[$method['method']]++;
        }
        $kmethods = array_values($methods);

        $currencies = Helpers_Currency::getCurrencies();
        $kcurrencies = [];
        foreach ($currencies as $currency) {
            $kcurrencies[$currency['id']] = $currency['code'];
        }
        asort($kcurrencies);

        if (
            $this->param("action") == "new" ||
            $this->param("action") == "edit"
        ) {
            $edit = [];
            $data = [];

            // Is equal to index of the row in table from Front-end greater than 0
            // - index of the row within $kmethods prepared from 0 (so it has to
            // be decreased by 1
            $edit_lp = null;
            if (
                $this->param("action") == "edit" &&
                isset($kmethods[intval($this->param("id")) - 1])
            ) {
                $edit_lp = intval($this->param("id"));
                $whitelabel_cc_method = Model_Whitelabel_CC_Method::find_by_pk($kmethods[$edit_lp - 1]['id']);
                if (
                    $whitelabel_cc_method !== null &&
                    $whitelabel_cc_method->whitelabel_id == $whitelabel['id']
                ) {
                    $edit = $whitelabel_cc_method;
                    if (
                        isset($cmethods[$edit['method']]) &&
                        $cmethods[$edit['method']] > 0
                    ) {
                        $cmethods[$edit['method']]--;
                    }
                    $data = unserialize($whitelabel_cc_method->settings);
                }
            }

            $inside = View::forge("whitelabel/settings/ccsettings_edit");
            if (null !== Input::post("input.method")) {
                $ccpayments = new Forms_Whitelabel_CCPayments();
                $val = $ccpayments->get_prepared_form($kcurrencies);

                if ($val->run()) {
                    $val2 = null;
                    if ($val->validated("input.method") == Helpers_Payment_Method::CC_EMERCHANT) {
                        $emerchant_pay = new Forms_Whitelabel_Emerchantpay();
                        $val2 = $emerchant_pay->get_prepared_form();
                    }

                    if ($val2 == null || $val2->run()) {
                        if (isset($smethods[$val->validated("input.method")])) {
                            if (
                                !isset($cmethods[$val->validated("input.method")]) ||
                                $cmethods[$val->validated("input.method")] == 0
                            ) {
                                $method = null;
                                if (isset($edit['id'])) {
                                    $method = $edit;
                                } else {
                                    $method = Model_Whitelabel_CC_Method::forge();
                                }
                                $data = [];
                                if ($val2 != null) {
                                    switch ($val->validated("input.method")) {
                                        case Helpers_Payment_Method::CC_EMERCHANT:
                                            $data['accountid'] = $val2->validated("input.accountid");
                                            $data['apikey'] = $val2->validated("input.apikey");
                                            $data['endpoint'] = $val2->validated("input.endpoint");
                                            $data['secretkey'] = $val2->validated("input.secretkey");
                                            $data['minorder'] = $val2->validated("input.minorder");
                                            $data['descriptor'] = $val2->validated("input.descriptor");
                                            $data['test'] = $val2->validated("input.test") == 1 ? 1 : 0;
                                            break;
                                    }
                                }

                                // TODO: At this moment I left it as it is, so it has
                                // the same single currency without
                                // functionality for more than one currency
                                // SEE description of the funciton!
                                $method_set = [
                                    'whitelabel_id' => $whitelabel['id'],
                                    'method' => $val->validated("input.method"),
                                    'settings' => serialize($data),
                                    'cost_percent' => empty($val->validated("input.cost_percent")) ? 0 : $val->validated("input.cost_percent"),
                                    'cost_fixed' => empty($val->validated("input.cost_fixed")) ? 0 : $val->validated("input.cost_fixed"),
                                    'cost_currency_id' => empty($val->validated("input.cost_fixed")) ? null : $val->validated("input.cost_currency"),
                                    'payment_currency_id' => $val->validated("input.payment_currency")
                                ];
                                $method->set($method_set);
                                $method->save();

                                Lotto_Helper::clear_cache('model_whitelabel_cc_method.ccmethods.' . $whitelabel['id']);

                                Session::set_flash("message", ["success", _("Payment method has been saved!")]);
                                Response::redirect('ccsettings' . Lotto_View::query_vars());
                            } else {
                                $errors = ['input.method' => _("This gateway is already added!")];
                                $inside->set("errors", $errors);
                            }
                        } else {
                            $errors = ['input.method' => _("Wrong gateway!")];
                            $inside->set("errors", $errors);
                        }
                    } else {
                        $errors = Lotto_Helper::generate_errors($val2->error());
                        $inside->set("errors", $errors);
                    }
                } else {
                    $errors = Lotto_Helper::generate_errors($val->error());
                    $inside->set("errors", $errors);
                }
            }

            $inside->set("data", $data);
            $inside->set("edit", $edit);
            $inside->set("edit_lp", $edit_lp);
            $inside->set("methods", $smethods);
            $inside->set("cmethods", $cmethods);
            $inside->set("currencies", $kcurrencies);
            $this->view->inside = $inside;
        } else {
            $inside = View::forge("whitelabel/settings/ccsettings");
            $inside->set("methods", $methods);
            $inside->set("currencies", $kcurrencies);
            $this->view->inside = $inside;
        }

        $this->view->set_global("title", _("Credit Card methods"));
        return Response::forge($this->view);
    }

    /**
     *
     * @param array $whitelabel
     * @param string $rparam
     * @return void
     */
    private function transaction_accept($whitelabel, $rparam): void
    {
        $transaction_accept = new Forms_Whitelabel_Transactions_Accept(
            Helpers_General::SOURCE_WHITELABEL,
            $this->param("id"),
            $whitelabel,
            $rparam
        );
        $result = $transaction_accept->process_form();

        switch ($result) {
            case Forms_Whitelabel_Transactions_Accept::RESULT_OK:
                Response::redirect($rparam . Lotto_View::query_vars());
                break;
            case Forms_Whitelabel_Transactions_Accept::RESULT_SECURITY_ERROR:
                exit('Bad request');
                break;
            case Forms_Whitelabel_Transactions_Accept::RESULT_INCORRECT_TRANSACTION:
                Response::redirect($rparam . Lotto_View::query_vars());
        }
    }

    /**
     *
     * @param array $whitelabel
     * @param string $rparam
     * @return null
     */
    private function transaction_view($whitelabel, $rparam): void
    {
        $view_template = "whitelabel/transactions/view";
        $transaction_view = new Forms_Whitelabel_Transactions_View(
            $this->param("id"),
            $whitelabel,
            $rparam
        );
        $result = $transaction_view->process_form($view_template);

        switch ($result) {
            case Forms_Whitelabel_Transactions_View::RESULT_OK:
                $inside = $transaction_view->get_inside();
                $this->view->inside = $inside;
                break;
            case Forms_Whitelabel_Transactions_View::RESULT_INCORRECT_TRANSACTION:
                Session::set_flash("message", ["danger", _("Incorrect transaction!")]);
                Response::redirect("transactions");
                break;
        }
    }

    /**
     *
     * @param array $whitelabel
     * @return null
     */
    private function transaction_cancel($whitelabel)
    {
        // removed, not used at least for now
//        if ($this->param("action") == "cancel") {
//            $transaction = Model_Whitelabel_Transaction::find_by_pk($this->param("id"));
//            if ($transaction !== null && $transaction->whitelabel_id == $whitelabel['id'] && $transaction->status == 1) {
//                $processed = DB::query("SELECT COUNT(*) AS count FROM whitelabel_user_ticket wut WHERE
//                                        wut.whitelabel_transaction_id = :transaction AND date_processed IS NOT NULL");
//                $processed->param(":transaction", $transaction->id);
//                $processed = $processed->execute()->as_array();
//                $processed = $processed[0]['count'];
//                if ($processed == 0) {
//                    $transaction->set(array(
//                        'status' => 3
//                    ));
//                    $transaction->save();
//                    if ($rparam == "transactions") {
//                        $tickets = Model_Whitelabel_User_Ticket::find_by_whitelabel_transaction_id($transaction->id);
//                        if ($tickets !== null) {
//                            foreach ($tickets AS $ticket) {
//                                $ticket->set(array(
//                                    'paid' => 0
//                                ));
//                                $ticket->save();
//                            }
//                        }
//                    }
//                    $user = Model_Whitelabel_User::find_by_pk($transaction->whitelabel_user_id);
//
//                    $balance = $user['balance'];
//                    $balance = bcadd($balance, $transaction->amount, 2);
//                    $user->set(array(
//                        'balance' => $balance,
//                        'last_update' => DB::expr("NOW()")
//                    ));
//                    $user->save();
//
//                    Session::set_flash("message", array("success", _("Transaction has been canceled. The transaction amount has been returned to user account.")));
//                } else {
//                    Session::set_flash("message", array("danger", _("Incorrect transaction!")));
//                }
//            } else {
//                Session::set_flash("message", array("danger", _("Incorrect transaction!")));
//            }
//            Response::redirect($rparam.Lotto_View::query_vars());
//        }
        return;
    }

    /**
     *
     * @param array $whitelabel
     * @param array $rparam
     * @return void
     */
    private function transaction_list(array $whitelabel, string $rparam): void
    {
        $view_template = "whitelabel/transactions/list";
        $transaction_list = new Forms_Whitelabel_Transactions_List(
            Helpers_General::SOURCE_WHITELABEL,
            $whitelabel,
            $rparam
        );

        $result = $transaction_list->process_form($view_template);

        switch ($result) {
            case Forms_Whitelabel_Transactions_List::RESULT_OK:
                $inside = $transaction_list->get_inside();
                break;
            case Forms_Whitelabel_Transactions_List::RESULT_NULL_COUNTED:
            case Forms_Whitelabel_Transactions_List::RESULT_NULL_DATA:
                exit('Bad request');
                break;
        }

        $title = "";
        if ($rparam == "transactions") {
            $title = _("Purchases");
        } else {
            $title = _("Deposits");
        }

        $this->view->set_global("title", $title);

        $this->view->inside = $inside;
    }

    /**
     *
     * @param string $rparam
     * @return Response
     */
    public function action_transactions($rparam = "transactions"): Response
    {
        if (!$this->is_user) {
            return Response::forge($this->view);
        }
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        switch ($this->param("action")) {
            case "accept":
                $this->transaction_accept($whitelabel, $rparam);
                break;
            case "view":
                $this->transaction_view($whitelabel, $rparam);
                break;
            case "cancel":
                $this->transaction_cancel($whitelabel);
                break;
            default:
                $this->transaction_list($whitelabel, $rparam);
                break;
        }

        $wcount = Model_Withdrawal_Request::count_for_whitelabel($whitelabel);
        $this->view->set_global("wcount", $wcount);

        $pcount = Model_Whitelabel_Transaction::count_for_whitelabel(Helpers_General::TYPE_TRANSACTION_PURCHASE, $whitelabel);
        $this->view->set_global("pcount", $pcount);

        $dcount = Model_Whitelabel_Transaction::count_for_whitelabel(Helpers_General::TYPE_TRANSACTION_DEPOSIT, $whitelabel);
        $this->view->set_global("dcount", $dcount);

        return Response::forge($this->view);
    }

    /**
     *
     * @param array $whitelabel
     * @param string $rparam
     */
    private function user_delete($whitelabel, $rparam)
    {
        $user_db = new Model_Whitelabel_User($this->param("id"), $whitelabel);
        $result = $user_db->delete_user();

        if ($result) {
            Session::set_flash("message", ["success", _("User has been deleted!")]);
        } else {
            Session::set_flash("message", ["danger", _("Incorrect user.")]);
        }

        Response::redirect($rparam . Lotto_View::query_vars());
    }

    /**
     *
     * @param array $whitelabel
     */
    private function user_activate($whitelabel)
    {
        $user_db = new Model_Whitelabel_User($this->param("id"), $whitelabel);
        $result = $user_db->activate_user();

        if ($result) {
            Session::set_flash("message", ["success", _("User has been activated and confirmed!")]);
        } else {
            Session::set_flash("message", ["danger", _("Incorrect user.")]);
        }

        Response::redirect("inactive" . Lotto_View::query_vars());
    }

    /**
     *
     * @param array $whitelabel
     * @return null That function returns null only in the case that there is
     *              something wrong with DB
     */
    private function user_restore($whitelabel)
    {
        $user_db = new Model_Whitelabel_User($this->param("id"), $whitelabel);
        $result = $user_db->user_restore();

        switch ($result) {
            case Model_Whitelabel_User::RESULT_OK:
                Session::set_flash("message", ["success", _("User has been restored!")]);
                break;
            case Model_Whitelabel_User::RESULT_INCORRECT_USER:
                Session::set_flash("message", ["danger", _("Incorrect user.")]);
                break;
            case Model_Whitelabel_User::RESULT_USER_EXIST:
                Session::set_flash("message", ["danger", _("User cannot be restored because there exists active user with this e-mail.")]);
                break;
            case Model_Whitelabel_User::RESULT_DB_ERROR:
                Session::set_flash("message", ["danger", _("There is a problem with database!")]);
                break;
        }

        Response::redirect("deleted" . Lotto_View::query_vars());
    }

    /**
     *
     * @param array $whitelabel
     * @param string $rparam
     * @return void
     */
    private function user_aff($whitelabel, $rparam): void
    {
        $user_token = $this->param("id");
        $user_db = new Model_Whitelabel_User($user_token, $whitelabel);
        $users = $user_db->get_active_user();

        if ($users !== null && count($users) > 0) {
            $user = $users[0];
            $path_to_view = "whitelabel/users/aff";

            $user_aff = new Forms_Whitelabel_User_Aff($whitelabel);
            $result = $user_aff->process_form($path_to_view, $user, $rparam);

            switch ($result) {
                case Forms_Whitelabel_User_Aff::RESULT_OK:
                    Session::set_flash("message", ["success", _("User affiliate has been changed.")]);
                    Response::redirect($rparam . Lotto_View::query_vars());
                    break;
                case Forms_Whitelabel_User_Aff::RESULT_GO_FURTHER:
                case Forms_Whitelabel_User_Aff::RESULT_WITH_ERRORS:
                    $inside = $user_aff->get_inside();
                    $this->view->inside = $inside;
                    break;
            }
        } else {
            Session::set_flash("message", ["danger", _("Incorrect user.")]);
            Response::redirect("users");
        }
    }

    /**
     *
     * @param array $whitelabel
     */
    private function user_confirm($whitelabel)
    {
        $user_db = new Model_Whitelabel_User($this->param("id"), $whitelabel);
        $result = $user_db->user_confirm();

        if ($result) {
            Session::set_flash("message", ["success", _("User e-mail address confirmed!")]);
        } else {
            Session::set_flash("message", ["danger", _("Incorrect user.")]);
        }

        Response::redirect("users" . Lotto_View::query_vars());
    }

    /**
     *
     * @param array $whitelabel
     * @return void
     */
    private function user_edit($whitelabel): void
    {
        $user_token = $this->param("id");
        $users_db = new Model_Whitelabel_User($user_token, $whitelabel);
        $users = $users_db->get_active_user();

        if ($users !== null && count($users) > 0) {
            $user = $users[0];

            $view_template = "whitelabel/users/edit";
            $user_edit = new Forms_Whitelabel_User_Edit($whitelabel);
            $result = $user_edit->process_form($view_template, $user);

            switch ($result) {
                case Forms_Whitelabel_User_Edit::RESULT_OK:
                    Session::set_flash("message", ["success", _("User details has been saved!")]);
                    Response::redirect("users/view/" . $user_token . Lotto_View::query_vars());
                    break;
                case Forms_Whitelabel_User_Edit::RESULT_GO_FURTHER:
                case Forms_Whitelabel_User_Edit::RESULT_WITH_ERRORS:
                    $inside = $user_edit->get_inside();
                    $this->view->inside = $inside;
            }
        } else {
            Session::set_flash("message", ["danger", _("Incorrect user.")]);
            Response::redirect("users");
        }
    }

    /**
     *
     * @param array $whitelabel
     * @return void
     */
    private function user_password($whitelabel): void
    {
        $user_token = $this->param("id");
        $users_db = new Model_Whitelabel_User($user_token, $whitelabel);
        $users = $users_db->get_active_user();

        if ($users !== null && count($users) > 0) {
            $user = $users[0];

            $view_template = "whitelabel/users/password";

            $user_password = new Forms_Whitelabel_User_Password();
            $result = $user_password->process_form($view_template, $user);

            switch ($result) {
                case Forms_Whitelabel_User_Password::RESULT_OK:
                    Session::set_flash("message", ["success", _("User password has been changed!")]);
                    Response::redirect("users/view/" . $user_token . Lotto_View::query_vars());
                    break;
                case Forms_Whitelabel_User_Password::RESULT_GO_FURTHER:
                case Forms_Whitelabel_User_Password::RESULT_WITH_ERRORS:
                    $inside = $user_password->get_inside();
                    $this->view->inside = $inside;
                    break;
            }
        } else {
            Session::set_flash("message", ["danger", _("Incorrect user.")]);
            Response::redirect("users");
        }
    }

    /**
     *
     * @param array $whitelabel
     * @return void
     */
    private function user_email($whitelabel): void
    {
        $user_token = $this->param("id");
        $users_db = new Model_Whitelabel_User($user_token, $whitelabel);
        $users = $users_db->get_active_user();

        if ($users !== null && count($users) > 0) {
            $user = $users[0];

            $view_template = "whitelabel/users/email";

            $user_email = new Forms_Whitelabel_User_Email($whitelabel);
            $result = $user_email->process_form($view_template, $user);

            switch ($result) {
                case Forms_Whitelabel_User_Email::RESULT_OK:
                    Session::set_flash("message", ["success", _("User e-mail address has been saved!")]);
                    Response::redirect("users/view/" . $user_token . Lotto_View::query_vars());
                    break;
                case Forms_Whitelabel_User_Email::RESULT_GO_FURTHER:
                case Forms_Whitelabel_User_Email::RESULT_WITH_ERRORS:
                    $inside = $user_email->get_inside();
                    $this->view->inside = $inside;
                    break;
                case Forms_Whitelabel_User_Email::RESULT_NULL_COUNTED:
                    exit('Bad request');
                    break;
            }
        } else {
            Session::set_flash("message", ["danger", _("Incorrect user.")]);
            Response::redirect("users");
        }
    }

    /**
     *
     * @param array $whitelabel
     * @return void
     */
    private function user_view($whitelabel): void
    {
        $view_template = "whitelabel/users/view";
        $user_token = $this->param("id");
        $user_view = new Forms_Whitelabel_User_View(
            Helpers_General::SOURCE_WHITELABEL,
            $user_token,
            $whitelabel
        );
        $result = $user_view->process_form($view_template);

        switch ($result) {
            case Forms_Whitelabel_User_View::RESULT_OK:
                $inside = $user_view->get_inside();
                $this->view->inside = $inside;
                break;
            case Forms_Whitelabel_User_View::RESULT_INCORRECT_USER:
                Session::set_flash("message", ["danger", _("Incorrect user.")]);
                Response::redirect("users");
                break;
        }
    }

    /**
     *
     * @param array $whitelabel
     * @param string $rparam
     * @param int $deleted
     * @param string $link
     * @return void
     */
    private function user_list(&$whitelabel, $rparam, $deleted, $link): void
    {
        $inside = View::forge("whitelabel/users/index");

        $user_list = new Forms_Whitelabel_User_List(
            Helpers_General::SOURCE_WHITELABEL,
            $whitelabel
        );
        $user_list->process_form($inside, $rparam, $deleted, $link);
        $this->view->inside = $inside;
    }

    /**
     *
     * @param array $whitelabel
     * @param string $rparam
     * @param int $deleted
     * @param string $link
     * @return void
     */
    private function user_list_export(&$whitelabel, $rparam, $deleted, $link): void
    {
        $user_list_export = new Forms_Whitelabel_User_List(
            Helpers_General::SOURCE_WHITELABEL,
            $whitelabel
        );
        $user_list_export->prepare_for_export($rparam, $deleted, $link);
    }

    /**
     *
     * @param string $rparam
     * @return Response
     */
    public function action_users($rparam = "users"): Response
    {
        if (!$this->is_user) {
            return Response::forge($this->view);
        }

        // Set the title for current page
        $titles_arr = [
            "users" => _("Active users"),
            "inactive" => _("Inactive users"),
            "deleted" => _("Deleted users"),
        ];
        $this->view->set_global("title", isset($titles_arr[$rparam]) ? $titles_arr[$rparam] : null);

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        $deleted = 0;
        $link = "/users";
        if ($rparam == "deleted") {
            $deleted = 1;
            $link = "/deleted";
        } elseif ($rparam == "inactive") {
            $link = "/inactive";
        }

        switch ($this->param("action")) {
            case "delete":
                $this->user_delete($whitelabel, $rparam);
                break;
            case "activate":
                $this->user_activate($whitelabel);
                break;
            case "restore":
                $this->user_restore($whitelabel);
                break;
            case "aff":
                $this->user_aff($whitelabel, $rparam);
                break;
            case "confirm":
                $this->user_confirm($whitelabel);
                break;
            case "edit":
                $this->user_edit($whitelabel);
                break;
            case "password":
                $this->user_password($whitelabel);
                break;
            case "email":
                $this->user_email($whitelabel);
                break;
            case "view":
                $this->user_view($whitelabel);
                break;
            case "export":
                $this->user_list_export($whitelabel, $rparam, $deleted, $link);
                break;
            default:
                $this->user_list($whitelabel, $rparam, $deleted, $link);
                break;
        }

        $this->view->set_global("link", $link);
        $this->view->set_global("rparam", $rparam);

        $active_users = Model_Whitelabel::count_active_users($whitelabel);
        $this->view->set_global("active_cnt", $active_users);

        $inactive_users = Model_Whitelabel::count_inactive_users($whitelabel);
        $this->view->set_global("inactive_cnt", $inactive_users);

        $deleted_users = Model_Whitelabel::count_deleted_users($whitelabel);
        $this->view->set_global("deleted_cnt", $deleted_users);

        return Response::forge($this->view);
    }

    /**
     *
     * @return Response
     */
    public function action_index(): Response
    {
        if (!$this->is_user) {
            return Response::forge($this->view);
        }
        // maybe some cache info?
        // system info in future
        $this->view->inside = View::forge("whitelabel/dashboard/index");
        return Response::forge($this->view);
    }

    /**
     * Banners code generator
     *
     * @param array $whitelabel
     * @return void
     */
    private function aff_banners_generator(array $whitelabel): void
    {
        $inside = View::forge("whitelabel/affs/banners");
        $this->view->inside = $inside;

        // Get all lotteries and languages available for whitelabel
        $lotteries = Model_Lottery::get_all_lotteries_for_whitelabel($whitelabel);
        $whitelabel_languages = Model_Whitelabel_Language::get_whitelabel_languages($whitelabel);

        $this->view->inside->set("whitelabel", $whitelabel);
        $this->view->inside->set("langs", $whitelabel_languages);
        $this->view->inside->set("lotteries", $lotteries);

        // Get sizes and sort them
        $sizes = Banners_Create::$allowed_methods;
        //sort($sizes);

        $this->view->inside->set("sizes", $sizes);

        $this->view->set_global("title", _("Banners"));

        $user = null;
        if (isset($this->user)) {
            $user = $this->user;
        }

        // Process form
        $aff_links = new Forms_Aff_Links($whitelabel);
        $aff_links->process_banner_form($this->view->inside, $user);

        // Temporary function to flush the cache
        if (Input::get("flush_cache") != null) {
            $cache = new Banners_Cache();
            array_map('unlink', glob(realpath('../' . $cache->cache_directory) . "/*.jpg"));
            Response::redirect('/affs/banners');
        }
    }

    /**
     *
     * @param array $whitelabel
     * @return void
     */
    private function aff_widgets_generator(array $whitelabel): void
    {
        $inside = View::forge("whitelabel/affs/widgets");
        $this->view->inside = $inside;

        // Get all lotteries and languages available for whitelabel
        $lotteries = Model_Lottery::get_all_lotteries_for_whitelabel($whitelabel);
        $whitelabel_languages = Model_Whitelabel_Language::get_whitelabel_languages($whitelabel);

        $this->view->inside->set("whitelabel", $whitelabel);
        $this->view->inside->set("langs", $whitelabel_languages);
        $this->view->inside->set("lotteries", $lotteries);

        // Get widget types and sort them
        $types = Banners_Widgets::$allowed_methods;
        // sort($sizes);

        $this->view->inside->set("types", $types);

        $this->view->set_global("title", _("Widgets"));

        $user = null;
        if (isset($this->user)) {
            $user = $this->user;
        }

        // Process form
        $aff_links = new Forms_Aff_Links($whitelabel);
        $aff_links->process_widgets_form($this->view->inside, $user);

        //return Response::forge($this->view);
    }

    /**
     * Mails list
     *
     * @return Response
     */
    public function action_mailsettings(): Response
    {
        if (!$this->is_user) {
            return Response::forge($this->view);
        }

        // Get url params
        $id = $this->param("id");
        $lang = $this->param("lang");

        $this->view->set_global("title", 'Mail settings');

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        switch ($this->param("action")) {
            case "edit":
                // Don't show language list if it's main template
                //if (empty($lang) && $id != "template") { // that's if you would like to to turn off language list for specific email template
                if (empty($lang)) {
                    $this->mail_languages_list($whitelabel, $id);
                } else {
                    $this->mail_edit($whitelabel, $id, $lang);
                }
                break;
            case "preview":
                $this->view = $this->mail_edit_preview($whitelabel);
                break;
            case "restore":
                $form = new Forms_Whitelabel_Email($whitelabel);
                $form->restore_default($whitelabel['id'], $id, $lang);

                return Response::redirect('/mailsettings');
                break;
            default:
                $this->mails_list($whitelabel);
                break;
        }

        return Response::forge($this->view);
    }

    /**
     * Available mail list to edit
     *
     * @param array $whitelabel
     * @return void
     */
    private function mails_list(array $whitelabel): void
    {
        $inside = View::forge("whitelabel/settings/mails/list");
        $mails = Model_Whitelabel_Mails_Template::get_whitelabel_templates_manager_list($whitelabel['id']);

        $inside->set("mails", $mails);
        $this->view->inside = $inside;
    }

    /**
     * Edit custom mail template for whitelabel
     *
     * @param array $whitelabel
     * @param string $slug
     * @param string $mail_lang
     * @return void
     */
    private function mail_edit(array $whitelabel, string $slug, string $mail_lang): void
    {
        $inside = View::forge("whitelabel/settings/mails/edit");
        $mail = Model_Whitelabel_Mails_Template::get_whitelabel_templates(
            $whitelabel['id'],
            $slug,
            $mail_lang
        );

        $wordpress_email = new Forms_Wordpress_Email($whitelabel);

        $mail['content'] = $wordpress_email->build_email($mail['content']);
        $mail['additional_translates'] = $wordpress_email->get_additional_translations($mail, $mail_lang);

        $form = new Forms_Whitelabel_Email($whitelabel);

        $variables = [];
        if (!empty($form->public_variables[$slug])) {
            $variables = $form->public_variables[$slug];
        }

        $inside->set("mail", $mail);
        $inside->set("mail_lang", $mail_lang);
        $inside->set("variables", $variables);

        // Form process
        $form->process_form(
            $mail['custom_template_id'],
            $mail['template_id'],
            $inside,
            $mail_lang,
            $slug,
            $mail['additional_translates']
        );

        $this->view->inside = $inside;
    }

    /**
     *  List available languages for mail edit
     *
     * @param array $whitelabel
     * @param string $slug
     * @return void
     */
    private function mail_languages_list(array $whitelabel, string $slug): void
    {
        $inside = View::forge("whitelabel/settings/mails/lang_select");
        $mail = Model_Whitelabel_Mails_Template::get_whitelabel_templates(
            $whitelabel['id'],
            $slug
        );
        $whitelabel_languages = Model_Whitelabel_Language::get_whitelabel_languages($whitelabel);

        $prepared_whitelabel_langages = [];

        foreach ($whitelabel_languages as $key => $whitelabel_language) {
            $lang_text = Lotto_View::format_language($whitelabel_language['code']);
            $lang_show_text = Security::htmlentities($lang_text);

            $single_language_data = [
                "id" => $whitelabel_language['id'],
                "text" => $lang_show_text,
                "code" => $whitelabel_language['code']
            ];

            $prepared_whitelabel_langages[] = $single_language_data;
        }
        $inside->set("mail", $mail);
        $inside->set("id", $slug);                  // In fact this is slug
        $inside->set("languages", $prepared_whitelabel_langages);

        $this->view->inside = $inside;
    }

    /**
     * Email template preview (ajax request)
     * @param $whitelabel
     * @return string
     */
    private function mail_edit_preview(array $whitelabel): string
    {
        $content = Input::post("content");

        $main_template = Model_Whitelabel_Mails_Template::get_whitelabel_templates($whitelabel['id'], 'template');
        $wp_email = new Forms_Wordpress_Email($whitelabel);

        return $wp_email->build_email($main_template['content'], $content);
    }

    /**
     *
     * @param array $whitelabel
     * @return void
     */
    private function bonuses_welcome(array $whitelabel): void
    {
        $welcome_bonus = new Forms_Whitelabel_Bonuses_Welcome(
            Helpers_General::SOURCE_WHITELABEL,
            $whitelabel
        );
        $result = $welcome_bonus->process_form();

        switch ($result) {
            case Forms_Whitelabel_Bonuses_Welcome::RESULT_OK:
                Response::redirect("bonuses");
                break;
            case Forms_Whitelabel_Bonuses_Welcome::RESULT_WITH_ERRORS:
            case Forms_Whitelabel_Bonuses_Welcome::RESULT_GO_FURTHER:
                $inside = $welcome_bonus->get_inside();
                $this->view->inside = $inside;
                break;
        }

        $this->view->inside->set('rparam', 'welcome');
    }

    /**
     *
     * @param array $whitelabel
     * @return void
     */
    private function bonuses_referafriend(array $whitelabel): void
    {
        $referafriend_bonus = new Forms_Whitelabel_Bonuses_Referafriend($whitelabel);
        $referafriend_bonus->process_form();

        $default_currency = Model_Whitelabel_Default_Currency::get_default_for_whitelabel($whitelabel);

        $this->view->inside = $referafriend_bonus->get_inside();
        $this->view->inside->set('currency_code', $default_currency['currency_code']);
        $this->view->inside->set('rparam', 'referafriend');
    }

    /**
     *
     * @param array $whitelabel
     * @return void
     */
    private function bonusesFreeSpins(array $whitelabel): void
    {
        switch ($this->param('page')) {
            case 'users':
                $inside = View::forge('whitelabel/bonuses/freespins/users.php');
                $id = (int)$this->param('id');
                $users = $this->miniGameUserPromoCodeRepository->getUsersByPromoCodeId($id);
                $this->view->inside = $inside;
                $this->view->inside->set('users', $users);
                $this->view->inside->set('rparam', 'freespins');
                break;
            default:
                $inside = View::forge('whitelabel/bonuses/freespins/list.php');
                $freeSpins = $this->miniGamePromoCodeRepository->getAllPromoCodes($whitelabel['id']);
                $this->view->inside = $inside;
                $this->view->inside->set('freeSpins', $freeSpins);
                $this->view->inside->set('rparam', 'freespins');
                break;
        }
    }

    private function bonusesFreeSpinsNew(array $whitelabel): void
    {
        $renderedView = View::forge('whitelabel/bonuses/freespins/new.php');
        $availableMiniGames = $this->miniGameRepository->getAllEnabledGamesBasicInfoById();
        $availableBetsByGameId = $this->miniGameRepository->getAvailableBetsByGameId();
        $this->view->inside = $renderedView;
        $this->view->inside->set('rparam', 'freespins');
        $this->view->inside->set('whitelabelId', $whitelabel['id']);
        $this->view->inside->set('miniGames', $availableMiniGames);
        $this->view->inside->set('availableBetsByGameId', $availableBetsByGameId);

        if (!empty(Input::post())) {
            $result = $this->miniGamePromoCodeService->createMiniGamePromoCode($whitelabel['id']);

            if ($result) {
                Response::redirect('bonuses/freespins' . Lotto_View::query_vars());
            } else {
                $this->view->inside->set('errors', $this->miniGamePromoCodeService->getErrors());
            }
        }
    }

    private function bonusesFreeSpinsEdit(array $whitelabel, int $promoCodeId): void
    {
        $availableMiniGames = $this->miniGameRepository->getAllEnabledGamesBasicInfoById();
        $availableBetsByGameId = $this->miniGameRepository->getAvailableBetsByGameId();
        $promoCode = $this->miniGamePromoCodeRepository->findOneBy('id', $promoCodeId);

        $renderedView = View::forge('whitelabel/bonuses/freespins/edit.php');
        $this->view->inside = $renderedView;
        $this->view->inside->set('miniGames', $availableMiniGames);
        $this->view->inside->set('availableBetsByGameId', $availableBetsByGameId);
        $this->view->inside->set('rparam', 'freespins');
        $this->view->inside->set('promoCode', $promoCode);

        if (!empty(Input::post())) {
            $result = $this->miniGamePromoCodeService->updateMiniGamePromoCode($promoCodeId);
            if ($result) {
                Session::set_flash('message', ['success', 'The promo code was successfully updated']);
                Response::redirect('bonuses/freespins' . Lotto_View::query_vars());
            }

            $this->view->inside->set('errors', $this->miniGamePromoCodeService->getErrors());
        }
    }

    private function bonuses_promocodes(array $whitelabel): void
    {
        switch ($this->param('page')) {
            case 'codes':
                $token = $this->param("id");
                $promocodes_list = new Forms_Whitelabel_Bonuses_Promocodes_Codes($whitelabel, $token);
                break;
            case 'users':
                $token = $this->param("id");
                $promocodes_list = new Forms_Whitelabel_Bonuses_Promocodes_Users($whitelabel, $token);
                break;
            default:
                $promocodes_list = new Forms_Whitelabel_Bonuses_Promocodes_List($whitelabel);
        }

        $promocodes_list->process_form();
        $this->view->inside = $promocodes_list->get_inside();
        $this->view->inside->set('rparam', 'promocodes');
    }

    /**
     *
     * @param array $whitelabel
     * @return void
     */
    private function bonuses_promocodes_edit(array $whitelabel): void
    {
        $token = $this->param("id");
        $promocodes_edit = new Forms_Whitelabel_Bonuses_Promocodes_Edit($whitelabel, $token);
        $result = $promocodes_edit->process_form();
        switch ($result) {
            case Forms_Whitelabel_Bonuses_Promocodes_Edit::RESULT_OK:
                Response::redirect("bonuses/promocodes" . Lotto_View::query_vars());
                break;
            case Forms_Whitelabel_Bonuses_Promocodes_Edit::RESULT_GO_FURTHER:
            case Forms_Whitelabel_Bonuses_Promocodes_Edit::RESULT_WITH_ERRORS:
                $inside = $promocodes_edit->get_inside();
                $this->view->inside = $inside;
                break;
        }
    }

    /**
     *
     * @param array $whitelabel
     * @return void
     */
    private function bonuses_promocodes_new(array $whitelabel): void
    {
        $promocodes_new = new Forms_Whitelabel_Bonuses_Promocodes_New($whitelabel);
        $result = $promocodes_new->process_form();
        switch ($result) {
            case Forms_Whitelabel_Bonuses_Promocodes_New::RESULT_OK:
                Response::redirect("bonuses/promocodes" . Lotto_View::query_vars());
                break;
            case Forms_Whitelabel_Bonuses_Promocodes_New::RESULT_GO_FURTHER:
            case Forms_Whitelabel_Bonuses_Promocodes_New::RESULT_WITH_ERRORS:
                $inside = $promocodes_new->get_inside();
                $this->view->inside = $inside;
                break;
        }
    }

    /**
     *
     * @return Response
     */
    public function action_bonuses(): Response
    {
        if (!$this->is_user) {
            return Response::forge($this->view);
        }

        $this->view->set_global("title", _("Bonuses"));

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        switch ($this->param("action")) {
            case "welcome":
                $this->bonuses_welcome($whitelabel);
                break;
            case "referafriend":
                $this->bonuses_referafriend($whitelabel);
                break;
            case "promocodes":
                if ($this->param("id") == 'new') {
                    $this->bonuses_promocodes_new($whitelabel);
                } elseif ($this->param("page") == 'edit') {
                    $this->bonuses_promocodes_edit($whitelabel);
                } else {
                    $this->bonuses_promocodes($whitelabel);
                }
                break;
            case 'freespins':
                if ($this->param('id') === 'new') {
                    $this->bonusesFreeSpinsNew($whitelabel);
                } elseif ($this->param('page') === 'edit') {
                    $this->bonusesFreeSpinsEdit($whitelabel, (int)$this->param('id'));
                } else {
                    $this->bonusesFreeSpins($whitelabel);
                }
                break;
            default:
                $this->bonuses_welcome($whitelabel);
                break;
        }

        return Response::forge($this->view);
    }

    /**
     *
     * @return View
     */
    public function action_multidrawsettings()
    {
        if (!$this->is_user) {
            return Response::forge($this->view);
        }

        $id = $this->param("id");

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        switch ($this->param("action")) {
            case "new":
                $this->multi_draws_settings_new($whitelabel);
                break;
            case "edit":
                $this->multi_draws_settings_edit($whitelabel, $id);
                break;
            case "delete":
                $this->multi_draws_settings_delete($whitelabel, $id);
                break;
            case "lotteries":
                $this->multi_draws_settings_lotteries($whitelabel);
                break;
            default:
                $this->multi_draws_settings_list($whitelabel);
                break;
        }

        $this->view->set_global("title", _("Lottery settings"));

        return Response::forge($this->view);
    }

    /**
     * List available multi draw options
     *
     * @param array $whitelabel
     * @return void
     */
    private function multi_draws_settings_list(array $whitelabel = []): void
    {
        if (empty($whitelabel)) {
            exit('No whitelabel set!');
        }

        $inside = View::forge("whitelabel/settings/multidraw/list");
        $multi_draws_options = Model_Whitelabel_Multidraw_Option::get_whitelabel_options($whitelabel['id']);

        $inside->set("multi_draws_options", $multi_draws_options);

        $this->view->inside = $inside;
    }

    /**
     * List available multi draw options
     * @param $whitelabel
     */
    private function multi_draws_settings_lotteries(array $whitelabel = []): void
    {
        if (empty($whitelabel)) {
            exit('No whitelabel set!');
        }

        $inside = View::forge("whitelabel/settings/multidraw/lottery");

        $lottery_form = new Forms_Whitelabel_Multidraw_Lottery($whitelabel);
        $lottery_form->process_form($inside);

        $whitelabel_id = (int) $whitelabel['id'];

        $whitelabel_lotteries = Model_Whitelabel::get_lotteries_by_custom_order_for_whitelabel(
            $whitelabel_id,
            ['name']
        );
        $enabled_lotteries = Model_Whitelabel_Multidraw_Lottery::get_whitelabel_lotteries($whitelabel_id);

        $lotteries = [];
        foreach ($enabled_lotteries as $id => $lottery) {
            $lotteries[] = $lottery['lottery_id'];
        }

        $inside->set("whitelabel_lotteries", $whitelabel_lotteries);
        $inside->set("lotteries", $lotteries);

        $this->view->inside = $inside;
    }

    /**
     * List available multi draw options
     *
     * @param array $whitelabel
     * @return void
     */
    private function multi_draws_settings_new(array $whitelabel = []): void
    {
        if (empty($whitelabel)) {
            Response::redirect("multidrawsettings");
        }

        $inside = View::forge("whitelabel/settings/multidraw/new");

        $multidraw_option = new Forms_Whitelabel_Multidraw_Option($whitelabel);
        $result = $multidraw_option->process_form($inside);

        switch ($result) {
            case Forms_Whitelabel_Multidraw_Option::RESULT_OK:
                Response::redirect("multidrawsettings");
                break;
            case Forms_Whitelabel_Multidraw_Option::RESULT_GO_FURTHER:
            case Forms_Whitelabel_Multidraw_Option::RESULT_WITH_ERRORS:
                break;
        }

        $this->view->inside = $inside;
    }

    /**
     * List available multi draw options
     *
     * @param array $whitelabel
     * @param int $id
     * @return void
     */
    private function multi_draws_settings_edit(
        array $whitelabel = [],
        int $id = null
    ): void {
        if (empty($whitelabel) || is_null($id)) {
            Response::redirect("multidrawsettings");
        }

        $option = Model_Whitelabel_Multidraw_Option::get_whitelabel_option(
            (int) $whitelabel['id'],
            $id
        );

        if (empty($option->id)) {
            return ;
        }

        $inside = View::forge("whitelabel/settings/multidraw/edit");
        $inside->set("option", $option);

        $multidraw_option = new Forms_Whitelabel_Multidraw_Option($whitelabel);
        $result = $multidraw_option->process_form($inside, (int) $option->id);

        switch ($result) {
            case Forms_Whitelabel_Multidraw_Option::RESULT_OK:
                Response::redirect("multidrawsettings");
                break;
            case Forms_Whitelabel_Multidraw_Option::RESULT_GO_FURTHER:
            case Forms_Whitelabel_Multidraw_Option::RESULT_WITH_ERRORS:
                break;
        }

        $this->view->inside = $inside;
    }

    /**
     * List available languages for mail edit
     *
     * @param type $whitelabel
     * @param int $id
     * @return void
     */
    private function multi_draws_settings_delete(
        array $whitelabel = [],
        int $id = null
    ): void {
        if (empty($whitelabel) || is_null($id)) {
            Response::redirect("multidrawsettings");
        }

        $multi_draw_option = Model_Whitelabel_Multidraw_Option::get_whitelabel_option(
            (int) $whitelabel['id'],
            $id
        );

        if ($multi_draw_option != null) {
            $multi_draw_option->delete();

            Session::set_flash("message", ["success", _("Multi draw option has been deleted!")]);
        } else {
            Session::set_flash("message", ["danger", _("Wrong multi draw option!")]);
        }

        Response::redirect("multidrawsettings");
    }

    /**
     *
     * @param \Response $response
     * @return \Response
     */
    public function after($response)
    {
        return $response;
    }
}
