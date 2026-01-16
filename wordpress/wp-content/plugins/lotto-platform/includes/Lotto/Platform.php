<?php

use Carbon\Carbon;
use Exceptions\WrongLotteryNumbersException;
use Fuel\Core\Event;
use Fuel\Core\Input;
use Fuel\Core\Session;
use Fuel\Tasks\Seeders\Wordpress\FaireumDepositAndWithdrawalInstructionsPage;
use Helpers\{CurrencyHelper,
    RouteHelper,
    TransactionTokenEncryptorHelper,
    UrlHelper,
    CaptchaHelper,
    CountryHelper,
    AssetHelper,
    UserHelper,
    Wordpress\LanguageHelper,
    Wordpress\PageEditorHelper,
    Wordpress\PageHelper};
use Fuel\Core\Response;
use Models\WordpressTag;
use Repositories\LotteryRepository;
use Repositories\WhitelabelRepository;
use Repositories\WhitelabelTransactionRepository;
use Services\CartService;
use Services\PageCacheService;
use Services\QuickPickService;
use Services\RedirectService;
use Services\WordpressApiService;
use Modules\Payments\PaymentStatus;
use Modules\Payments\PaymentUrlHelper;
use Services\Shared\Logger\LoggerContract;
use Repositories\Orm\TransactionRepository;
use Yoast\WP\SEO\Presentations\Indexable_Post_Type_Presentation;
use Models\Language;
use Models\Whitelabel;
use Validators\Rules\PrefixedToken;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Classes\Orm\Criteria\With\Model_Orm_Criteria_With_Relation;
use Fuel\Core\Fuel;
use Fuel\Core\Config;
use Fuel\Core\Security;
use Models\RaffleDraw;
use Services\Auth\AutoLoginService;
use Services\Logs\FileLoggerService;
use LanguageHelper as BasicLanguageHelper;

if (!defined('WPINC')) {
    die;
}

class Lotto_Platform
{
    /**
     * @var array
     */
    private $admin_notices = [];
    private RedirectService $redirectService;
    private WordpressApiService $wordpressApiService;
    private PageCacheService $pageCacheService;
    private FileLoggerService $fileLoggerService;
    private CartService $cartService;
    private const SLUGS_TO_EXCLUDE = ['account', 'last-steps'];

    private const SLUGS_EXCLUDED_FROM_PAGE_CACHE = [
        'account',
        'auth',
        'login',
        'lostpassword',
        'signup',
        'activation',
        'activated',
        'deposit',
        'failure',
        'success',
        'order',
        'play-purchase',
        'purchase',
    ];

    public function __construct()
    {
        $this->redirectService = Container::get(RedirectService::class);
        $this->wordpressApiService = Container::get(WordpressApiService::class);
        $this->pageCacheService = Container::get(PageCacheService::class);
        $this->fileLoggerService = Container::get(FileLoggerService::class);
        $this->cartService = Container::get(CartService::class);
    }

