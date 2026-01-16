<?php

use Carbon\Carbon;
use Fuel\Core\Config;
use Fuel\Core\Controller;
use Fuel\Core\DB;
use Fuel\Core\Input;
use Fuel\Core\Pagination;
use Fuel\Core\Presenter;
use Fuel\Core\Session;
use Fuel\Core\Validation;
use Fuel\Core\Response;
use Helpers\UserHelper;
use Repositories\LotteryDrawRepository;
use Repositories\LotteryRepository;
use Services\Logs\FileLoggerService;
use Fuel\Core\View;
use Fuel\Core\Request;
use Fuel\Core\Uri;

/**
 * The Admin Controller.
 *
 * A basic controller example.  Has examples of how to set the
 * response body and status.
 *
 * @package  app
 * @extends  Controller
 */
class Controller_Admin extends Controller
{
    use Controller_Trait_Payment_Validator;
    use Controller_Trait_Payment_Data;

    /**
     * The basic welcome message
     *
     * @access  public
     * @return  Response
     */
    protected $view;

    /** @var bool */
    protected $is_user;

    /** @var string */
    protected $settings;

    private FileLoggerService $fileLoggerService;

    /**
     *
     */
    public function before()
    {
        if (!Lotto_Helper::allow_access("empire")) {
            $error = Request::forge('index/404')->execute();
            echo $error;
            exit();
        }

        $this->fileLoggerService = Container::get(FileLoggerService::class);

        $settings = Model_Setting::get_settings("admin");

        if (!empty($settings['admin_language'])) {
            $lang_id = $settings['admin_language'];
            $lang = Model_Language::find_by_pk($lang_id);
            if ($lang != null) {
                Lotto_Settings::getInstance()->set("locale_default", $lang['code'] . '.utf8');
                //Locale::setDefault($lang['code'].'.utf8');
                putenv('LC_ALL=' . $lang['code'] . '.utf8');
                setlocale(LC_ALL, $lang['code'] . '.utf8');
                bindtextdomain("admin", APPPATH . "lang/gettext");
                textdomain("admin");
                Config::set("language", substr($lang['code'], 0, 2));
            }
        }
        $timezone = "UTC";
        if (!empty($settings['admin_timezone'])) {
            $timezone = $settings['admin_timezone'];
        }
        Lotto_Settings::getInstance()->set("timezone", $timezone);

        $currencies = Helpers_Currency::getCurrencies();

        $this->is_user = false;
        $this->view = View::forge("admin/index");

        $login = new Forms_Login("admin");
        $result = $login->process_form($this->view);

        switch ($result) {
            case Forms_Login::RESULT_OK:
                Response::redirect("/");
                break;
            case Forms_Login::RESULT_GO_FURTHER:
            case Forms_Login::RESULT_SECURITY_ERROR:
            case Forms_Login::RESULT_TOO_MANY_ATTEMPTS:
            case Forms_Login::RESULT_WRONG_CREDENTIALS:
            case Forms_Login::RESULT_EMPTY_SALT:
                break;
        }

        if (
            Session::get("admin.remember") === null ||
            (int)Session::get("admin.remember") === 0
        ) {
            Session::set(UserHelper::SHOULD_LOGOUT_AFTER_BROWSER_CLOSE_KEY, true);
        }

        if (
            !empty(Session::get("admin.name")) &&
            !empty(Session::get("admin.hash")) &&
            Model_Setting::check_admin_credentials_hashed(Session::get("admin.name"), Session::get("admin.hash"))
        ) {
            $this->is_user = true;
        }

        $this->view->set_global("action", $this->request->action);
        $this->view->set_global("params", $this->request->params());
        $this->view->set_global("is_user", $this->is_user);

        $titles_arr = [
            "lotteries" => _("Lottery List"),
            "delays" => _("Postponed Draws"),
            "logs" => _("Lottery Logs"),
            "imvalaplogs" => _("Imvalap Logs"),
            "lottorisqlogs" => _("Lottorisq Logs"),
            "paymentlogs" => _("Payment Logs"),
            "whitelabels" => _("Whitelabels"),
            "index" => $this->is_user ? _("Dashboard") : null
        ];

        $this->view->set_global("title", isset($titles_arr[$this->request->action]) ? $titles_arr[$this->request->action] : null);
        $this->view->header = View::forge("admin/shared/header");
        $this->view->footer = View::forge("admin/shared/footer");

        if (!$this->is_user) {
            $this->view->inside = View::forge("admin/signin/index");
        }
    }

    public function action_signout(): void
    {
        Session::delete("admin");
        Session::set(UserHelper::SHOULD_LOGOUT_AFTER_BROWSER_CLOSE_KEY, true);
        Response::redirect('/');
    }

    /**
     *
     * @return Request  The new request object
     */
    public function action_index(): Response
    {
        if (!$this->is_user) {
            return Response::forge($this->view);
        }
        // maybe some cache info?
        // system info in future
        $this->view->inside = View::forge("admin/dashboard/index");
        return Response::forge($this->view);
    }

    /**
     *
     * @return void
     */
    private function edit_whitelabel(): void
    {
        $whitelabel = Model_Whitelabel::get_single_by_id($this->param("id"));

        if ($whitelabel === null) {
            Session::set_flash('message', ['danger', _("Wrong whitelabel!")]);
            Response::redirect('whitelabels');
        }

        $edit = new Forms_Whitelabel_Edit($whitelabel);
        $result = $edit->process_form();

        switch ($result) {
            case Forms_Whitelabel_Edit::RESULT_OK:
                Response::redirect('whitelabels');
                break;
            case Forms_Whitelabel_Edit::RESULT_GO_FURTHER:
            case Forms_Whitelabel_Edit::RESULT_WITH_ERRORS:
                break;
        }

        $inside = $edit->get_inside();
        $this->view->inside = $inside;
    }

    /**
     *
     *
     * @return void
     */
    private function new_whitelabel(): void
    {
        $new_whitelabel = new Forms_Whitelabel_New();
        $new_whitelabel->process_form();

        $errors = $new_whitelabel->get_errors();
        if (!empty($errors)) {
            $this->view->set_global("errors", $errors);
        }

        $inside = $new_whitelabel->get_inside();
        $this->view->inside = $inside;
    }

    /**
     * @return void
     */
    private function settings_whitelabel(): void
    {
        $path_to_view = "admin/whitelabels/settings";
        $whitelabel = Model_Whitelabel::get_single_by_id($this->param("id"));

        if ($whitelabel === null) {
            Session::set_flash('message', ['danger', _("Wrong whitelabel!")]);
            Response::redirect('whitelabels');
        }
        $settings = new Forms_Whitelabel_Settings($whitelabel, false, $path_to_view);

        $redirect_path = 'whitelabels/settings/' . $whitelabel['id'] . Lotto_View::query_vars();
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
     * @param array $whitelabel
     * @return void
     */
    public function prepaid_whitelabel_new(array $whitelabel): void
    {
        $path_to_view = "admin/whitelabels/prepaid/new";
        $prepaid_new = new Forms_Admin_Whitelabels_Prepaid_New(
            $whitelabel,
            $path_to_view
        );
        $result = $prepaid_new->process_form();

        switch ($result) {
            case Forms_Admin_Whitelabels_Prepaid_New::RESULT_OK:
                $message = [
                    "success",
                    _("Prepaid transaction have been added!")
                ];
                Session::set_flash("message", $message);

                $url = 'whitelabels/prepaid/' .
                    $whitelabel['id'] .
                    Lotto_View::query_vars();

                Response::redirect($url);
                break;
            case Forms_Admin_Whitelabels_Prepaid_New::RESULT_GO_FURTHER:
            case Forms_Admin_Whitelabels_Prepaid_New::RESULT_WITH_ERRORS:
                $inside = $prepaid_new->get_inside();
                $this->view->inside = $inside;
                break;
        }
    }

    private function prepaid_whitelabel_list(array $whitelabel): void
    {
        $path_to_view = "admin/whitelabels/prepaid/list";
        $request_page = $this->request->param("page");

        $prepaid_list = new Forms_Admin_Whitelabels_Prepaid_List(
            Helpers_General::SOURCE_ADMIN,
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
                    "There is a problem with pull data of whitelabels prepaids."
                );
                exit('Bad request');
                break;
        }
    }

    /**
     *
     * @return void
     */
    private function prepaid_whitelabel()
    {
        if (!$this->is_user) {
            return Response::forge($this->view);
        }

        $whitelabel = Model_Whitelabel::get_single_by_id($this->param("id"));

        if (Helpers_Whitelabel::is_V1($whitelabel['type'])) {
            return;
        }

        if ($whitelabel === null) {
            Session::set_flash('message', ['danger', _("Wrong whitelabel!")]);
            Response::redirect('whitelabels');
        }

        switch ($this->param("subaction")) {
            case "new":
                $this->prepaid_whitelabel_new($whitelabel);
                break;
            default:
                $this->prepaid_whitelabel_list($whitelabel);
                break;
        }

        $this->view->set_global("title", _("Prepaid"));

        return Response::forge($this->view);
    }

    /**
     * TODO: (if it will be working in the future)
     * At this moment this is not fully functional because there is no such functionality
     * such as currencies for cc payment
     *
     * @return null
     */
    private function ccpayments_whitelabel()
    {
        $inside = View::forge("admin/whitelabels/ccpayments");
        $whitelabel = Model_Whitelabel::get_single_by_id($this->param("id"));

        if ($whitelabel === null) {
            Session::set_flash('message', ['danger', _("Wrong whitelabel!")]);
            Response::redirect('whitelabels');
            return;
        }

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
        $inside->set("methods", $methods);

        $currencies = Helpers_Currency::getCurrencies();
        $kcurrencies = [];
        foreach ($currencies as $currency) {
            $kcurrencies[$currency['id']] = $currency['code'];
        }
        asort($kcurrencies);
        $inside->set("currencies", $kcurrencies);

        if ($this->param("subaction") == "new" || $this->param("subaction") == "edit") {
            $edit = [];
            $data = [];

            // Is equal to index of the row in table from Front-end greater than 0
            // - index of the row within $kmethods prepared from 0 (so it has to
            // be decreased by 1
            $edit_lp = null;
            if ($this->param("subaction") == "edit" && isset($kmethods[intval($this->param("sid")) - 1])) {
                $edit_lp = intval($this->param("sid"));
                $pm = Model_Whitelabel_CC_Method::find_by_pk($kmethods[$edit_lp - 1]['id']);
                if ($pm !== null && $pm->whitelabel_id == $whitelabel['id']) {
                    $edit = $pm;
                    if (isset($cmethods[$edit['method']]) && $cmethods[$edit['method']] > 0) {
                        $cmethods[$edit['method']]--;
                    }
                    $data = unserialize($pm->settings);
                }
            }
            $inside = View::forge("admin/whitelabels/ccpayments_edit");

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
                                $method->set([
                                    'whitelabel_id' => $whitelabel['id'],
                                    'method' => $val->validated("input.method"),
                                    'settings' => serialize($data),
                                    'cost_percent' => empty($val->validated("input.cost_percent")) ? 0 : $val->validated("input.cost_percent"),
                                    'cost_fixed' => empty($val->validated("input.cost_fixed")) ? 0 : $val->validated("input.cost_fixed"),
                                    'cost_currency_id' => empty($val->validated("input.cost_fixed")) ? null : $val->validated("input.cost_currency"),
                                    'payment_currency_id' => $val->validated("input.payment_currency")
                                ]);
                                $method->save();
                                Lotto_Helper::clear_cache('model_whitelabel_cc_method.ccmethods.' . $whitelabel['id']);

                                Session::set_flash("message", ["success", _("Payment method has been saved!")]);
                                Response::redirect('whitelabels/ccpayments/' . $whitelabel['id'] . Lotto_View::query_vars());
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
        }

        $inside->set("whitelabel", $whitelabel);

        $this->view->inside = $inside;

        return;
    }

