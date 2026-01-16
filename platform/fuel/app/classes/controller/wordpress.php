<?php

use Fuel\Core\Controller;
use Fuel\Core\Cookie;
use Fuel\Core\Event;
use Fuel\Core\Input;
use Fuel\Core\Response;
use Fuel\Core\Security;
use Fuel\Core\Session;
use Fuel\Core\View;
use Helpers\CurrencyHelper;
use Helpers\FlashMessageHelper;
use Helpers\UrlHelper;
use Helpers\UserHelper;
use Helpers\Wordpress\LanguageHelper;
use Helpers\Wordpress\SecurityHelper;
use libphonenumber\PhoneNumberFormat;
use Models\Whitelabel;
use Models\WhitelabelOAuthClient;
use Models\WhitelabelPaymentMethod;
use Modules\View\ViewHelper;
use Repositories\Orm\WhitelabelUserRepository;
use Repositories\WithdrawalRequestRepository;
use Services\{Auth\UserActivationService,
    Auth\WordpressLoginService,
    CartService,
    Plugin\MauticPluginService,
    Logs\FileLoggerService,
    SocialMediaConnect\ActivationService,
    SocialMediaConnect\FormService,
    SocialMediaConnect\MessageHelper};

class Controller_Wordpress extends Controller
{
    use Traits_Checks_Block_Ip;
    use Traits_Checks_Block_Usa_States;
    use Traits_Checks_Block_IP_White;

    public const SHOW_POPUP_COOKIE_NAME = 'show_popup';
    public const SHOW_POPUP_COOKIE_EXPIRATION_IN_SECONDS = Helpers_Time::DAY_IN_SECONDS * 31;

    private WithdrawalRequestRepository $withdrawalRequestRepository;
    private WhitelabelUserRepository $whitelabelUserRepository;
    private FileLoggerService $fileLoggerService;
    private CartService $cartService;