    /**
     * @param string $page
     *
     * @return bool
     * @global array $post
     */
    public function is_parent_page($page)
    {
        global $post;
        if ($post != null && !empty($post->post_parent)) {
            $id = apply_filters('wpml_object_id', $post->post_parent, 'page', true, 'en');
            $orig_post = get_post($id);
            if ($orig_post != null && $orig_post->post_name == $page) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $page
     *
     * @return bool
     * @global array $post
     */
    public static function is_page($page, ?string $domain = null)
    {
        global $post;

        if ($post != null) {
            $id = apply_filters('wpml_object_id', $post->ID, 'page', true, 'en');
            $orig_post = get_post($id);

            if ($domain !== null) {
                $page = RouteHelper::getValidCasinoSlugForDomain($page, $domain);
            }

            if ($orig_post != null && $orig_post->post_name == $page) {
                return true;
            }
        }

        return false;
    }

    /**
     * Used to handle posts on casino and lottery pages.
     * Allows opening post in "casino-news" category through casino.lottopark.loc/post-name
     * The same post through lottopark.loc/post-name will redirect to home page
     */
    private static function isCasinoPost(): bool
    {
        global $post;

        if ($post != null) {
            $id = apply_filters('wpml_object_id', $post->ID, 'post', true);
            $orig_post = get_post($id);

            $casinoNewsCategory = get_term_by('slug', Lotto_Widget_News::CASINO_NEWS_CATEGORY_SLUG, 'category');
            if (empty($casinoNewsCategory->term_id)) {
                return false;
            }
            $translatedCategoryId = apply_filters( 'wpml_object_id', $casinoNewsCategory->term_id, 'category');
            $isCurrentCasinoPost = in_category($translatedCategoryId);
            if ($orig_post != null && $isCurrentCasinoPost) {
                return true;
            }
        }

        return false;
    }

    public function load()
    {
        $required = array('jquery');
        if (Lotto_Settings::getInstance()->get("load_tablesorter") == true) {
            $required[] = 'jquery-tablesorter';
            wp_enqueue_script(
                'jquery-tablesorter',
                LOTTO_PLUGIN_URL . 'public/js/jquery.tablesorter.min.js',
                array('jquery'),
                false,
                true
            );
        }

        if (Lotto_Settings::getInstance()->get("load_datepicker") == true) {
            wp_enqueue_script('jquery-ui-datepicker');
        }
    }

    /**
     * @return void
     */
    public static function save_extra_session_data(): void
    {
        $fileLoggerService = Container::get(FileLoggerService::class);
        $query_variables = Input::get();

        if (
            !empty($query_variables) &&
            isset($query_variables['stripe_metadata']) &&
            (int)$query_variables['stripe_metadata'] === 1
        ) {
            if (
                empty($query_variables['userId']) &&
                empty($query_variables['vendorId'])
            ) {
                $fileLoggerService->error(
                    "Lack of userId and vendorId variables sent with stripe_metadata=1."
                );

                return;
            }

            $stripe_metadata = Session::get('stripe_metadata');
            $user_id = Session::get('userId');
            $vendor_id = Session::get('vendorId');

            if ($stripe_metadata != $query_variables['stripe_metadata']) {
                Session::set('stripe_metadata', $query_variables['stripe_metadata']);
            }

            /** Used only in stripe payments */
            if ($user_id != $query_variables['userId']) {
                Session::set('userId', $query_variables['userId']);
            }

            if ($vendor_id != $query_variables['vendorId']) {
                Session::set('vendorId', $query_variables['vendorId']);
            }
        }
    }

    public function platform_session()
    {
        $response = Request::forge('wordpress/before')->execute()->response();
    }

    public function check_register_or_lostpassword()
    {
        $response = Request::forge('wordpress/check_register')->execute()->response();
        $response = Request::forge('wordpress/check_lostpassword')->execute()->response();
    }

    public function rewrite_rules(): void
    {
        $redirects = $this->redirectService->getWordpressRedirects();
        foreach ($redirects as $language => $slugs) {
            foreach ($slugs as $slug => $rules) {
                foreach ($rules as $rule) {
                    ['regex' => $regex, 'query' => $query] = $rule;
                    add_rewrite_rule($regex, $query, 'top');
                }
            }
        }
    }

    /**
     * @param array $query_vars
     *
     * @return array
     */
    public function add_query_vars(array $query_vars): array
    {
        $query_vars[] = 'section';
        $query_vars[] = "id";
        $query_vars[] = "hash";
        $query_vars[] = "action";
        $query_vars[] = "remove";
        $query_vars[] = "clear";
        $query_vars[] = "date";
        $query_vars[] = "lottery";
        $query_vars[] = "amount";

        return $query_vars;
    }

    /**
     * At this moment this is special function only for Stripe payment
     *
     * @param array $query_vars
     *
     * @return array
     */
    public function add_whitelotto_query_vars(array $query_vars): array
    {
        $query_vars[] = 'section';
        $query_vars[] = "action";

        return $query_vars;
    }

    function wpseoRegisterExtraReplacements()
    {
        wpseo_register_var_replacement('%%defaultMetaDescription%%', function () {
            global $post;
            $excerpt = get_the_excerpt($post);

            if (!empty($excerpt)) {
                return $excerpt;
            }

            $domain = Lotto_Helper::getWhitelabelDomainFromUrl();
            $whitelabel = Whitelabel::find('first', [
                'where' => [
                    'domain' => $domain
                ]
            ]);

            return sprintf(
                _('Welcome to %s, the best place to play lotteries from all around the world. Pick your lucky numbers and win big!'),
                $whitelabel->theme
            );
        }, 'advanced', 'Excerpt if exist or default text');
    }

    /**
     * At this moment this is special function only for Stripe payment
     *
     * @return void
     */
    public function rewrite_whitelotto_rules(): void
    {
        add_rewrite_rule('^order/confirm/([0-9]+)/?', 'index.php?action=whitelotto_confirm&section=$matches[1]', 'top');
    }

    /**
     * At this moment this is special function only for Stripe payment
     *
     * @return void
     * @global array  $post
     * @global string $sitepress
     */
    public function check_whitelotto_order_confirm(): void
    {
        $action = get_query_var("action");
        $section = get_query_var("section");

        // Check if order/confirm url was trigged
        if ((string)$action == "whitelotto_confirm") {
            if (empty($section)) {
                exit();
            }

            $whitelabel_payment_method = Model_Whitelabel_Payment_Method::find_by_pk((int)$section);

            if (!isset($whitelabel_payment_method['payment_method_id'])) {
                exit();
            }

            switch ((int)$whitelabel_payment_method['payment_method_id']) {
                case Helpers_Payment_Method::STRIPE:
                    Request::forge("wordpress/order_confirm_stripe")->execute();
                    break;
                case Helpers_Payment_Method::FLUTTERWAVE:
                    Request::forge("wordpress/order_confirm_flutterwave")->execute();
                    break;
                case Helpers_Payment_Method::FLUTTERWAVE_AFRICA:
                    Request::forge("wordpress/order_confirm_flutterwave_africa")->execute();
                    break;
                case Helpers_Payment_Method::PSPGATE_ID:
                    Request::forge("wordpress/order_confirm_pspgate")->execute();
                    break;
                case Helpers_Payment_Method::ONRAMPER_ID:
                    Request::forge("wordpress/order_confirm_onramper")->execute();
                    break;
                case Helpers_Payment_Method::LENCO_ID:
                    Request::forge("wordpress/order_confirm_lenco")->execute();
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * @param bool $redirect
     *
     * @return bool
     */
    public function disable_canonical_redirect_for_front_page($redirect)
    {
        if (is_page() && $front_page = get_option('page_on_front')) {
            if (is_page($front_page)) {
                if (in_array(get_query_var("action"), array("lostpassword", "resend", "gresend"))) {
                    $redirect = false;
                }
            }
        }

        return $redirect;
    }

    public const CASINO_TERMS_SLUG = 'general-terms-and-conditions';

    /**
     * @global string $sitepress
     * @global array  $post
     */
    public function check_access()
    {
        global $sitepress;
        global $post;

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        $user = null;
        $is_user = Lotto_Settings::getInstance()->get("is_user");
        if ($is_user) {
            $user = Lotto_Settings::getInstance()->get("user");
        }

        if (IS_CASINO) {
            $uri = UrlHelper::shorten_uri($_SERVER['REQUEST_URI']);
            $isPageDenied = $post !== null && !is_front_page() &&
                !$this->is_page(Helper_Route::CASINO_HOMEPAGE, $whitelabel['domain']) &&
                !$this->is_page(Helper_Route::CASINO_PLAY, $whitelabel['domain']) &&
                !$this->is_page(Helper_Route::CASINO_LOBBY, $whitelabel['domain']) &&
                !$this->is_page(Helper_Route::CASINO_PRIVACY_POLICY, $whitelabel['domain']) &&
                !$this->is_page(self::CASINO_TERMS_SLUG) &&
                !$this->is_parent_page("auth") &&
                !$this->is_page('activation') &&
                !$this->is_page('login') &&
                !$this->is_page('signup') &&
                !$this->is_page('activated') &&
                !$this->is_page('account') &&
                !$this->is_page(FaireumDepositAndWithdrawalInstructionsPage::SLUG) &&
                !$this->is_page('casino-promotion', $whitelabel['domain']) &&
                !$this->is_page('casino-promotions', $whitelabel['domain']) &&
                !$this->is_page('privacy-policy') &&
                !$this->is_page('payment-methods') &&
                !$this->is_page('contact') &&
                !$this->is_page('affiliate') &&
                !$this->is_page('last-steps') &&
                !$this->isCasinoPost() &&
                !($this->is_parent_page('account') && in_array(get_query_var('section'), [
                    'transactions',
                    'withdrawal',
                    'profile',
                    'exportalldata',
                    'logout'
                ])) &&
                !$this->is_page('deposit') &&
                !in_array($uri, [
                    Helper_Route::ORDER_SUCCESS,
                    Helper_Route::ORDER_FAILURE,
                    Helper_Route::RESEND_ACTIVATION_EMAIL,
                ]) &&
                !($this->is_parent_page('order') && get_query_var('section') === 'result');
            $slidesCount = get_theme_mod('casino_promo_slider_slides_count', 0);
            for ($i = 1; $i <= $slidesCount; $i++) {
                $slideSlug = get_theme_mod('casino_promo_slider_slug_' . $i);
                $isPageDenied = $isPageDenied && !$this->is_page($slideSlug);
            }
        } else {
            $isPageDenied = $this->is_page(Helper_Route::CASINO_HOMEPAGE) ||
                $this->is_page(Helper_Route::CASINO_PLAY, $whitelabel['domain']) ||
                $this->is_page(Helper_Route::CASINO_LOBBY, $whitelabel['domain']) ||
                $this->is_page(Helper_Route::CASINO_PRIVACY_POLICY, $whitelabel['domain']) ||
                $this->is_page(self::CASINO_TERMS_SLUG) ||
                $this->is_page('casino-footer', $whitelabel['domain']) ||
                $this->is_page(FaireumDepositAndWithdrawalInstructionsPage::SLUG) ||
                $this->is_page('casino-promotion', $whitelabel['domain']) ||
                $this->is_page('casino-promotions', $whitelabel['domain']) ||
                $this->is_page('payment-methods') ||
                $this->is_page('casino-contact') ||
                $this->isCasinoPost();
        }

        if ($isPageDenied) {
            Response::redirect(lotto_platform_home_url());
        }

        $path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
        $isApiRoute = !empty($path) && str_contains($path, '/api/');
        if ($isApiRoute) {
            $this->wordpressApiService->run($path);
        }

        if ($this->is_page("auth")) {
            Response::redirect(lotto_platform_home_url());
        }

        if ($is_user && $this->is_parent_page("auth")) {
            Response::redirect(lotto_platform_home_url('/'));
        }

        if (
            Session::get('additional_text_on_failure_success_page') != null &&
            !($this->is_page('success') ||
                $this->is_page('failure'))
        ) {
            Session::delete('additional_text_on_failure_success_page');
        }

        // Deposit and withdrawal pages are not in page cache
        if ($this->is_page(FaireumDepositAndWithdrawalInstructionsPage::SLUG) && !$is_user) {
            Response::redirect(lotto_platform_get_permalink_by_slug("login"));
        }

        // This section won't work with PageCache
        if (Session::get("truevocc_transaction") != null) {
            $this->log_truevocc_transaction_cancelled();
            Session::delete("truevocc_transaction");
        }

        // Pages order and deposit don't have PageCache
        if (
            Session::get("entropay_bp") != null &&
            ($this->is_page("order") ||
                $this->is_page("deposit"))
        ) {
            Lotto_Settings::getInstance()->set("entropay_bp", Session::get("entropay_bp"));
            Session::delete("entropay_bp");
        }

        if (
            get_query_var("action") == "entropay" &&
            ($this->is_page("order") || $this->is_page("deposit"))
        ) {
            $deposit_value = true;
            if ($this->is_page("order")) {
                $deposit_value = false;
            }
            Lotto_Settings::getInstance()->set("entropay_deposit", $deposit_value);
            Request::forge("wordpress/order_entropay")->execute()->response();
        }

        // This action only happens on /auth/lostpassword page
        if (get_query_var("action") == "lostpassword") {
            Request::forge("wordpress/lostpassword_before")->execute()->response();
        }

        if ($this->is_page("order") && get_query_var("action") == "quickpick") {
            $lotteries = Model_Lottery::get_lotteries_for_whitelabel($whitelabel);
            if (
                !empty(get_query_var("lottery")) &&
                !empty(get_query_var("amount")) &&
                isset($lotteries["__by_slug"][get_query_var("lottery")])
            ) {
                $pos_order = Lotto_Helper::get_possible_order();
                $pos_order_cnt = Lotto_Helper::get_possible_order_count();

                $lottery = $lotteries["__by_slug"][get_query_var("lottery")];
                $pricing = lotto_platform_get_pricing($lottery, Helpers_Quickpick::get_ticket_multiplier($lottery['id']));
                $amount = intval(get_query_var("amount"));

                $total_add_price = round($pricing * $amount, 2);

                if ($lottery['is_temporarily_disabled'] == 1) {
                    $msg_txt = _("The lottery is temporarily disabled!");
                    Session::set("message", array("error", $msg_txt));
                    Response::redirect(lotto_platform_get_permalink_by_slug("order"));
                }

                if ($lottery['playable'] != 1) {
                    $msg_txt = _("The lottery is not playable!");
                    Session::set("message", array("error", $msg_txt));
                    Response::redirect(lotto_platform_get_permalink_by_slug("order"));
                }

                if (
                    ((($amount - 1) % $lottery["max_bets"]) < ($lottery["min_bets"] - 1)) ||
                    !($amount >= $lottery["min_lines"] &&
                        ($lottery["multiplier"] == 0 ||
                            ($amount % $lottery["multiplier"] == 0)))
                ) {
                    $msg_txt = _(
                        "We encountered a problem while adding the tickets " .
                            "to your order. Please contact us."
                    );
                    Session::set("message", array("error", $msg_txt));

                    Response::redirect(lotto_platform_get_permalink_by_slug("order"));
                }

                if ($pos_order_cnt > 0 && $pos_order >= $total_add_price) {
                    if ($amount > 0) {
                        $order = Session::get("order");
                        if (empty($order)) {
                            $order = [];
                        }

                        $lines = [];
                        $lottery_is_not_keno = $lottery['type'] !== Helpers_Lottery::TYPE_KENO;
                        $isKeno = !$lottery_is_not_keno;

                        /**
                         * This feature allow users to set first line with numbers
                         * And next lines with random values
                         * Add e.g. numbers=1,2,3 and bnumbers=1 to url query
                         */
                        $quickPickService = Container::get(QuickPickService::class);
                        $shouldGetFirstLineFromUser = $quickPickService->shouldGetFirstLineFromUser();
                        if ($shouldGetFirstLineFromUser) {
                            $lotteryRepository = Container::get(LotteryRepository::class);
                            $lotteryOrmModel = $lotteryRepository->findOneById($lottery['id']);
                            try {
                                [
                                    'normalNumbers' => $normalNumbersFromUser,
                                    'bonusNumbers' => $bonusNumbersFromUser
                                ] = $quickPickService->getUsersFirstLineNumbers($lotteryOrmModel);
                            } catch (WrongLotteryNumbersException) {
                                $shouldGetFirstLineFromUser = false;
                                $normalNumbersFromUser = null;
                                $bonusNumbersFromUser = null;
                            }
                        }

                        for ($i = 0; $i < $amount; $i++) {
                            $next_draw = Lotto_Helper::get_lottery_real_next_draw($lottery);
                            $lottery_type = Model_Lottery_Type::get_lottery_type_for_date(
                                $lottery,
                                $next_draw->format("Y-m-d")
                            );
                            $brandom = [];
                            if ($lottery_is_not_keno) {
                                $random = Lotto_Helper::get_random_values($lottery_type['ncount'], $lottery_type['nrange']);
                            } else {
                                $numbersPerLine = Helpers_Quickpick::get_numbers_per_line($lottery['id']);
                                $random = Lotto_Helper::get_random_values($numbersPerLine, $lottery_type['nrange']);
                            }

                            $isFirstLine = $i === 0;
                            $shouldReplaceFirstLine = $isFirstLine &&
                                $shouldGetFirstLineFromUser &&
                                !empty($normalNumbersFromUser);
                            if ($shouldReplaceFirstLine) {
                                $random = $normalNumbersFromUser;
                            }

                            if ($lottery_type["bextra"] == 0 && $lottery_type["bcount"] > 0 && $lottery_is_not_keno) {
                                $brandom = Lotto_Helper::get_random_values(
                                    $lottery_type["bcount"],
                                    $lottery_type["brange"]
                                );

                                $shouldReplaceBonusNumbers = $shouldReplaceFirstLine &&
                                    !empty($bonusNumbersFromUser);
                                if ($shouldReplaceBonusNumbers) {
                                    $brandom = $bonusNumbersFromUser;
                                }
                            }

                            $lines[] = [
                                'numbers' => $random,
                                'bnumbers' => $brandom
                            ];
                        }

                        $order_item = [
                            'lottery' => $lottery["id"],
                            'lines' => $lines,
                        ];
                        if ($isKeno) {
                            $order_item['ticket_multiplier'] = Helpers_Quickpick::get_ticket_multiplier($lottery['id']);
                            $order_item['numbers_per_line'] = $numbersPerLine ?? null;
                        }

                        $order[] = $order_item;

                        Session::set("order", $order);

                        $userId = lotto_platform_user_id();
                        if ($userId) {
                            $this->cartService->createOrUpdateCart($userId, $order);
                        }

//                      Removed direct Facebook Pixel call; the event is now sent via GTM
//                        Event::trigger('user_cart_add', [
//                            'whitelabel_id' => $whitelabel['id'],
//                            'user_id' => $userId,
//                            'plugin_data' => array("items" => array(array(
//                                "id" => $whitelabel['prefix'] . '_' . Lotto_Helper::get_lottery_short_name($lottery) . '_TICKET',
//                                "name" => $lottery['name'],
//                                "list_name" => "Quick Pick",
//                                "quantity" => $amount,
//                                "price" => $pricing,
//                                "currency" => lotto_platform_user_currency()
//                            )))
//                        ]);

                        $msg_txt = _("The Quick Pick ticket has been added to your order.");
                        Session::set("message", array("success", $msg_txt));
                        Session::set('ticket_added', true);

                        Response::redirect(lotto_platform_get_permalink_by_slug("order"));
                    }
                } else {
                    $msg_txt = _("Failed to add more tickets, you have reached the maximum order!");
                    Session::set("message", array("error", $msg_txt));

                    Response::redirect(lotto_platform_get_permalink_by_slug("order"));
                }
            }
        }

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        if ($is_user && (int)$whitelabel['user_activation_type'] !== Helpers_General::ACTIVATION_TYPE_NONE) {
            if (
                $this->is_page('activation') &&
                ((int)$whitelabel['user_activation_type'] === Helpers_General::ACTIVATION_TYPE_REQUIRED ||
                    (int)$user['is_confirmed'] === 1)
            ) {
                Response::redirect(lotto_platform_home_url('activated'));
            }

            if ($this->is_page('activated') && (int)$user['is_confirmed'] === 0) {
                Response::redirect(lotto_platform_home_url('activation'));
            }
        }

        if (
            $is_user &&
            $this->is_page('activation') &&
            (int)$whitelabel['user_activation_type'] === Helpers_General::ACTIVATION_TYPE_NONE
        ) {
            Response::redirect(lotto_platform_home_url('activated'));
        }

        if ($this->is_page('activated') && !$is_user) {
            Response::redirect(lotto_platform_home_url());
        }

        if ($this->is_page('play-raffle') || $this->is_page('information-raffle') || $this->is_page('results-raffle')) {
            wp_redirect(lotto_platform_home_url('/'), '301');
            exit;
        }

        $uri = $_SERVER["REQUEST_URI"];
        $transactionToken = Input::get('transactionToken', '');

        if (!$is_user && empty($transactionToken) &&
            ($this->is_page("account") ||
                $this->is_page("deposit") ||
                ($this->is_page("success") && empty($transactionToken)) ||
                ($this->is_page("failure") && empty($transactionToken)))
        ) {
            Response::redirect(lotto_platform_get_permalink_by_slug("login"));
        }

        /**
         * For payment return urls (success/failure) we want to redirect to user's language
         * As they are always set in English in outside payment gateways for simplicity
         */
        if ($is_user && Session::get("transaction") != null) {
            if (
                UrlHelper::shorten_uri($uri) == Helper_Route::ORDER_SUCCESS ||
                UrlHelper::shorten_uri($uri) == Helper_Route::ORDER_FAILURE
            ) {
                $slug = substr($uri, 7, 7);
                $user = UserHelper::getUser();
                $usersLanguageId = $user->languageId;

                $currentWhitelabelLanguage = LanguageHelper::getCurrentWhitelabelLanguage();
                $shouldRedirectToUsersLanguage = (int)$currentWhitelabelLanguage['id'] !== $usersLanguageId;
                if ($shouldRedirectToUsersLanguage) {
                    $transaction = Model_Whitelabel_Transaction::find_by_pk(Session::get("transaction"));

                    $slug = "deposit/" . $slug;
                    if ((int)$transaction->type === Helpers_General::TYPE_TRANSACTION_PURCHASE) {
                        $slug = "order/" . $slug;
                    }

                    $id = lotto_platform_get_post_id_by_slug($slug);

                    $usersLanguageCode = $user->language->code ?? null;
                    if (!empty($usersLanguageCode)) {
                        $languageShortcode = BasicLanguageHelper::getLanguageCodeFromLocale($usersLanguageCode);
                        $sitepress->switch_lang($languageShortcode, true);
                        $id = apply_filters(
                            "wpml_object_id",
                            $id,
                            "post",
                            true,
                            $languageShortcode
                        );

                        Response::redirect(get_permalink($id));
                    }
                }
            }
        }

        if (
            !($this->is_page("account") &&
                get_query_var("section") == "withdrawal")
        ) {
            Lotto_Settings::getInstance()->set("withdrawal_step", 1);
        }

        if ($this->is_page("account")) {
            if (!empty(Input::post("myaccount_remove"))) {
                Request::forge("wordpress/myaccount_remove_before")->execute();
            } else {
                Request::forge("wordpress/myaccount_before")->execute();
            }

            switch (get_query_var("section")) {
                case "slip":
                    // slip image
                    Request::forge("wordpress/slip")->execute();
                    break;
                case "tickets":
                    // add lightbox for viewing scans
                    if (get_query_var("action") == "details") {
                        Lotto_Settings::getInstance()->set("load_lightbox", true);
                    }

                    if (get_query_var("action") == "playagain") {
                        Request::forge("wordpress/myaccount_ticket_playagain_before")->execute();
                    }

                    if (get_query_var("action") == "quickpick") {
                        Request::forge("wordpress/myaccount_ticket_quickpick_before")->execute();
                    }
                    break;
                case "profile":
                    // add datepicker for setting date
                    if (get_query_var("action") == null) {
                        Lotto_Settings::getInstance()->set("load_datepicker", true);
                    }
                    break;
                case "logout":
                    Request::forge("wordpress/logout")->execute();
                    break;
                case "withdrawal":
                    Request::forge("wordpress/myaccount_withdrawal_before")->execute();
                    break;
                case "payments":
                    if (get_query_var("action") == "remove") {
                        Request::forge("wordpress/myaccount_payments_before")->execute();
                    }
                    break;
            }
        }

        if ($this->is_page("results") || $this->is_page("lotteries") || $this->is_page("keno-results") || $this->is_page("keno-lotteries")) {
            Lotto_Settings::getInstance()->set("load_tablesorter", true);
        }

        if ($this->is_page("contact")) {
            if (function_exists("wpcf7_enqueue_scripts")) {
                wpcf7_enqueue_scripts();
            }

            if (function_exists("wpcf7_enqueue_styles")) {
                wpcf7_enqueue_styles();
            }
        }

        $showCategories = (int)$whitelabel['show_categories'];
        if (!$showCategories && is_category()) {
            // 307 because whitelabel can change decision and show categories
            Response::redirect(lotto_platform_home_url(), 'location', 307);
        }

        if (is_front_page() || is_home() || is_category()) {
            Lotto_Settings::getInstance()->set("load_masonry", true);
        }

        CaptchaHelper::loadCaptchaConfig();

        if ($this->is_page("order") && get_query_var("action") == "confirm") {
            Request::forge("wordpress/order_confirm")->execute();
        }

        if (
            $is_user &&
            (string)get_query_var("action") === "gresend" &&
            (int)$whitelabel["user_activation_type"] === Helpers_General::ACTIVATION_TYPE_OPTIONAL &&
            (int)$user["is_confirmed"] === 0
        ) {
            Request::forge("wordpress/gresend")->execute();
        }

        if (
            (!$is_user || ((int)$user["is_confirmed"] === 0)) &&
            (int)$whitelabel["user_activation_type"] !== Helpers_General::ACTIVATION_TYPE_NONE
        ) {
            // Only activation, resend or lostpassword pages have hash query
            if (!empty(get_query_var("id")) && !empty(get_query_var("hash"))) {
                if (
                    !empty(get_query_var("action")) &&
                    get_query_var("action") == "resend"
                ) {
                    Request::forge("wordpress/resend")->execute();
                } elseif (
                    !empty(get_query_var("action")) &&
                    get_query_var("action") == "lostpassword"
                ) {
                    // do nothing
                } else {
                    Request::forge("wordpress/activation")->execute();
                }
            }
        }

        // when Zen gateway redirects the user to the LottoPark domain, we need to determine from which
        // whitelabel the transaction originates based on the token, and then we must redirect the user
        // to the appropriate domain
        $transactionToken = Input::get('transactionToken', '');
        if (!empty($transactionToken) && ($this->is_page('success') || $this->is_page('failure'))) {
            $isSuccessPage = $this->is_page('success');
            $whitelabelTransactionRepository = Container::get(WhitelabelTransactionRepository::class);
            $whitelabelRepository = Container::get(WhitelabelRepository::class);
            $decryptedTransactionToken = TransactionTokenEncryptorHelper::decrypt($transactionToken);
            $transactionTokenWithoutPrefix = substr($decryptedTransactionToken, 3);
            $transactionByToken = $whitelabelTransactionRepository->findOneByToken($transactionTokenWithoutPrefix);
            $whitelabelFromTransaction = $whitelabelRepository->findOneById($transactionByToken->whitelabelId);
            if ($whitelabelFromTransaction && $transactionByToken) {
                $orderStatusUrl = $isSuccessPage ? Helper_Route::ORDER_SUCCESS : Helper_Route::ORDER_FAILURE;
                if ($transactionByToken->isCasino) {
                    $casinoPrefix = UrlHelper::getCasinoPrefixForWhitelabel($whitelabelFromTransaction->domain);
                    Response::redirect(
                        "https://$casinoPrefix.{$whitelabelFromTransaction->domain}{$orderStatusUrl}"
                    );
                }

                Response::redirect('https://' . $whitelabelFromTransaction->domain . $orderStatusUrl);
            }
        }

        if ($this->is_page("success")) {
            if (Session::get("transaction") == null) {
                Response::redirect(lotto_platform_home_url());
            }
            $transaction = Model_Whitelabel_Transaction::find_by_pk(Session::get("transaction"));

            if (
                $transaction == null ||
                (int)$transaction->whitelabel_id !== (int)$whitelabel["id"] ||
                (int)$transaction->whitelabel_user_id !== (int)$user["id"]
            ) {
                Response::redirect(lotto_platform_home_url());
            }
            Lotto_Settings::getInstance()->set("transaction", $transaction);

            Session::delete("transaction");

            if ((int)$transaction->type === Helpers_General::TYPE_TRANSACTION_PURCHASE) {
                Session::delete("order");
                $this->cartService->deleteCart($user["id"]);
            }
        } elseif ($this->is_page("failure")) {
            if (Session::get("transaction") == null) {
                Response::redirect(lotto_platform_home_url());
            }
            $transaction = Model_Whitelabel_Transaction::find_by_pk(Session::get("transaction"));

            if (
                $transaction == null ||
                (int)$transaction->whitelabel_id !== (int)$whitelabel["id"] ||
                (int)$transaction->whitelabel_user_id !== (int)$user["id"] ||
                (int)$transaction->status === Helpers_General::STATUS_TRANSACTION_APPROVED
            ) {
                Response::redirect(lotto_platform_home_url());
            }

            $entropay_show = true;
            $whitelabel_payment_methods_without_currency = null;
            if ((int)$transaction->payment_method_type === Helpers_General::PAYMENT_TYPE_BALANCE) {
                $entropay_show = false;
            } else {
                $whitelabel_payment_methods_without_currency = Model_Whitelabel_Payment_Method::get_payment_methods_for_whitelabel($whitelabel);
                $found = false;
                foreach ($whitelabel_payment_methods_without_currency as $whitelabel_payment_method) {
                    if (
                        (int)$whitelabel_payment_method["payment_method_id"] === Helpers_Payment_Method::ENTROPAY &&
                        $whitelabel_payment_method['show'] == true
                    ) {
                        $found = true;
                    }
                }

                if (!$found) {
                    $entropay_show = false;
                } else {
                    $entropay_banned_countries = Forms_Wordpress_Payment_Entropay::get_banned_countries();
                    $user_country = Lotto_Helper::get_best_match_user_country();

                    if (in_array($user_country, $entropay_banned_countries)) {
                        $entropay_show = false;
                    }
                }
            }

            $transaction->set(array("status" => Helpers_General::STATUS_TRANSACTION_ERROR))->save();
            Lotto_Settings::getInstance()->set("transaction", $transaction);
            Lotto_Settings::getInstance()->set("entropay_show", $entropay_show);

            // Send email about failure deposit
            // Prepare email content
            $auser = Model_Whitelabel_User::find_by_pk($transaction->whitelabel_user_id);
            $user_currency_tab = CurrencyHelper::getCurrentCurrency()->to_array();
            $wlang = LanguageHelper::getCurrentWhitelabelLanguage();

            $payment_method_name = "";

            switch ($transaction->payment_method_type) {
                case Helpers_General::PAYMENT_TYPE_BONUS_BALANCE: // bonus balance
                    $payment_method_name = _("Bonus balance");
                    break;
                case Helpers_General::PAYMENT_TYPE_BALANCE: // balance
                    $payment_method_name = _("Balance");
                    break;
                case Helpers_General::PAYMENT_TYPE_CC: // CC
                    //$payment_methods = Model_Whitelabel_CC_Method::get_cc_methods_for_whitelabel($whitelabel);
                    $payment_method_name = _("Credit Card");
                    break;
                case Helpers_General::PAYMENT_TYPE_OTHER: // other
                    if (is_null($whitelabel_payment_methods_without_currency)) {
                        $whitelabel_payment_methods_without_currency = Model_Whitelabel_Payment_Method::get_payment_methods_for_whitelabel($whitelabel);
                    }

                    $whitelabel_payment_methods_with_currency = Helpers_Currency::get_whitelabel_payment_methods_with_currency(
                        $whitelabel,
                        $whitelabel_payment_methods_without_currency,
                        $user_currency_tab
                    );
                    $payment_method_name = $whitelabel_payment_methods_with_currency[$transaction->whitelabel_payment_method_id]['pname'];
                    break;
            }

            if ((int)$transaction->type === Helpers_General::TYPE_TRANSACTION_PURCHASE) {
                // Ticket fail
                $email_data = [
                    'transaction_id' => $whitelabel['prefix'] . 'P' . $transaction->token,
                    'amount' => $transaction->amount,
                    'payment_method_name' => $payment_method_name,
                    'currency' => $user_currency_tab['code']
                ];

                $email_helper = new Helpers_Mail($whitelabel, $auser);
                $email_helper->send_ticket_failure_email($auser->email, $wlang['code'], $email_data);
            } else {
                // Deposit fail
                $email_data = [
                    'transaction_id' => $whitelabel['prefix'] . 'D' . $transaction->token,
                    'amount' => $transaction->amount,
                    'payment_method_name' => $payment_method_name,
                    'currency' => $user_currency_tab['code']
                ];

                $email_helper = new Helpers_Mail($whitelabel, $auser);
                $email_helper->send_deposit_failure_email($auser->email, $wlang['code'], $email_data);
            }

            Session::delete("transaction");
        } elseif ($this->is_page("order") || $this->is_page("deposit")) {
            if ($this->is_page("order")) {
                $clear = get_query_var("clear");
                $remove = get_query_var("remove");
                if (!empty($clear)) {
                    Request::forge("wordpress/order_clear")->execute();
                }
                if (isset($remove) && $remove !== null && $remove !== "") {
                    Request::forge("wordpress/order_remove")->execute();
                }
            }
            Request::forge("wordpress/order_before")->execute();
        }

        if (!$this->is_page("order")) {
            Session::delete("order_last_removed");
        }

        if (!$this->is_page("deposit") && !$this->is_page("failure")) {
            Session::delete("deposit");
            Session::delete("deposit_amount");
            Session::delete("deposit_amount_gateway");
        }

        if (
            !$this->is_page('success') &&
            !empty(Session::get('bonus_lottery_name'))
        ) {
            Session::delete("bonus_lottery_name");
        }

        if (
            !$this->is_page('success') &&
            !empty(Session::get('referafriend_bonus_message'))
        ) {
            Session::delete("referafriend_bonus_message");
        }

        global $shouldTriggerUserViewItemEvent;
        global $userViewItemEventPageName;
        $shouldTriggerUserViewItemEvent = false;

        // pixels
        if (
            $this->is_parent_page("results") ||
            $this->is_parent_page("lotteries") ||
            $this->is_parent_page("play")
        ) {
            $lotteries = Model_Lottery::get_lotteries_for_whitelabel($whitelabel);
            if (
                !empty($post) && !empty($post->post_name) &&
                isset($lotteries["__by_slug"][$post->post_name])
            ) {
                /**
                 * Action is triggered in JS by
                 * @see wordpress/wp-content/themes/base/header.php
                 * @see  resources/wordpress/themes/base/js/Tracking.js
                 * @see platform/fuel/app/classes/controller/api/Internal/Tracking.php
                 *
                 * Here we only decide which pages should trigger this event
                 */
                $shouldTriggerUserViewItemEvent = true;

                $pageName = 'Unknown';
                $parentPageNameMap = [
                    'results' => 'Results',
                    'lotteries' => 'Lotteries',
                    'play' => 'Play'
                ];
                foreach ($parentPageNameMap as $parentPageSlug => $pageNameToSet) {
                    if ($this->is_parent_page($parentPageSlug)) {
                        $pageName = $pageNameToSet;
                        break;
                    }
                }
                $userViewItemEventPageName = $pageName;
            }
        }
    }

    /**
     * @param string $desc Unused
     *
     * @return bool
     */
    public function remove_wpseodesc($desc)
    {
        return false;
    }

    /**
     * @param string $title
     *
     * @return string
     */
    public function update_wpseotitle($title)
    {
        $date = Lotto_Settings::getInstance()->get("results_date");
        $title = explode(WPSEO_Utils::get_title_separator(), $title);
        $date_formatted = Lotto_View::format_date_without_timezone(
            $date,
            IntlDateFormatter::LONG,
            IntlDateFormatter::NONE
        );
        array_splice($title, 1, 0, ' ' . $date_formatted . ' ');

        return implode(WPSEO_Utils::get_title_separator(), $title);
    }

    /**
     * @return int
     */
    public function is_login_page()
    {
        return !strncmp($_SERVER['REQUEST_URI'], '/wp-login.php', strlen('/wp-login.php'));
    }

    public function run_ajax()
    {
        $fileLoggerService = Container::get(FileLoggerService::class);
        $url_contains_language_code = substr($_SERVER['REQUEST_URI'], 3, 6) === '/ajax/';

        if (
            substr($_SERVER['REQUEST_URI'], 0, 6) === '/ajax/' ||
            $url_contains_language_code
        ) {
            $url = parse_url('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            $path = explode("/", $url['path']);

            if ($url_contains_language_code) {
                array_splice($path, 1, 1);
            }

            if (empty($path[2])) {
                exit("0");
            }

            $action = $path[2];

            switch ($action) {
                case "content-raffle-results":
                    if (empty($path[3])) {
                        exit("0");
                    }

                    // language is default
                    $language = "";

                    if ($url_contains_language_code) {
                        $locale = explode('_', get_locale());
                        $language = '/' . $locale[0];
                    }

                    /** @var RaffleDraw $draw_dao */
                    $raffle_draw = Container::get(RaffleDraw::class);
                    $draw_id = (int)Input::get('draw_id');

                    $raffle_draw_not_exists = !empty($draw_id) && !$raffle_draw->exists($draw_id);

                    if ($raffle_draw_not_exists) {
                        exit("0");
                    }

                    $lottery_name = $path[3];

                    $slug = "results-raffle/{$lottery_name}";
                    $post = get_post(lotto_platform_get_post_id_by_slug($slug));

                    $_SERVER['REQUEST_URI'] = "{$language}/results-raffle/${lottery_name}";

                    exit(get_template_part('content', 'raffle-results', [
                        'post' => $post,
                    ]));
            }
        }
    }

    public function refreshSinglePostIdsAndPermalinks(int $postId): void
    {
        global $sitepress;

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        $post = get_post($postId);
        if ($post && $post->post_status === 'auto-draft') {
            return;
        }

        $domain = $_SERVER['HTTP_HOST'];
        $language = $sitepress->get_current_language();
        shell_exec("WORDPRESS_DOMAIN_IN_CLI='$domain' php8.0 {$_ENV['SCHEDULER_OIL_PATH']} r optimize:refreshSinglePostsIds $postId $language >/dev/null 2>&1 &");
        shell_exec("WORDPRESS_DOMAIN_IN_CLI='$domain' php8.0 {$_ENV['SCHEDULER_OIL_PATH']} r optimize:refreshSinglePostPermalinks $postId $language >/dev/null 2>&1 &");
    }

    public function clearPageCacheByLanguage(): void
    {
        global $sitepress;
        $languageCode = $sitepress->get_current_language();
        $defaultLanguage = $sitepress->get_default_language() ?? 'en';

        $isDefaultLanguage = $languageCode === $defaultLanguage;
        if ($isDefaultLanguage) {
            $languageCode = null;
        }

        $this->pageCacheService->clearWhitelabelByLanguage($languageCode);
    }

    public function clearPageCacheByWhitelabel(): void
    {
        $this->pageCacheService->clearWhitelabel();
    }

    public function clearPageCacheOfAllActiveWhitelabels($upgrader_object, $options): void
    {
        try {
            $this->pageCacheService->clearAllActiveWhitelabels();
        } catch (Throwable $exception) {
            $logger = Container::get(FileLoggerService::class);
            $logger->error(
                'Something wrong while clearing page cache after update. Error message: ' . $exception->getMessage()
            );
        }
    }

    public function clearPageCacheAfterWidgetChange($instance)
    {
        $this->pageCacheService->clearWhitelabel();
        return $instance;
    }

    public function turnOnPageCache(): void
    {
        // This action happens after check_access and wordpress.php/before
        // So there should be impossible to catch page with turned on page cache and wit\h redirect
        // This action should happen after all redirects which happen on each page
        // When we firstly turn on page cache, and then we redirect, it is possible that page cache save this page as
        // redirect
        $isNotCasino = !IS_CASINO;

        global $post;

        $isNotExcludedPage = false;
        if ($post) {
            $id = apply_filters('wpml_object_id', $post->ID, 'page', true, 'en');
            $postInDefaultLanguage = get_post($id);
            $isSpecificPage = $postInDefaultLanguage && is_string($postInDefaultLanguage->post_name);
            if ($isSpecificPage) {
                $isNotExcludedPage = !in_array($postInDefaultLanguage->post_name, self::SLUGS_EXCLUDED_FROM_PAGE_CACHE);
            }
        }

        $isFAQPage = $this->is_page('faq') || is_tax('faq-category');
        $isNewsPage = PageHelper::isNewsMainPage() || is_category() || PageHelper::isPostPage();
        $shouldTurnOnPageCache = PageHelper::isNotMainPageWithQuery() &&
            PageHelper::isNotFeedPage() &&
            $isNotCasino &&
            (
                $isFAQPage ||
                $isNewsPage ||
                $isNotExcludedPage
            );
        if ($shouldTurnOnPageCache) {
            $pageCacheService = Container::get(PageCacheService::class);
            $pageCacheService->turnOnPageCache();
        }
    }

    /**
     * @param Object $query
     */
    public function change_query($query)
    {
        // It filters pages to show correct news on casino vs lottery pages
        // And configure /news page to work
        if ($query->is_main_query()) {
            if (IS_CASINO) {
                $query->set('category_name', Lotto_Widget_News::CASINO_NEWS_CATEGORY_SLUG);
            } else {
                $category = get_category_by_slug(Lotto_Widget_News::CASINO_NEWS_CATEGORY_SLUG);
                if (!empty($category)) {
                    $query->set('category__not_in', $category->term_id);
                }
            }
        }

        // It sets correct order on /faq page
        if (is_tax("faq-category")) {
            $query->set('posts_per_page', -1);
            $query->set('orderby', 'title');
            $query->set("order", 'asc');
        }
    }

    public function register_post_types()
    {
        $args = array(
            'label' => _("FAQ Category"),
            'public' => true,
            'hierarchical' => true
        );
        register_taxonomy('faq-category', array('faq'), $args);

        $args = array(
            'public' => true,
            'label' => _('FAQ'),
            'menu_position' => 20,
            'rewrite' => false,
            'publicly_queryable' => false,
            'menu_icon' => 'dashicons-admin-page',
            'supports' => array('title', 'editor', 'page-attributes'),
            'taxonomies' => array('faq-category'),
            'show_in_nav_menus' => false
        );
        register_post_type('faq', $args);
    }

    /**
     * @param int    $id
     * @param string $category
     */
    public function prevent_delete_term($id, $category)
    {
        if ($category == 'category') {
            $id = apply_filters('wpml_object_id', $id, 'category', true, 'en');
            $term = get_term($id);

            // TODO: I am not quite sure but I think it could be done
            // by DB or finally defined somewhere to make access globally
            // something like this: Helpers_General::like get_slugs_for_term();
            $slugs_array = array(
                'powerball',
                'mega-millions',
                'eurojackpot',
                'superenalotto',
                'lotto-uk',
                'euromillions',
                'lotto-pl',
                'la-primitiva',                     // New one
                'bonoloto',                         // New one
                'oz-lotto',                         // New one
                'powerball-au',                     // New one
                'saturday-lotto-au',                // New one
                'monday-wednesday-lotto-au',        // New one
                'el-gordo-primitiva',               // New one
            );

            if (in_array($term->slug, $slugs_array)) {
                wp_send_json_error(_("This category cannot be deleted."));
                wp_die();
            }
        }
    }

    /**
     * @param int $id
     */
    public function prevent_delete($id)
    {
        $screen = get_current_screen();
        if (
            !empty($screen) && isset($screen->post_type) &&
            $screen->post_type == "page"
        ) {
            $id = apply_filters('wpml_object_id', $id, 'post', true, 'en');
            $post = get_post($id);
            $restricted = array(
                'account',
                'contact',
                'form',
                'email-template',
                'footer',
                'faq',
                'home',
                'lotteries',
                'eurojackpot',
                'euromillions',
                'superenalotto',
                'lotto-pl',
                'lotto-uk',
                'mega-millions',
                'powerball',
                'la-primitiva',                     // New one
                'bonoloto',                         // New one
                'oz-lotto',                         // New one
                'powerball-au',                     // New one
                'saturday-lotto-au',                // New one
                'monday-wednesday-lotto-au',        // New one
                'el-gordo-primitiva',               // New one
                'results',
                'news',
                'failure',
                'success',
                'play',
                'activated',
                'activation',
                'order',
                'deposit'
            );

            if (!is_super_admin() && in_array($post->post_name, $restricted)) {
                echo _("Lotto Platform: You cannot delete this page.");
                exit();
            }
        }
    }

    public function set_language()
    {
        load_plugin_textdomain('lotto-platform', false, 'lotto-platform/languages');
    }

    /**
     * @param string $uri
     * @param string $payment_method_uri
     *
     * @return int|null
     */
    public function validate_url_and_get_whitelabel_payment_method_id(
        string $uri = "",
        string $payment_method_uri = ""
    ): ?int {
        $whitelabel_payment_method_id = null;

        $regex_to_check = "/\/order\/result\/([a-z]+)\/([0-9]+)\/?/";

        $maches = preg_match($regex_to_check, $uri);

        if ($maches === false || $maches === 0) {
            return $whitelabel_payment_method_id;
        } else {
            $splited = preg_split("/\//", $uri);

            if ((string)$splited[3] !== $payment_method_uri) {
                return $whitelabel_payment_method_id;
            }

            $whitelabel_payment_method_id = (int)$splited[4];
        }

        return $whitelabel_payment_method_id;
    }

    public function check_entercash()
    {
        $is_user = Lotto_Settings::getInstance()->get("is_user");

        if (!$is_user || Session::get('transaction') == null) {
            return;
        }

        $uri = $_SERVER['REQUEST_URI'];

        $whitelabel_payment_method_id = $this->validate_url_and_get_whitelabel_payment_method_id(
            $uri,
            Helpers_Payment_Method::ENTERCASH_URI
        );

        // special step for entercash which do not differ between success and failure URL's
        if (
            !empty($whitelabel_payment_method_id) &&
            Input::get("clitxid") !== null
        ) {
            $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
            $entercash_obj = new Forms_Wordpress_Payment_Entercash($whitelabel);
            $entercash_obj->set_whitelabel_payment_method_id($whitelabel_payment_method_id);
            $entercash_obj->process_checking();
        }
    }

    /**
     * Check apcopay transaction sttatus and redirect
     */
    public function check_apcopay()
    {
        $uri = $_SERVER['REQUEST_URI'];

        $whitelabel_payment_method_id = $this->validate_url_and_get_whitelabel_payment_method_id(
            $uri,
            "apcopay"               // Here I have to enter strict string
        );

        if (empty($whitelabel_payment_method_id)) {
            return false;
        }

        if (empty(Input::get("params"))) {
            return false;
        }

        if (strpos(Input::get("params"), '<Result>OK</Result>') !== false) {
            Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_SUCCESS));
        }

        Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_FAILURE));
    }

    /**
     * Check Flutterwave transaction status and redirect
     */
    public function check_flutterwave()
    {
        $uri = $_SERVER['REQUEST_URI'];
        $parts = parse_url($uri);
        $data = [];

        if (!isset($parts['query'])) {
            return false;
        }

        parse_str($parts['query'], $data);

        if (!isset($parts['path'])) {
            return false;
        }

        $path = (string)$parts['path'];

        $whitelabel_payment_method_id = $this->validate_url_and_get_whitelabel_payment_method_id(
            $path,
            Helpers_Payment_Method::FLUTTERWAVE_URI
        );

        if (empty($whitelabel_payment_method_id)) {
            return false;
        }

        if (empty($data)) {
            return false;
        }

        // Normally flutterwave send POST data, but sometimes we received GET request
        // The lines below are for GET request
        $data_get = null;
        if (isset($_GET['response'])) {
            $request_string = urldecode($_GET['response']);
            if ($request_string) {
                $request_string_prepared = preg_replace('/\\\"/', "\"", $request_string);
                $data_get = json_decode($request_string_prepared, true);
            }
        }

        if (isset($data['cancelled']) || isset($data_get['cancelled'])) {
            Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_FAILURE));
        }

        if (isset($data['txref']) || isset($data_get['txRef'])) { // TODO: more validation, ex. compare with deposit
            Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_SUCCESS));
        }

        Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_FAILURE));
    }

    public function check_truevocc()
    {
        $uri = $_SERVER['REQUEST_URI'];
        $parts = parse_url($uri);

        if (!isset($parts['query'])) {
            return false;
        }

        $get_method_data = [];
        parse_str($parts['query'], $get_method_data);

        if (!isset($parts['path'])) {
            return false;
        }

        $path = (string)$parts['path'];

        $whitelabel_payment_method_id = $this->validate_url_and_get_whitelabel_payment_method_id(
            $path,
            Helpers_Payment_Method::TRUEVOCC_URI
        );

        if (empty($whitelabel_payment_method_id)) {
            return false;
        }

        if (empty($get_method_data)) {
            return false;
        }

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        $model_whitelabel_payment_methods = Model_Whitelabel_Payment_Method::find([
            'where' => [
                'id' => $whitelabel_payment_method_id
            ],
            'limit' => 1
        ]);

        if (
            empty($model_whitelabel_payment_methods) ||
            empty($model_whitelabel_payment_methods[0])
        ) {
            return false;
        }

        $model_whitelabel_payment_method = $model_whitelabel_payment_methods[0];

        $truevocc_form = new Forms_Wordpress_Payment_TruevoCC(
            $whitelabel,
            [],
            null,
            $model_whitelabel_payment_method
        );
        $truevocc_form->process_checking($get_method_data);
    }

    /**
     * @return void
     */
    private function log_truevocc_transaction_cancelled(): void
    {
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        $subtypes = Model_Whitelabel_Payment_Method::find([
            'where' => [
                'whitelabel_id' => (int)$whitelabel['id'],
                'payment_method_id' => Helpers_Payment_Method::TRUEVOCC,
            ],
            'limit' => 1
        ]);

        if (empty($subtypes) || empty($subtypes[0])) {
            return;
        }
        $subtype = $subtypes[0];

        if (Session::get("truevocc_transaction") == null) {
            return;
        }

        $transaction = Session::get("truevocc_transaction");

        $truevocc_form = new Forms_Wordpress_Payment_TruevoCC(
            $whitelabel,
            [],
            $transaction,
            $subtype
        );
        $truevocc_form->log_transaction_is_cancelled();
    }

    /**
     * @return mixed
     */
    public function check_visanet()
    {
        $uri = $_SERVER['REQUEST_URI'];
        $parts = parse_url($uri);

        $input_get = Input::get();

        $timeout_happened = false;
        // Check if timeout happened
        if (
            !is_null(Session::get("transaction")) &&
            !is_null($input_get) &&
            !empty($input_get['timeout'])
        ) {
            $timeout_happened = true;
        }

        if (
            !$timeout_happened &&
            !isset($parts['query'])
        ) {
            return false;
        }

        $get_method_data = [];
        parse_str($parts['query'], $get_method_data);

        if (
            !$timeout_happened &&
            !isset($parts['path'])
        ) {
            return false;
        }

        $path = (string)$parts['path'];

        $whitelabel_payment_method_id = $this->validate_url_and_get_whitelabel_payment_method_id(
            $path,
            Helpers_Payment_Method::VISANET_URI
        );

        if (empty($whitelabel_payment_method_id)) {
            return false;
        }

        if (empty($get_method_data)) {
            return false;
        }

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        $user = Lotto_Settings::getInstance()->get('user');

        $model_whitelabel_payment_methods = Model_Whitelabel_Payment_Method::find([
            'where' => [
                'id' => $whitelabel_payment_method_id,
            ],
            'limit' => 1
        ]);

        if (
            empty($model_whitelabel_payment_methods) ||
            empty($model_whitelabel_payment_methods[0])
        ) {
            return false;
        }
        $model_whitelabel_payment_method = $model_whitelabel_payment_methods[0];

        $transaction_data = Input::post();

        $visanet_form = new Forms_Wordpress_Payment_VisaNet(
            $whitelabel,
            $user,
            null,
            $model_whitelabel_payment_method
        );

        $visanet_form->process_checking(
            $get_method_data,
            $transaction_data,
            $timeout_happened
        );
    }

    /**
     * @return mixed
     */
    public function check_bhartipay()
    {
        $whitelabel_payment_method_id = $this->validate_url_and_get_whitelabel_payment_method_id(
            Input::server('REQUEST_URI'),
            Helpers_Payment_Method::BHARTIPAY_URI
        );

        if (empty($whitelabel_payment_method_id)) {
            return false;
        }

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        $user = Lotto_Settings::getInstance()->get('user');

        $model_whitelabel_payment_method = Model_Whitelabel_Payment_Method::find_one_by(
            'id',
            $whitelabel_payment_method_id
        );

        $bhartipay_form = new Forms_Wordpress_Payment_Bhartipay(
            $whitelabel,
            $user,
            null,
            $model_whitelabel_payment_method
        );

        $bhartipay_form->process_checking();
    }

    /**
     * @return mixed
     */
    public function check_sepa()
    {
        $uri = $_SERVER['REQUEST_URI'];

        $whitelabel_payment_method_id = $this->validate_url_and_get_whitelabel_payment_method_id(
            $uri,
            Helpers_Payment_Method::SEPA_URI
        );

        if (empty($whitelabel_payment_method_id)) {
            return false;
        }

        if (empty(Input::post()) || empty(Input::post("status"))) {
            return false;
        }

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        $user = Lotto_Settings::getInstance()->get('user');

        $model_whitelabel_payment_methods = Model_Whitelabel_Payment_Method::find([
            'where' => [
                'id' => $whitelabel_payment_method_id,
            ],
            'limit' => 1
        ]);

        if (
            empty($model_whitelabel_payment_methods) ||
            empty($model_whitelabel_payment_methods[0])
        ) {
            return false;
        }
        $model_whitelabel_payment_method = $model_whitelabel_payment_methods[0];

        $transaction_data = Input::post();

        $sepa_form = new Forms_Wordpress_Payment_Sepa(
            $whitelabel,
            $user,
            null,
            $model_whitelabel_payment_method
        );

        $sepa_form->process_checking($transaction_data);
    }

    public function defer_jquery()
    {
        wp_scripts()->add_data('jquery', 'group', 1);
        wp_scripts()->add_data('jquery-core', 'group', 1);
        wp_scripts()->add_data('jquery-migrate', 'group', 1);
    }

    /**
     * @param Object $scripts
     */
    public function remove_jquery_migrate($scripts)
    {
        if (!empty($scripts->registered['jquery'])) {
            $scripts->registered['jquery']->deps = array_diff($scripts->registered['jquery']->deps, array('jquery-migrate'));
        }
    }

    /**
     * @return bool
     * @global Object $wp_scripts
     */
    public function remove_imagesloaded()
    {
        global $wp_scripts;

        $script = $wp_scripts->query('masonry', 'registered');
        if (!$script) {
            return false;
        }

        $found = -1;
        foreach ($script->deps as $key => $dep) {
            if ($dep == "imagesloaded") {
                $found = $key;
            }
        }
        if ($found != -1) {
            unset($script->deps[$found]);
        }

        return true;
    }

    /**
     * @param string $tag
     * @param string $handle
     *
     * @return string
     */
    public function defer_js($tag, $handle)
    {
        if (!in_array($handle, ['base-theme-scripts', 'google-recaptcha'])) {
            return $tag;
        }

        return str_replace(' src', ' defer async src', $tag);
    }

    public function development_disable_ssl_verify($ssl_verify)
    {
        if (Fuel::$env == Fuel::DEVELOPMENT) {
            return false;
        }

        return $ssl_verify;
    }

    public function pageIdsToExcludeFromXmlSitemaps(): array
    {
        $excludedBySlug = self::SLUGS_TO_EXCLUDE;
        $activeLanguages = apply_filters('wpml_active_languages', NULL);
        foreach ($excludedBySlug as $slug) {
            foreach ($activeLanguages as $language) {
                $id = lotto_platform_get_post_id_by_slug($slug, 'page', $language['code']);
                $ids[] = $id;
            }
        }
        return $ids;
    }

    public function run()
    {
        // docker local ssl_verify disable
        add_filter('https_ssl_verify', array($this, 'development_disable_ssl_verify'), 10, 1);
        add_action('wpseo_register_extra_replacements', array($this, 'wpseoRegisterExtraReplacements'), 10, 0);

        $this->addCasinoFilters();

        if (!is_main_site()) {
            $isAutologinRequest = strpos(Input::uri(), '/autologin') !== false;
            if ($isAutologinRequest) {
                /** @var AutoLoginService $AutoLoginService */
                $AutoLoginService = Container::get(AutoLoginService::class);
                add_action('plugins_loaded', [$AutoLoginService, 'login'], 10, 0);
            }

            // overwrite wordpress addslashes behaviour
            add_action('widgets_init', array($this, 'unregister_widgets'), 21, 0);
            add_action('widgets_init', array($this, 'register_widgets'), 10, 0);
            add_action('init', array($this, 'register_post_types'), 10, 0);

            // Pages that have rewrite_rules cannot be cached by server (PageCache)
            // Because they are dynamic
            add_action('init', array($this, 'rewrite_rules'), 10, 0);

            // Every special payment function should be insert within this
            // block if you would like to insert that as add_action() of course!
            {
                add_action('init', array($this, 'check_entercash'));
                add_action('init', array($this, 'check_asiapayment'));
                add_action('init', array($this, 'check_paypalpayment'));

                add_action('init', array($this, 'check_apcopay'));
                add_action('init', array($this, 'check_flutterwave'));
                add_action('init', array($this, 'check_truevocc'));
                add_action('init', array($this, 'check_visanet'));
                add_action('init', array($this, 'check_astropay'));
                add_action('init', array($this, 'check_bhartipay'));
                add_action('init', array($this, 'check_sepa'));
                add_action('init', array($this, 'check_astro'));
                add_action('init', array($this, 'check_wonderlandpay'));
                add_action('init', array($this, 'check_picksell'));
                add_action('init', array($this, 'check_pspgate'));
            }

            // TODO: Soon I will change all above to that function
            // because this is not fine to create as many functions
            // as payment methods called by result URL
            //add_action('init', array($this, 'check_payments'));

            add_action('plugins_loaded', array($this, 'set_language'), 10, 0);

            // fix for wpml when removed default post categories and taxonomies
            add_action('wpml_new_duplicated_terms', array($this, 'check_wpml'), 9, 2);

            add_filter('w3tc_can_print_comment', '__return_false', 10, 1);

            if (!is_admin() && !$this->is_login_page()) {
                // page plugins

                // before WPML (to override language choosing behavior)
                add_action('muplugins_loaded', array($this, 'platform_session'), 10, 0);

                add_action('init', array($this, 'remove_scripts'), 10, 0);

                add_filter('wpcf7_form_elements', array($this, 'contact_default'), 10, 1);

                add_action('wp_enqueue_scripts', array($this, 'load'), 10);
                add_action('wp_enqueue_scripts', array($this, 'remove_imagesloaded'), 11);
                add_filter('script_loader_tag', array($this, 'defer_js'), 10, 2);

                add_action('wp', array($this, 'check_access'), 10, 0); // it is the first hook, where is_page gives true results
                // after WPML (to reset to previously overrided language choosing behavior)
                // this should only be used right after payment
                add_action('wp', array($this, 'turnOnPageCache'), 10, 0);
                add_action('wp_loaded', array($this, 'run_ajax'), 11, 0);
                add_action('wp_loaded', array($this, 'check_register_or_lostpassword'), 12);

                add_action('wp_loaded', array($this, 'set_locale'), 10, 0);

                // Check/add banners before header was loaded
                // Only for specific path e.g. /images?size=1&lottery=powerball&type=white
                add_action('wp_loaded', array($this, 'banners_route'));

                /**
                 * Looks like connected with this feature
                 * @see platform/fuel/app/classes/services/RedirectService.php
                 */
                add_filter('query_vars', array($this, 'add_query_vars'));

                // important one to map translation pages to english pages
                add_filter('template_include', array($this, 'page_template'));

                add_action('pre_get_posts', array($this, 'change_query'));

                add_action('wp', array($this, 'addCaptcha'), 9999);

                add_filter('redirect_canonical', array($this, 'disable_canonical_redirect_for_front_page'));

                add_filter('human_time_diff', array($this, 'adjust_human_time_diff'));

                add_action('wp_enqueue_scripts', array($this, 'defer_jquery'));
                add_action('wp_default_scripts', array($this, 'remove_jquery_migrate'));

                // remove wpcf7 scripts (load them only on contact page)
                add_filter('wpcf7_load_js', '__return_false');
                add_filter('wpcf7_load_css', '__return_false');

                add_action('wp_footer', array($this, 'remove_embed'));

                add_filter('style_loader_src', array($this, 'remove_version_css_js'), 9999);
                add_filter('script_loader_src', array($this, 'remove_version_css_js'), 9999);

                add_filter('the_content', array($this, 'replace_company_details'));

                add_filter('replace_wordpress_tags', array($this, 'replace_wordpress_tags'), 10, 2);

                add_filter('redirect_canonical', array($this, 'remove_redirect_guess_404_permalink'));
            } elseif (is_admin()) {
                // These actions run on wp-admin page
                // There is no need to check these actions while turning on PageCache

                add_action('muplugins_loaded', array($this, 'platform_session'), 10, 0);

                // admin plugins
                add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
                add_action('admin_notices', array($this, 'add_admin_notices'));

                PageEditorHelper::turnOffPagesEditing();

                add_action('wp_trash_post', array($this, 'prevent_delete'), 10, 1);
                add_action('before_delete_post', array($this, 'prevent_delete'), 10, 1);

                add_action('pre_delete_term', array($this, 'prevent_delete_term'), 10, 2);

                add_action('save_post', [$this, 'refreshSinglePostIdsAndPermalinks']);
                add_action('save_post', [$this, 'clearPageCacheByLanguage']);
                add_action('added_option', [$this, 'clearPageCacheByWhitelabel'], 10, 0);
                add_action('updated_option', [$this, 'clearPageCacheByWhitelabel'], 10, 0);
                add_filter('widget_update_callback', [$this, 'clearPageCacheAfterWidgetChange'], 10, 1);
            }

            add_action('init', [$this, 'redirectAuthor']);
        } else {
            // This part is only for main network site - e.g. whitelotto.com
            add_action('init', array($this, 'rewrite_whitelotto_rules'), 10, 0);
            $shouldTurnOnPageCacheOnWhitelottoMainPage = PageHelper::isNotAnyOrderPage() && PageHelper::isNotFeedPage();
            if ($shouldTurnOnPageCacheOnWhitelottoMainPage) {
                $pageCacheService = Container::get(PageCacheService::class);
                $pageCacheService->turnOnPageCache();
            }

            add_filter('query_vars', array($this, 'add_whitelotto_query_vars'));

            add_action('widgets_init', array($this, 'unregister_widgets'), 11, 0);
            add_action('init', array($this, 'remove_scripts'), 10, 0);

            add_action('wp', array($this, 'check_whitelotto_order_confirm'), 10, 0);
        }

        // disable xmlrpc
        add_filter('xmlrpc_enabled', '__return_false');
        // Disable X-Pingback header
        add_filter('wp_headers', array($this, 'disable_pingback'));
        add_action('wpml_loaded', function () {
            // It's called only on /post-sitemap.xml with installed Yoast Wordpress Plugin
            add_filter('wpseo_exclude_from_sitemap_by_post_ids', fn () => $this->pageIdsToExcludeFromXmlSitemaps());
        });

        add_action('upgrader_process_complete', array($this, 'clearPageCacheOfAllActiveWhitelabels'), 10, 2);
    }

    /**
     * @param string $redirect_url
     *
     * @return string|bool
     */
    public function remove_redirect_guess_404_permalink($redirect_url)
    {
        if (is_404() && !isset($_GET['p'])) {
            return false;
        }

        return $redirect_url;
    }

    /**
     * @param string $content
     *
     * @return string
     */
    public function replace_company_details($content)
    {
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        if (empty($whitelabel['company_details'])) {
            return $content;
        }

        $content_block = str_replace(array("\r\n", "\n"), "<br>", $whitelabel['company_details']);
        $content_inline = str_replace(array("\r\n", "\n"), ", ", $whitelabel['company_details']);
        $content = str_replace("{company}", $content_block, $content);
        $content = str_replace("{company_inline}", $content_inline, $content);

        return $content;
    }

    /**
     * @param string $content
     * @param string $tag_name
     * @param string $column_name
     * @param array  $tags
     *
     * @return string
     */
    public function replace_wordpress_tag(string $content, string $tag_name, string $column_name, array $tags): string
    {
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        // check if its not V1
        if (intval($whitelabel['type']) !== 1) {
            return $content;
        }

        $lang_code = get_locale();

        $language = Language::forge()->push_criterias([
            new Model_Orm_Criteria_Where('code', $lang_code),
            new Model_Orm_Criteria_With_Relation('tag')
        ])->find_one();

        // check if row with this language exist in wordpress_tags table
        // if exist check if contains tag relation
        if (!is_object($language) || !$language->tag) {
            return $content;
        }

        /** @var WordpressTag $wordpress_tag */
        $wordpress_tag = $language->tag;

        // check if wordpress_tags table contains $column_name
        if (!isset($wordpress_tag->$column_name)) {
            return $content;
        }

        //check if tag's content contains inner tags
        $tag_content = $this->replace_wordpress_tags($wordpress_tag->$column_name, $tags);

        $content = str_replace("{{$tag_name}}", $tag_content, $content);

        return $content;
    }

    /**
     * @param string   $content
     * @param string[] $tags
     *
     * @return string
     */
    public function replace_wordpress_tags(string $content, array $tags)
    {
        /** @var string $tag */
        foreach ($tags as $tag_name => $column_name) {
            if (strpos($content, "{{$tag_name}}") !== false) {
                $content = $this->replace_wordpress_tag($content, $tag_name, $column_name, $tags);
            }
        }

        return $content;
    }

    /**
     * @param string $src
     *
     * @return string
     */
    public function remove_version_css_js($src)
    {
        if (strpos($src, 'ver=')) {
            $src = remove_query_arg('ver', $src);
        }

        return $src;
    }

    /**
     * @param string $time_diff
     *
     * @return string
     */
    public function adjust_human_time_diff($time_diff)
    {
        $currentWhitelabelLanguage = LanguageHelper::getCurrentWhitelabelLanguage();
        $currentCode = $currentWhitelabelLanguage['code'] ?? LanguageHelper::DEFAULT_LANGUAGE_CODE;
        $isSerbianLanguage = $currentCode === 'sr_RS';
        if ($isSerbianLanguage) {
            $trans = Transliterator::create("Serbian-Latin/BGN");

            return $trans->transliterate($time_diff);
        }

        return $time_diff;
    }

    public function addCaptcha(): void
    {
        $isLostPasswordPage = Input::post("lost") != null;
        $isActivationPage = $this->is_page('activation') || $this->is_page('activated');
        $isPageWithCaptcha = $this->is_parent_page('auth') ||
            $isLostPasswordPage ||
            $isActivationPage ||
            $this->is_page('contact') ||
            $this->is_page('last-steps');
        $isNoCaptchaOnThisPage = !$isPageWithCaptcha;
        if ($isNoCaptchaOnThisPage) {
            return;
        }

        $defaultWhitelabelLanguageShortcode = LanguageHelper::getDefaultWhitelabelLanguageShortcode();
        $userIP = Lotto_Security::get_IP();
        $useHCaptcha = CountryHelper::isIPFromCountries($userIP, CaptchaHelper::HCAPTCHA_COUNTRIES);
        if ($useHCaptcha) {
            $this->addHCaptcha($defaultWhitelabelLanguageShortcode);
        } else {
            $this->addReCaptcha($defaultWhitelabelLanguageShortcode);
        }
    }

    public function addReCaptcha(string $languageShortcode): void
    {
        wp_dequeue_script('google-recaptcha');
        wp_deregister_script('google-recaptcha');
        wp_enqueue_script('google-recaptcha', CaptchaHelper::RECAPTCHA_API_URL . '?hl=' .
            $languageShortcode, [], false, true);
    }

    public function addHCaptcha(string $languageShortcode): void
    {
        wp_dequeue_script('hcaptcha');
        wp_deregister_script('hcaptcha');
        wp_enqueue_script('hcaptcha', CaptchaHelper::HCAPTCHA_API_URL . '?hl=' .
            $languageShortcode, array(), false, true);
    }

    public function remove_embed()
    {
        wp_deregister_script('wp-embed');
    }

    /**
     * @param array   $post_ids        Not used
     * @param bool    $duplicates_only Not used
     *
     * @global string $sitepress
     */
    public function check_wpml($post_ids, $duplicates_only = true)
    {
        global $sitepress;
        $taxonomies = $sitepress->get_translatable_taxonomies(true);
        foreach ($taxonomies as $key => $tax) {
            if (!is_taxonomy_hierarchical($tax)) {
                unset($taxonomies[$key]);
            }
        }
        if (empty($taxonomies)) {
            $result = remove_action('wpml_new_duplicated_terms', 'new_duplicated_terms_filter', 10);
        }
    }

    /**
     * @param array $headers
     *
     * @return array
     */
    public function disable_pingback($headers)
    {
        unset($headers['X-Pingback']);

        return $headers;
    }

    /**
     * @param string $shortcode
     *
     * @return string
     */
    public function contact_default($shortcode)
    {
        // TODO: prepopulate WPCF7 fields
        return $shortcode;
    }

    /**
     * @param string $hook
     */
    public function admin_enqueue_scripts($hook)
    {
        if ($hook == 'widgets.php') {
            $widgetsJsPath = AssetHelper::mix('js/admin/widgets.min.js', AssetHelper::TYPE_LOTTO_PLATFORM);
            wp_enqueue_script(
                'lotto_platform_widget_script',
                $widgetsJsPath,
                ['jquery'],
                false,
                true
            );
        }

        $adminCssPath = AssetHelper::mix('css/admin/style.min.css', AssetHelper::TYPE_LOTTO_PLATFORM);
        wp_enqueue_style(
            'lotto_platform_admin_style',
            $adminCssPath
        );

        $adminJsPath = AssetHelper::mix('js/admin/scripts.min.js', AssetHelper::TYPE_LOTTO_PLATFORM);
        wp_enqueue_script(
            'lotto_platform_admin_script',
            $adminJsPath,
            ['jquery']
        );
    }

    public function set_locale()
    {
        LanguageHelper::configureLocale();
    }

    /**
     * @global string $sitepress
     */
    public function remove_scripts()
    {
        global $sitepress;

        remove_action('wp_head', 'rsd_link');
        remove_action('wp_head', 'rest_output_link_wp_head', 10);
        remove_action('wp_head', 'wlwmanifest_link');
        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('wp_head', array($sitepress, 'meta_generator_tag'));
    }

    public function unregister_widgets()
    {
        unregister_widget('WP_Widget_Archives');
        unregister_widget('WP_Widget_Calendar');
        unregister_widget('WP_Widget_Categories');
        unregister_widget('WP_Nav_Menu_Widget');
        unregister_widget('WP_Widget_Meta');
        unregister_widget('WP_Widget_Pages');
        unregister_widget('WP_Widget_Recent_Comments');
        unregister_widget('WP_Widget_Recent_Posts');
        unregister_widget('WP_Widget_RSS');
        unregister_widget('WP_Widget_Search');
        unregister_widget('WP_Widget_Tag_Cloud');
        unregister_widget('WP_Widget_Text');
        unregister_Widget('WP_Widget_Custom_HTML');
        unregister_widget('WPML_LS_Widget');
        unregister_widget('WP_Widget_Text_Icl');
        unregister_widget('WP_Widget_Media_Audio');
        unregister_widget('WP_Widget_Media_Image');
        unregister_widget('WP_Widget_Media_Video');
    }

    public function add_admin_notices()
    {
        foreach ($this->admin_notices as $notice) {
            $class = 'notice notice-' . $notice[0];
            if (isset($notice[2]) && $notice[2]) {
                $class .= ' is-dismissible';
            }
            $message = Security::htmlentities($notice[1]);

            printf('<div class="%1$s"><p>%2$s</p></div>', $class, $message);
        }
    }

    public function register_widgets()
    {
        if (!IS_CASINO) {
            register_widget('Lotto_Widget_Featured');
            register_widget('Lotto_Widget_Raffle_Promo');
            register_widget('Lotto_Widget_List');
            register_widget('Lotto_Widget_Ticket');
            register_widget('Lotto_Widget_Small_Results');
            register_widget('Lotto_Widget_Small_Slider');
            register_widget('Lotto_Widget_Small_Winners');
            register_widget('Lotto_Widget_Small_Lottery');
            register_widget('Lotto_Widget_Small_Draw');
            register_widget('Lotto_Widget_Small_Text');
            register_widget('Lotto_Widget_Banner');
            register_widget('Lotto_Widget_Sidebar');
            register_widget('Lotto_Widget_Promo');
            register_widget('Lotto_Widget_Raffle_Carousel');
        }

        register_widget('Lotto_Widget_CasinoSlider');
        register_widget('Lotto_Widget_ExternalCasinoSlider');
        register_widget('Lotto_Widget_News');
    }

    /**
     * @param string $template In fact this is finally returned
     *
     * @return string
     * @global array $post
     */
    public function page_template($template)
    {
        global $post;

        if (is_page()) {
            $platform_pages = array(
                'account',
                'lostpassword',
                'login',
                'results',
                'play',
                'signup',
                'activated',
                'activation',
                'order',
                'lotteries',
                'contact',
                'success',
                'failure',
                'faq',
                'deposit',
                'terms',
                'privacy'
            );

            $platform_child_pages = array(
                'play',
                'results',
                'lotteries'
            );

            // child pages will use parent template
            if ($post->post_parent != 0) {
                $id = apply_filters('wpml_object_id', $post->post_parent, 'post', true, 'en');

                // only first ancestor, we don't need more (for now)
                $parent = get_post($id);
                if (in_array($parent->post_name, $platform_child_pages)) {
                    $newtemplate = locate_template(array(
                        'page-' . $parent->post_name . '.php',
                        'page-' . $parent->ID . '.php',
                        'page.php',
                    ));

                    if (!empty($newtemplate)) {
                        return $newtemplate;
                    }
                }
            }

            $id = apply_filters('wpml_object_id', $post->ID, 'post', true, 'en');
            $orig_post = get_post($id);

            // platform pages will use english template
            if (in_array($orig_post->post_name, $platform_pages)) {
                $newtemplate = locate_template(array(
                    'page-' . $orig_post->post_name . '.php',
                    'page-' . $orig_post->ID . '.php',
                    'page.php',
                ));

                if (!empty($newtemplate)) {
                    return $newtemplate;
                }
            }
        }

        return $template;
    }

    /**
     * Check AsiaPayment
     *
     */
    public function check_asiapayment()
    {
        $uri = $_SERVER['REQUEST_URI'];

        $whitelabel_payment_method_id = $this->validate_url_and_get_whitelabel_payment_method_id(
            $uri,
            Helpers_Payment_Method::ASIAPAYMENT_URI
        );

        if (empty($whitelabel_payment_method_id)) {
            return;
        }

        $asiapayment_check = new Forms_Wordpress_Payment_Asiapayment();
        $asiapayment_check->set_whitelabel_payment_method_id($whitelabel_payment_method_id);
        $apg_result = $asiapayment_check->check_payment_result();

        if ($apg_result === true) {
            Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_SUCCESS));
        } else {
            Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_FAILURE));
        }

        exit();
    }

    public function check_astro(): void
    {
        $transactionPrefixedToken = Input::get('token');
        $chunks = explode('/', Input::uri());
        $isAstro = isset($chunks[3]) && $chunks[3] === 'astro';

        if (empty($transactionPrefixedToken) || !$isAstro) {
            return;
        }

        $whitelabelId = Container::get('whitelabel')->id;
        $facade = Container::getPaymentFacade('astro');
        /** @var LoggerContract $logger */
        $logger = Container::get('payments.logger');
        $isCasino = false;
        try {
            /** @var TransactionRepository $transactionRepository */
            $transactionRepository = Container::get(TransactionRepository::class);
            $isCasino = $transactionRepository->getByToken($transactionPrefixedToken, $whitelabelId)->isCasino;
            $status = $facade->getPaymentStatus($transactionPrefixedToken, $whitelabelId);
        } catch (Throwable $e) {
            $logger->logErrorFromException($e);
            Response::redirect($this->getFailureUrl($isCasino));
            exit();
        }

        if ($status->equals(PaymentStatus::PAID())) {
            try {
                $facade->confirmPayment($transactionPrefixedToken, $whitelabelId);
            } catch (Exception $e) {
                # Ugly solution to prevent multiple confirm payment calls (every endpoint calls all defined
                # method many times without any control!)
                if ($e->getMessage() !== 'Attempted to pay already approved transaction') {
                    throw $e;
                }
            }
            Response::redirect($this->getSuccessUrl($isCasino));
            exit();
        }
        Response::redirect($this->getFailureUrl($isCasino));
        exit();
    }

    /**
     * Check PaypalPayment
     *
     */
    public function check_paypalpayment()
    {
        $uri = $_SERVER['REQUEST_URI'];

        $whitelabel_payment_method_id = $this->validate_url_and_get_whitelabel_payment_method_id(
            $uri,
            Helpers_Payment_Method::PAYPAL_URI
        );

        if (empty($whitelabel_payment_method_id)) {
            return;
        }

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        $token = Input::get('token');

        if (empty($token)) {
            Response::redirect(lotto_platform_home_url('/'));
        }

        $transaction = Model_Whitelabel_Transaction::find([
            'where' => [
                "transaction_out_id" => $token,
                "status" => 0
            ]
        ]);

        if (empty($transaction[0]['whitelabel_payment_method_id'])) {
            Response::redirect(lotto_platform_home_url('/'));
        }

        $model_whitelabel_payment_method = Model_Whitelabel_Payment_Method::find_by_pk(
            $transaction[0]['whitelabel_payment_method_id']
        );

        $paypal_form = new Forms_Wordpress_Payment_Paypal();
        $paypal_form->set_whitelabel($whitelabel);
        $paypal_form->set_model_whitelabel_payment_method($model_whitelabel_payment_method);
        $paypal_form->set_payment_credentials();
        $paypal_form->set_paypal_url_by_payment_credentials();
        $paypal_form->receive_order();
        exit();
    }

    /**
     * (Check payment results) - old message.
     * I changed the name to check_astropay() because
     * payment method is priority to run now!
     *
     */
    public function check_astropay()
    {
        $uri = $_SERVER['REQUEST_URI'];
        $parts = parse_url($uri);

        if (!isset($parts['path'])) {
            return false;
        }

        $path = (string)$parts['path'];

        $whitelabel_payment_method_id = $this->validate_url_and_get_whitelabel_payment_method_id(
            $path,
            Helpers_Payment_Method::ASTRO_PAY_URI
        );

        if (empty($whitelabel_payment_method_id)) {
            return false;
        }

        $ip = Lotto_Security::get_IP();
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        $model_whitelabel_payment_methods = Model_Whitelabel_Payment_Method::find([
            'where' => [
                'id' => $whitelabel_payment_method_id
            ],
            'limit' => 1
        ]);

        if (
            empty($model_whitelabel_payment_methods) ||
            empty($model_whitelabel_payment_methods[0])
        ) {
            return false;
        }

        // At this moment is unused
        $model_whitelabel_payment_method = $model_whitelabel_payment_methods[0];

        $transaction = null;
        $data = [];
        $out_id = null;

        // Creation of the payment class
        $astropay = new Helpers_Payment_Astropay_Return($whitelabel);
        $astropay->set_ip($ip);
        $astropay->set_whitelabel_payment_method_id($whitelabel_payment_method_id);

        $ok = $astropay->confirm_payment(
            $transaction,
            $out_id,
            $data
        );

        $isCasino = !empty($transaction) && (int)$transaction['is_casino'] === 1;

        if ($transaction !== null && $ok) {
            $accept_transaction_result = Lotto_Helper::accept_transaction(
                $transaction,
                $out_id,
                $data,
                $whitelabel
            );

            if ($accept_transaction_result === Forms_Transactions_Accept::RESULT_WITH_ERRORS) {
                Response::redirect($this->getFailureUrl($isCasino));
            }
        }

        $isPendingOrSuccessful = (!$ok && $astropay->is_pending) || $ok;
        if ($isPendingOrSuccessful) {
            Response::redirect($this->getSuccessUrl($isCasino));
        }

        Response::redirect($this->getFailureUrl($isCasino));
    }

    public function check_wonderlandpay()
    {
        $uri = $_SERVER['REQUEST_URI'];
        $parts = parse_url($uri);

        if (!isset($parts['path'])) {
            return false;
        }

        $path = (string)$parts['path'];

        $whitelabel_payment_method_id = $this->validate_url_and_get_whitelabel_payment_method_id(
            $path,
            Helpers_Payment_Method::WONDERLANDPAY_URI
        );

        if (empty($whitelabel_payment_method_id)) {
            return false;
        }

        $ip = Lotto_Security::get_IP();
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        $model_whitelabel_payment_methods = Model_Whitelabel_Payment_Method::find([
            'where' => [
                'id' => $whitelabel_payment_method_id
            ],
            'limit' => 1
        ]);

        if (
            empty($model_whitelabel_payment_methods) ||
            empty($model_whitelabel_payment_methods[0])
        ) {
            return false;
        }

        $transaction = null;
        $data = [];
        $out_id = null;

        // Creation of the payment class
        $wonderlandPay = new Helpers_Payment_WonderlandPay_Return($whitelabel);
        $wonderlandPay->set_ip($ip);
        $wonderlandPay->set_whitelabel_payment_method_id($whitelabel_payment_method_id);

        $ok = $wonderlandPay->confirm_payment(
            $transaction,
            $out_id,
            $data
        );

        if ($transaction !== null && $ok) {
            $accept_transaction_result = Lotto_Helper::accept_transaction(
                $transaction,
                $out_id,
                $data,
                $whitelabel
            );

            if ($accept_transaction_result === Forms_Transactions_Accept::RESULT_WITH_ERRORS) {
                Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_FAILURE));
            }
        }

        $isPendingOrSuccessful = (!$ok && $wonderlandPay->is_pending) || $ok;
        if ($isPendingOrSuccessful) {
            Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_SUCCESS));
        }

        Response::redirect(lotto_platform_home_url(Helper_Route::ORDER_FAILURE));
    }

    public function check_picksell()
    {
        $uri = $_SERVER['REQUEST_URI'];
        $parts = parse_url($uri);

        if (!isset($parts['path'])) {
            return false;
        }

        $fileLoggerService = Container::get(FileLoggerService::class);

        $validation = Validation::forge('transaction_token');
        $rule = PrefixedToken::build('token');
        $rule->setValidation($validation);
        $rule->applyRules();
        $isValid = $validation->run(['token' => Input::get('token')]);
        if (!$isValid) {
            return false;
        }
        $transactionPrefixedToken = $validation->validated('token');

        $path = (string)$parts['path'];

        $whitelabel_payment_method_id = $this->validate_url_and_get_whitelabel_payment_method_id(
            $path,
            Helpers_Payment_Method::PICKSELL_URI
        );

        if (empty($whitelabel_payment_method_id)) {
            return false;
        }

        $model_whitelabel_payment_methods = Model_Whitelabel_Payment_Method::find([
            'where' => [
                'id' => $whitelabel_payment_method_id
            ],
            'limit' => 1
        ]);

        if (
            empty($model_whitelabel_payment_methods) ||
            empty($model_whitelabel_payment_methods[0])
        ) {
            return false;
        }

        $transaction = null;
        try {
            /** @var TransactionRepository $transactionRepository */
            $transactionRepository = Container::get(TransactionRepository::class);
            $transaction = $transactionRepository->getByToken($transactionPrefixedToken);
        } catch (Throwable $exception) {
            $fileLoggerService->error(
                "Cannot find transaction by token: {$transactionPrefixedToken}. Detailed message: " . $exception->getMessage()
            );
        }

        if (empty($transaction)) {
            Response::redirect($this->getFailureUrl());
        }

        $isCasino = !empty($transaction) && (int)$transaction['is_casino'] === 1;

        $transactionStatus = $transaction->status;
        if ($transactionStatus === Helpers_General::STATUS_TRANSACTION_ERROR) {
            Response::redirect($this->getFailureUrl($isCasino));
        }

        $isTransactionApprovedOrPending = ($transactionStatus === Helpers_General::STATUS_TRANSACTION_APPROVED) || ($transactionStatus === Helpers_General::STATUS_TRANSACTION_PENDING);
        if ($isTransactionApprovedOrPending) {
            Response::redirect($this->getSuccessUrl($isCasino));
        }

        Response::redirect($this->getFailureUrl($isCasino));
    }

    public function check_pspgate()
    {
        $uri = $_SERVER['REQUEST_URI'];
        $parts = parse_url($uri);

        if (!isset($parts['path'])) {
            return false;
        }

        $validation = Validation::forge('pspgate_transaction_token');
        $rule = PrefixedToken::build('token');
        $rule->setValidation($validation);
        $rule->applyRules();
        $isValid = $validation->run(['token' => Input::get('token')]);
        if (!$isValid) {
            return false;
        }

        $path = (string)$parts['path'];

        $whitelabel_payment_method_id = $this->validate_url_and_get_whitelabel_payment_method_id(
            $path,
            Helpers_Payment_Method::PSPGATE_URI
        );

        if (empty($whitelabel_payment_method_id)) {
            return false;
        }

        $ip = Lotto_Security::get_IP();
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        $model_whitelabel_payment_methods = Model_Whitelabel_Payment_Method::find([
                                                                                      'where' => [
                                                                                          'id' => $whitelabel_payment_method_id
                                                                                      ],
                                                                                      'limit' => 1
                                                                                  ]);

        if (
            empty($model_whitelabel_payment_methods) ||
            empty($model_whitelabel_payment_methods[0])
        ) {
            return false;
        }

        $transaction = null;
        $data = [];
        $out_id = null;

        // Creation of the payment class
        $pspGate = new Helpers_Payment_PspGate_Return($whitelabel);
        $pspGate->set_ip($ip);
        $pspGate->set_whitelabel_payment_method_id($whitelabel_payment_method_id);

        $ok = $pspGate->confirm_payment(
            $transaction,
            $out_id,
            $data
        );

        $isCasino = !empty($transaction) && (int)$transaction['is_casino'] === 1;

        if ($transaction !== null && $ok) {
            $accept_transaction_result = Lotto_Helper::accept_transaction(
                $transaction,
                $out_id,
                $data,
                $whitelabel
            );

            if ($accept_transaction_result === Forms_Transactions_Accept::RESULT_WITH_ERRORS) {
                Response::redirect($this->getFailureUrl($isCasino));
            }
        }

        $isPendingOrSuccessful = (!$ok && $pspGate->is_pending) || $ok;
        if ($isPendingOrSuccessful) {
            Response::redirect($this->getSuccessUrl($isCasino));
        }

        Response::redirect($this->getFailureUrl($isCasino));
    }

    /*
     * Banners route
     */
    public function banners_route()
    {
        global $sitepress;

        $banner = Input::get('size');
        $lottery = Input::get('lottery');
        $type = Input::get('type');

        if (!empty($banner) && !empty($lottery)) {
            // Load lottery info by slug
            $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
            $lottery = lotto_platform_get_lottery_by_slug($lottery);
            $lang = $sitepress->get_current_language();

            // Get translated text
            $translated_jackpot = _("next jackpot");

            $translated_play = _("Play now");

            $translations = [
                'lang_code' => ($lang != null) ? $lang : get_locale(),
                'nearest_jackpot' => $translated_jackpot,
                'play_now' => $translated_play
            ];

            // Create banner
            $obj = new Banners_Create();
            $obj->create_banner($lottery, $banner, $type, $lang, $translations);

            exit;
        }
    }

    /**
     * SEO - CRITICAL
     * @throws Throwable on database failure, or if any of the wordpress functions doesnt work. Consider it fatal
     */
    protected function configureCasinoSeoFilters(Whitelabel $whitelabel): void
    {
        remove_filter('template_redirect', 'redirect_canonical');
        // template_redirect - This action hook executes just before WordPress determines which template page to load.
        // we remove the redirect_canonical because this function reject redirect to casino.lotttopark.com from lottopark.com
        // and add when casino.lottopark.com is loaded for fix trailing slash (for example  casino.lottopark.com/deposit -> casino.lottopark.com/deposit/)
        add_action('wp_loaded', 'redirect_canonical');
        $getPostIdBySlug = function (Indexable_Post_Type_Presentation $post): int {
            $post->model->permalink = UrlHelper::addEndingSlash($post->model->permalink);
            $isCasinoHomePage = $post->model->permalink === UrlHelper::addEndingSlash(get_home_url());
            if ($isCasinoHomePage) {
                $currentCasinoPrefix = UrlHelper::getCurrentCasinoPrefix();
                $post->model->permalink .= $currentCasinoPrefix; // e.g. https://lottopark.work/id/ to https://lottopark//.work/id/casinowhich will point to proper home page
            }
            return url_to_postid($post->model->permalink); // other pages are get like this for the sake of proper default value for casino (yoastseo for them works)
        };
        $defaultCasinoTitle = sprintf(
            _('The Best Online Casino - %s - Play Now'),
            $whitelabel->theme
        );
        $defaultCasinoDescription = sprintf(
            _('Welcome to %s, the best place to play casino games online. Choose your favorite game, try your luck and win big!'),
            $whitelabel->theme
        );

        if ($whitelabel->isTheme(Whitelabel::FAIREUM_THEME)) {
            $defaultCasinoTitle = _('Faireum Games');
            $defaultCasinoDescription = _('Welcome to faireum, the best place to play Faireum Games online. Choose your favorite game, try your luck and win big!');
        }

        $generateMetaTitle = function (string $title, $post) use ($getPostIdBySlug, $defaultCasinoTitle): string {
            if (!($post instanceof Indexable_Post_Type_Presentation)) {
                return $defaultCasinoTitle;
            }

            return get_post_meta($getPostIdBySlug($post), '_yoast_wpseo_title', true) ?: $defaultCasinoTitle;
        };
        $generateMetaDescription = function (string $metadesc, $post) use ($getPostIdBySlug, $defaultCasinoDescription): string {
            if (!($post instanceof Indexable_Post_Type_Presentation)) {
                return $defaultCasinoDescription;
            }

            return get_post_meta($getPostIdBySlug($post), '_yoast_wpseo_metadesc', true) ?: $defaultCasinoDescription;
        };
        $generateMetaUrl = function (string $url): string {
            $url = UrlHelper::addEndingSlash($url);
            $currentCasinoPrefix = UrlHelper::getCurrentCasinoPrefix();
            $isCasinoHomePage = $url === UrlHelper::addEndingSlash(get_home_url()) . $currentCasinoPrefix . '/';
            if ($isCasinoHomePage) {
                // e.g. https://lottopark.work/id/casino to https://lottopark.work/id/
                $url = substr($url, 0, strpos($url, $currentCasinoPrefix));
            }
            return UrlHelper::changeAbsoluteUrlToCasinoUrl($url);
        };
        add_filter('wpseo_title', $generateMetaTitle, 10, 2);
        add_filter('wpseo_metadesc', $generateMetaDescription, 10, 2);
        add_filter('wpseo_opengraph_title', $generateMetaTitle, 10, 2);
        add_filter('wpseo_opengraph_desc', $generateMetaDescription, 10, 2);
        add_filter('wpseo_opengraph_url', $generateMetaUrl, 10, 1);
        add_filter('wpseo_canonical', $generateMetaUrl, 10, 1);
    }

    /** @see also wordpress/wp-content/themes/base/functions.php rewriteNavMenus() */
    public function getCasinoNavigationFilter(): Closure
    {
        /**
        * Transformation:
        * 1. ://www.casino to ://casino
        * 2. we fetch play page transformed into casino e.g. ://casino.lottopark.com/lottery/play/ to ://casino.lottopark.com/lottery/play/ TODO: strange
        * 3. we replace all occurences of this wrong link to proper one e.g.  all ://casino.lottopark.com/lottery/play/ to ://lottopark.com/lottery/play/
        * @param string[]|string items
        * @return string[]|string transformed items
        */
        return function ($items) {
            $items = str_replace('www.', '', $items); // NOTE: www. whitelabels need to discard www. part for casino (casino is without www)
            $playUrl = lotto_platform_get_permalink_by_slug('play');
            $currentCasinoPrefix = UrlHelper::getCurrentCasinoPrefix();
            $newPlayUrl = preg_replace("/\/$currentCasinoPrefix\./", '/', $playUrl);
            return str_replace($playUrl, $newPlayUrl, $items);
        };
    }

    /** These filters:
     *      - improve seo for casino
     *      - change link to casino in menu in lottery page
     *      - change link to lotteries in menu in casino page
     */
    private function addCasinoFilters(): void
    {
        $isSitemap = $_SERVER['REQUEST_URI'] === '/sitemap.xml';
        if (!empty(IS_CASINO) && $isSitemap) {
            Request::forge("wordpress/casinoSitemap")->execute()->response();
        }

        if (IS_CASINO) {
            $domain = Lotto_Helper::getWhitelabelDomainFromUrl();
            $whitelabel = Whitelabel::find('first', [
                'where' => [
                    'domain' => $domain
                ]
            ]);
            // deploy mode has null whitelabel
            if (!empty($whitelabel)) {
                $this->configureCasinoSeoFilters($whitelabel);
            }

            add_filter('wpml_hreflangs', function (array $links) {
                foreach ($links as $key => $link) {
                    $links[$key] = UrlHelper::changeAbsoluteUrlToCasinoUrl($link);
                }
                return $links;
            }, 10, 1);
            add_filter('get_shortlink', function (string $shortLink) {
                return UrlHelper::changeAbsoluteUrlToCasinoUrl($shortLink);
            }, 10, 1);
            add_filter('wp_nav_menu', $this->getCasinoNavigationFilter(), 10, 1);
            add_filter('wp_footer', $this->getCasinoNavigationFilter(), 10, 1);
        } else {
            add_filter('wp_nav_menu', function ($items) {
                return UrlHelper::changeUrlsToCasino($items);
            }, 10, 1);
        }
        add_filter('wpseo_xml_sitemap_post_url', function ($url, $post) {
            global $sitepress;
            $defaultLang = $sitepress->get_default_language();
            $defaultPostId = apply_filters('wpml_object_id', $post->ID, 'page', false, $defaultLang);
            $defaultPost = get_post($defaultPostId);
            $currentCasinoPrefix = UrlHelper::getCurrentCasinoPrefix();
            if ($defaultPost && str_contains($defaultPost->post_name, $currentCasinoPrefix)) {
                return UrlHelper::changeAbsoluteUrlToCasinoUrl($url, true);
            }

            return $url;
        }, 10, 2);
    }

    private function getSuccessUrl(bool $isCasino = false): string
    {
        $urlHelper = Container::get(PaymentUrlHelper::class);
        return $urlHelper->getSuccessUrl($isCasino);
    }

    private function getFailureUrl(bool $isCasino = false): string
    {
        $urlHelper = Container::get(PaymentUrlHelper::class);
        return $urlHelper->getFailureUrl($isCasino);
    }

    /** We redirect this param because it spams slack due to wp security issue */
    public function redirectAuthor()
    {
        $isHomepage = UrlHelper::isHomepage();
        if ($isHomepage && isset($_GET['author'])) {
            wp_redirect(lotto_platform_home_url());
            exit;
        }
    }
}