    /**
     *
     * @param array $whitelabel
     * @param array $methods
     * @param array $lang_methods
     * @param array $klangs
     * @param array $kcurrencies
     * @return void
     */
    private function payment_method_list(
        array $whitelabel,
        array $methods,
        array $lang_methods,
        array $klangs,
        array $kcurrencies
    ): void {
        $inside = Presenter::forge("admin/whitelabels/payments/list");
        $inside->set("whitelabel", $whitelabel);
        $inside->set("methods", $methods);
        $inside->set("langs", $klangs);
        $inside->set("lang_methods", $lang_methods);
        $inside->set("currencies", $kcurrencies);
        $inside->set("source", Helpers_General::SOURCE_ADMIN);

        $this->view->inside = $inside;
    }

    /**
     *
     * @param array $whitelabel
     * @param array $lang_methods
     * @param int $whitelabel_payment_method_id
     * @return void
     */
    private function payment_method_order_down(
        array $whitelabel,
        array $lang_methods,
        int $whitelabel_payment_method_id
    ): void {
        $whitelabel_payment_method = Model_Whitelabel_Payment_Method::find_by_pk($whitelabel_payment_method_id);

        if (
            $whitelabel_payment_method !== null &&
            $whitelabel_payment_method->whitelabel_id == $whitelabel['id'] &&
            $whitelabel_payment_method->order < $lang_methods[$whitelabel_payment_method['language_id']]
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
            Response::redirect("whitelabels/payments/" . $whitelabel['id'] . Lotto_View::query_vars());
        }
    }