    public function __construct(...$args)
    {
        parent::__construct(...$args);
        $this->withdrawalRequestRepository = Container::get(WithdrawalRequestRepository::class);
        $this->whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);
        $this->fileLoggerService = Container::get(FileLoggerService::class);
        $this->cartService = Container::get(CartService::class);
    }

    /**
     * IMPORTANT: during installation of whitelabel, $whitelabel will be null here.
     * @return void
     */
    public function action_before()
    {
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        $isWhitelabelDuringInstallation = false; // TODO: {Vordis 2021-09-01 13:32:07} it should have separate flow altogether
        if ($whitelabel == null) {
            if (!(defined('WP_CLI') && WP_CLI)) {
                $domain = Container::get('domain');
                $this->fileLoggerService->error(
                    "Lack of settings for domain in DB. Name of domain: " . $domain
                );
                exit("There is a problem on server");
            } else {
                // special case when white-label is installed
                $isWhitelabelDuringInstallation = true;
            }
        } else {
            $whitelabel = $whitelabel->to_array();
        }

        Lotto_Settings::getInstance()->set("whitelabel", $whitelabel);

        /** Don't remove it, it sets currencies for singleton */
        // Reset PageCache in deploy is enough here
        Helpers_Currency::getCurrencies();

        /** Probably it is used only on the /order/confirm path */
        Lotto_Platform::save_extra_session_data();

        // Register events
        Event::register('user_login', 'Events_User_Login::handle');
        Event::register('user_register', 'Events_User_Register::handle');
        Event::register('user_deposit', 'Events_User_Transaction_Deposit::handle');
        Event::register('user_purchase', 'Events_User_Transaction_Purchase::handle');
        Event::register('user_edit_email', 'Events_User_Edit_Email::handle');
        Event::register('user_edit_profile', 'Events_User_Edit_Profile::handle');
        Event::register('user_account_confirm', 'Events_User_Account_Confirm::handle');
        Event::register('user_update', 'Events_User_Update::handle');

        // Java-Script only
        Event::register('user_cart_add', 'Events_User_Cart_Add::handle');
        Event::register('user_cart_checkout', 'Events_User_Cart_Checkout::handle');
        Event::register('user_purchase_success', 'Events_User_Transaction_Purchase_Success::handle');
        Event::register('user_purchase_failure', 'Events_User_Transaction_Purchase_Failure::handle');
        Event::register('user_deposit_failure', 'Events_User_Transaction_Deposit_Failure::handle');

        Event::register('whitelabel_ticket_payout', 'Events_Whitelabel_Ticket_Payout::handle');
        Event::register('whitelabel_transaction_accept', 'Events_Whitelabel_Transaction_Accept::handle');
        Event::register('whitelabel_withdrawal_approve', 'Events_Whitelabel_Withdrawal_Approve::handle');
        Event::register('whitelabel_withdrawal_cancel', 'Events_Whitelabel_Withdrawal_Cancel::handle');

        $user = UserHelper::getUser();
        $isUser = !empty($user);
        $user = !empty($user) ? $user->to_array() : [];
        Lotto_Settings::getInstance()->set("user", $user);
        Lotto_Settings::getInstance()->set("is_user", $isUser);

        /**
         * Spain is also blocked by CloudFlare and this is second line protection
         * It happens before setting PageCache header - PageCache is set up in page-{slug}.php files
         * So, this response with redirect doesn't have page-cache header, so nginx won't cache it
         * It's very important to maintain this order cause:
         * If set PageCache happened first, then the server would cache
         * our redirect for rest users, but we only want to redirect users from Spain without cache it
         *
         * @link https://gginternational.slite.com/app/docs/cdNzjECJu5sw4e
         */
        $shouldBlockSpain = !$isWhitelabelDuringInstallation;
        if ($shouldBlockSpain) {
            $shouldBlockSpain = SecurityHelper::shouldBlockSpainForV1($whitelabel);
            if ($shouldBlockSpain) {
                Response::redirect("https://blocked.{$whitelabel['domain']}");
            }
        }

        if (preg_match('/.*account\/deposit.*/', $_SERVER['REQUEST_URI'])) {
            Response::redirect(lotto_platform_home_url());
        }

        Lotto_Settings::getInstance()->set("timezone", Lotto_View::get_user_timezone());
    }

    public function action_slip()
    {
        $id = get_query_var("id");
        ob_clean();
        if (!empty($id)) {
            $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
            $user = Lotto_Settings::getInstance()->get('user');

            $slip = Model_Whitelabel_User_Ticket_Slip::get_ticket_id_for_slip($id);
            if (
                $slip !== null && count($slip) == 1 &&
                $whitelabel['id'] == $slip[0]['whitelabel_id'] &&
                $user['id'] == $slip[0]['whitelabel_user_id']
            ) {
                try {
                    $image = file_get_contents($slip[0]['ticket_scan_url']);
                } catch (Throwable $exception) {
                    status_header(404);
                    exit();
                }
                header('Content-Type: image/jpeg');
                echo $image;
                exit();
            }
        }
        status_header(404);
        exit();
    }

    /**
     *
     * @return bool|View
     */
    public function action_myaccount()
    {
        $is_user = Lotto_Settings::getInstance()->get("is_user");
        if (!$is_user) {
            return false;
        }
        $errors = Lotto_Settings::getInstance()->get("errors");
        if (empty($errors)) {
            $errors = [];
        }

        $user = lotto_platform_user();
        $auser = Model_Whitelabel_User::find_by_pk($user['id']);

        if ($auser === null) {
            return false;
        }

        $accountlink = lotto_platform_get_permalink_by_slug('account');
        $countries = Lotto_Helper::get_localized_country_list();
        $isPromoteAccess = $auser['connected_aff_id'] == true;

        $phone_countries = Lotto_Helper::filter_phone_countries($countries);

        $tprefixes = Lotto_Helper::get_telephone_prefix_list();
        $timezones = Lotto_Helper::get_timezone_list();
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        $groups = Lotto_Helper::get_all_whitelabel_groups_list($whitelabel['id']);

        $section = get_query_var("section");
        $view = View::forge(Lotto_Helper::get_file_template('/box/myaccount.php'));
        $view->set("section", $section);
        $view->set("whitelabel", $whitelabel);

        switch ($section) {
            case 'exportalldata':
                $view->set("timezones", $timezones);
                $view->set("countries", $countries);
                $view->set("pcountries", $phone_countries);
                $exportall_data = new Forms_Wordpress_Exportalldata($user, $whitelabel);
                $messages = $exportall_data->get_messages();
                if (!empty($messages)) {
                    Session::set("message", $messages);

                    Response::redirect($accountlink);
                }

                $res = $exportall_data->prepare_files_for_zip();
                $messages = $exportall_data->get_messages();

                if ($res && empty($messages)) {
                    $exportall_data->get_zipped_file();
                    exit();
                } else {
                    Session::set("message", $messages);

                    Response::redirect($accountlink);
                }
                break;
            case 'withdrawal':
                if (get_query_var('action') === 'details') {
                    $token = get_query_var('id');
                    $withdrawalRequest = $this->withdrawalRequestRepository->findByTokenForWhitelabel($token, $whitelabel['id']);

                    if (!empty($withdrawalRequest)) {
                        $backUrl = lotto_platform_get_permalink_by_slug('account') . 'withdrawal';

                        return ViewHelper::render('account/WithdrawalDetails.twig', [
                            'data' => unserialize($withdrawalRequest->data),
                            'prefix' => $withdrawalRequest->whitelabel->prefix,
                            'token' => $token,
                            'backUrl' => $backUrl
                        ]);
                    }
                }
                $withdrawal = new Forms_Wordpress_Myaccount_Withdrawal_List(
                    $whitelabel,
                    $user,
                    $accountlink
                );
                $withdrawal->set_errors($errors);
                $result = $withdrawal->process_form($view);

                switch ($result) {
                    case Forms_Wordpress_Myaccount_Withdrawal_List::RESULT_OK:
                        break;
                    case Forms_Wordpress_Myaccount_Withdrawal_List::RESULT_WITH_MESSAGE:
                    case Forms_Wordpress_Myaccount_Withdrawal_List::RESULT_ALREADY_REQUESTED:
                        $messages = $withdrawal->get_messages();
                        if (!empty($messages)) {
                            FlashMessageHelper::addMany($messages);
                        }
                        break;
                    case Forms_Wordpress_Myaccount_Withdrawal_List::RESULT_WITH_ERRORS:
                        $errors = $withdrawal->get_errors();
                        break;
                }

                break;
            case 'deposit':
                $language = LanguageHelper::getCurrentWhitelabelLanguage();
                $view->set("language", $language);
                $currency = CurrencyHelper::getCurrentCurrency()->to_array();
                $view->set("currency", $currency);
                $currencies = Lotto_Settings::getInstance()->get("currencies");
                $view->set('currencies', $currencies);

                $this->setViewDefaults($view);
                break;
            case 'tickets':
                $tickets_link = $accountlink . 'tickets/';

                $view->set("tickets_link", $tickets_link);
                $view->set("whitelabel", $whitelabel);

                if (get_query_var("action") == "details") {
                    $transaction_link = $accountlink . 'transactions/';
                    $view->set("transaction_link", $transaction_link);
                    $view->set("action", "details");

                    $ticket_details_form = new Forms_Wordpress_Myaccount_Ticket_Details(
                        $whitelabel,
                        $user
                    );
                    $result = $ticket_details_form->process_form($view);
                    if ($result === Forms_Wordpress_Myaccount_Ticket_Details::RESULT_WITH_ERRORS) {
                        $errors = $ticket_details_form->get_errors();
                    }
                } elseif (get_query_var("action") == "awaiting") {
                    $tickets_link = $accountlink . 'tickets/awaiting/';
                    $ticket_list_obj = new Forms_Wordpress_Myaccount_Ticket_List(
                        $whitelabel,
                        $user
                    );
                    $ticket_list_obj->process_form_awaiting($view, $tickets_link);
                } else {
                    $ticket_list_obj = new Forms_Wordpress_Myaccount_Ticket_List(
                        $whitelabel,
                        $user
                    );
                    $ticket_list_obj->process_form($view, $tickets_link);
                }
                $count_past = Model_Whitelabel_User_Ticket::get_counted_by_user_and_whitelabel_filtered(
                    $whitelabel,
                    $user,
                    [],
                    "",
                    'past'
                );

                $count_awaiting = Model_Whitelabel_User_Ticket::get_counted_by_user_and_whitelabel_filtered(
                    $whitelabel,
                    $user,
                    [],
                    "",
                    'awaiting'
                );

                $view->set('count_past', $count_past);
                $view->set('count_awaiting', $count_awaiting);
                break;
            case 'transactions':
                $transaction_form = new Forms_Wordpress_Myaccount_Transaction(
                    $whitelabel,
                    $user,
                    $accountlink
                );
                $result = $transaction_form->process_form($view);
                switch ($result) {
                    case Forms_Wordpress_Myaccount_Transaction::RESULT_OK:
                        break;
                    case Forms_Wordpress_Myaccount_Transaction::RESULT_WITH_ERRORS:
                        $errors = $transaction_form->get_errors();
                        break;
                }

                break;
            case 'payments':
                $saved_cards = Lotto_Helper::get_e_merchant_pay_saved_cards($user['id']);
                $view->set("cards", $saved_cards);
                break;
            default:
                $this->setViewDefaults($view);
                break;
        }
        $view->set("accountlink", $accountlink);
        $view->set("isPromoteAccess", $isPromoteAccess);
        $view->set("isSocialConnected", $auser->isSocialConnected($user['id']));

        $view->set("user", $user);
        $view->set("errors", $errors);

        return $view;
    }

    private function setViewDefaults(View $view): View
    {
        $countries = Lotto_Helper::get_localized_country_list();
        $phone_countries = Lotto_Helper::filter_phone_countries($countries);
        $tprefixes = Lotto_Helper::get_telephone_prefix_list();

        $timezones = Lotto_Helper::get_timezone_list();
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        $groups = Lotto_Helper::get_all_whitelabel_groups_list($whitelabel['id']);

        $view->set("timezones", $timezones);
        $view->set("countries", $countries);
        $view->set("pcountries", $phone_countries);
        $view->set("groups", $groups);
        $view->set("prefixes", $tprefixes);
        $view->set("whitelabel", $whitelabel);

        return $view;
    }

    /**
     *
     * @return void
     */
    public function action_myaccount_before(): void
    {
        $user = lotto_platform_user();
        $auser = Model_Whitelabel_User::find_by_pk($user['id']);

        if ($auser === null) {
            return ;
        }

        $countries = Lotto_Helper::get_localized_country_list();

        $phone_countries = Lotto_Helper::filter_phone_countries($countries);

        $timezones = Lotto_Helper::get_timezone_list();

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        $groups = Lotto_Helper::get_selectable_whitelabel_groups_list($whitelabel['id']);

        $profile = new Forms_Wordpress_Myaccount_Profile(
            $countries,
            $timezones,
            $phone_countries,
            $groups,
            $user,
            $auser,
            $whitelabel
        );
        $status_form = $profile->process_form();
        $errors = $profile->get_errors();

        // At this moment messages are not used,
        // I don't know why, but I left it as is
        $messages = $profile->get_messages();

        Lotto_Settings::getInstance()->set('errors', $errors);
        if (!empty($messages)) {
            FlashMessageHelper::addMany($messages);
        }

        switch ($status_form) {
            case Forms_Wordpress_Myaccount_Profile::RESULT_GO_FURTHER:    // profile variable is not set yet (OK)
            case Forms_Wordpress_Myaccount_Profile::RESULT_OK:     // This is OK
            case Forms_Wordpress_Myaccount_Profile::RESULT_SECURITY_ERROR:     // Token not set (go further)
            case Forms_Wordpress_Myaccount_Profile::RESULT_WITH_ERRORS:    // Got errors
                break;
            case Forms_Wordpress_Myaccount_Profile::RESULT_ERRORS_IP_NOT_ALLOWED:
            case Forms_Wordpress_Myaccount_Profile::RESULT_NO_ERRORS_NOT_REQUIRED_TYPE:     // No errors - chmail - not required type
                Response::redirect(lotto_platform_get_permalink_by_slug('account'));
                break;
            case Forms_Wordpress_Myaccount_Profile::RESULT_NO_ERRORS_REQUIRED_TYPE:     // No errors - chmail - required type
                Response::redirect(lotto_platform_get_permalink_by_slug('activation'));
            // no break
            case Forms_Wordpress_Myaccount_Profile::RESULT_NO_ERRORS_REDIRECT:     // No errors
                Response::redirect(lotto_platform_get_permalink_by_slug('account'));
            // no break
            case Forms_Wordpress_Myaccount_Profile::RESULT_ERRORS_EMAIL_NOT_CHANGED:     // Errors
                Response::redirect(lotto_platform_home_url('/'));
            // no break
            case Forms_Wordpress_Myaccount_Profile::RESULT_ERRORS_EMAIL_CHANGED:
                Response::redirect(lotto_platform_get_permalink_by_slug('account'));
        }
    }

    /**
     *
     * @return bool|View
     */
    public function action_myaccount_nav()
    {
        $is_user = Lotto_Settings::getInstance()->get("is_user");
        if (!$is_user) {
            return false;
        }
        $whitelabel = Lotto_Settings::getInstance()->get('whitelabel');

        $view = View::forge(Lotto_Helper::get_file_template('/box/myaccount_nav.php'));
        $view->set("section", get_query_var('section'));

        $subtitle = null;
        switch (get_query_var('section')) {
            case 'email':
                $subtitle = _("Change e-mail address");
                break;
            case 'password':
                $subtitle = _("Change password");
                break;
            case 'profile':
                $subtitle = _("Edit profile");
                break;
            case 'tickets':
                $subtitle = _("My tickets");
                break;
            case 'payments':
                $subtitle = _("My payment methods");
                break;
            case 'transactions':
                $subtitle = _("My transactions");
                break;
            case 'deposit':
                $subtitle = _("Deposit");
                break;
            case 'withdrawal':
                $subtitle = _("Withdrawal");
                break;
            case 'promote':
                $subtitle = _('Promote and earn');
                break;
            default:
                $subtitle = _("My details");
        }
        Lotto_Settings::getInstance()->set("subtitle", $subtitle);
        return $view;
    }

    /** Redirects user after success login */
    public function action_check_login(): void
    {
        $isUserLoggedIn = !is_null($this->whitelabelUserRepository->getUserFromSession());
        if ($isUserLoggedIn) {
            // Continue autologin flow to the Whitelabel OAuth client site
            $autologinUri = Cookie::get(WhitelabelOAuthClient::AUTOLOGIN_URI_KEY);
            if (!empty($autologinUri)) {
                Response::redirect($autologinUri);
            }

            // We would like to redirect user to order page
            $isCartNotEmpty = !empty(Session::get('order'));
            if ($isCartNotEmpty) {
                Response::redirect(lotto_platform_get_permalink_by_slug('order'));
            }

            Response::redirect(lotto_platform_home_url('/'));
        }
    }

    /**
     *
     */
    public function action_order_remove()
    {
        $remove = intval(get_query_var("remove"));

        $order = Session::get('order');
        if (isset($order[$remove])) {
            Session::set("order_last_removed", $order[$remove]);
            array_splice($order, $remove, 1);
            Session::set('order', $order);

            $userId = $this->getUserId();
            if ($userId) {
                $this->cartService->createOrUpdateCart($userId, $order);
            }

            $msg = _('The item has been deleted!') .
                ' <a href="' . lotto_platform_get_permalink_by_slug('order') . 'undo/' .
                '">' . _('Undo') . '</a>';

            Session::set("message", ["success", $msg]);
        } else {
            $msg = _("Couldn't find the specified item to remove!");
            Session::set("message", ["error", $msg]);
        }

        Response::redirect(lotto_platform_get_permalink_by_slug('order'));
        exit();
    }

    public function getUserId(): ?int
    {
        return lotto_platform_is_user() ? lotto_platform_user()['id'] : null;
    }

    /**
     *
     */
    public function action_order_clear()
    {
        Session::delete('order');

        $userId = $this->getUserId();
        if ($userId) {
            $this->cartService->deleteCart($userId);
        }

        Session::set("message", ["success", _('Your order has been deleted!')]);

        Response::redirect(lotto_platform_get_permalink_by_slug('order'));
    }

    /**
     *
     * @global array $post
     * @throws Exception
     */
    public function action_order_before()
    {
        $user = Lotto_Settings::getInstance()->get('user'); // get whitelabel_user cache instance
        $is_user = Lotto_Settings::getInstance()->get("is_user"); // get user status (is logged in)
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel"); // get whitelabel for current user

        // US State block
        if ($this->is_us_state_blocked($whitelabel)) {
            $message = _(
                "Your state has been blocked. Please purchase " .
                "your ticket from different state."
            );
            Session::set("message", ["error", $message]);

            Response::redirect(lotto_platform_home_url('/'));
        }

        // check if current user should be blocked (do it only if user is logged).
        // TODO: ?? - additonal security from undefined index - invalid user object.
        // Remove this when we are sure that cache objects will always have keys,
        // even null just for existence sake.
        if (
            $is_user && ($this->is_ip_blocked($whitelabel['id']) ||
                $this->is_country_blocked($user['country'] ?? null, $whitelabel['id']))
        ) {
            // ip or country is blocked - set message and redirect to home page (message will be rendered by home view).
            $message = _("Your country has been blocked.");
            Session::set("message", ["error", $message]);

            Response::redirect(lotto_platform_home_url('/'));
        }

        if ($is_user && ($this->is_ip_allowed($user['id']) === false)) {
            $message = _(
                "You cannot purchase tickets or make a deposit using this account. " .
                "Please logout and login to your personal account to buy tickets."
            );
            Session::set("message", ["error", $message]);

            Response::redirect(lotto_platform_home_url('/'));
        }

        if (get_query_var("action") == "undo") {
            if (!empty(Session::get("order_last_removed"))) {
                $order = Session::get("order");
                if (empty($order)) {
                    $order = [];
                }
                $order[] = Session::get("order_last_removed");
                Session::set("order", $order);
                $userId = $this->getUserId();

                if ($userId) {
                    $this->cartService->createOrUpdateCart($userId, $order);
                }

                $message = _("Your item has been restored!");
                Session::set("message", ["success", $message]);

                Response::redirect(lotto_platform_get_permalink_by_slug('order'));
            }
        }

        global $post;

        $deposit = false;
        $post_id = apply_filters('wpml_object_id', $post->ID, 'page', true, 'en');
        $orig_post = get_post($post_id);
        if ($orig_post != null && $orig_post->post_name == 'deposit') {
            $deposit = true;
        }

        $input_post = Input::post();

        $order = new Forms_Order_Create(
            $whitelabel,
            $user,
            $deposit,
            $is_user,
            $input_post
        );
        $order->process_form();

        $errors = $order->get_errors();

        Lotto_Settings::getInstance()->set('errors', $errors);
    }

    /**
     *
     * @return null Could also exit()
     */
    public function action_order_entropay()
    {
        $is_user = Lotto_Settings::getInstance()->get("is_user");
        if (!$is_user) {
            return;
        }
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        $user_currency = CurrencyHelper::getCurrentCurrency()->to_array();

        $whitelabel_payment_methods_without_currency = Model_Whitelabel_Payment_Method::get_payment_methods_for_whitelabel($whitelabel);
        $whitelabel_payment_methods_with_currency = Helpers_Currency::get_whitelabel_payment_methods_with_currency(
            $whitelabel,
            $whitelabel_payment_methods_without_currency,
            $user_currency
        );

        $whitelabel_payment_method_id = null;

        $found = false;
        foreach ($whitelabel_payment_methods_with_currency as $key => $whitelabel_payment_method) {
            if ($whitelabel_payment_method['payment_method_id'] == Helpers_Payment_Method::ENTROPAY) {
                $whitelabel_payment_method_id = (int)$whitelabel_payment_method['id'];
                $found = $key;
                break;
            }
        }

        if ($found === false) {
            return;
        }

        $whitelabel_payment_method_data = unserialize($whitelabel_payment_methods_with_currency[$key]['data']);

        if (
            empty($whitelabel_payment_method_data['referrer_id']) ||
            !isset($whitelabel_payment_method_data['test'])
        ) {
            return;
        }

        $user = Lotto_Settings::getInstance()->get("user");
        $wlanguage = LanguageHelper::getCurrentWhitelabelLanguage();

        $amount = explode("-", get_query_var("amount"));

        $deposit = Lotto_Settings::getInstance()->get("entropay_deposit");

        $query = [
            'method' => 'start',
            'referrerID' => $whitelabel_payment_method_data['referrer_id'],
            'timestamp' => (new DateTime("now", new DateTimeZone("UTC")))->format('YmdHis'),
            'emailVerified' => $user['is_confirmed'] ? 'true' : 'false',
            'pref_currency' => lotto_platform_user_currency(),
            'pref_language' => $wlanguage['code'],
            'aff_email' => substr($user['email'], 0, 255)
        ];
        if (!empty($user['name'])) {
            $query['aff_firstName'] = remove_accents(mb_substr($user['name'], 0, 30));
        }
        if (!empty($user['surname'])) {
            $query['aff_lastName'] = remove_accents(mb_substr($user['surname'], 0, 30));
        }
        if (!empty($user['birthdate'])) {
            $date = DateTime::createFromFormat("Y-m-d", $user['birthdate']);
            $query['aff_dobDay'] = $date->format('d');
            $query['aff_dobMonth'] = $date->format('m');
            $query['aff_dobYear'] = $date->format('Y');
        }
        if (!empty($user['address_1'])) {
            $query['aff_address1'] = remove_accents(mb_substr($user['address_1'], 0, 60));
        }
        if (!empty($user['address_2'])) {
            $query['aff_address2'] = remove_accents(mb_substr($user['address_2'], 0, 60));
        }
        if (!empty($user['city'])) {
            $query['aff_town'] = remove_accents(mb_substr($user['city'], 0, 60));
        }
        if (!empty($user['state'])) {
            $query['aff_state'] = remove_accents(mb_substr(Lotto_view::get_region_name($user['state'], false), 0, 20));
        }
        if (!empty($user['zip'])) {
            $query['aff_zipCode'] = substr($user['zip'], 0, 10);
        }
        if (!empty($user['country'])) {
            $query['aff_country'] = substr($user['country'], 0, 10);
        }
        if (!empty($user['phone']) && !empty($user['phone_country'])) {
            $phone_country_code = Lotto_View::get_phone_country_code(
                $user['phone'],
                $user['phone_country']
            );

            if (!is_null($phone_country_code)) {
                $query['aff_phoneCountryCode'] = $phone_country_code;

                $query['aff_phoneNumber'] = Lotto_View::format_phone(
                    $user['phone'],
                    $user['phone_country'],
                    true,
                    PhoneNumberFormat::E164
                );
            }
        }

        $query['aff_amountPounds'] = $amount[0];
        $query['aff_amountCents'] = $amount[1];
        $query['returnURL'] = lotto_platform_get_permalink_by_slug($deposit ? 'deposit' : 'order');
        $query['aff_returnCardDetails'] = 'true';

        $amount_n = round($amount[0] * 100);
        $amount_n += $amount[1];
        $amount_n = round($amount_n / 100, 2);

        Session::set("entropay_bp", $amount_n);

        Model_Payment_Log::add_log(
            Helpers_General::TYPE_SUCCESS,
            Helpers_General::PAYMENT_TYPE_OTHER,
            Helpers_Payment_Method::ENTROPAY,
            null,
            $whitelabel['id'],
            null,
            "Redirecting to entropay.com.",
            $query,
            $whitelabel_payment_method_id
        );

        $entropay = View::forge("wordpress/payments/entropay");
        $entropay->set("query", $query);
        $entropay->set("pdata", $whitelabel_payment_method_data);
        $entropay->set("lang", $wlanguage['code']);
        echo $entropay;
        exit();

        /*

          header("Location: https://".($data['test'] ? 'staging' : 'secure2').entropay.com/consumer/u/refer?".http_build_query($query, null, '&', PHP_QUERY_RFC3986));
          exit();

         */
    }

    /**
     *
     * @return View
     */
    public function action_payment_failure()
    {
        $is_user = Lotto_Settings::getInstance()->get("is_user");
        if (!$is_user) {
            return;
        }
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        $transaction = Lotto_Settings::getInstance()->get('transaction');
        $user = Lotto_Settings::getInstance()->get("user");

        $type = "deposit";
        if ((int)$transaction->type === Helpers_General::TYPE_TRANSACTION_PURCHASE) {
            $type = "purchase";
        }

        /** Removed direct Facebook Pixel call; the event is now sent via GTM */
//        if ((int)$transaction->type === Helpers_General::TYPE_TRANSACTION_DEPOSIT) {
//            $userData = lotto_platform_user();
//
//            $pluginService = Container::get(MauticPluginService::class);
//            $pluginService->setWhitelabelUser($userData['id']);
//
//            $pluginData = $pluginService->createDeposit($transaction->id);
//        }

//        $pluginData['transaction_id'] = Lotto_Helper::get_transaction_token($transaction);
//        $pluginData['price'] = $transaction->amount;
//        $pluginData['currency'] = lotto_platform_user_currency();
//        Event::trigger('user_' . $type . '_failure', [
//            'whitelabel_id' => $whitelabel['id'],
//            'user_id' => lotto_platform_user()["id"],
//            'plugin_data' => $pluginData
//        ]);

        $view = View::forge(Lotto_Helper::get_file_template('/box/payment_failure.php'));
        $view->set("transaction", $transaction);

        if ($type == 'deposit') {
            $depositData = [
                'event' => 'deposit_failure',
                'user_id' => $user ? $whitelabel['prefix'] . 'U' . $user['token'] : '',
                'currency' => lotto_platform_user_currency(),
                'value' => $transaction['amount'],
                'payment_method' => WhitelabelPaymentMethod::find($transaction['whitelabel_payment_method_id'])['name']
            ];

            $view->set('depositData', $depositData);
        } else {
            $tickets = Model_Whitelabel_User_Ticket::get_full_data_with_counted_lines($transaction->id, true);
            $lotteries = Model_Lottery::get_really_all_lotteries_for_whitelabel($whitelabel);

            $purchaseDataItems = [];
            foreach ($tickets as $ticket) {
                $lottery = $lotteries['__by_id'][$ticket['lottery_id']];

                $purchaseDataItems[] = [
                    'item_id' => $lottery['slug'],
                    'item_name' => $lottery['name'],
                    'lines' => $ticket['line_count'],
                    'multiplier' => $ticket['ticket_multiplier'],
                    'price' => $ticket['line_price'],
                    'item_variant' => $ticket['multi_draw_id'] ? 'multidraw' : 'single',
                    'discount' => $ticket['multi_draw_id'] ? $ticket['multi_draw_discount'] : 0,
                ];
            }

            $purchaseData = [
                'event' => 'purchase_failure',
                'user_id' => $user ? $whitelabel['prefix'] . 'U' . $user['token'] : '',
                'transaction_id' => $whitelabel['prefix'] . $transaction['token'],
                'value' => $transaction['amount'],
                'currency' => lotto_platform_user_currency(),
                'items' => $purchaseDataItems
            ];

            $view->set('purchaseData', $purchaseData);
        }

        if (!empty(Session::get('additional_text_on_failure_success_page'))) {
            $additional_text_on_failure_success_page = Session::get('additional_text_on_failure_success_page');
            $view->set("additional_text", $additional_text_on_failure_success_page);
            Session::delete('additional_text_on_failure_success_page');
        }

        return $view;
    }

    /**
     *
     * @return View
     */
    public function action_payment_success()
    {
        $is_user = Lotto_Settings::getInstance()->get("is_user");
        if (!$is_user) {
            return;
        }

        $user = Lotto_Settings::getInstance()->get("user");
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        $transaction = Lotto_Settings::getInstance()->get('transaction');

        $view = View::forge(Lotto_Helper::get_file_template('/box/payment_success.php'));

        $currencies = Lotto_Settings::getInstance()->get("currencies");

        $view->set('currencies', $currencies);

        $is_free_ticket = Model_Whitelabel_User_Promo_Code::is_free_ticket_transaction($transaction->id);
        $accountlink = lotto_platform_get_permalink_by_slug('account');
        $my_tickets_link = $accountlink . 'tickets/awaiting';
        $view->set('my_tickets_link', $my_tickets_link);
        $view->set('is_free_ticket', $is_free_ticket);

        if ((int)$transaction->type === Helpers_General::TYPE_TRANSACTION_PURCHASE) {
            $tickets = Model_Whitelabel_User_Ticket::get_full_data_with_counted_lines($transaction->id, true);

            $lotteries = Model_Lottery::get_really_all_lotteries_for_whitelabel($whitelabel);

            $items = [];
            $purchaseDataItems = [];

            $lastPurchasedLotteryName = '';
            foreach ($tickets as &$ticket) {
                $lottery = $lotteries['__by_id'][$ticket['lottery_id']];

                $items[] = [
                    "id" => $whitelabel['prefix'] . '_' . Lotto_Helper::get_lottery_short_name($lottery) . '_TICKET',
                    "name" => $lottery['name'],
                    "list_name" => "Thank You Page",
                    "quantity" => $ticket['line_count'],
                    "price" => $ticket['line_price'],
                    "currency" => lotto_platform_user_currency()
                ];

                $purchaseDataItems[] = [
                    'item_id' => $lottery['slug'],
                    'item_name' => $lottery['name'],
                    'lines' => $ticket['line_count'],
                    'multiplier' => $ticket['ticket_multiplier'],
                    'price' => $ticket['line_price'],
                    'item_variant' => $ticket['multi_draw_id'] ? 'multidraw' : 'single',
                    'discount' => $ticket['multi_draw_id'] ? $ticket['multi_draw_discount'] : 0,
                ];

                $lastPurchasedLotteryName = $lottery['name'];
            }

            Event::trigger('user_purchase_success', [
                'whitelabel_id' => $whitelabel['id'],
                'whitelabel_theme' => $whitelabel['theme'],
                'user_id' => lotto_platform_user()["id"],
                'plugin_data' => [
                    'transaction_id' => Lotto_Helper::get_transaction_token($transaction),
                    'price' => $transaction->amount,
                    'currency' => lotto_platform_user_currency(),
                    'items' => $items,
                    'last_purchase_lottery' => $lastPurchasedLotteryName
                ]
            ]);

            $purchaseData = [
                'event' => 'purchase',
                'user_id' => $user ? $whitelabel['prefix'] . 'U' . $user['token'] : '',
                'transaction_id' => $whitelabel['prefix'] . $transaction['token'],
                'value' => $transaction['amount'],
                'currency' => lotto_platform_user_currency(),
                'items' => $purchaseDataItems
            ];

            $view->set('purchaseData', $purchaseData);
            $view->set('tickets', $tickets);
            $view->set('lotteries', $lotteries);
        } else {
//            Removed direct Facebook Pixel call; the event is now sent via GTM
//            Event::trigger('user_deposit_success', [
//                'whitelabel_id' => $whitelabel['id'],
//                'user_id' => lotto_platform_user()["id"],
//                'plugin_data' => [
//                    "transaction_id" => Lotto_Helper::get_transaction_token($transaction),
//                    "price" => $transaction->amount,
//                    "currency" => lotto_platform_user_currency(),
//                    "items" => [[
//                        "id" => $whitelabel['prefix'] . '_DEPOSIT',
//                        "name" => "Deposit",
//                        "list_name" => "Thank You Page",
//                        "quantity" => 1,
//                        "price" => $transaction->amount,
//                        "currency" => lotto_platform_user_currency()
//                    ]]
//                ]
//            ]);

            $depositData = [
                'event' => 'deposit',
                'user_id' => $user ? $whitelabel['prefix'] . 'U' . $user['token'] : '',
                'currency' => lotto_platform_user_currency(),
                'value' => $transaction['amount'],
                'payment_method' => WhitelabelPaymentMethod::find($transaction['whitelabel_payment_method_id'])['name']
            ];

            $view->set('depositData', $depositData);
        }

        $view->set("transaction", $transaction);

        if (!empty(Session::get('additional_text_on_failure_success_page'))) {
            $additional_text_on_failure_success_page = Session::get('additional_text_on_failure_success_page');
            $view->set("additional_text", $additional_text_on_failure_success_page);
            Session::delete('additional_text_on_failure_success_page');
        }

        return $view;
    }

    /**
     * This is special function is to confirm Stripe notification
     * about payment for every whitelabel.
     * $whitelabel_payment_method_id should be given within section
     * to make possible to confirm that payment
     */
    public function action_order_confirm_stripe()
    {
        // remove wordpress output
        ob_clean();

        $whitelabel_payment_method_id = get_query_var("section");
        if (empty($whitelabel_payment_method_id)) {
            $this->fileLoggerService->error(
                "Lack of whitelabel_payment_method_id (in section)"
            );
            exit("There is a problem on server.");
        }

        $stripe_form = new Forms_Wordpress_Payment_Stripe();
        $stripe_form->prepare_settings_for_confirmation_all_whitelabels($whitelabel_payment_method_id);
        $stripe_result = $stripe_form->check_payment_result();

        if (is_array($stripe_result)) {
            $transaction = $stripe_result['transaction'];
            $out_id = $stripe_result['out_id'];
            $data = $stripe_result['data'];

            $whitelabel = $stripe_form->get_whitelabel();

            $accept_transaction_result = Lotto_Helper::accept_transaction(
                $transaction,
                $out_id,
                $data,
                $whitelabel
            );

            // Now transaction returns result as INT value and
            // we can redirect user to fail page or success page
            // or simply inform system about that fact
            if ($accept_transaction_result === Forms_Transactions_Accept::RESULT_WITH_ERRORS) {
                status_header(400);
            }
        }

        // This is a little tricky because
        // normally method should return Response
        // but because of the purpose it should return nothing
        exit();
    }

    public function action_order_confirm_pspgate()
    {
        // remove wordpress output
        ob_clean();

        $whitelabel_payment_method_id = get_query_var("section");
        if (empty($whitelabel_payment_method_id)) {
            $this->fileLoggerService->error(
                'Lack of whitelabel_payment_method_id (in section) - PSPGATE'
            );
            exit("There is a problem on server.");
        }

        $transaction = null;
        $data = [];
        $out_id = null;

        $pspGateReceiver = new Helpers_Payment_PspGate_Receiver(null);
        $pspGateReceiver->set_whitelabel_payment_method_id($whitelabel_payment_method_id);
        $ok = $pspGateReceiver->confirm_payment(
            $transaction,
            $out_id,
            $data
        );

        if ($transaction !== null && $ok) {
            $whitelabel = $pspGateReceiver->getWhitelabel();
            if (!empty($whitelabel)) {
                $accept_transaction_result = Lotto_Helper::accept_transaction(
                    $transaction,
                    $out_id,
                    $data,
                    $whitelabel
                );
            } else {
                $this->fileLoggerService->error(
                    'PSPGATE whitelotto order confirm - could not retrieve whitelabel from transaction'
                );
                $accept_transaction_result = Forms_Status::RESULT_WITH_ERRORS;
            }

            // Now transaction returns result as INT value and
            // we can redirect user to fail page or success page
            // or simply inform system about that fact
            if ($accept_transaction_result === Forms_Transactions_Accept::RESULT_WITH_ERRORS) {
                status_header(400);
            }
        }

        // This is a little tricky because
        // normally method should return Response
        // but because of the purpose it should return nothing
        exit();
    }

    public function action_order_confirm_onramper()
    {
        // remove wordpress output
        ob_clean();

        $whitelabel_payment_method_id = get_query_var("section");
        if (empty($whitelabel_payment_method_id)) {
            $this->fileLoggerService->error(
                'Lack of whitelabel_payment_method_id (in section) - Onramper'
            );
            exit("There is a problem on server.");
        }

        /** @var Model_Whitelabel_Transaction|null $transaction */
        $transaction = null;
        $data = [];
        $out_id = null;

        $onramperReceiver = new Helpers_Payment_Onramper_Receiver(null);
        $onramperReceiver->set_whitelabel_payment_method_id($whitelabel_payment_method_id);
        $ok = $onramperReceiver->confirm_payment(
            $transaction,
            $out_id,
            $data
        );

        if ($transaction !== null && $ok) {
            $whitelabel = $onramperReceiver->getWhitelabel();
            if (!empty($whitelabel)) {
                $accept_transaction_result = Lotto_Helper::accept_transaction(
                    $transaction,
                    $out_id,
                    $data,
                    $whitelabel
                );
            } else {
                $transactionId = $transaction->id;
                $this->fileLoggerService->error(
                    "Onramper whitelotto order confirm - could not retrieve whitelabel from transaction ID #$transactionId"
                );
                $accept_transaction_result = Forms_Status::RESULT_WITH_ERRORS;
            }

            // Now transaction returns result as INT value and
            // we can redirect user to fail page or success page
            // or simply inform system about that fact
            if ($accept_transaction_result === Forms_Transactions_Accept::RESULT_WITH_ERRORS) {
                status_header(400);
            }
        }

        // This is a little tricky because
        // normally method should return Response
        // but because of the purpose it should return nothing
        exit();
    }

    public function action_order_confirm_lenco()
    {
        // remove wordpress output
        ob_clean();

        $whitelabel_payment_method_id = get_query_var("section");
        if (empty($whitelabel_payment_method_id)) {
            $this->fileLoggerService->error(
                'Lack of whitelabel_payment_method_id (in section) - Lenco'
            );
            exit("There is a problem on server.");
        }

        /** @var Model_Whitelabel_Transaction|null $transaction */
        $transaction = null;
        $data = [];
        $out_id = null;

        $lencoReceiver = new Helpers_Payment_Lenco_Receiver(null);
        $lencoReceiver->set_whitelabel_payment_method_id($whitelabel_payment_method_id);
        $ok = $lencoReceiver->confirm_payment(
            $transaction,
            $out_id,
            $data
        );

        if ($transaction !== null && $ok) {
            $whitelabel = $lencoReceiver->getWhitelabel();
            if (!empty($whitelabel)) {
                $accept_transaction_result = Lotto_Helper::accept_transaction(
                    $transaction,
                    $out_id,
                    $data,
                    $whitelabel
                );
            } else {
                $transactionId = $transaction->id;
                $this->fileLoggerService->error(
                    "Lenco whitelotto order confirm - could not retrieve whitelabel from transaction ID #$transactionId"
                );
                $accept_transaction_result = Forms_Status::RESULT_WITH_ERRORS;
            }

            // Now transaction returns result as INT value and
            // we can redirect user to fail page or success page
            // or simply inform system about that fact
            if ($accept_transaction_result === Forms_Transactions_Accept::RESULT_WITH_ERRORS) {
                status_header(400);
            }
        }

        // This is a little tricky because
        // normally method should return Response
        // but because of the purpose it should return nothing
        exit();
    }

    /**
     *
     */
    public function action_order_confirm_flutterwave()
    {
        // remove wordpress output
        ob_clean();

        $whitelabel_payment_method_id = get_query_var("section");
        if (empty($whitelabel_payment_method_id)) {
            $this->fileLoggerService->error(
                "Lack of whitelabel_payment_method_id (in section)"
            );
            exit("There is a problem on server.");
        }

        $flutterwave_form = new Forms_Wordpress_Payment_Flutterwave();
        $flutterwave_form->prepare_settings_for_confirmation_all_whitelabels($whitelabel_payment_method_id);
        $flutterwave_result = $flutterwave_form->check_payment_result();

        if (is_array($flutterwave_result)) {
            $transaction = $flutterwave_result['transaction'];
            $out_id = $flutterwave_result['out_id'];
            $data = $flutterwave_result['data'];

            $whitelabel = $flutterwave_form->get_whitelabel();

            $accept_transaction_result = Lotto_Helper::accept_transaction(
                $transaction,
                $out_id,
                $data,
                $whitelabel
            );

            // Now transaction returns result as INT value and
            // we can redirect user to fail page or success page
            // or simply inform system about that fact
            if ($accept_transaction_result === Forms_Transactions_Accept::RESULT_WITH_ERRORS) {
                status_header(400);
            }
        }

        // This is a little tricky because
        // normally method should return Response
        // but because of the purpose it should return nothing
        exit();
    }

    public function action_order_confirm_flutterwave_africa()
    {
        // remove wordpress output
        ob_clean();

        $whitelabel_payment_method_id = get_query_var("section");
        if (empty($whitelabel_payment_method_id)) {
            $this->fileLoggerService->error(
                "Lack of whitelabel_payment_method_id (in section)"
            );
            exit("There is a problem on server.");
        }

        $flutterwave_form = new Forms_Wordpress_Payment_FlutterwaveAfrica();
        $flutterwave_form->prepare_settings_for_confirmation_all_whitelabels($whitelabel_payment_method_id);
        $flutterwave_result = $flutterwave_form->check_payment_result();

        if (is_array($flutterwave_result)) {
            $transaction = $flutterwave_result['transaction'];
            $out_id = $flutterwave_result['out_id'];
            $data = $flutterwave_result['data'];

            $whitelabel = $flutterwave_form->get_whitelabel();

            $accept_transaction_result = Lotto_Helper::accept_transaction(
                $transaction,
                $out_id,
                $data,
                $whitelabel
            );

            // Now transaction returns result as INT value and
            // we can redirect user to fail page or success page
            // or simply inform system about that fact
            if ($accept_transaction_result === Forms_Transactions_Accept::RESULT_WITH_ERRORS) {
                status_header(400);
            }
        }

        // This is a little tricky because
        // normally method should return Response
        // but because of the purpose it should return nothing
        exit();
    }

    /**
     *
     */
    public function action_order_confirm()
    {
        // remove wordpress output
        ob_clean();

        $ip = Lotto_Security::get_IP();
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        $whitelabel_id = null;
        if (!empty($whitelabel['id'])) {
            $whitelabel_id = $whitelabel['id'];
        }

        $section = get_query_var("section");
        $whitelabel_payment_method_id = get_query_var("id");

        if (empty($section) || empty($whitelabel_payment_method_id)) {
            status_header(400);
            exit();
        }

        $confirm = new Forms_Order_Confirm(
            $whitelabel,
            $ip,
            $section,
            $whitelabel_payment_method_id
        );
        $confirm->process_form();
    }

    public function action_logout()
    {
        UserHelper::logOutUser();
        Session::set('message', ['success', _('You have been successfully logged out!')]);

        Response::redirect(lotto_platform_home_url('/'));
    }

    public function action_login(): string
    {
        /** @var WordpressLoginService $wordpressLoginService */
        $wordpressLoginService = Container::get(WordpressLoginService::class);
        $wordpressLoginService->login();
        return $wordpressLoginService->view();
    }

    /**
     * NOTE: virutal route - this method doesn't use route defined in config
     *
     * @return null|View
     */
    public function action_myaccount_remove(): ?View
    {
        // critical check if user is present
        $is_user = Lotto_Settings::getInstance()->get("is_user");
        if (!$is_user) {
            return null;
        }

        // render view
        $view = View::forge(Lotto_Helper::get_file_template('/box/myaccount_remove.php'));

        // get errors or create them if they are empty, create messages
        $errors = Lotto_Settings::getInstance()->get("errors");
        if (empty($errors)) {
            $errors = [];
        }

        // critical check token
        if (!Security::check_token()) {
            $errors = ['profile' => _('Security error! Please try again.')];
            $view->set("errors", $errors);
            return $view;
        }

        $messages = FlashMessageHelper::getAll();

        // set messages into view
        // TODO: maybe use presenter for preparation logic.
        $view->set("messages", $messages);
        $view->set("errors", $errors);

        return $view;
    }

    /**
     *
     */
    public function action_myaccount_remove_before(): void
    {
        // get user and user model
        $user = lotto_platform_user();
        $user_model = Model_Whitelabel_User::find_by_pk($user['id']);

        // critical check user model
        if ($user_model === null) {
            Response::redirect(lotto_platform_home_url('/'));
        }

        if ($this->is_ip_allowed($user['id']) === false) {
            $message = _("You cannot delete your account.");
            Session::set("message", ["error", $message]);

            Response::redirect(lotto_platform_home_url('/'));
        }

        // everything ok - confirm password
        // TODO: change logic into validate in one object (better yet trait)
        // and delete logic in other object.
        $confirmation_password = new Forms_Wordpress_Myaccount_Remove_Confirmationpassword($user_model);

        $result = $confirmation_password->process_form();

        if ($result === Forms_Wordpress_Myaccount_Remove_Confirmationpassword::RESULT_OK) {
            Response::redirect(lotto_platform_home_url('/'));
        }

        $messages = $confirmation_password->get_messages();
        $errors = $confirmation_password->get_errors();

        if (!empty($messages)) {
            FlashMessageHelper::addMany($messages);
        }
        Lotto_Settings::getInstance()->set('errors', $errors);
    }

    /**
     *
     * @return void
     */
    public function action_myaccount_ticket_playagain_before(): void
    {
        $user = lotto_platform_user();
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        $ticket_playagain_obj = new Forms_Wordpress_Myaccount_Ticket_Playagain(
            $whitelabel,
            $user
        );

        $result = $ticket_playagain_obj->process_form();

        switch ($result) {
            case Forms_Wordpress_Myaccount_Ticket_Playagain::RESULT_OK:     // OK
                Response::redirect(lotto_platform_get_permalink_by_slug('order'));
                break;
            case Forms_Wordpress_Myaccount_Ticket_Playagain::RESULT_WITH_ERRORS:     // Different errors
                $errors = $ticket_playagain_obj->get_errors();
                Lotto_Settings::getInstance()->set('errors', $errors);
                break;
            case Forms_Wordpress_Myaccount_Ticket_Playagain::RESULT_ZERO_TICKET_LINES:     // count $ticket_lines = 0
                break;
        }
    }

    /**
     *
     * @return void
     */
    public function action_myaccount_withdrawal_before(): void
    {
        $accountlink = lotto_platform_get_permalink_by_slug('account');
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        $user = lotto_platform_user();
        $auser = Model_Whitelabel_User::find_by_pk($user['id']);

        if ($this->is_ip_allowed($user['id']) === false) {
            $message = _("You cannot request a withdrawal using this account.");
            Session::set("message", ["error", $message]);

            Response::redirect(lotto_platform_home_url('/'));
        }

        $withdrawal_form = new Forms_Wordpress_Myaccount_Withdrawal(
            $whitelabel,
            $user,
            $auser,
            $accountlink
        );
        $result = $withdrawal_form->process_form();

        switch ($result) {
            case Forms_Wordpress_Myaccount_Withdrawal::RESULT_OK:
                Response::redirect($accountlink . 'withdrawal/');
                break;
            case Forms_Wordpress_Myaccount_Withdrawal::RESULT_WITH_ERRORS:
                $errors = $withdrawal_form->get_errors();
                Lotto_Settings::getInstance()->set('errors', $errors);
                break;
            case Forms_Wordpress_Myaccount_Withdrawal::RESULT_GO_FURTHER:
            case Forms_Wordpress_Myaccount_Withdrawal::RESULT_GO_TO_NEXT_STEP:
                break;
        }
    }

    /**
     *
     */
    public function action_myaccount_payments_before(): void
    {
        $accountlink = lotto_platform_get_permalink_by_slug('account');
        $user = lotto_platform_user();
        $saved_cards = Lotto_Helper::get_e_merchant_pay_saved_cards($user['id']);

        $id = get_query_var("id");
        if (!empty($id) && isset($saved_cards[$id - 1])) {
            // there is no native function in emerchantpay to delete a card from their system
            // so we need to keep the flag on our side
            $saved_cards[$id - 1]
                ->set(["is_deleted" => 1])
                ->save();

            $msg = _("Your card has been removed!");

            Session::set("message", ["success", $msg]);

            Response::redirect($accountlink . 'payments/');
        } else {
            $errors = ["details" => _("Wrong card!")];
            Lotto_Settings::getInstance()->set('errors', $errors);
        }
    }

    /**
     *
     * @param int $source_function  1 - call from action_lostpassword_before(),
     *                              2 - call from action_check_lostpassword()
     * @param string $token
     * @param string $hash
     * @return void
     */
    private function lost_password_new(
        int $source_function,
        string $token = "",
        string $hash = ""
    ): void {
        $lostpassword_new = new Forms_Wordpress_User_Lostpassword_New();
        if (!empty($token)) {
            $lostpassword_new->set_token($token);
        }

        if (!empty($hash)) {
            $lostpassword_new->set_hash($hash);
        }

        $result = Forms_Wordpress_User_Lostpassword_New::RESULT_OK;
        if ($source_function === 1) {
            $result = $lostpassword_new->process_form();
        } else {
            $result = $lostpassword_new->process_form_second_step();
        }

        $errors = $lostpassword_new->get_errors();

        switch ($result) {
            case Forms_Wordpress_User_Lostpassword_New::RESULT_OK:
                Response::redirect(lotto_platform_home_url('/'));
                break;
            case Forms_Wordpress_User_Lostpassword_New::RESULT_GO_FURTHER:
                break;
            case Forms_Wordpress_User_Lostpassword_New::RESULT_INCORRECT_USER:
            case Forms_Wordpress_User_Lostpassword_New::RESULT_LINK_EXPIRED:
            case Forms_Wordpress_User_Lostpassword_New::RESULT_DB_ERROR:
                Response::redirect(lotto_platform_home_url('/'));
                break;
            case Forms_Wordpress_User_Lostpassword_New::RESULT_WITH_ERRORS:
                break;
        }

        Lotto_Settings::getInstance()->set("login_errors", $errors);
    }

    /**
     *
     */
    public function action_lostpassword_before()
    {
        $is_user = Lotto_Settings::getInstance()->get('is_user');
        if ($is_user) {
            Response::redirect(lotto_platform_home_url('/'));
        }

        $token = get_query_var("id");
        $hash = get_query_var("hash");

        if (empty($token) || empty($hash)) {
            $errors = Lotto_Settings::getInstance()->get("login_errors");
            Lotto_Settings::getInstance()->set("login_errors", $errors);
            return ;
        }

        $this->lost_password_new(1, $token, $hash);

        if (
            Input::post("lost") != null &&
            !empty(Input::post("lost.newpassword")) &&
            (int)Input::post("lost.newpassword") === 1
        ) {
            $this->lost_password_new(2);
        }
    }

    /**
     *
     * @return void
     */
    private function lost_password_request(): void
    {
        $check_lostpassword_request = new Forms_Wordpress_User_Lostpassword_Request();
        $result = $check_lostpassword_request->process_form();

        $errors = $check_lostpassword_request->get_errors();

        switch ($result) {
            case Forms_Wordpress_User_Lostpassword_Request::RESULT_OK:
                Response::redirect(lotto_platform_home_url('/'));
                break;
            case Forms_Wordpress_User_Lostpassword_Request::RESULT_DB_ERROR:
            case Forms_Wordpress_User_Lostpassword_Request::RESULT_EMAIL_NOT_SENT:
                Response::redirect(lotto_platform_home_url('/'));
                break;
            case Forms_Wordpress_User_Lostpassword_Request::RESULT_GO_FURTHER:
                break;
            case Forms_Wordpress_User_Lostpassword_Request::RESULT_WITH_ERRORS:
                break;
        }

        Lotto_Settings::getInstance()->set("login_errors", $errors);
    }

    /**
     *
     */
    public function action_check_lostpassword()
    {
        $is_user = Lotto_Settings::getInstance()->get("is_user");

        // Property LOST in POST body exists only on page /auth/lostpassword after sending the form
        if ($is_user || (Input::post("lost") == null)) {
            $errors = Lotto_Settings::getInstance()->get("login_errors");
            Lotto_Settings::getInstance()->set("login_errors", $errors);
            return;
        }

        if (
            !empty(Input::post("lost.request")) &&
            (int)Input::post("lost.request") === 1
        ) {
            $this->lost_password_request();
        } elseif (
            !empty(Input::post("lost.newpassword")) &&
            (int)Input::post("lost.newpassword") === 1
        ) {
            // Here nothing to do!
        } else {
            $errors = Lotto_Settings::getInstance()->get("login_errors");
            Lotto_Settings::getInstance()->set("login_errors", $errors);
            return;
        }
    }

    /**
     *
     * @return View
     */
    public function action_lostpassword()
    {
        $is_user = Lotto_Settings::getInstance()->get("is_user");
        if ($is_user) {
            return;
        }

        $view = View::forge(Lotto_Helper::get_file_template('/box/lostpassword.php'));

        $step = Lotto_Settings::getInstance()->get("lostpasswordstep");

        if ($step !== null) {
            $view->set("step", $step);
        }
        $view->set("errors", Lotto_Settings::getInstance()->get("login_errors"));

        return $view;
    }

    /** @return View */
    public function action_activation()
    {
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        $id = get_query_var('id');
        $hash = get_query_var('hash');
        $isEmailChanged = Input::get('type') === 'email_change';

        $auser = Model_Whitelabel_User::find([
            'where' => [
                'whitelabel_id' => $whitelabel->id,
                'token' => $id
            ]
        ]);

        $activationService = Container::get(ActivationService::class);
        try {
            if ($activationService->isSocialActivation()) {
                $activationService->startSocialAccountActivation();
            }
        } catch (Throwable $exception) {
            /**
             * When user after normal registration is trying to activate account AND has added manually socialName parameter to url,
             * we do not want to log this as error. This behaviour is blocked.
             */
            if (!is_null($auser) && $auser[0]['activation_hash'] !== $hash && $activationService->isSocialActivation()) {
                $this->fileLoggerService->error($exception->getMessage());
            }
            FlashMessageHelper::set(FlashMessageHelper::TYPE_ERROR, MessageHelper::getTranslatedActivationSecureError(), true);
            UrlHelper::redirectToHomepage();
        }

        $is_user = Lotto_Settings::getInstance()->get("is_user");
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        $user = Lotto_Settings::getInstance()->get("user");
        $wlang = LanguageHelper::getCurrentWhitelabelLanguage();
        if (
            $is_user && !$isEmailChanged &&
            ((int)$whitelabel['user_activation_type'] !== Helpers_General::ACTIVATION_TYPE_OPTIONAL ||
                (int)$user['is_confirmed'] === 1)
        ) {
            Response::redirect(lotto_platform_home_url('/'));
            return;
        }

        if (
            $auser !== null &&
            count($auser) > 0 &&
            (int)$auser[0]['whitelabel_id'] === (int)$whitelabel['id'] &&
            (int)$auser[0]['is_deleted'] === 0 &&
            !empty($hash) &&
            (string)$auser[0]['activation_hash'] === (string)$hash
        ) {
            $auser = $auser[0];
            $activation_valid = new DateTime($auser['activation_valid'], new DateTimeZone("UTC"));
            $now = new DateTime("now", new DateTimeZone("UTC"));
            $pendingEmail = $auser['pending_email'];

            if ($activation_valid >= $now) {
                if (
                    (int)$auser['is_active'] === 0 ||
                    (int)$auser['is_confirmed'] === 0
                ) {
                    $user_set = [
                        'is_active' => 1,
                        'is_confirmed' => 1,
                        'last_update' => $now->format("Y-m-d H:i:s")
                    ];

                    if ($isEmailChanged && !empty($pendingEmail)) {
                        $user_set['email'] = $pendingEmail;
                        $user_set['pending_email'] = null;
                    }

                    $auser->set($user_set);
                    $auser->save();

                    if (isset($auser['connected_aff_id'])) {
                        $aff = Model_Whitelabel_Aff::find_by_pk($auser['connected_aff_id']);

                        if (
                            $aff !== null &&
                            (int)$aff->is_deleted === 0 &&
                            ((int)$aff->is_active === 0 ||
                                (int)$aff->is_confirmed === 0)
                        ) {
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
                        }
                    }

                    Event::trigger('user_account_confirm', [
                        'whitelabel_id' => $whitelabel['id'],
                        'user_id' => $auser['id'],
                        'plugin_data' => array_merge($user_set, [
                            "token" => Lotto_Helper::get_user_token($auser),
                        ]),
                    ]);

                    Lotto_Security::reset_IP();

                    // Send welcome email to user
                    $email_data = [
                    ];

                    $email_helper = new Helpers_Mail($whitelabel, $auser);
                    $email_helper->send_welcome_email(
                        $isEmailChanged ? 0 : $auser['sent_welcome_mail'],
                        $auser['email'],
                        $wlang['code'],
                        $email_data
                    );

                    $message = _('Your account has been activated. Please login to access your account.');
                    Session::set("message", ["success", $message]);

                    Response::redirect(lotto_platform_get_permalink_by_slug('activated'));
                } else {
                    $message = _(
                        "Your account has been activated before. " .
                        "Please login to access your account."
                    );
                    Session::set("message", ["success", $message]);

                    Response::redirect(lotto_platform_home_url('/'));
                }
            } else {
                $time_date = new DateTime("now", new DateTimeZone("UTC"));
                $resend_hash = Lotto_Security::generate_time_hash($auser['salt'], $time_date);

                $auser->set(['resend_hash' => $resend_hash]);
                $auser->save();

                $resend_link = lotto_platform_home_url('/');
                $resend_link = $resend_link . 'resend/' . $auser->token . '/' . $resend_hash;

                $message_part = _(
                    'Your activation link has expired. Please <a href="%s">' .
                    'try to resend</a> activation e-mail or contact us.'
                );
                $message = sprintf($message_part, $resend_link);
                Session::set("message", ["error", $message]);

                Response::redirect(lotto_platform_home_url('/'));
            }
        } else {
            $message = _("Incorrect activation link. Please contact us for manual activation.");
            Session::set("message", ["error", $message]);

            Response::redirect(lotto_platform_home_url('/'));
        }
    }

    /** @deprecated - should be removed in future versions*/
    public function action_gresend(): void
    {
        $this->action_resend();
    }

    public function action_resend(): void
    {
        /** @var UserActivationService $userActivationService */
        $userActivationService = Container::get(UserActivationService::class);
        $userActivationService->resendActivationEmail();
    }

    /**
     *
     * @return View
     */
    public function action_register()
    {
        $errors = Lotto_Settings::getInstance()->get("login_errors");
        /** @var FormService $socialConnectFormService */
        $socialConnectFormService = Container::get(FormService::class);
        /**
         * We use sign up form on page-last-steps.php (this page is created for sign up with social media. User can fill custom whitelabel data which we not download from social media).
         * Page with last steps won't work without social name parameter.
         * Below function shows sign up form error from last steps page.
         */
        $socialConnectFormService->loadFormErrorOnLastSteps($errors);
        $view = View::forge(Lotto_Helper::get_file_template('/box/register.php'));
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        if ((int)$whitelabel['can_user_register_via_site'] !== 1) {
            Response::redirect(lotto_platform_home_url());
        }

        $whitelabel_default_currencies = Model_Whitelabel_Default_Currency::get_all_by_whitelabel($whitelabel, "c.code");
        $is_register_bonus_active = Model_Whitelabel_Campaign::is_active_register($whitelabel['id']);
        $whitelabel_selectable_user_groups = Model_Whitelabel_User_Group::get_all_selectable_by_whitelabel($whitelabel['id']);
        $default_whitelabel_user_group = Model_Whitelabel_User_Group::get_default_not_selectable_for_whitelabel($whitelabel['id']);

        if (count($default_whitelabel_user_group) > 0 && count($whitelabel_selectable_user_groups) > 0) {
            array_push($whitelabel_selectable_user_groups, $default_whitelabel_user_group);
        }

        $view->set("show_captcha", true);
        $view->set("errors", $errors);
        $view->set("default_currencies", $whitelabel_default_currencies);
        $view->set("bonus_active", $is_register_bonus_active);
        $view->set("user_groups", $whitelabel_selectable_user_groups);

        return $view;
    }

    /**
     *
     */
    public function action_check_register()
    {
        $register_form = new Forms_Wordpress_User_Register();
        $register_form->process_form();
        $errors = $register_form->get_errors();

        Lotto_Settings::getInstance()->set("login_errors", $errors);
    }

    public function action_casinoSitemap()
    {
        header('Content-Type: application/xml');

        $domain = Lotto_Helper::getWhitelabelDomainFromUrl();
        $casinoPrefix = UrlHelper::getCasinoPrefixForWhitelabel($domain);

        echo ViewHelper::render('casino/Sitemap', [
            'casinoPrefix' => $casinoPrefix,
            'domain' => $domain,
        ]);
        exit();
    }
}