    /**
     *
     * @param array $whitelabel
     * @param int $whitelabel_payment_method_id
     * @return void
     */
    private function payment_method_order_up(
        array $whitelabel,
        int $whitelabel_payment_method_id
    ): void {
        $whitelabel_payment_method = Model_Whitelabel_Payment_Method::find_by_pk($whitelabel_payment_method_id);

        if (
            $whitelabel_payment_method !== null &&
            $whitelabel_payment_method->whitelabel_id == $whitelabel['id'] &&
            $whitelabel_payment_method->order > 1
        ) {
            $whitelabel_payment_method_later = Model_Whitelabel_Payment_Method::find_by([
                "whitelabel_id" => $whitelabel['id'],
                "language_id" => $whitelabel_payment_method['language_id'],
                "order" => $whitelabel_payment_method['order'] - 1
            ]);

            if (
                $whitelabel_payment_method_later !== null &&
                count($whitelabel_payment_method_later) > 0
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
            Response::redirect("whitelabels/payments/" . $whitelabel['id'] . Lotto_View::query_vars());
        }
    }

    /**
     *
     * @param array $whitelabel
     * @param array $methods
     * @param array $kmethods
     * @param array $kcurrencies
     * @param array $langs
     * @param array $klangs
     * @param int $whitelabel_payment_method_id
     * @return void
     */
    private function payment_method_add_edit(
        array $whitelabel,
        array $methods,
        array $kmethods,
        array $kcurrencies,
        array $langs,
        array $klangs,
        int $whitelabel_payment_method_id = null
    ): void {
        $action = $this->param("subaction");

        $template_path = "admin/whitelabels/payments/edit";

        $payments_edit = new Forms_Whitelabel_Payment_Method_Edit(
            Helpers_General::SOURCE_ADMIN,
            $whitelabel,
            $methods,
            $kmethods,
            $kcurrencies,
            $langs,
            $klangs
        );

        $result = $payments_edit->process_form(
            $action,
            $template_path,
            $whitelabel_payment_method_id
        );

        switch ($result) {
            case Forms_Whitelabel_Payment_Method_Edit::RESULT_OK:
                Lotto_Helper::clear_cache('model_whitelabel_payment_method.paymentmethods.' . $whitelabel['id']);
                Session::set_flash("message", ["success", _("Payment method has been saved!")]);
                Response::redirect("whitelabels/payments/" . $whitelabel['id'] . Lotto_View::query_vars());
                break;
            case Forms_Whitelabel_Payment_Method_Edit::RESULT_WRONG_PAYMENT_METHOD:
                Session::set_flash("message", ["danger", _("Wrong payment method!")]);
                Response::redirect("whitelabels/payments/" . $whitelabel['id'] . Lotto_View::query_vars());
                break;
            case Forms_Whitelabel_Payment_Method_Edit::RESULT_WITH_ERRORS:
                $inside = $payments_edit->get_inside();
                $this->view->inside = $inside;
                break;
        }
    }

    /**
     *
     * @param array $whitelabel
     * @param array $kmethods
     * @param int $whitelabel_payment_method_id
     * @return void
     */
    private function payment_method_currency_list(
        array $whitelabel,
        array $kmethods,
        int $whitelabel_payment_method_id
    ): void {
        $template_path = "admin/whitelabels/payments/currency/list";

        $payment_method_currency_list = new Forms_Whitelabel_Payment_Method_Currency_List(
            Helpers_General::SOURCE_ADMIN,
            $whitelabel,
            $kmethods
        );

        $result = $payment_method_currency_list->process_form(
            $whitelabel_payment_method_id,
            $template_path
        );

        switch ($result) {
            case Forms_Whitelabel_Payment_Method_Currency_List::RESULT_OK:
                $inside = $payment_method_currency_list->get_inside();
                $this->view->inside = $inside;
                break;
            case Forms_Whitelabel_Payment_Method_Currency_List::RESULT_WRONG_PAYMENT_METHOD:
                Session::set_flash("message", ["danger", _("Incorrect payment method!")]);
                Response::redirect("whitelabels/payments/" . $whitelabel['id'] . Lotto_View::query_vars());
                break;
        }
    }

    /**
     *
     * @param array $whitelabel
     * @param array $kmethods
     * @param int $whitelabel_payment_method_id
     * @param array $kcurrencies
     * @return void
     */
    private function payment_method_currency_add_edit(
        array $whitelabel,
        array $kmethods,
        int $whitelabel_payment_method_id,
        array $kcurrencies
    ): void {
        $edit_id = null;
        if (!empty($this->param("edit_id"))) {
            $edit_id = $this->param("edit_id");
        }

        $template_path = "admin/whitelabels/payments/currency/edit";

        $payment_method_currency_edit = new Forms_Whitelabel_Payment_Method_Currency_Edit(
            Helpers_General::SOURCE_ADMIN,
            $whitelabel,
            $kmethods,
            $kcurrencies
        );

        $result = $payment_method_currency_edit->process_form(
            $whitelabel_payment_method_id,
            $template_path,
            $edit_id
        );

        switch ($result) {
            case Forms_Whitelabel_Payment_Method_Currency_Edit::RESULT_OK:
                // Because it is previously decreased
                $current_kmethod_idx = $whitelabel_payment_method_id;
                $url_redirect = "whitelabels/payments/" . $whitelabel['id'] .
                    '/currency/' .
                    $current_kmethod_idx . '/list/' . Lotto_View::query_vars();
                Session::set_flash("message", ["success", _("Currency to payment method has been saved!")]);
                Response::redirect($url_redirect);
                break;
            case Forms_Whitelabel_Payment_Method_Currency_Edit::RESULT_WITH_ERRORS:
                $inside = $payment_method_currency_edit->get_inside();
                $this->view->inside = $inside;
                break;
            case Forms_Whitelabel_Payment_Method_Currency_Edit::RESULT_WRONG_PAYMENT_METHOD:
                Session::set_flash("message", ["danger", _("Incorrect payment method!")]);
                Response::redirect("whitelabels/payments/" . $whitelabel['id'] . Lotto_View::query_vars());
                break;
        }
    }

    /**
     *
     * @param array $whitelabel
     * @param int $whitelabel_payment_method_id
     * @return void
     */
    private function payment_method_currency_delete(
        array $whitelabel,
        int $whitelabel_payment_method_id
    ): void {
        $edit_id = null;
        if (!empty($this->param("edit_id"))) {
            $edit_id = $this->param("edit_id");
        }

        $payment_method_currency_delete = new Forms_Whitelabel_Payment_Method_Currency_Delete(
            Helpers_General::SOURCE_ADMIN,
            $whitelabel
        );

        $result = $payment_method_currency_delete->process_form(
            $whitelabel_payment_method_id,
            $edit_id
        );

        switch ($result) {
            case Forms_Whitelabel_Payment_Method_Currency_Delete::RESULT_OK:
                $current_kmethod_idx = $whitelabel_payment_method_id;
                $url_redirect = "whitelabels/payments/" . $whitelabel['id'] .
                    '/currency/' .
                    $current_kmethod_idx . '/list/' . Lotto_View::query_vars();
                Session::set_flash("message", ["success", _("Currency to payment method has been deleted!")]);
                Response::redirect($url_redirect);
                break;
            case Forms_Whitelabel_Payment_Method_Currency_Delete::RESULT_WRONG_PAYMENT_METHOD:
                Session::set_flash("message", ["danger", _("Incorrect payment method!")]);
                Response::redirect("whitelabels/payments/" . $whitelabel['id'] . Lotto_View::query_vars());
                break;
            case Forms_Whitelabel_Payment_Method_Currency_Delete::RESULT_WRONG_ID_GIVEN:
                Session::set_flash("message", ["danger", _("Wrong ID given!")]);
                Response::redirect("whitelabels/payments/" . $whitelabel['id'] . Lotto_View::query_vars());
                break;
        }
    }

    /**
     *
     * @param array $whitelabel
     * @param array $kmethods list of methods where key is from 0 not by id of method
     * @param int $whitelabel_payment_method_id
     * @return void
     */
    private function payment_method_customize_list(
        array $whitelabel,
        array $kmethods,
        int $whitelabel_payment_method_id
    ): void {
        $payment_method_customize_list = new Forms_Whitelabel_Payment_Method_Customize_List(
            Helpers_General::SOURCE_ADMIN,
            $whitelabel,
            $kmethods
        );

        $result = $payment_method_customize_list->process_form($whitelabel_payment_method_id);

        switch ($result) {
            case Forms_Whitelabel_Payment_Method_Customize_List::RESULT_OK:
                $inside = $payment_method_customize_list->get_inside();
                $this->view->inside = $inside;
                break;
            case Forms_Whitelabel_Payment_Method_Customize_List::RESULT_WRONG_PAYMENT_METHOD:
                Session::set_flash("message", ["danger", _("Incorrect payment method!")]);
                Response::redirect("whitelabels/payments/" . $whitelabel['id'] . Lotto_View::query_vars());
                break;
        }
    }

    /**
     *
     * @param array $whitelabel
     * @param array $kmethods
     * @param int $whitelabel_payment_method_id
     * @return void
     */
    private function payment_method_customize_add_edit(
        array $whitelabel,
        array $kmethods,
        int $whitelabel_payment_method_id
    ): void {
        $edit_id = null;
        if (!empty($this->param("edit_id"))) {
            $edit_id = $this->param("edit_id");
        }

        $action = $this->param("subaction");

        $payment_method_customize = new Forms_Whitelabel_Payment_Method_Customize_Edit(
            Helpers_General::SOURCE_ADMIN,
            $whitelabel,
            $kmethods
        );

        $result = $payment_method_customize->process_form(
            $action,
            $whitelabel_payment_method_id,
            $edit_id
        );

        switch ($result) {
            case Forms_Whitelabel_Payment_Method_Customize_Edit::RESULT_OK:
                $current_kmethod_idx = $whitelabel_payment_method_id;
                $url_redirect = 'whitelabels/payments/' . $whitelabel['id'] .
                    '/customize/' .
                    $current_kmethod_idx . '/list/' . Lotto_View::query_vars();
                Session::set_flash("message", ["success", _("Customization has been saved!")]);
                Response::redirect($url_redirect);
                break;
            case Forms_Whitelabel_Payment_Method_Customize_Edit::RESULT_NO_FREE_LANGUAGES:
                $current_kmethod_idx = $whitelabel_payment_method_id;
                $url_redirect = 'whitelabels/payments/' . $whitelabel['id'] .
                    '/customize/' .
                    $current_kmethod_idx . '/list/' . Lotto_View::query_vars();
                Response::redirect($url_redirect);
                break;
            case Forms_Whitelabel_Payment_Method_Customize_Edit::RESULT_WRONG_PAYMENT_METHOD:
                Session::set_flash("message", ["danger", _("Incorrect payment method!")]);
                Response::redirect("whitelabels/payments/" . $whitelabel['id'] . Lotto_View::query_vars());
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
     * @param int $whitelabel_payment_method_id
     * @return void
     */
    private function payment_method_customize_delete(
        array $whitelabel,
        int $whitelabel_payment_method_id
    ): void {
        $edit_id = null;
        if (!empty($this->param("edit_id"))) {
            $edit_id = $this->param("edit_id");
        }

        $payment_method_customize_delete = new Forms_Whitelabel_Payment_Method_Customize_Delete(
            Helpers_General::SOURCE_ADMIN,
            $whitelabel
        );

        $result = $payment_method_customize_delete->process_form(
            $whitelabel_payment_method_id,
            $edit_id
        );

        switch ($result) {
            case Forms_Whitelabel_Payment_Method_Customize_Delete::RESULT_OK:
                $current_kmethod_idx = $whitelabel_payment_method_id;
                $url_redirect = 'whitelabels/payments/' . $whitelabel['id'] .
                    '/customize/' .
                    $current_kmethod_idx . '/list/' . Lotto_View::query_vars();
                Session::set_flash("message", ["success", _("Customization row to payment method has been deleted!")]);
                Response::redirect($url_redirect);
                break;
            case Forms_Whitelabel_Payment_Method_Customize_Delete::RESULT_WRONG_PAYMENT_METHOD:
                Session::set_flash("message", ["danger", _("Incorrect payment method!")]);
                Response::redirect("whitelabels/payments/" . $whitelabel['id'] . Lotto_View::query_vars());
                break;
            case Forms_Whitelabel_Payment_Method_Customize_Delete::RESULT_WRONG_ID_GIVEN:
                Session::set_flash("message", ["danger", _("Wrong ID given!")]);
                Response::redirect("whitelabels/payments/" . $whitelabel['id'] . Lotto_View::query_vars());
                break;
            case Forms_Whitelabel_Payment_Method_Customize_Delete::RESULT_WITH_ERRORS:
                Session::set_flash("message", ["danger", _("There is a problem with database! Please contact us.")]);
                Response::redirect("whitelabels/payments/" . $whitelabel['id'] . Lotto_View::query_vars());
        }
    }

    /**
     *
     * @return void
     */
    private function payments_whitelabel(): void
    {
        $whitelabel = Model_Whitelabel::get_single_by_id($this->param("id"));

        if ($whitelabel === null) {
            Session::set_flash('message', ['danger', _("Wrong whitelabel!")]);
            Response::redirect('whitelabels');
            return;
        }

        $title = _("Payment methods");

        $user_currency = [];
        $whitelabel_payment_methods_without_currency = Model_Whitelabel_Payment_Method::get_payment_methods_for_whitelabel($whitelabel);
        $whitelabel_payment_methods_with_currency = Helpers_Currency::get_whitelabel_payment_methods_with_currency(
            $whitelabel,
            $whitelabel_payment_methods_without_currency,
            $user_currency
        );

        $whitelabel_languages = Model_Whitelabel_Language::get_whitelabel_languages($whitelabel);
        $whitelabel_languages_indexed_by_id = [];
        foreach ($whitelabel_languages as $whitelabel_language) {
            $whitelabel_languages_indexed_by_id[$whitelabel_language['id']] = $whitelabel_language;
        }

        $language_methods = [];
        foreach ($whitelabel_payment_methods_with_currency as $whitelabel_payment_method) {
            if (!isset($language_methods[$whitelabel_payment_method['language_id']])) {
                $language_methods[$whitelabel_payment_method['language_id']] = 0;
            }
            $language_methods[$whitelabel_payment_method['language_id']]++;
        }

        $currencies = Helpers_Currency::getCurrencies();
        $currencies_indexed_by_id = [];
        foreach ($currencies as $currency) {
            $currencies_indexed_by_id[$currency['id']] = $currency['code'];
        }
        asort($currencies_indexed_by_id);

        $whitelabel_payment_methods_indexed = array_values($whitelabel_payment_methods_with_currency);

        $payment_method_name = "";

        $go_to_default = false;

        $whitelabel_payment_method_id = -1;
        if (
            !is_null($this->param("sid")) &&
            (int)$this->param("sid") > 0
        ) {
            $whitelabel_payment_method_id = intval($this->param("sid"));
            $payment_method_name = (string)$whitelabel_payment_methods_with_currency[$whitelabel_payment_method_id]["name"];
            $title = _("Payment method " . $payment_method_name);
        }

        switch ($this->param("subaction")) {
            case "orderdown":
                if (!empty($whitelabel_payment_method_id)) {
                    $this->payment_method_order_down(
                        $whitelabel,
                        $language_methods,
                        $whitelabel_payment_method_id
                    );
                } else {
                    $go_to_default = true;
                }
                break;
            case "orderup":
                if (!empty($whitelabel_payment_method_id)) {
                    $this->payment_method_order_up(
                        $whitelabel,
                        $whitelabel_payment_method_id
                    );
                } else {
                    $go_to_default = true;
                }
                break;
            case "new":
            case "edit":
                $this->payment_method_add_edit(
                    $whitelabel,
                    $whitelabel_payment_methods_with_currency,
                    $whitelabel_payment_methods_indexed,
                    $currencies_indexed_by_id,
                    $whitelabel_languages,
                    $whitelabel_languages_indexed_by_id,
                    $whitelabel_payment_method_id
                );
                break;
            case "currency":
                if (!empty($whitelabel_payment_method_id)) {
                    switch ($this->param("deeplevelaction")) {
                        case "list":
                            $title = _("Currencies for " . $payment_method_name);
                            $this->payment_method_currency_list(
                                $whitelabel,
                                $whitelabel_payment_methods_indexed,
                                $whitelabel_payment_method_id
                            );
                            break;
                        case "new":
                        case "edit":
                            $title = _("Currencies for " . $payment_method_name);
                            $this->payment_method_currency_add_edit(
                                $whitelabel,
                                $whitelabel_payment_methods_indexed,
                                $whitelabel_payment_method_id,
                                $currencies_indexed_by_id
                            );
                            break;
                        case "delete":
                            $this->payment_method_currency_delete(
                                $whitelabel,
                                $whitelabel_payment_method_id
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
                if (isset($whitelabel_payment_method_id)) {
                    $title = _("Customize for " . $payment_method_name);
                    switch ($this->param("deeplevelaction")) {
                        case "list":
                            $this->payment_method_customize_list(
                                $whitelabel,
                                $whitelabel_payment_methods_indexed,
                                $whitelabel_payment_method_id
                            );
                            break;
                        case "new":
                        case "edit":
                            $this->payment_method_customize_add_edit(
                                $whitelabel,
                                $whitelabel_payment_methods_indexed,
                                $whitelabel_payment_method_id,
                                $currencies_indexed_by_id
                            );
                            break;
                        case "delete":
                            $this->payment_method_customize_delete(
                                $whitelabel,
                                $whitelabel_payment_method_id
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
                $whitelabel,
                $whitelabel_payment_methods_with_currency,
                $language_methods,
                $whitelabel_languages_indexed_by_id,
                $currencies_indexed_by_id
            );
        }

        $this->view->set_global("title", $title);
    }

    /**
     *
     * @return void
     */
    private function languages_whitelabel(): void
    {
        $inside = null;
        $subaction = "";
        if ($this->param("subaction") == "new") {
            $inside = View::forge("admin/whitelabels/languages_new");
            $subaction = "new";
        } else {
            $inside = View::forge("admin/whitelabels/languages");
            $subaction = "show";
        }

        $whitelabel = Model_Whitelabel::get_single_by_id($this->param("id"));

        if ($whitelabel === null) {
            Session::set_flash('message', ['danger', _("Wrong whitelabel!")]);
            Response::redirect('whitelabels');
        }

        $languages = new Forms_Whitelabel_Languages($whitelabel, $subaction);
        $res = $languages->process_form($inside);

        $errors = $languages->get_errors();
        if (!empty($errors)) {
            $this->view->set_global("errors", $errors);
        }

        $this->view->inside = $inside;
    }

    /**
     * @param array $whitelabel
     * @return void
     */
    private function settings_currency_add(array $whitelabel): void
    {
        $path_to_view = "admin/whitelabels/settings/currency/edit";
        $redirect_path = "whitelabels/settings_currency/" . $whitelabel['id'];
        $settings_currency_add = new Forms_Whitelabel_Settings_Currency_Edit(
            Helpers_General::SOURCE_ADMIN,
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
        if (empty($this->param("sid"))) {
            return;
        }

        $edit_id = intval($this->param("sid"));

        $path_to_view = "admin/whitelabels/settings/currency/edit";
        $redirect_path = "whitelabels/settings_currency/" . $whitelabel['id'];
        $settings_currency_edit = new Forms_Whitelabel_Settings_Currency_Edit(
            Helpers_General::SOURCE_ADMIN,
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
     * @param array $whitelabel
     * @return void
     */
    private function settings_currency_delete(array $whitelabel): void
    {
        if (empty($this->param("sid"))) {
            return;
        }

        $result = Model_Whitelabel_Country_Currency::delete_row_by_default_currency_id($this->param("sid"));

        if (!(!empty($result) && $result == 1 || empty($result))) {
            Session::set_flash("message", ["danger", _("There is a problem with database! Please contact us.")]);
        } else {
            $result = Model_Whitelabel_Default_Currency::delete_row($this->param("sid"));

            if (!empty($result) && $result == 1) {
                Session::set_flash("message", ["success", _("Record successfully removed!")]);
            } else {
                Session::set_flash("message", ["danger", _("There is a problem with database! Please contact us.")]);
            }
        }

        $redirect_path = "whitelabels/settings_currency/" . $whitelabel['id'];

        Response::redirect($redirect_path);
    }

    /**
     *
     * @param array $whitelabel
     * @return void
     */
    private function settings_currency_list(array $whitelabel): void
    {
        $path_to_view = "admin/whitelabels/settings/currency/list";
        $redirect_path = "whitelabels/settings_currency/" . $whitelabel['id'];
        $settings_currency_list = new Forms_Whitelabel_Settings_Currency_List(
            Helpers_General::SOURCE_ADMIN,
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
    private function settings_currency_whitelabel()
    {
        if (!$this->is_user) {
            return Response::forge($this->view);
        }

        $whitelabel = Model_Whitelabel::get_single_by_id($this->param("id"));

        switch ($this->param("subaction")) {
            case "new":
                $this->settings_currency_add($whitelabel);
                break;
            case 'edit':
                $this->settings_currency_edit($whitelabel);
                break;
            case "delete":
                $this->settings_currency_delete($whitelabel);
                break;
            default:
                $this->settings_currency_list($whitelabel);
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
        $path_to_view = "admin/whitelabels/settings/country/currency/edit";
        $redirect_path = "whitelabels/settings_country_currency/" . $whitelabel['id'];
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
        if (empty($this->param("sid"))) {
            return;
        }

        $edit_id = intval($this->param("sid"));

        $path_to_view = "admin/whitelabels/settings/country/currency/edit";
        $redirect_path = "whitelabels/settings_country_currency/" . $whitelabel['id'];
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
     * @param array $whitelabel
     * @return void
     */
    private function settings_country_currency_delete(array $whitelabel): void
    {
        if (empty($this->param("sid"))) {
            return;
        }

        $result = Model_Whitelabel_Country_Currency::delete_row($this->param("sid"));

        if (!empty($result) && $result == 1) {
            Session::set_flash("message", ["success", _("Record successfully removed!")]);
        } else {
            Session::set_flash("message", ["danger", _("There is a problem with database! Please contact us.")]);
        }

        $redirect_path = "whitelabels/settings_country_currency/" . $whitelabel['id'];

        Response::redirect($redirect_path);
    }

    /**
     *
     * @param array $whitelabel
     * @return void
     */
    private function settings_country_currency_list(array $whitelabel): void
    {
        $path_to_view = "admin/whitelabels/settings/country/currency/list";
        $redirect_path = "whitelabels/settings_country_currency/" . $whitelabel['id'];
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
     * @return Request The new request object
     */
    public function settings_country_currency_whitelabel()
    {
        if (!$this->is_user) {
            return Response::forge($this->view);
        }

        $whitelabel = Model_Whitelabel::get_single_by_id($this->param("id"));

        switch ($this->param("subaction")) {
            case 'new':
                $this->settings_country_currency_add($whitelabel);
                break;
            case 'edit':
                $this->settings_country_currency_edit($whitelabel);
                break;
            case "delete":
                $this->settings_country_currency_delete($whitelabel);
                break;
            default:
                $this->settings_country_currency_list($whitelabel);
                break;
        }

        $this->view->set_global("title", _("Currency settings"));

        return Response::forge($this->view);
    }

    /**
     * @return null
     */
    private function list_whitelabels(): void
    {
        $path_to_view = "admin/whitelabels/list";
        $whitelabels_list = new Forms_Admin_Whitelabels_List($path_to_view);
        $result = $whitelabels_list->process_form();

        switch ($result) {
            case Forms_Admin_Whitelabels_List::RESULT_DB_ERROR:
                $this->fileLoggerService->error(
                    "There is a problem with pull data of whitelabels."
                );
                exit('Bad request');
                break;

            case Forms_Admin_Whitelabels_List::RESULT_OK:
                $inside = $whitelabels_list->get_inside();
                $this->view->inside = $inside;
                break;
        }
    }

    /**
     * Main action method for manage whitelabels menu
     *
     * @return Response
     */
    public function action_whitelabels(): Response
    {
        if (!$this->is_user) {
            return Response::forge($this->view);
        }

        switch ($this->param("action")) {
            case "edit":
                $this->edit_whitelabel();
                break;
            case "new":
                $this->new_whitelabel();
                break;
            case "settings":
                $this->settings_whitelabel();
                break;
            case "prepaid":
                $this->prepaid_whitelabel();
                break;
            case "ccpayments":
                $this->ccpayments_whitelabel();
                break;
            case "payments":
                $this->payments_whitelabel();
                break;
            case "languages":
                $this->languages_whitelabel();
                break;
            case "settings_currency":
                $this->settings_currency_whitelabel();
                break;
            case "settings_country_currency":
                $this->settings_country_currency_whitelabel();
                break;
            default:
                $this->list_whitelabels();
                break;
        }

        return Response::forge($this->view);
    }

    /**
     *
     * @param string $rparam
     * @return void
     */
    private function user_delete($rparam): void
    {
        $user_id = $this->param("id");
        $result = Model_Whitelabel_User::delete_user_for_id($user_id);

        if ($result) {
            Session::set_flash("message", ["success", _("User has been deleted!")]);
        } else {
            Session::set_flash("message", ["danger", _("Incorrect user.")]);
        }

        Response::redirect($rparam . Lotto_View::query_vars());
    }

    /**
     *
     * @return void
     */
    private function user_activate(): void
    {
        $user_id = $this->param("id");
        $result = Model_Whitelabel_User::activate_user_for_id($user_id);

        if ($result) {
            Session::set_flash("message", ["success", _("User has been activated and confirmed!")]);
        } else {
            Session::set_flash("message", ["danger", _("Incorrect user.")]);
        }

        Response::redirect("inactive" . Lotto_View::query_vars());
    }

    /**
     *
     * @return void
     */
    private function user_restore(): void
    {
        $user_token = $this->param("id");
        $result = Model_Whitelabel_User::user_restore_for_id($user_token);

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
     * @param string $rparam
     * @return void
     */
    private function user_aff($rparam): void
    {
        // TODO: this should be update probably
        $user_token = $this->param("id");
        $user_db = new Model_Whitelabel_User($user_token);
        $users = $user_db->get_active_user();

        if ($users !== null && count($users) > 0) {
            $user = $users[0];
            $path_to_view = "admin/users/aff";

            $whitelabel = $user_db->get_whitelabel();
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
     */
    private function user_confirm(): void
    {
        $user_id = $this->param("id");
        $result = Model_Whitelabel_User::user_confirm_for_id($user_id);

        if ($result) {
            Session::set_flash("message", ["success", _("User e-mail address confirmed!")]);
        } else {
            Session::set_flash("message", ["danger", _("Incorrect user.")]);
        }

        Response::redirect("users" . Lotto_View::query_vars());
    }

    /**
     *
     * @return void
     */
    private function user_edit(): void
    {
        $user_token = $this->param("id");
        $user = Model_Whitelabel_User::find_by_pk($user_token);

        if ($user !== null) {
            $view_template = "admin/users/edit";

            $whitelabel = Model_Whitelabel::get_single_by_id($user->whitelabel_id);

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
     * @return null
     */
    private function user_password(): void
    {
        $user_token = $this->param("id");
        $user = Model_Whitelabel_User::find_by_pk($user_token);

        if ($user !== null) {
            $view_template = "admin/users/password";

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
     * @return void
     */
    private function user_email(): void
    {
        $user_token = $this->param("id");
        $user = Model_Whitelabel_User::find_by_pk($user_token);

        if ($user !== null) {
            $view_template = "admin/users/email";

            $whitelabel = Model_Whitelabel::get_single_by_id($user->whitelabel_id);

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
     * @return void
     */
    private function user_view(): void
    {
        $view_template = "admin/users/view";
        $user_token = $this->param("id");
        $user_view = new Forms_Whitelabel_User_View(
            Helpers_General::SOURCE_ADMIN,
            $user_token
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
     * @param string $rparam
     * @param int $deleted
     * @param string $link
     * @return null
     */
    private function user_list($rparam, $deleted, $link): void
    {
        $inside = View::forge("admin/users/index");

        $user_list = new Forms_Whitelabel_User_List(Helpers_General::SOURCE_ADMIN);
        $user_list->process_form($inside, $rparam, $deleted, $link);

        $this->view->inside = $inside;
    }

    /**
     *
     * @param string $rparam
     * @param int $deleted
     * @param string $link
     * @return null
     */
    private function user_list_export($rparam, $deleted, $link): void
    {
        $user_list_export = new Forms_Whitelabel_User_List(Helpers_General::SOURCE_ADMIN);
        $user_list_export->prepare_for_export($rparam, $deleted, $link);
    }

    /**
     * @param string $rparam
     *
     * @return Response
     */
    public function action_users($rparam = "users"): Response
    {
        if (!$this->is_user) {
            return Response::forge($this->view);
        }

        // Set title for current page
        $titles_arr = [
            "users" => _("Active users"),
            "inactive" => _("Inactive users"),
            "deleted" => _("Deleted users"),
        ];
        $this->view->set_global("title", isset($titles_arr[$rparam]) ? $titles_arr[$rparam] : null);

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
                $this->user_delete($rparam);
                break;
            case "activate":
                $this->user_activate();
                break;
            case "restore":
                $this->user_restore();
                break;
            case "aff":
                $this->user_aff($rparam);
                break;
            case "confirm":
                $this->user_confirm();
                break;
            case "edit":
                $this->user_edit();
                break;
            case "password":
                $this->user_password();
                break;
            case "email":
                $this->user_email();
                break;
            case "view":
                $this->user_view();
                break;
            case "export":
                $this->user_list_export($rparam, $deleted, $link);
                break;
            default:
                $this->user_list($rparam, $deleted, $link);
                break;
        }

        $this->view->set_global("link", $link);
        $this->view->set_global("rparam", $rparam);

        $active_users = Model_Whitelabel::count_active_users();
        $this->view->set_global("active_cnt", $active_users);

        $inactive_users = Model_Whitelabel::count_inactive_users();
        $this->view->set_global("inactive_cnt", $inactive_users);

        $deleted_users = Model_Whitelabel::count_deleted_users();
        $this->view->set_global("deleted_cnt", $deleted_users);

        return Response::forge($this->view);
    }

    /**
     *
     * @param string $rparam
     * @return void
     */
    private function transaction_list($rparam): void
    {
        $whitelabel = [];

        $inside = View::forge("admin/transactions/index");

        $type = Helpers_General::TYPE_TRANSACTION_PURCHASE;
        $title = _("Purchases");
        if ($rparam == "deposits") {
            $type = Helpers_General::TYPE_TRANSACTION_DEPOSIT;
            $title = _("Deposits");
        }
        $this->view->set_global("title", $title);

        $list = new Forms_Transactions_List(Helpers_General::SOURCE_ADMIN);
        $list->process_form($inside, $rparam);

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

        switch ($this->param("action")) {
            case "accept":
                break;
            case "view":
                break;
            default:
                $this->transaction_list($rparam);
                break;
        }

        $wcount = Model_Withdrawal_Request::count_for_whitelabel();
        $this->view->set_global("wcount", $wcount);

        $pcount = Model_Whitelabel_Transaction::count_for_whitelabel(Helpers_General::TYPE_TRANSACTION_PURCHASE);
        $this->view->set_global("pcount", $pcount);

        $dcount = Model_Whitelabel_Transaction::count_for_whitelabel(Helpers_General::TYPE_TRANSACTION_DEPOSIT);
        $this->view->set_global("dcount", $dcount);

        return Response::forge($this->view);
    }

    /**
     *
     * @return null
     */
    public function tickets_list(): void
    {
        $inside = View::forge("admin/tickets/index");

        $this->view->inside = $inside;
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

        // THIS ALL STUFF WILL BE DONE SOON
        // TO DO!!!!!!
        // IN THE CONNECTION WITH WHITELABEL

        switch ($this->param("action")) {
            case "payout":
                break;
            case "paidout":
                break;
            case "view":
                break;
            default:
                $this->tickets_list();
                break;
        }

        $tcount = Model_Whitelabel_User_Ticket::count_for_whitelabel_paid(Helpers_General::TICKET_PAID);
        $this->view->set_global("tcount", $tcount);

        return Response::forge($this->view);
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

        switch ($this->param("action")) {
            case "confirm":
                $this->multidraws_cancellation_confirm();
                break;
            case "details":
                $this->multidraws_cancellation_details();
                break;
            default:
                $this->multidraws_cancellation();
                break;
        }

        $tcount = Model_Whitelabel_User_Ticket::count_for_whitelabel_paid(Helpers_General::TICKET_PAID);
        $this->view->set_global("tcount", $tcount);

        return Response::forge($this->view);
    }

    /**
     * @param $whitelabel
     */
    private function multidraws_cancellation_details()
    {
        $view_template = "admin/tickets/multidraws_cancellation_details";
        $ticket_view = new Forms_Admin_Multidraw_Cancellation(
            Input::get('lottery'),
            Input::get('range_from')
        );
        $ticket_view->process_form($view_template, $this->param("subaction"));

        $inside = $ticket_view->get_inside();
        $this->view->inside = $inside;
    }

    private function multidraws_cancellation()
    {
        $inside = View::forge("admin/tickets/multidraws_cancellation");
        $lotteries = Model_Lottery::get_all_lotteries();
        $inside->set("lotteries", $lotteries);
        $this->view->inside = $inside;
    }


    private function multidraws_cancellation_confirm()
    {
        $multidraws = new Forms_Admin_Multidraw_Cancellation(
            Input::get('lottery'),
            Input::get('range_from')
        );

        $multidraws->confirm_cancellation();
    }

    /**
     * @return void
     */
    private function withdrawals_list(): void
    {
        $inside = View::forge("admin/transactions/withdrawals");

        $this->view->inside = $inside;
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

        // THIS ALL STUFF WILL BE DONE SOON
        // TO DO!!!!!!
        // IN THE CONNECTION WITH WHITELABEL

        switch ($this->param("action")) {
            case "view":
                break;
            case "approve":
                break;
            case "decline":
                break;
            case "cancel":
                break;
            default:
                $this->withdrawals_list();
                break;
        }

        $wcount = Model_Withdrawal_Request::count_for_whitelabel();
        $this->view->set_global("wcount", $wcount);

        $pcount = Model_Whitelabel_Transaction::count_for_whitelabel(Helpers_General::TYPE_TRANSACTION_PURCHASE);
        $this->view->set_global("pcount", $pcount);

        $dcount = Model_Whitelabel_Transaction::count_for_whitelabel(Helpers_General::TYPE_TRANSACTION_DEPOSIT);
        $this->view->set_global("dcount", $dcount);

        return Response::forge($this->view);
    }

    /**
     *
     * @return Response
     */
    public function action_lotteries(): Response
    {
        if (!$this->is_user) {
            return Response::forge($this->view);
        }

        if ($this->param("action") == "jackpot") {
            $lottery = Model_Lottery::find_by_pk($this->param("id"));
            if ($lottery !== null) {
                if (Input::post("input.jackpot") !== null) {
                    $val = Validation::forge();

                    $val->add("input.jackpot", _("Current Jackpot"))
                        ->add_rule("trim")
                        ->add_rule("required")
                        ->add_rule("valid_string", ["numeric", "dots"]);

                    if ($val->run()) {
                        $lottery->set(
                            [
                                "current_jackpot" => $val->validated("input.jackpot"),
                                "draw_jackpot_set" => 1
                            ]
                        );
                        $lottery->save();

                        Lotto_Helper::clear_cache(['model_lottery', 'model_whitelabel']);
                        Session::set_flash('message', ['success', _("Current Jackpot has been changed!")]);
                        Response::redirect('lotteries');
                    } else {
                        $errors = Lotto_Helper::generate_errors($val->error());
                        $this->view->set_global("errors", $errors);
                    }
                }

                $currencies = Helpers_Currency::getCurrencies();
                $inside = View::forge("admin/lotteries/jackpot");
                $inside->set("lottery", $lottery);
                $inside->set("currencies", $currencies);
                $this->view->inside = $inside;
            }
        } elseif ($this->param("action") == "nextdraw") {
            $lottery = Model_Lottery::find_by_pk($this->param("id"));
            if ($lottery !== null) {
                if (Input::post("input.nextdraw") !== null) {
                    $val = Validation::forge();

                    $val->add("input.nextdraw", _("Next Draw Date"))
                        ->add_rule("trim")
                        ->add_rule("required");

                    $val->add("input.nextdrawtime", _("Time of the draw"))
                        ->add_rule("trim")
                        ->add_rule("required");

                    if ($val->run()) {
                        $date = DateTime::createFromFormat(
                            'm/d/Y H:i',
                            $val->validated("input.nextdraw") . ' ' . $val->validated("input.nextdrawtime"),
                            new DateTimeZone($lottery['timezone'])
                        );

                        if ($date !== null && $date !== false) {
                            $dbdate = $date->format(Helpers_Time::DATETIME_FORMAT);
                            $date->setTimezone(new DateTimeZone("UTC"));
                            $olddate = $lottery['next_date_local'];
                            $lottery->set(["next_date_local" => $dbdate,
                                "next_date_utc" => $date->format(Helpers_Time::DATETIME_FORMAT),
                                "last_update" => DB::expr("NOW()")]);
                            $lottery->save();

                            $tickets = Model_Whitelabel_User_Ticket::find([
                                "where" => [
                                    "lottery_id" => $lottery['id'],
                                    "draw_date" => $olddate
                                ]
                            ]);
                            if ($tickets !== null) {
                                foreach ($tickets as $ticket) {
                                    $ticket->set(
                                        [
                                            "draw_date" => $dbdate,
                                            "valid_to_draw" => $dbdate
                                        ]
                                    );
                                    $ticket->save();
                                }
                            }

                            // change quick picks
                            $tickets = Model_Whitelabel_User_Ticket::find([
                                "where" => [
                                    "lottery_id" => $lottery['id'],
                                    "valid_to_draw" => $olddate
                                ]
                            ]);
                            if ($tickets !== null) {
                                foreach ($tickets as $ticket) {
                                    $ticket->set(
                                        [
                                            "valid_to_draw" => $dbdate
                                        ]
                                    );
                                    $ticket->save();
                                }
                            }

                            Lotto_Helper::clear_cache(['model_lottery', 'model_whitelabel']);
                            Session::set_flash('message', ['success', _("Lottery next draw has been changed!")]);
                            Response::redirect('lotteries');
                        } else {
                            $errors = ["input.nextdraw" => _("Wrong date format!")];
                            $this->view->set_global("errors", $errors);
                        }
                    } else {
                        $errors = Lotto_Helper::generate_errors($val->error());
                        $this->view->set_global("errors", $errors);
                    }
                }

                $inside = View::forge("admin/lotteries/nextdraw");
                $inside->set("lottery", $lottery);
                $this->view->inside = $inside;
            }
        } elseif ($this->param("action") == "switch") {
            $lottery = Model_Lottery::find_by_pk($this->param("id"));
            if ($lottery !== null) {
                $lottery->set(["is_enabled" => DB::expr("!is_enabled")]);
                $lottery->save();
                Lotto_Helper::clear_cache(["model_lottery", "model_whitelabel"]);
            }
            Response::redirect("lotteries");
        } elseif ($this->param("action") == "source") {
            $lottery = Model_Lottery::find_by_pk($this->param("id"));
            if ($lottery !== null) {
                if (Input::post("input.source") !== null) {
                    $val = Validation::forge();
                    $val->add("input.source", _("Source"))->add_rule("required")->add_rule("is_numeric");
                    if ($val->run()) {
                        $source = Model_Lottery_Source::find_by_pk($val->validated("input.source"));
                        if ($source != null && $source['lottery_id'] == $lottery['id']) {
                            $lottery->set(["source_id" => $source['id']]);
                            $lottery->save();
                            Lotto_Helper::clear_cache('model_lottery');
                            Session::set_flash('message', ['success', _("The source has been changed!")]);
                            Response::redirect('lotteries');
                        }
                    } else {
                        $this->view->set_global('errors', Lotto_Helper::generate_errors($val->error()));
                    }
                }
                $inside = View::forge("admin/lotteries/source");
                $inside->set("lottery", $lottery);
                $sources = Model_Lottery_Source::find([
                    "where" => [
                        "lottery_id" => $lottery['id']],
                    'order_by' => ['id' => 'asc']
                ]);
                $inside->set("sources", $sources);
                $this->view->inside = $inside;
            }
        } elseif ($this->param("action") == "view") {
            $lottery = Model_Lottery::find_by_pk($this->param("id"));
            if ($lottery !== null) {
                if ($this->param("subaction") == "add") {
                    $inside = View::forge("admin/lotteries/draw_add");
                    $inside->set("lottery", $lottery);

                    $last_draw = Model_Lottery_Draw::find([
                        'where' => ['lottery_id' => $this->param("id")],
                        'order_by' => ['date_local' => 'desc'],
                        'limit' => '1'
                    ]);

                    $currencies = Helpers_Currency::getCurrencies();
                    // TODO: maybe there will be a need to make this 2-step, to get the type for specified lottery date
                    // anyway, the manual addition is just an emergency feature, this should be enough
                    $lottery_type = Model_Lottery_Type::get_lottery_type_for_date(
                        $lottery->to_array(),
                        Lotto_Helper::get_lottery_next_draw($lottery)->format('Y-m-d')
                    );
                    $inside->set("lottery_type", $lottery_type);

                    $types = Model_Lottery_Type_Data::find([
                        'where' => ['lottery_type_id' => $lottery_type['id']],
                        'order_by' => ['id' => 'asc']]);

                    $inside->set("types", $types);
                    if ($last_draw != null) {
                        $date = $last_draw[0];
                        $date = new DateTime($date->date_local, new DateTimeZone("UTC"));
                        $date->add(new DateInterval("P1D"));
                        $inside->set("last_draw", $date->format('m/d/Y'));
                    }
                    $inside->set("currencies", Model_Currency::get_all_currencies());

                    if (Input::post("input.date") !== null) {
                        $val = Validation::forge();

                        $val->add("input.date", _("Date"))
                            ->add_rule("trim")
                            ->add_rule("required")
                            ->add_rule("match_pattern", '/[0-9]{2}\/[0-9]{2}\/[0-9]{4}/');

                        $val->add("input.time", _("Time"))
                            ->add_rule("trim")
                            ->add_rule("required");

                        $val->add("input.jackpot", _("Next Jackpot"))
                            ->add_rule("trim")
                            ->add_rule("required")
                            ->add_rule("valid_string", ["numeric", "dots"]);

                        for ($i = 0; $i < $lottery_type['ncount']; $i++) {
                            $val->add("input.number." . $i, sprintf(_("Draw Number #%s"), $i + 1))
                                ->add_rule("trim")
                                ->add_rule("required")
                                ->add_rule("valid_string", ["numeric"])
                                ->add_rule("numeric_between", 1, $lottery_type['nrange']);
                        }
                        if ($lottery_type['bextra'] > 0 || $lottery_type['bcount'] > 0) {
                            $loop_limit = ($lottery_type['bextra'] > 0 ? $lottery_type['bextra'] : $lottery_type['bcount']);
                            for ($i = 0; $i < $loop_limit; $i++) {
                                $bnumber_input_text = "";
                                if ($lottery_type['bextra'] > 0) {
                                    $bnumber_input_text = sprintf(_("Extra Number #%s"), $i + 1);
                                } else {
                                    $bnumber_input_text = sprintf(_("Bonus Number #%s"), $i + 1);
                                }
                                $val->add("input.bnumber." . $i, $bnumber_input_text)
                                    ->add_rule("trim")
                                    ->add_rule("required")
                                    ->add_rule("valid_string", ["numeric"])
                                    ->add_rule("numeric_between", 1, $lottery_type['bextra'] ? $lottery_type['nrange'] : $lottery_type['brange']);
                            }
                        }
                        for ($i = 0; $i < count($types); $i++) {
                            $val->add("input.wcount." . $i, _("Winners count"))
                                ->add_rule("trim")
                                ->add_rule("required")
                                ->add_rule("valid_string", ["numeric"])->add_rule("numeric_min", 0);
                            if ($types[$i]['type'] != Helpers_General::LOTTERY_TYPE_DATA_QUICK_PICK) {
                                $val->add("input.prize." . $i, _("Prize per winner"))
                                    ->add_rule("trim")
                                    ->add_rule("required")
                                    ->add_rule("is_numeric")
                                    ->add_rule("numeric_min", 0);
                            }
                        }
                        $additional_data = null;
                        if ($lottery_type['additional_data'] && Input::post("input.refund") !== null) {
                            $a_data = unserialize($lottery_type['additional_data']);
                            if (isset($a_data['refund']) && isset($a_data['refund_min']) && isset($a_data['refund_max'])) {
                                $val->add("input.refund", _("Refund"))
                                    ->add_rule("trim")->add_rule("required")->add_rule("valid_string", ["numeric"])
                                    ->add_rule("numeric_between", $a_data['refund_min'], $a_data['refund_max']);

                                $additional_data['refund'] = Input::post("input.refund");
                            }
                        }
                        if ($lottery_type['additional_data'] && Input::post("input.super") !== null) {
                            $a_data = unserialize($lottery_type['additional_data']);
                            if (isset($a_data['super']) && isset($a_data['super_min']) && isset($a_data['super_max'])) {
                                $val->add("input.super", _("Super"))
                                    ->add_rule("trim")->add_rule("required")->add_rule("valid_string", ["numeric"])
                                    ->add_rule("numeric_between", $a_data['super_min'], $a_data['super_max']);

                                $additional_data['super'] = Input::post("input.super");
                            }
                        }
                        if ($val->run()) {
                            $nums = [];
                            $nums_sum = [];
                            foreach ($val->validated('input.number') as $number) {
                                $nums[$number] = 1;
                                $nums_sum[] = $number;
                            }
                            if (count($nums) == $lottery_type['ncount']) {
                                $bnums = [];

                                if ($lottery_type['bcount'] > 0 || $lottery_type['bextra'] > 0) {
                                    foreach ($val->validated('input.bnumber') as $number) {
                                        $bnums[$number] = 1;
                                        $nums_sum[] = $number;
                                    }
                                }
                                $numc = array_unique($nums_sum);

                                if (
                                    ($lottery_type['bextra'] == 0 && count($bnums) == $lottery_type['bcount']) ||
                                    ($lottery_type['bextra'] == 1 && count($numc) == $lottery_type['ncount'] + $lottery_type['bextra'])
                                ) {
                                    $prizes = [];
                                    $inputprizes = $val->validated('input.prize');
                                    foreach ($val->validated('input.wcount') as $key => $wcount) {
                                        $prizes[] = [$wcount, isset($inputprizes[$key]) ? $inputprizes[$key] : "0"];
                                    }
                                    $date = DateTime::createFromFormat(
                                        "m/d/Y H:i",
                                        $val->validated("input.date") . ' ' . $val->validated("input.time"),
                                        new DateTimeZone($lottery['timezone'])
                                    );

                                    $date_utc = clone $date;
                                    $date_utc->setTimezone(new DateTimeZone("UTC"));

                                    $lottery->currency = $currencies[$lottery->currency_id]['code'];

                                    $lottery_classes = Model_Lottery::LOTTERY_CLASSES;
                                    $l = new $lottery_classes[$lottery->slug]();

                                    if ($l) {
                                        $l->set_lottery(
                                            $lottery->to_array(),
                                            floatval($val->validated("input.jackpot")),
                                            $date,
                                            $date_utc,
                                            $val->validated('input.number'),
                                            $val->validated('input.bnumber'),
                                            $prizes,
                                            null,
                                            null,
                                            $additional_data
                                        );
                                    }
                                    Session::set_flash('message', ['success', _("The draw has been successfully added!")]);
                                    Response::redirect('lotteries/view/' . $lottery['id']);
                                } else {
                                    $this->view->set_global('errors', ["input.bnumber" => _("Bonus numbers must differ (from each other and from draw numbers).")]);
                                    $this->view->set_global("error_bnumber", 1);
                                }
                            } else {
                                $this->view->set_global('errors', ["input.number" => _("Draw numbers must differ.")]);
                                $this->view->set_global("error_number", 1);
                            }
                        } else {
                            $errors = Lotto_Helper::generate_errors($val->error());
                            foreach ($errors as $key => $error) {
                                if (substr($key, 0, strlen("input.number")) == "input.number") {
                                    $this->view->set_global("error_number", 1);
                                }
                                if (substr($key, 0, strlen("input.bnumber")) == "input.bnumber") {
                                    $this->view->set_global("error_bnumber", 1);
                                }
                            }
                            $this->view->set_global('errors', $errors);
                        }
                    }
                    $this->view->inside = $inside;
                } else {
                    $draws_count = Model_Lottery_Draw::count(null, true, ["lottery_id" => $this->param("id")]);

                    $config = [
                        'pagination_url' => '/lotteries/view/' . $this->param("id") . '/s/',
                        'total_items' => $draws_count,
                        'per_page' => 10,
                        'uri_segment' => 5
                    ];
                    $pagination = Pagination::forge('drawpagination', $config);

                    $currencies = Helpers_Currency::getCurrencies();
                    $draws = Model_Lottery_Draw::find([
                        'where' => ["lottery_id" => $this->param("id")],
                        "order_by" => ["date_download" => "desc"],
                        "limit" => $pagination->per_page,
                        "offset" => $pagination->offset]);

                    $inside = View::forge("admin/lotteries/draws");
                    $inside->set("lottery", $lottery);
                    $inside->set("draws", $draws);
                    $inside->set("currencies", $currencies);
                    $inside->set("pages", $pagination);
                    $inside->set("page", $this->request->param("page") !== null ? $this->request->param("page") : 1);

                    $this->view->inside = $inside;
                }
            }
        } else {
            $inside = View::forge("admin/lotteries/index");
            $lotteries = DB::query("SELECT lottery.*, currency.code AS currency FROM lottery JOIN currency ON currency.id = lottery.currency_id ORDER BY id")->execute()->as_array();
            $sources = Model_Lottery_Source::get_sources();
            $inside->set("sources", $sources);
            $inside->set("lotteries", $lotteries);

            $this->view->inside = $inside;
        }
        return Response::forge($this->view);
    }

    /**
     *
     * @return Response
     */
    public function action_delays(): Response
    {
        if (!$this->is_user) {
            return Response::forge($this->view);
        }

        if ($this->request->param("action") == "delete") {
            $delete = $this->request->param("id");
            $delay = Model_Lottery_Delay::find_by_pk($delete);

            $lotteries = Model_Lottery::get_all_lotteries();
            $lottery = $lotteries['__by_id'][$delay->lottery_id];

            $delaydate = DateTime::createFromFormat(
                Helpers_Time::DATETIME_FORMAT,
                $delay->date_local,
                new DateTimeZone($lottery['timezone'])
            );
            $nextdate = DateTime::createFromFormat(
                Helpers_Time::DATETIME_FORMAT,
                $lottery['next_date_local'],
                new DateTimeZone($lottery['timezone'])
            );

            if ($delaydate > $nextdate) {
                $delay->delete();

                Lotto_Helper::clear_cache(['model_lottery_delay']);
                Session::set_flash('message', ['success', _("The postponed date has been deleted!")]);
                Response::redirect('lotteries/delays' . Lotto_View::query_vars());
            } else {
                Session::set_flash('message', ['danger', _("It's too late to delete this postponed date. Use coding skills to adjust tickets and draw date within the system!")]);
                Response::redirect('lotteries/delays' . Lotto_View::query_vars());
            }
        }
        if ($this->request->param("action") == "new" || $this->request->param("action") == "edit") {
            $inside = View::forge("admin/lotteries/delays_edit");

            $lotteries = Model_Lottery::get_all_lotteries();
            $inside->set("lotteries", $lotteries);


            $edit = $this->request->param("id");
            $delay = null;
            if ($edit !== null) {
                $delay = Model_Lottery_Delay::find_by_pk($edit);
                if ($delay !== null) {
                    $inside->set("edit", $delay);
                    $datelocal = new DateTime($delay['date_local'], new DateTimeZone("UTC"));
                    $datedelay = new DateTime($delay['date_delay'], new DateTimeZone("UTC"));
                    $inside->set("datelocal", $datelocal);
                    $inside->set("datedelay", $datedelay);
                }
            }
            if ($delay === null) {
                $delay = Model_Lottery_Delay::forge();
            }
            if (Input::post("input.datelocal") !== null) {
                $val = Validation::forge();
                $val->add("input.datelocal", _("Original date"))
                    ->add_rule("trim")
                    ->add_rule("required")
                    ->add_rule("match_pattern", '/[0-9]{2}\/[0-9]{2}\/[0-9]{4}/');

                $val->add("input.datedelay", _("Delayed date"))
                    ->add_rule("trim")
                    ->add_rule("required")
                    ->add_rule("match_pattern", '/[0-9]{2}\/[0-9]{2}\/[0-9]{4}/');

                $val->add("input.lottery", _("Lottery"))
                    ->add_rule("trim")
                    ->add_rule("required")
                    ->add_rule("is_numeric");

                if ($val->run()) {
                    if (isset($lotteries['__by_id'][$val->validated("input.lottery")])) {
                        $lottery = $lotteries['__by_id'][$val->validated("input.lottery")];
                        $nextdate = DateTime::createFromFormat(
                            Helpers_Time::DATETIME_FORMAT,
                            $lottery['next_date_local'],
                            new DateTimeZone($lottery['timezone'])
                        );

                        $date = DateTime::createFromFormat(
                            "m/d/Y H:i:s",
                            $val->validated("input.datelocal") . ' ' . $val->validated("input.timelocal"),
                            new DateTimeZone($lottery['timezone'])
                        );
                        $datedelay = DateTime::createFromFormat(
                            "m/d/Y H:i:s",
                            $val->validated("input.datedelay") . ' ' . $val->validated("input.timedelay"),
                            new DateTimeZone($lottery['timezone'])
                        );
                        if ($date >= $nextdate) {
                            $where = [
                                "lottery_id" => $lottery['id'],
                                "date_local" => $date->format(Helpers_Time::DATETIME_FORMAT)
                            ];
                            if ($edit !== null && $delay->id !== null) {
                                $where[] = ["id", "!=", $delay->id];
                            }
                            $count = Model_Lottery_Delay::count(null, true, $where);
                            if ($count == 0) {
                                $delay->set([
                                    "lottery_id" => $val->validated("input.lottery"),
                                    "date_local" => $date->format(Helpers_Time::DATETIME_FORMAT),
                                    "date_delay" => $datedelay->format(Helpers_Time::DATETIME_FORMAT)
                                ]);
                                $delay->save();
                                Lotto_Helper::clear_cache(['model_lottery_delay']);
                                Session::set_flash('message', ['success', _("The postponed date has been saved!")]);
                                Response::redirect('lotteries/delays' . Lotto_View::query_vars());
                            } else {
                                $errors = ["input.datelocal" => _("The postponed date for this draw already exists!")];
                                $this->view->set_global("errors", $errors);
                            }
                        } else {
                            $errors = ["input.datelocal" => _("The draw date should be within the future draws!")];
                            $this->view->set_global("errors", $errors);
                        }
                    } else {
                        $errors = ["input.lottery" => _("Wrong lottery!")];
                        $this->view->set_global("errors", $errors);
                    }
                } else {
                    $errors = Lotto_Helper::generate_errors($val->error());
                    $this->view->set_global("errors", $errors);
                }
            }

            $this->view->inside = $inside;
        } else {
            $inside = View::forge("admin/lotteries/delays");

            $delays_count = Model_Lottery_Delay::count();
            $config = [
                'pagination_url' => '/lotteries/delays/' . '?' . http_build_query(Input::get()),
                'total_items' => $delays_count,
                'per_page' => 10,
                'uri_segment' => 'page'
            ];
            $pagination = Pagination::forge('delayspagination', $config);
            $delays = Model_Lottery_Delay::find([
                "order_by" => ["date_local" => "DESC"],
                "limit" => $pagination->per_page,
                "offset" => $pagination->offset
            ]);
            $inside->set("pages", $pagination);
            $inside->set("page", $this->request->param("page") !== null ? $this->request->param("page") : 1);

            $inside->set("delays", $delays);
            $inside->set("lotteries", Model_Lottery::get_all_lotteries());

            $this->view->inside = $inside;
        }
        return Response::forge($this->view);
    }

    /**
     *
     * @return Response
     */
    public function action_logs(): Response
    {
        if (!$this->is_user) {
            return Response::forge($this->view);
        }
        $lotteries = Model_Lottery::get_lotteries_order_by_id();
        $filter = [];
        $params = [];


        if (!empty(Input::get("filter.range_start"))) {
            $date_start = DateTime::createFromFormat(
                "m/d/Y H:i:s",
                Input::get("filter.range_start") . ' 00:00:00',
                new DateTimeZone("UTC")
            );
            if ($date_start !== false) {
                $filter[] = "lottery_log.`date` >= :start";
                $params[':start'] = $date_start->format('Y-m-d H:i:s');
            }
        }
        if (!empty(Input::get("filter.range_end"))) {
            $date_end = DateTime::createFromFormat(
                "m/d/Y H:i:s",
                Input::get("filter.range_end") . ' 23:59:59',
                new DateTimeZone("UTC")
            );
            if ($date_end !== false) {
                $filter[] = "lottery_log.`date` <= :end";
                $params[':end'] = $date_end->format('Y-m-d H:i:s');
            }
        }
        if (!empty(Input::get("filter.lottery"))) {
            $filter[] = "lottery_log.lottery_id = :lottery";
            $params[':lottery'] = intval(Input::get("filter.lottery"));
        }
        if (Input::get("filter.type") != null && Input::get("filter.type") != -1) {
            $filter[] = "lottery_log.type = :type";
            $params[':type'] = intval(Input::get("filter.type"));
        }
        $filter = implode(' AND ', $filter);
        if (!empty($filter)) {
            $filter = ' WHERE ' . $filter;
        }

        $res = DB::query("SELECT COUNT(*) AS count FROM lottery_log" . $filter);
        foreach ($params as $param => $value) {
            $res->param($param, $value);
        }
        $res = $res->execute()->as_array();
        $config = [
            'pagination_url' => '/lotteries/logs/s/?' . Uri::build_query_string(Input::get()),
            'total_items' => $res[0]['count'],
            'per_page' => 100,
            'uri_segment' => 4
        ];
        $pagination = Pagination::forge('logspagination', $config);

        $res = DB::query("SELECT lottery_log.*, lottery.name FROM lottery_log JOIN lottery ON lottery.id = lottery_log.lottery_id" . $filter . " ORDER BY `id` DESC LIMIT :offset, :limit");
        foreach ($params as $param => $value) {
            $res->param($param, $value);
        }
        $res->param(":limit", $pagination->per_page);
        $res->param(":offset", $pagination->offset);

        $res = $res->execute()->as_array();
        $inside = View::forge("admin/lotteries/logs");
        $inside->set("logs", $res);
        $inside->set("lotteries", $lotteries);
        $inside->set("pages", $pagination);
        $inside->set("page", $this->request->param("page") !== null ? $this->request->param("page") : 1);

        $this->view->inside = $inside;
        return Response::forge($this->view);
    }

    /**
     *
     * @return Response
     */
    public function action_imvalaplogs(): Response
    {
        if (!$this->is_user) {
            return Response::forge($this->view);
        }
        $whitelabels = Model_Whitelabel::find([
            "order_by" => ["name" => "asc"]]);

        $lotteries = Model_Lottery::get_lotteries_order_by_id();
        $filter = [];
        $params = [];

        if (!empty(Input::get("filter.range_start"))) {
            $date_start = DateTime::createFromFormat(
                "m/d/Y H:i:s",
                Input::get("filter.range_start") . ' 00:00:00',
                new DateTimeZone("UTC")
            );
            if ($date_start !== false) {
                $filter[] = "imvalap_log.`date` >= :start";
                $params[':start'] = $date_start->format('Y-m-d H:i:s');
            }
        }
        if (!empty(Input::get("filter.range_end"))) {
            $date_end = DateTime::createFromFormat(
                "m/d/Y H:i:s",
                Input::get("filter.range_end") . ' 23:59:59',
                new DateTimeZone("UTC")
            );
            if ($date_end !== false) {
                $filter[] = "imvalap_log.`date` <= :end";
                $params[':end'] = $date_end->format('Y-m-d H:i:s');
            }
        }
        if (!empty(Input::get("filter.whitelabel"))) {
            $filter[] = "imvalap_log.whitelabel_id = :whitelabel";
            $params[':whitelabel'] = intval(Input::get("filter.whitelabel"));
        }
        if (Input::get("filter.type") != null && Input::get("filter.type") != -1) {
            $filter[] = "imvalap_log.type = :type";
            $params[':type'] = intval(Input::get("filter.type"));
        }
        $filter = implode(' AND ', $filter);
        if (!empty($filter)) {
            $filter = ' WHERE ' . $filter;
        }

        $res = DB::query("SELECT COUNT(*) AS count FROM imvalap_log" . $filter);
        foreach ($params as $param => $value) {
            $res->param($param, $value);
        }
        $res = $res->execute()->as_array();
        $config = [
            'pagination_url' => '/lotteries/imvalaplogs/s/?' . Uri::build_query_string(Input::get()),
            'total_items' => $res[0]['count'],
            'per_page' => 100,
            'uri_segment' => 4
        ];
        $pagination = Pagination::forge('logspagination', $config);

        $res = DB::query("SELECT imvalap_log.*, whitelabel.name, whitelabel.prefix, whitelabel_user_ticket.token, imvalap_job.jobid FROM " .
            "imvalap_log LEFT JOIN whitelabel ON whitelabel.id = imvalap_log.whitelabel_id LEFT JOIN imvalap_job ON imvalap_job.id = imvalap_log.imvalap_job_id
			LEFT JOIN whitelabel_user_ticket ON whitelabel_user_ticket.id = whitelabel_user_ticket_id" . $filter . " ORDER BY `id` DESC LIMIT :offset, :limit");
        foreach ($params as $param => $value) {
            $res->param($param, $value);
        }
        $res->param(":limit", $pagination->per_page);
        $res->param(":offset", $pagination->offset);

        $res = $res->execute()->as_array();
        $inside = View::forge("admin/lotteries/imvalaplogs");
        $inside->set("whitelabels", $whitelabels);
        $inside->set("logs", $res);
        $inside->set("lotteries", $lotteries);
        $inside->set("pages", $pagination);
        $inside->set("page", $this->request->param("page") !== null ? $this->request->param("page") : 1);

        $this->view->inside = $inside;
        return Response::forge($this->view);
    }

    /**
     *
     * @return Response
     */
    public function action_paymentlogs(): Response
    {
        if (!$this->is_user) {
            return Response::forge($this->view);
        }

        $whitelabels = Model_Whitelabel::find(
            [
                "order_by" => ["name" => "asc"]
            ]
        );

        $filter = [];
        $params = [];

        if (!empty(Input::get("filter.range_start"))) {
            $date_start = DateTime::createFromFormat(
                "m/d/Y H:i:s",
                Input::get("filter.range_start") . ' 00:00:00',
                new DateTimeZone("UTC")
            );
            if ($date_start !== false) {
                $filter[] = " AND payment_log.`date` >= :start";
                $params[':start'] = $date_start->format('Y-m-d H:i:s');
            }
        }
        if (!empty(Input::get("filter.range_end"))) {
            $date_end = DateTime::createFromFormat(
                "m/d/Y H:i:s",
                Input::get("filter.range_end") . ' 23:59:59',
                new DateTimeZone("UTC")
            );
            if ($date_end !== false) {
                $filter[] = " AND payment_log.`date` <= :end";
                $params[':end'] = $date_end->format('Y-m-d H:i:s');
            }
        }
        if (!empty(Input::get("filter.whitelabel"))) {
            $filter[] = " AND payment_log.whitelabel_id = :whitelabel";
            $params[':whitelabel'] = intval(Input::get("filter.whitelabel"));
        }
        if (Input::get("filter.type") != null && Input::get("filter.type") != -1) {
            $filter[] = " AND payment_log.type = :type";
            $params[':type'] = intval(Input::get("filter.type"));
        }
        $filter_add = implode(' ', $filter);

        $count = 0;
        $result = Model_Payment_Log::get_count_filtered($filter_add, $params);
        if (!is_null($result)) {
            $count = $result;
        }

        $config = [
            'pagination_url' => '/paymentlogs/s/?' . Uri::build_query_string(Input::get()),
            'total_items' => $count,
            'per_page' => 100,
            'uri_segment' => 3
        ];
        $pagination = Pagination::forge('logspagination', $config);

        $payment_log_results = Model_Payment_Log::get_data_filtered(
            $filter_add,
            $params,
            $pagination->offset,
            $pagination->per_page
        );

        if (is_null($payment_log_results)) {
            ;               // Loggin?
        }

        $payment_methods = Model_Payment_Method::get_payment_methods();
        $ccmethods = Lotto_Helper::get_cc_gateways();

        $inside = View::forge("admin/payment/paymentlogs");
        $inside->set("whitelabels", $whitelabels);
        $inside->set("methods", $payment_methods);
        $inside->set("ccmethods", $ccmethods);
        $inside->set("logs", $payment_log_results);
        $inside->set("pages", $pagination);
        $inside->set("page", $this->request->param("page") !== null ? $this->request->param("page") : 1);

        $this->view->inside = $inside;
        return Response::forge($this->view);
    }

    /**
     *
     * @return Response
     */
    public function action_lottorisqlogs(): Response
    {
        if (!$this->is_user) {
            return Response::forge($this->view);
        }
        $whitelabels = Model_Whitelabel::find([
            "order_by" => ["name" => "asc"]]);

        $lotteries = Model_Lottery::get_lotteries_order_by_id();
        $filter = [];
        $params = [];

        if (!empty(Input::get("filter.range_start"))) {
            $date_start = DateTime::createFromFormat(
                "m/d/Y H:i:s",
                Input::get("filter.range_start") . ' 00:00:00',
                new DateTimeZone("UTC")
            );
            if ($date_start !== false) {
                $filter[] = "lottorisq_log.`date` >= :start";
                $params[':start'] = $date_start->format('Y-m-d H:i:s');
            }
        }
        if (!empty(Input::get("filter.range_end"))) {
            $date_end = DateTime::createFromFormat(
                "m/d/Y H:i:s",
                Input::get("filter.range_end") . ' 23:59:59',
                new DateTimeZone("UTC")
            );
            if ($date_end !== false) {
                $filter[] = "lottorisq_log.`date` <= :end";
                $params[':end'] = $date_end->format('Y-m-d H:i:s');
            }
        }
        if (!empty(Input::get("filter.whitelabel"))) {
            $filter[] = "lottorisq_log.whitelabel_id = :whitelabel";
            $params[':whitelabel'] = intval(Input::get("filter.whitelabel"));
        }
        if (Input::get("filter.type") != null && Input::get("filter.type") != -1) {
            $filter[] = "lottorisq_log.type = :type";
            $params[':type'] = intval(Input::get("filter.type"));
        }
        $filter = implode(' AND ', $filter);
        if (!empty($filter)) {
            $filter = ' WHERE ' . $filter;
        }


        $res = DB::query("SELECT COUNT(*) AS count FROM lottorisq_log" . $filter);
        foreach ($params as $param => $value) {
            $res->param($param, $value);
        }
        $res = $res->execute()->as_array();
        $config = [
            'pagination_url' => '/lotteries/lottorisqlogs/s/?' . Uri::build_query_string(Input::get()),
            'total_items' => $res[0]['count'],
            'per_page' => 100,
            'uri_segment' => 4
        ];
        $pagination = Pagination::forge('logspagination', $config);

        $res = DB::query("SELECT lottorisq_log.*, whitelabel.name, whitelabel_ltech.name as ltech_name, whitelabel.prefix, whitelabel_user_ticket.token FROM lottorisq_log LEFT JOIN whitelabel ON whitelabel.id = lottorisq_log.whitelabel_id 
            LEFT JOIN whitelabel_ltech ON whitelabel_ltech.id = lottorisq_log.whitelabel_ltech_id 
			LEFT JOIN whitelabel_user_ticket ON whitelabel_user_ticket.id = whitelabel_user_ticket_id" . $filter . " ORDER BY `id` DESC LIMIT :offset, :limit");
        foreach ($params as $param => $value) {
            $res->param($param, $value);
        }
        $res->param(":limit", $pagination->per_page);
        $res->param(":offset", $pagination->offset);

        $res = $res->execute()->as_array();
        $inside = View::forge("admin/lotteries/lottorisqlogs");
        $inside->set("whitelabels", $whitelabels);
        $inside->set("logs", $res);
        $inside->set("lotteries", $lotteries);
        $inside->set("pages", $pagination);
        $inside->set("page", $this->request->param("page") !== null ? $this->request->param("page") : 1);

        $this->view->inside = $inside;
        return Response::forge($this->view);
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

        $reports = new Forms_Admin_Reports_Reports();
        $reports->process_form();

        $inside = $reports->get_inside();
        $this->view->inside = $inside;

        $this->view->set_global("title", _("Generate report"));

        return Response::forge($this->view);
    }

    /**
     *
     * @param Response $response
     * @return Response
     */
    public function after($response)
    {
        return $response;
    }

    public function action_invoice(): Response
    {
        $isUserNotLogged = !$this->is_user;
        if ($isUserNotLogged) {
            Response::redirect('/');
        }

        $this->view->inside = View::forge('admin/invoice/invoice.php');
        return Response::forge($this->view);
    }

    private function generateImageWithText(string $imagePath, string $fontFilePath, array $textCoordinates, ?array $drawDetails = null): void
    {
        if (!file_exists($imagePath)) {
            throw new InvalidArgumentException('Banner template file not found: ' . $imagePath);
        }

        $image = imagecreatefromjpeg($imagePath);
        $textColor = imagecolorallocate($image, 255, 255, 255);

        foreach ($textCoordinates as $coordinate) {
            $fontSize = $coordinate['fontSize'];
            $text = $coordinate['text'];
            $y = $coordinate['y'];
            $fontWeight = $coordinate['fontWeight'] ?? 'normal';

            $fontPath = $fontWeight === 'bold' ?
                __DIR__ . '/../../../../public/assets/fonts/Figtree-ExtraBold.ttf' :
                __DIR__ . '/../../../../public/assets/fonts/Figtree-Light.ttf';

            $bbox = imagettfbbox($fontSize, 0, $fontPath, $text);
            $textWidth = $bbox[2] - $bbox[0];
            $imageWidth = imagesx($image);

            if (isset($coordinate['x'])) {
                $x = $coordinate['x'];
            } else {
                $x = ($imageWidth - $textWidth) / 2;
            }

            imagettftext($image, $fontSize, 0, $x, $y, $textColor, $fontPath, $text);
        }

        $ballImage = imagecreatefrompng(__DIR__ . '/../../../../public/assets/images/bannerTemplates/ball.png');
        $ballWidth = 89;
        $ballHeight = 88;
        $spacing = 20;
        $ballYPosition = 650;

        if ($drawDetails !== null) {
            $numbers = explode(',', $drawDetails[0]['numbers']);
            $bonusNumbers = explode(',', $drawDetails[0]['bnumbers']);
            $allNumbers = array_merge($numbers, $bonusNumbers);

            $totalWidth = (count($allNumbers) - 1) * ($ballWidth + $spacing) + $ballWidth;
            $startX = (imagesx($image) - $totalWidth) / 2;

            foreach ($allNumbers as $index => $number) {
                $ballXPosition = $startX + $index * ($ballWidth + $spacing);

                imagecopy($image, $ballImage, $ballXPosition, $ballYPosition, 0, 0, $ballWidth, $ballHeight);

                $bbox = imagettfbbox(30, 0, $fontFilePath, $number);
                $textWidth = $bbox[2] - $bbox[0];
                $textHeight = abs($bbox[5] - $bbox[3]);

                $textX = $ballXPosition + ($ballWidth - $textWidth) / 2;
                $textY = $ballYPosition + ($ballHeight + $textHeight) / 2;

                imagettftext($image, 30, 0, $textX, $textY, $textColor, $fontFilePath, $number);
            }
        }

        header('Content-Type: image/jpeg');
        header('Content-Disposition: attachment; filename="banner.jpg"');

        imagejpeg($image);
        imagedestroy($image);
        imagedestroy($ballImage);
    }

    public function action_marketingTools(): Response
    {
        $isUserLoggedOut = !$this->is_user;
        if ($isUserLoggedOut) {
            Response::redirect('/');
        }

        $lotteryRepository = Container::get(LotteryRepository::class);
        $lotteryDrawRepository = Container::get(LotteryDrawRepository::class);

        if ($this->hasValidInputsForBanner()) {
            $bannerData = $this->prepareBannerData($lotteryRepository, $lotteryDrawRepository);
            try {
                $this->generateImageWithText(
                    $bannerData['imagePath'],
                    $bannerData['fontFilePath'],
                    $bannerData['textCoordinates'],
                    $bannerData['isResultBanner'] ? $bannerData['drawDetails'] : null
                );
            } catch (Exception $e) {
                echo 'Error: ' . $e->getMessage();
            }

            exit;
        }

        [$lotteries, $lotteryDraws] = $this->getLotteriesData($lotteryRepository, $lotteryDrawRepository);
        $view = View::forge('admin/marketing-tools/marketing-tools');
        $view->set('lotteries', $lotteries);
        $view->set('lotteryDraws', $lotteryDraws);

        $this->view->inside = $view;
        return Response::forge($this->view);
    }

    private function hasValidInputsForBanner(): bool
    {
        return !empty(Input::get('bannerType')) && !empty(Input::get('lotteryId')) && !empty(Input::get('drawDate'));
    }

    private function prepareBannerData(LotteryRepository $lotteryRepository, LotteryDrawRepository $lotteryDrawRepository): array
    {
        $bannerType = (int)Input::get('bannerType');
        $lotteryId = (int)Input::get('lotteryId');
        $drawDate = Input::get('drawDate');
        $isNextDraw = $drawDate === 'next_draw';
        $formattedDrawDate = $isNextDraw ? '' : Carbon::parse($drawDate)->format('d/m/Y');

        $isResultBanner = $bannerType === 2;
        $lotteryDetails = $lotteryRepository->getLotteryDataForBannerByLotteryId($lotteryId);
        $drawDetails = $this->getDrawDetails($lotteryDrawRepository, $lotteryId, $drawDate, $lotteryDetails);

        $bannerTypeText = $bannerType === 1 ? 'jackpot' : 'result';
        $imagePath = __DIR__ . "/../../../../public/assets/images/bannerTemplates/$bannerTypeText-$lotteryId.jpg";
        $fontFilePath = __DIR__ . '/../../../../public/assets/fonts/Figtree-ExtraBold.ttf';

        $jackpot = $isNextDraw ? $lotteryDetails['current_jackpot'] : $drawDetails[0]['jackpot'];
        $jackpotFormatted = Lotto_View::format_currency($jackpot * 1000000, $lotteryDetails['currency'], 0, 'en_US');

        $textCoordinates = $this->getTextCoordinates($lotteryDetails['name'], $jackpotFormatted, $formattedDrawDate, $isResultBanner);

        return compact('imagePath', 'fontFilePath', 'textCoordinates', 'drawDetails', 'isResultBanner');
    }

    private function getDrawDetails(LotteryDrawRepository $lotteryDrawRepository, int $lotteryId, string $drawDate, array $lotteryDetails): array
    {
        if ($drawDate === 'next_draw') {
            return [['jackpot' => $lotteryDetails['current_jackpot']]];
        }

        return $lotteryDrawRepository->getDrawDetailsForLotteryIdAndDate($lotteryId, $drawDate);
    }

    private function getTextCoordinates(string $lotteryName, string $lotteryJackpot, string $formattedDrawDate, bool $isResultBanner): array
    {
        if ($isResultBanner) {
            return [
                ['text' => "$lotteryName results", 'fontSize' => 55, 'y' => 540, 'fontWeight' => 'bold'],
                ['text' => $formattedDrawDate, 'fontSize' => 29, 'y' => 592, 'fontWeight' => 'light'],
            ];
        }

        $jackpotFontSize = 90;
        if (strlen($lotteryJackpot) >= 14) {
            $jackpotFontSize = 75;
        }

        return [
            ['text' => $lotteryName, 'fontSize' => 60, 'y' => 600, 'fontWeight' => 'bold'],
            ['text' => 'Jackpot', 'fontSize' => 55, 'y' => 690, 'x' => 175, 'fontWeight' => 'bold'],
            ['text' => 'is growing!', 'fontSize' => 55, 'y' => 690, 'x' => 480, 'fontWeight' => 'light'],
            ['text' => $lotteryJackpot, 'fontSize' => $jackpotFontSize, 'y' => 880, 'fontWeight' => 'bold'],
        ];
    }

    private function getLotteriesData(LotteryRepository $lotteryRepository, LotteryDrawRepository $lotteryDrawRepository): array
    {
        $supportedLotteryIds = [
            'results' => [1, 2, 4, 6, 10, 11, 14, 17, 28, 54, 56, 67, 68, 69],
            'jackpot' => [1, 2, 3, 4, 6, 10, 11, 14, 17, 28, 54, 56, 67, 68, 69],
        ];

        $uniqueLotteryIds = array_unique(array_merge($supportedLotteryIds['results'], $supportedLotteryIds['jackpot']));

        $lotteries = $lotteryRepository->getLotteryNamesByIds($uniqueLotteryIds);
        $lotteryDraws = [];

        foreach ($uniqueLotteryIds as $lotteryId) {
            $lotteryDraws[$lotteryId][] = $lotteryDrawRepository->getLotteryDrawDatesForLotteryId($lotteryId, 14);
        }

        return [$lotteries, $lotteryDraws];
    }
}
