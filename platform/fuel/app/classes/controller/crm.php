<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Fuel\Core\Config;
use Fuel\Core\Controller_Rest;
use Fuel\Core\Input;
use Fuel\Core\Request;
use Fuel\Core\Response;
use Fuel\Core\Session;
use Fuel\Core\View;
use Helpers\CountryHelper;
use Helpers\CrmModuleHelper;
use Helpers\SanitizerHelper;
use Helpers\FullTokenHelper;
use Models\AdminUser;
use Models\SlotTransaction;
use Models\WhitelabelWithdrawal;
use Modules\CrmTable\Config as CrmConfig;
use Repositories\CrmLogRepository;
use Repositories\CurrencyRepository;
use Repositories\LotteryRepository;
use Repositories\Orm\WhitelabelUserRepository;
use Repositories\PaymentMethodRepository;
use Repositories\SlotGameRepository;
use Repositories\SlotTransactionRepository;
use Repositories\WhitelabelLanguageRepository;
use Repositories\WhitelabelLotteryRepository;
use Repositories\WhitelabelRepository;
use Repositories\WhitelabelSlotProviderRepository;
use Repositories\WhitelabelTransactionRepository;
use Repositories\WhitelabelWithdrawalRepository;
use Services\CrmLoggerService;
use Services\CrmTableService;
use Services\Logs\FileLoggerService;
use Models\Whitelabel;
use Repositories\AdminWhitelabelRepository;
use Services\CacheService;
use Traits\Scans\ScansTrait;
use Validators\LotteryTicketNumbersValidator;
use Validators\LtechManualDrawValidator;

class Controller_Crm extends Controller_Rest
{
    use Traits_Prepare_Tickets;
    use Traits_Prepare_Withdrawals;
    use Traits_Prepare_Transactions;
    use ScansTrait;

    private CrmLoggerService $crmLoggerService;
    private CrmTableService $crmTableService;
    private AdminUser $user;
    private SlotTransactionRepository $slotTransactionRepository;
    protected WhitelabelRepository $whitelabelRepository;
    protected SlotGameRepository $slotGameRepository;
    private WhitelabelSlotProviderRepository $whitelabelSlotProviderRepository;
    private CurrencyRepository $currencyRepository;
    private PaymentMethodRepository $paymentMethodRepository;
    protected FileLoggerService $fileLoggerService;
    private WhitelabelLanguageRepository $whitelabelLanguageRepository;
    private WhitelabelLotteryRepository $whitelabelLotteryRepository;
    private LtechManualDrawService $ltechManualDrawService;
    private WhitelabelWithdrawalRepository $whitelabelWithdrawalRepository;
    private WhitelabelTransactionRepository $whitelabelTransactionRepository;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->crmLoggerService = Container::get(CrmLoggerService::class);
        $this->crmTableService = Container::get(CrmTableService::class);
        $this->slotTransactionRepository = Container::get(SlotTransactionRepository::class);
        $this->whitelabelRepository = Container::get(WhitelabelRepository::class);
        $this->slotGameRepository = Container::get(SlotGameRepository::class);
        $this->whitelabelSlotProviderRepository = Container::get(WhitelabelSlotProviderRepository::class);
        $this->currencyRepository = Container::get(CurrencyRepository::class);
        $this->paymentMethodRepository = Container::get(PaymentMethodRepository::class);
        $this->fileLoggerService = Container::get(FileLoggerService::class);
        $this->whitelabelLanguageRepository = Container::get(WhitelabelLanguageRepository::class);
        $this->whitelabelLotteryRepository = Container::get(WhitelabelLotteryRepository::class);
        $this->ltechManualDrawService = Container::get(LtechManualDrawService::class);
        $this->whitelabelWithdrawalRepository = Container::get(WhitelabelWithdrawalRepository::class);
        $this->whitelabelTransactionRepository = Container::get(WhitelabelTransactionRepository::class);
    }

    /**
     *
     * @var View
     * @var array
     */
    protected $view;
    private $algorithm = ['HS256'];

    public function before()
    {
        parent::before();
        if (!Lotto_Helper::allow_access("admin")) {
            $error = Request::forge('index/404')->execute();
            echo $error;
            exit();
        }
    }

    public function action_404(): array
    {
        echo Request::forge('index/404')->execute();
        exit();
    }

    public function action_index(): Response
    {
        if (file_exists(APPPATH . '/.maintenance-crm')) {
            http_response_code(503);
            exit(file_get_contents(APPPATH . '/.maintenance-crm'));
        }
        $this->view = View::forge("crm/index");
        return Response::forge($this->view);
    }

    private function authorizationError(): array
    {
        return [
            'code' => 403,
            'message' => 'Unauthorized'
        ];
    }

    /**
     *
     * @access  private
     * @return string
     */
    private function get_key()
    {
        Config::load("admin", true);
        $key = Config::get("admin.rsa_private_key");
        return $key;
    }

    /**
     *
     * @access  private
     * @return bool
     */
    private function middleware_auth()
    {
        $jwt_data = $this->decode_token();
        if (!$jwt_data) {
            return false;
        }

        $token_message = $jwt_data->message;
        if (!isset($token_message)) {
            return false;
        }
        if ($token_message !== 'logged') {
            return false;
        }

        $user_id = $jwt_data->user;

        if (empty($user_id)) {
            return false;
        }

        $user = Model_Admin_User::find_by_pk($user_id);

        if (empty($user)) {
            return false;
        }

        $this->user = AdminUser::find($user_id);
        return true;
    }

    /**
     *
     * @access public
     * @return array
     */
    public function get_check_middleware_auth()
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }
        return [
            'code' => 200
        ];
    }

    /**
     * @throws Exception when whitelabel doesn't exists for admin
     */
    public function get_current_admin_user(): array
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }

        $user = $this->current_user();

        return [
            'code' => 200,
            'user' => $user
        ];
    }

    /**
     * @return Object
     */
    protected function current_user()
    {
        $jwt_data = $this->decode_token();
        if ($jwt_data) {
            $user_id = $jwt_data->user;
            //TO-DO: Remove the if condition below when not necessary.
            //It's a temporary solution to prevent already logged users from sending whole object to the get_user() function
            if (is_object($user_id)) {
                $user_id = $user_id->id;
            }
            $user = Model_Admin_User::get_user($user_id);
            return $user;
        }
    }

    /**
     *
     * @access  private
     * @return array|bool
     */
    private function decode_token()
    {
        $key = $this->get_key();
        $header = Input::headers('Authorization');
        if (!isset($header)) {
            return false;
        }
        try {
            $header_explode = explode(" ", $header);
            $token = $header_explode[1];
            return JWT::decode($token, new Key($key, $this->algorithm[0]));
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param int $whitelabel_id
     */
    private function check_whitelabel_type_V2($whitelabel_id): bool
    {
        $permitted = false;
        $whitelabel = Model_Whitelabel::get_single_by_id($whitelabel_id);
        if ($whitelabel['type'] == 2) {
            $permitted = true;
        }
        return $permitted;
    }

    public function get_languages_roles_and_timezones(): array
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }
        $current_user = $this->current_user();
        $lang_code = $current_user->code;

        $roles = Model_Role::get_all_user_roles();
        $languages = $this->languages_list($lang_code, 0);
        $timezones = Lotto_Helper::get_timezone_list($lang_code);
        return [
            'code' => 200,
            'languages' => $languages,
            'roles' => $roles,
            'timezones' => $timezones
        ];
    }

    /**
     *
     * @access private
     * @param string $lng
     * @param int $whitelabel_id
     * @return array
     */
    private function languages_list($lng, $whitelabel_id)
    {
        if ($whitelabel_id == 0) {
            $languages_raw = Model_Language::get_all_languages();
        } else {
            $whitelabel = Model_Whitelabel::find_by_pk($whitelabel_id)->to_array();
            $languages_raw = Model_Whitelabel_Language::get_whitelabel_languages($whitelabel, 0);
        }
        $languages = [];
        foreach ($languages_raw as $lang) {
            $language = [];
            $language['name'] = Locale::getDisplayLanguage($lang['code'], $lng);
            $language['id'] = $lang['id'];
            array_push($languages, $language);
        }
        usort($languages, function ($lang_one, $lang_two) {
            return strcmp($lang_one['name'], $lang_two['name']);
        });
        return $languages;
    }

    /**
     *
     * @access  public
     * @return array
     */
    public function post_new_admin_user()
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }

        $user = Input::json('user');

        $validator = new Helpers_Crm_Validation();
        $errors = $validator->check_for_errors($user);
        if ($errors) {
            return [
                'code' => 400,
                'errors' => $errors
            ];
        }

        $id = Model_Admin_User::add_user($user);

        Model_Whitelabel_User::default_admin_user_visible_columns_crm($id);

        if ($user['role_id'] === "1") {
            $whitelabels = Model_Whitelabel::get_all_as_short_list();
            $modules = Model_Admin_Modules::all_modules();

            foreach ($modules as $module) {
                if (strpos($module['module_name'], 'admins') !== false) {
                    Model_Admin_Modules::add_module_access_to_user(0, $module['module_id'], $id);
                }
            }
            foreach ($whitelabels as $whitelabel) {
                Model_Admin_Modules::add_whitelabel_admin($whitelabel['id'], $id);
                foreach ($modules as $module) {
                    if (strpos($module['module_name'], 'admins') === false) {
                        Model_Admin_Modules::add_module_access_to_user($whitelabel['id'], $module['module_id'], $id);
                    }
                }
            }
        } else {
            if (!empty($user['accessList'])) {
                $whitelabels = [];
                foreach ($user['accessList'] as $whitelabel_id => $module_id) {
                    if (($whitelabel_id !== 0) && (!in_array($whitelabel_id, $whitelabels))) {
                        array_push($whitelabels, $whitelabel_id);
                    }
                    foreach ($module_id as $module) {
                        Model_Admin_Modules::add_module_access_to_user($whitelabel_id, $module, $id);
                    }
                }
                if (count($whitelabels) > 0) {
                    foreach ($whitelabels as $whitelabel) {
                        Model_Admin_Modules::add_whitelabel_admin($whitelabel, $id);
                    }
                }
            }
        }

        return [
            'code' => 200
        ];
    }

    /**
     * @return array
     * @throws Exception when whitelabel doesn't exists for admin
     */
    public function post_update_admin_user()
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }

        $mode = Input::json('mode');

        if ($mode !== 'self') {
            $current_user = $this->current_user();
            $role = $current_user->role_id;
            if ($role != 1 && $role != 3 && !Model_Admin_Modules::check_user_module('admins-edit', null, $current_user->id)) {
                return $this->authorizationError();
            }
        }
        $id = Input::json('id');
        $updated_user = Input::json('updatedUser');

        if (isset($updated_user['addedModules'])) {
            $modules = $updated_user['addedModules'];

            foreach ($modules as $module) {
                Model_Admin_Modules::add_module_access_to_user($module['whitelabel_id'], $module['module_id'], $id);
            }

            unset($updated_user['addedModules']);
        }
        if (isset($updated_user['addedWhitelabels'])) {
            $whitelabels = $updated_user['addedWhitelabels'];

            foreach ($whitelabels as $whitelabel) {
                Model_Admin_Modules::add_whitelabel_admin($whitelabel, $id);
            }

            unset($updated_user['addedWhitelabels']);
        }
        if (isset($updated_user['deletedModules'])) {
            $modules = $updated_user['deletedModules'];

            foreach ($modules as $module) {
                Model_Admin_Modules::delete_module_user_access($module['whitelabel_id'], $module['module_id'], $id);
            }

            unset($updated_user['deletedModules']);
        }
        if (isset($updated_user['deletedWhitelabels'])) {
            $whitelabels = $updated_user['deletedWhitelabels'];

            foreach ($whitelabels as $whitelabel) {
                Model_Admin_Modules::delete_admin_whitelabel($whitelabel, $id);
            }

            unset($updated_user['deletedWhitelabels']);
        }
        $hasAnyFieldsToUpdate = count($updated_user) > 0;
        if ($hasAnyFieldsToUpdate) {
            $validator = new Helpers_Crm_Validation();
            $errors = $validator->check_for_errors($updated_user);
            if ($errors) {
                return [
                    'code' => 400,
                    'errors' => $errors
                ];
            }

            $shouldUpdatePaymentEmail = !empty($updated_user['paymentEmail']);
            $shouldUpdateSupportEmail = !empty($updated_user['supportEmail']);
            $shouldUpdateContactEmails = $shouldUpdatePaymentEmail || $shouldUpdateSupportEmail;
            if ($shouldUpdateContactEmails) {
                /** @var CacheService $cacheService */
                $cacheService = Container::get(CacheService::class);

                /** @var AdminWhitelabelRepository $adminWhitelabelRepository */
                $adminWhitelabelRepository = Container::get(AdminWhitelabelRepository::class);
                /** @var WhitelabelRepository $whitelabelRepository */
                $whitelabelRepository = Container::get(WhitelabelRepository::class);
                $adminWhitelabel = $adminWhitelabelRepository->findOneByAdminUserId($id);

                if ($shouldUpdateSupportEmail) {
                    $whitelabelRepository->updateSupportEmail($adminWhitelabel->whitelabel, $updated_user['supportEmail']);
                    unset($updated_user['supportEmail']);
                }

                if ($shouldUpdatePaymentEmail) {
                    $whitelabelRepository->updatePaymentEmail($adminWhitelabel->whitelabel, $updated_user['paymentEmail']);
                    unset($updated_user['paymentEmail']);
                }

                $cacheService->deleteForWhitelabelByDomain('whitelabel');

                $hasAnyFieldsToUpdate = count($updated_user) > 0;
                if ($hasAnyFieldsToUpdate) {
                    Model_Admin_User::update_user($id, $updated_user);
                }
            }
        }

        return [
            'code' => 200
        ];
    }

    public function get_accessible_modules(): array
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }
        $user = $this->current_user();
        $modules = Model_Admin_Modules::accessible_modules($user->id);
        $whitelabels = Model_Admin_Modules::accessible_whitelabels($user->id);
        return [
            'code' => 200,
            'modules' => $modules,
            'whitelabels' => $whitelabels
        ];
    }

    /**
     *
     * @access public
     * @return array
     */
    public function get_admin_users()
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }
        $current_user = $this->current_user();
        $role = $current_user->role_id;
        if ($role != 1 && $role != 3 && !Model_Admin_Modules::check_user_module('admins-view', null, $current_user->id)) {
            return $this->authorizationError();
        }
        $users = [];

        if ($role == 1) {
            $users = Model_Admin_User::get_all_users_short();
        } else {
            $users = $this->get_accessible_users($role, $current_user->id);
        }

        return [
            'code' => 200,
            'users' => $users
        ];
    }

    /**
     *
     * @access public
     * @return array
     */
    public function post_admin_user_details()
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }
        $current_user = $this->current_user();
        $role = $current_user->role_id;
        if ($role != 1 && $role != 3 && !Model_Admin_Modules::check_user_module('admins-view', null, $current_user->id)) {
            return $this->authorizationError();
        }
        $id = Input::json('id');
        if ($role != 1) {
            $users = $this->get_accessible_users($role, $current_user->id);
            $exists = array_filter($users, function ($user) use ($id) {
                return $user['id'] == $id;
            });
            if (count($exists) == 0) {
                return $this->authorizationError();
            }
        }

        $user = [];
        $user = Model_Admin_User::get_user($id);

        $modules = Model_Admin_Modules::accessible_modules($id);

        $isWhitelabelSuperAdmin = (int) $user->role_id === AdminUser::WHITE_LABEL_SUPER_ADMINISTRATOR_ROLE_ID;
        if ($isWhitelabelSuperAdmin) {
            /** @var AdminWhitelabelRepository */
            $adminWhitelabelRepository = Container::get(AdminWhitelabelRepository::class);
            $whitelabel = $adminWhitelabelRepository->getWhitelabelByAdminId($user->id);
            $user->supportEmail = $whitelabel->supportEmail;
            $user->paymentEmail = $whitelabel->paymentEmail;
        }

        return [
            'code' => 200,
            'user' => $user,
            'user_modules' => $modules
        ];
    }

    /**
     *
     * @access private
     * @param int $admin_role
     * @param int $admin_id
     * @return array
     */
    private function get_accessible_users($admin_role, $admin_id)
    {
        $users = [];
        if ($admin_role == 2) {
            $users = Model_Admin_User::get_regular_users_short();
        } elseif ($admin_role == 3) {
            $whitelabels = Model_Admin_Modules::accessible_whitelabels($admin_id);
            foreach ($whitelabels as $whitelabel) {
                $sub_admins = Model_Admin_User::get_sub_whitelabel_admin_users_short($whitelabel['id']);
                foreach ($sub_admins as $sub_admin) {
                    if (!in_array($sub_admin, $users)) {
                        array_push($users, $sub_admin);
                    }
                }
            }
        }
        return $users;
    }

    /**
     *
     * @access public
     * @return array
     */
    public function post_delete_admin_user()
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }
        $id = Input::json('id');

        $current_user = $this->current_user();
        $role = $current_user->role_id;
        if ($role != 1 && $role != 3 && !Model_Admin_Modules::check_user_module('admins-delete', null, $current_user->id)) {
            return $this->authorizationError();
        }
        $id = Input::json('id');
        if ($role != 1) {
            $users = $this->get_accessible_users($role, $current_user->id);
            $exists = array_filter($users, function ($user) use ($id) {
                return $user['id'] == $id;
            });
            if (count($exists) == 0) {
                return $this->authorizationError();
            }
        }
        Model_Admin_Modules::delete_admin_accesses($id);
        Model_Whitelabel_User::delete_admin_user_visible_columns_crm($id);
        Model_Admin_User::delete_user($id);

        return [
            'code' => 200,
            'message' => 'User deleted successfully.'
        ];
    }

    public function post_update_users_columns(): array
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }
        $current_user = $this->current_user();
        $admin_id = $current_user->id;
        $slug = Input::json('slug');

        $admin_columns = Model_Whitelabel_User::get_admin_user_visible_columns_crm($admin_id);

        if (is_null($admin_columns)) {
            Model_Whitelabel_User::save_admin_user_visible_columns_crm($admin_id, $slug);
        } else {
            Model_Whitelabel_User::update_admin_user_visible_columns_crm($admin_id, $slug);
        }

        return [
            'code' => 200
        ];
    }

    /**
     *
     * @access  public
     * @return array
     */
    public function post_users_view_data()
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }
        $whitelabel_id = Input::json('whitelabel_id');
        $current_user = $this->current_user();
        $id = $current_user->id;
        $role = $current_user->role_id;
        if ($role != 1 && !Model_Admin_Modules::check_admin_whitelabel($whitelabel_id, $id)) {
            return $this->authorizationError();
        }
        $lang_code = $current_user->code;

        $columns = Model_Whitelabel_User::get_admin_user_visible_columns_crm($id);
        $languages = $this->languages_list($lang_code, $whitelabel_id);
        $timezones = Lotto_Helper::get_timezone_list($lang_code);
        $countries = Lotto_Helper::get_localized_country_list($lang_code);
        $currencies = Helpers_Currency::getCurrencies();
        $groups = Model_Whitelabel_User_Group::get_all_groups_for_whitelabel($whitelabel_id);
        usort($currencies, function ($curr_one, $curr_two) {
            return strcmp($curr_one['code'], $curr_two['code']);
        });

        return [
            'code' => 200,
            'languages' => $languages,
            'timezones' => $timezones,
            'countries' => $countries,
            'currencies' => $currencies,
            'columns' => $columns,
            'groups' => $groups
        ];
    }

    /**
     *
     * @access public
     * @return array
     */
    public function post_whitelabel_users_list()
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }
        $whitelabel = Input::json('whitelabel_id');
        $current_user = $this->current_user();
        if ($current_user->role_id != 1 && !Model_Admin_Modules::check_user_module('users-view', $whitelabel, $current_user->id)) {
            return $this->authorizationError();
        }
        $lang_code = $current_user->code;
        $activity = Input::json('activeTab');
        $filters = Input::json('filters');
        $page = (int)Input::json('page');
        $items_per_page = (int)Input::json('itemsPerPage');
        $sort_by = Input::json('sortBy');
        $order = Input::json('order');
        $group = Input::json('group_id');

        $active = Model_Whitelabel_User::get_counts_for_crm($whitelabel, 'active', $filters, $group);
        $inactive = Model_Whitelabel_User::get_counts_for_crm($whitelabel, 'inactive', $filters, $group);
        $deleted = Model_Whitelabel_User::get_counts_for_crm($whitelabel, 'deleted', $filters, $group);

        $users = Model_Whitelabel_User::get_data_for_crm($whitelabel, $activity, $filters, $page, $items_per_page, $sort_by, $order, $group);
        Autoloader::add_namespace('libphonenumber', APPPATH . 'vendor/libphonenumber/', true);
        foreach ($users as &$user) {
            $this->parse_whitelabel_user_data($user, $whitelabel, $lang_code);
        }

        return [
            'code' => 200,
            'users' => $users,
            'activeCount' => $active,
            'inactiveCount' => $inactive,
            'deletedCount' => $deleted
        ];
    }

    /**
     *
     * @access public
     * @return array
     */
    public function post_export_whitelabel_users()
    {
        set_time_limit(600);

        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }
        $whitelabel = Input::json('whitelabel_id');

        $current_user = $this->current_user();
        if ($current_user->role_id != 1 && !Model_Admin_Modules::check_user_module('users-view', $whitelabel, $current_user->id)) {
            return $this->authorizationError();
        }

        $columns = Input::json('columns');
        $filters = Input::json('filters');
        $activity = Input::json('activeTab');
        $group = Input::json('group_id');

        list($export_columns, $columns_headers) = $this->prepare_export_columns($columns);
        $users = Model_Whitelabel_User::get_export_data_for_crm($whitelabel, $activity, $filters, $export_columns, $group);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment');

        $this->closeSession();

        $output = fopen('php://output', 'w');

        fputcsv($output, $columns_headers);

        foreach ($users as $user) {
            $user_export = $this->prepare_export_data($user, $columns);
            fputcsv($output, $user_export);
            ob_flush();
            flush();
        }

        fclose($output);
    }

    /**
     *
     * @access public
     * @return array
     */
    public function post_user_details()
    {
        $fullToken = Input::json('token');
        if (!$this->middleware_auth() || FullTokenHelper::isNotValid($fullToken)) {
            return $this->authorizationError();
        }

        $userToken = FullTokenHelper::getToken($fullToken);
        $whitelabelRepository = Container::get(WhitelabelRepository::class);
        $whitelabel = $whitelabelRepository->getWhitelabelByFullToken($fullToken);
        $whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);
        $user = $whitelabelUserRepository->findByTokenAndWhitelabelId($userToken, $whitelabel->id);
        $currentLoggedInCrmUser = $this->current_user();

        if ($currentLoggedInCrmUser->role_id != 1 && !Model_Admin_Modules::check_user_module('users-view', $user->whitelabel_id, $currentLoggedInCrmUser->id)) {
            return $this->authorizationError();
        }

        $lang_code = $currentLoggedInCrmUser->code;
        $userInArray = $whitelabelUserRepository->getUserAsArrayForCrmUserDetails($user->id);
        $userInArray['full_token'] = $fullToken;
        $user->full_token = $fullToken;
        $this->parse_whitelabel_user_data($userInArray, $whitelabel, $lang_code);

        $user_groups = Model_Whitelabel_User_Group::get_for_user($user->id);

        return [
            'code' => 200,
            'user' => $userInArray,
            'groups' => $user_groups
        ];
    }

    /**
     *
     * @access public
     * @return array
     */
    public function post_whitelabel_user_edit_details()
    {
        $fullToken = Input::json('token');
        if (!$this->middleware_auth() || FullTokenHelper::isNotValid($fullToken)) {
            return $this->authorizationError();
        }

        $userToken = FullTokenHelper::getToken($fullToken);
        $whitelabelRepository = Container::get(WhitelabelRepository::class);
        $whitelabel = $whitelabelRepository->getWhitelabelByFullToken($fullToken);
        $whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);
        $user = $whitelabelUserRepository->findByTokenAndWhitelabelId($userToken, $whitelabel->id);
        $currentLoggedInCrmUser = $this->current_user();

        if ($currentLoggedInCrmUser->role_id != 1 && !Model_Admin_Modules::check_user_module('users-view', $user->whitelabel_id, $currentLoggedInCrmUser->id)) {
            return $this->authorizationError();
        }
        $user_groups = Model_Whitelabel_User_Group::get_for_user($user->id);
        $groups = Model_Whitelabel_User_Group::get_all_groups_for_whitelabel($user->whitelabel_id);

        return [
            'code' => 200,
            'user' => $user,
            'groups' => $groups,
            'user_groups' => $user_groups
        ];
    }

    /**
     *
     * @access public
     * @return array
     */
    public function post_whitelabel_user_countries_timezones()
    {
        $fullToken = Input::json('token');
        if (!$this->middleware_auth() || FullTokenHelper::isNotValid($fullToken)) {
            return $this->authorizationError();
        }

        $userToken = FullTokenHelper::getToken($fullToken);
        $whitelabelRepository = Container::get(WhitelabelRepository::class);
        $whitelabel = $whitelabelRepository->getWhitelabelByFullToken($fullToken);
        $whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);
        $user = $whitelabelUserRepository->findByTokenAndWhitelabelId($userToken, $whitelabel->id);
        $currentLoggedInCrmUser = $this->current_user();

        if ($currentLoggedInCrmUser->role_id != 1 && !Model_Admin_Modules::check_user_module('users-view', $user->whitelabel_id, $currentLoggedInCrmUser->id)) {
            return $this->authorizationError();
        }
        $lang_code = $currentLoggedInCrmUser->code;

        $timezones = Lotto_Helper::get_timezone_list($lang_code);
        $countries = Lotto_Helper::get_localized_country_list($lang_code);
        $prefixes = Lotto_Helper::get_telephone_prefix_list();
        $regions = $this->get_country_regions($user->country);

        return [
            'code' => 200,
            'timezones' => $timezones,
            'countries' => $countries,
            'prefixes' => $prefixes,
            'regions' => $regions
        ];
    }

    /**
     * @access private
     * @param array &$user
     * @param int $whitelabel
     * @param string $lng
     */
    private function parse_whitelabel_user_data(&$user, $whitelabel, $lng)
    {
        $lang = $user['language_code'];
        $country = $user['country'];
        $last_country = $user['last_country'];
        $register_country = $user['register_country'];
        $user['language'] = Locale::getDisplayLanguage($lang, $lng);
        $user['country_name'] = Locale::getDisplayRegion('-' . $country, $lng);
        $user['last_country_name'] = Locale::getDisplayRegion('-' . $last_country, $lng);
        $user['register_country_name'] = Locale::getDisplayRegion('-' . $register_country, $lng);
        if (!empty($user['phone']) && !empty($user['phone_country'])) {
            $user['phone'] = Lotto_View::format_phone($user['phone'], $user['phone_country'], false);
        }
        if (!empty($user['state'])) {
            $user['state'] = Lotto_View::get_region_name($user['state'], false, false);
        }
        $user['full_token'] = $user['whitelabel_prefix'] . "U" . $user['token'];
        switch ($user['gender']) {
            case "1":
                $user['gender'] = _("Male");
                break;
            case "2":
                $user['gender'] = _("Female");
                break;
            default:
                $user['gender'] = "-";
        }
        switch ($user['sale_status']) {
            case "1":
                $user['sale_status'] = _("Started deposit");
                break;
            case "2":
                $user['sale_status'] = _("Deposited");
                break;
            case "3":
                $user['sale_status'] = _("Started purchase");
                break;
            case "4":
                $user['sale_status'] = _("Purchased");
                break;
            default:
                $user['sale_status'] = "-";
        }
        $affiliate = _("None");
        if (isset($user['aff_id'])) {
            $affiliate = "";
            if ($user['aff_name'] || $user['aff_surname']) {
                $affiliate = $user['aff_name'] . " " . $user['aff_surname'] . " • ";
            }
            $affiliate .= $user['aff_login'];
        }
        $user['affiliate'] = $affiliate;

        $currency = "USD";
        $fmt = new NumberFormatter($lng, NumberFormatter::CURRENCY);

        if ($whitelabel !== 0) {
            $currency = $user['whitelabel_currency_code'];
        }

        $balanceCurrencyTab = [
            'id' => $user['user_currency_id'],
            'code' => $user['user_currency_code'],
            'rate' => $user['user_currency_rate'],
        ];
        $balanceInManagerCurrency = (float)Helpers_Currency::get_recalculated_to_given_currency(
            $user['balance'],
            $balanceCurrencyTab,
            $currency
        );
        $balanceInManagerCurrencyFull = $fmt->formatCurrency($balanceInManagerCurrency, $currency);

        $user['balance_display'] = $balanceInManagerCurrencyFull;

        $bonusBalanceInManagerCurrency = (float)Helpers_Currency::get_recalculated_to_given_currency(
            $user['bonus_balance'],
            $balanceCurrencyTab,
            $currency
        );
        $bonusBalanceInManagerCurrencyFull = $fmt->formatCurrency($bonusBalanceInManagerCurrency, $currency);

        $user['bonus_balance_display'] = $bonusBalanceInManagerCurrencyFull;

        $casinoBalanceInManagerCurrency = (float)Helpers_Currency::get_recalculated_to_given_currency(
            $user['casino_balance'],
            $balanceCurrencyTab,
            $currency
        );
        $casinoBalanceInManagerCurrencyFull = $fmt->formatCurrency($casinoBalanceInManagerCurrency, $currency);

        $user['casino_balance_display'] = $casinoBalanceInManagerCurrencyFull;

        if ($user['user_currency_code'] !== $currency) {
            if ((float)$user['balance'] > 0.00) {
                $user_balance_text = $fmt->formatCurrency($user['balance'], $user['user_currency_code']);
                $user['balance_additional_text'] = _("User currency") .
                    ": " . $user_balance_text;
            }

            if ((float)$user['bonus_balance'] > 0.00) {
                $user_bonus_balance_text = $fmt->formatCurrency($user['bonus_balance'], $user['user_currency_code']);
                $user['bonus_balance_additional_text'] = _("User currency") .
                    ": " . $user_bonus_balance_text;
            }

            if ((float)$user['casino_balance'] > 0.00) {
                $user_casino_balance_text = $fmt->formatCurrency($user['casino_balance'], $user['user_currency_code']);
                $user['casino_balance_additional_text'] = _("User currency") .
                    ": " . $user_casino_balance_text;
            }
        }

        $amounts = [
            "last_purchase_amount_manager",
            "first_deposit_amount_manager",
            "last_deposit_amount_manager",
            "total_deposit_manager",
            "total_withdrawal_manager",
            "total_purchases_manager",
            "total_net_income_manager",
            "net_winnings_manager",
            "pnl_manager"
        ];
        if ($whitelabel === 0) {
            foreach ($amounts as $amount) {
                $value = $user[$amount] / $user['whitelabel_currency_rate'];
                $user[$amount] = $fmt->formatCurrency($value, $currency);
            }
        } else {
            foreach ($amounts as $amount) {
                $user[$amount] = $fmt->formatCurrency($user[$amount], $user['whitelabel_currency_code']);
            }
            $currency = $user['whitelabel_currency_code'];
        }
    }

    /**
     *
     * @access public
     * @return array
     */
    public function post_user_country_regions()
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }
        $country = Input::json('country');
        $regions = $this->get_country_regions($country);

        return [
            'code' => 200,
            'regions' => $regions
        ];
    }

    /**
     *
     * @access private
     * @param string $country
     * @return array
     */
    private function get_country_regions($country)
    {
        $regions = [];
        $subdivisions = json_decode(file_get_contents(APPPATH . 'vendor/iso/subdivisions.json'), true);
        foreach ($subdivisions as $key => $item) {
            if ($item[0] == $country) {
                $regions[$key] = $item;
            }
        }
        return $regions;
    }

    public function post_logs_actions(): array
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }

        $whitelabelId = Input::json('whitelabelId');
        $denyAccess = $this->user->isNotSuperadmin() && !Model_Admin_Modules::check_user_module('logs-view', $whitelabelId, $this->user->id);
        if ($denyAccess) {
            return $this->authorizationError();
        }

        $this->crmTableService->setDangerousFields(['id']);
        $this->crmTableService->fetchTableDataByRepository(CrmLogRepository::class);

        return [
            'code' => 200,
            'tableData' => $this->crmTableService->getTableData(),
            'itemsCountPerTab' => $this->crmTableService->getItemsCountPerTab()
        ];
    }

    public function post_casino_transactions(): array
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }

        $whitelabelId = Input::json('whitelabelId');
        $denyAccess = $this->user->isNotSuperadmin() &&
            !Model_Admin_Modules::check_user_module(
                'casino-transactions-view',
                $whitelabelId,
                $this->user->id
            );
        if ($denyAccess) {
            return $this->authorizationError();
        }

        $this->crmTableService->setWhitelabelIdFieldLocation('whitelabel_slot_provider.whitelabel_id');
        $this->crmTableService->setDangerousFields(['id']);
        $this->crmTableService->fetchTableDataByRepository(
            SlotTransactionRepository::class,
            fn (SlotTransaction $slotTransaction) => [
                'token' => $slotTransaction->whitelabelSlotProvider->whitelabel->prefix . 'C' . $slotTransaction->token
            ]
        );

        return [
            'code' => 200,
            'tableData' => $this->crmTableService->getTableData(),
            'itemsCountPerTab' => $this->crmTableService->getItemsCountPerTab()
        ];
    }

    /**
     *
     * @access public
     * @return array
     */
    public function post_user_get_email()
    {
        $fullToken = Input::json('id');
        if (!$this->middleware_auth() || FullTokenHelper::isNotValid($fullToken)) {
            return $this->authorizationError();
        }

        $userToken = FullTokenHelper::getToken($fullToken);
        $whitelabelRepository = Container::get(WhitelabelRepository::class);
        $whitelabel = $whitelabelRepository->getWhitelabelByFullToken($fullToken);
        $whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);
        $user = $whitelabelUserRepository->findByTokenAndWhitelabelId($userToken, $whitelabel->id);
        $currentLoggedInCrmUser = $this->current_user();

        if ($currentLoggedInCrmUser->role_id != 1 && !Model_Admin_Modules::check_user_module(CrmModuleHelper::MODULE_USERS_EDIT_ACCOUNT_PERSONAL_DATA, $user->whitelabel_id, $currentLoggedInCrmUser->id)) {
            return $this->authorizationError();
        }

        return [
            'code' => 200,
            'email' => $user->email
        ];
    }

    /**
     *
     * @access public
     * @return array
     */
    public function post_user_get_balance_and_email()
    {
        $fullToken = Input::json('id');
        if (!$this->middleware_auth() || FullTokenHelper::isNotValid($fullToken)) {
            return $this->authorizationError();
        }

        $userToken = FullTokenHelper::getToken($fullToken);
        $whitelabelRepository = Container::get(WhitelabelRepository::class);
        $whitelabel = $whitelabelRepository->getWhitelabelByFullToken($fullToken);
        $whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);
        $user = $whitelabelUserRepository->findByTokenAndWhitelabelId($userToken, $whitelabel->id);
        $currentLoggedInCrmUser = $this->current_user();

        if (empty($user)) {
            return $this->authorizationError();
        }

        $bonus = Input::json('isBonus');
        $isBonus = $bonus === true;
        $casino = Input::json('isCasino');
        $isCasino = $casino === true;

        $moduleNameNeededToAccess = self::getEditUserBalanceModuleName($isBonus, $isCasino);

        $user_is_not_able_to_get_data = $currentLoggedInCrmUser->role_id != 1 &&
            !Model_Admin_Modules::check_user_module($moduleNameNeededToAccess, $user->whitelabel_id, $currentLoggedInCrmUser->id);

        if ($user_is_not_able_to_get_data) {
            return $this->authorizationError();
        }

        $email = $user->email;
        $balance = $user->balance;
        $bonus_balance = $user->bonus_balance;
        $casinoBalance = $user->casino_balance;
        $currency = Model_Currency::find_by_pk($user->currency_id);
        $currency_code = $currency->code;
        $currency = Lotto_View::format_currency_code($currency_code);

        return [
            'code' => 200,
            'email' => $email,
            'balance' => $balance,
            'bonus_balance' => $bonus_balance,
            'casino_balance' => $casinoBalance,
            'currency' => $currency,
        ];
    }

    /**
     *
     * @access public
     * @return array
     */
    public function post_user_get_affiliate_and_email()
    {
        $fullToken = Input::json('id');
        if (!$this->middleware_auth() || FullTokenHelper::isNotValid($fullToken)) {
            return $this->authorizationError();
        }

        $userToken = FullTokenHelper::getToken($fullToken);
        $whitelabelRepository = Container::get(WhitelabelRepository::class);
        $whitelabel = $whitelabelRepository->getWhitelabelByFullToken($fullToken);
        $whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);
        $user = $whitelabelUserRepository->findByTokenAndWhitelabelId($userToken, $whitelabel->id);
        $currentLoggedInCrmUser = $this->current_user();

        if ($currentLoggedInCrmUser->role_id != 1 && !Model_Admin_Modules::check_user_module(CrmModuleHelper::MODULE_USERS_EDIT_ACCOUNT_PERSONAL_DATA, $user->whitelabel_id, $currentLoggedInCrmUser->id)) {
            return $this->authorizationError();
        }

        $result =
            Model_Whitelabel_User::get_user_affiliate_for_crm($user->id);
        $email = $user->email;
        $affiliate = $result['affiliate'];
        $whitelabel_id = $result['whitelabel_id'];
        $params['where'] = ['is_deleted' => 0, 'is_active' => 1, 'is_accepted' => 1];
        $params["order_by"] = [
            "name" => "ASC",
            "surname" => "ASC",
            "login" => "ASC"
        ];
        $affiliates = Model_Whitelabel_Aff::find($params);

        return [
            'code' => 200,
            'whitelabel_id' => $whitelabel_id,
            'email' => $email,
            'affiliate' => $affiliate,
            'affiliates' => $affiliates
        ];
    }

    /**
     *
     * @access public
     * @return array
     */
    public function post_user_get_email_currency_payment_methods()
    {
        $fullToken = Input::json('id');
        if (!$this->middleware_auth() || FullTokenHelper::isNotValid($fullToken)) {
            return $this->authorizationError();
        }

        $userToken = FullTokenHelper::getToken($fullToken);
        $whitelabelRepository = Container::get(WhitelabelRepository::class);
        $whitelabel = $whitelabelRepository->getWhitelabelByFullToken($fullToken);
        $whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);
        $user = $whitelabelUserRepository->findByTokenAndWhitelabelId($userToken, $whitelabel->id);
        $currentLoggedInCrmUser = $this->current_user();

        $bonus = Input::json('isBonus');
        $isBonus = $bonus === true;

        // Some payment methods are disabled for casino in payment_method.is_enabled_for_casino
        $casino = Input::json('isCasino');
        $isCasino = $casino === true;

        $moduleNameNeededToAccess = self::getAddManualDepositModuleName($isBonus, $isCasino);

        if ($currentLoggedInCrmUser->role_id != 1 && !Model_Admin_Modules::check_user_module($moduleNameNeededToAccess, $whitelabel->id, $currentLoggedInCrmUser->id)) {
            return $this->authorizationError();
        }

        $email = $user->email;
        $currency = Model_Currency::find_by_pk($user->currency_id);
        $currencyCode = $currency->code;
        $currency = Lotto_View::format_currency_code($currencyCode);

        $paymentMethods = Model_Whitelabel_Payment_Method::get_all_payment_methods_for_whitelabel_id($whitelabel->id, $isCasino);

        return [
            'code' => 200,
            'currency' => $currency,
            'email' => $email,
            'methods' => $paymentMethods
        ];
    }

    /**
     *
     * @access public
     * @return array
     */
    public function post_user_edit_email()
    {
        $fullToken = Input::json('id');
        if (!$this->middleware_auth() || FullTokenHelper::isNotValid($fullToken)) {
            return $this->authorizationError();
        }

        $userToken = FullTokenHelper::getToken($fullToken);
        $whitelabelRepository = Container::get(WhitelabelRepository::class);
        $whitelabel = $whitelabelRepository->getWhitelabelByFullToken($fullToken);
        $whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);
        $user = $whitelabelUserRepository->findByTokenAndWhitelabelId($userToken, $whitelabel->id);
        $currentLoggedInCrmUser = $this->current_user();

        if ($currentLoggedInCrmUser->role_id != 1 && !Model_Admin_Modules::check_user_module(CrmModuleHelper::MODULE_USERS_EDIT_ACCOUNT_PERSONAL_DATA, $user->whitelabel_id, $currentLoggedInCrmUser->id)) {
            return $this->authorizationError();
        }
        $new_email = Input::json('email');

        $validation = Validation::forge();
        $validation->add("email", _('Email'))
            ->add_rule("required")
            ->add_rule("trim")
            ->add_rule("valid_email")
            ->add_rule("max_length", 254);

        if (!$validation->run(['email' => $new_email])) {
            $error = Lotto_Helper::generate_errors($validation->error());
            return [
                'code' => 400,
                'error' => $error['email']
            ];
        }

        Model_Whitelabel_User::update_email_by_crm($user->id, $new_email);

        return [
            'code' => 200,
            'message' => _('User email updated successfully.')
        ];
    }

    /**
     *
     * @access public
     * @return array
     */
    public function post_user_edit_password()
    {
        $fullToken = Input::json('id');
        if (!$this->middleware_auth() || FullTokenHelper::isNotValid($fullToken)) {
            return $this->authorizationError();
        }

        $userToken = FullTokenHelper::getToken($fullToken);
        $whitelabelRepository = Container::get(WhitelabelRepository::class);
        $whitelabel = $whitelabelRepository->getWhitelabelByFullToken($fullToken);
        $whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);
        $user = $whitelabelUserRepository->findByTokenAndWhitelabelId($userToken, $whitelabel->id);
        $currentLoggedInCrmUser = $this->current_user();

        if ($currentLoggedInCrmUser->role_id != 1 && !Model_Admin_Modules::check_user_module(CrmModuleHelper::MODULE_USERS_EDIT_ACCOUNT_PERSONAL_DATA, $user->whitelabel_id, $currentLoggedInCrmUser->id)) {
            return $this->authorizationError();
        }
        $new_password = Input::json('password');

        $validation = Validation::forge();
        $validation->add("password", _('Password'))
            ->add_rule("required")
            ->add_rule("trim")
            ->add_rule('min_length', 6);

        if (!$validation->run(['password' => $new_password])) {
            $error = Lotto_Helper::generate_errors($validation->error());
            return [
                'code' => 400,
                'error' => $error['password']
            ];
        }

        Model_Whitelabel_User::update_password_by_crm($user->id, $new_password);

        return [
            'code' => 200,
            'message' => _('User password updated successfully.')
        ];
    }

    /**
     *
     * @access public
     * @return array
     */
    public function post_user_edit_affiliate()
    {
        $fullToken = Input::json('id');
        if (!$this->middleware_auth() || FullTokenHelper::isNotValid($fullToken)) {
            return $this->authorizationError();
        }

        $userToken = FullTokenHelper::getToken($fullToken);
        $whitelabelRepository = Container::get(WhitelabelRepository::class);
        $whitelabel = $whitelabelRepository->getWhitelabelByFullToken($fullToken);
        $whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);
        $user = $whitelabelUserRepository->findByTokenAndWhitelabelId($userToken, $whitelabel->id);
        $currentLoggedInCrmUser = $this->current_user();

        if ($currentLoggedInCrmUser->role_id != 1 && !Model_Admin_Modules::check_user_module(CrmModuleHelper::MODULE_USERS_EDIT_ACCOUNT_PERSONAL_DATA, $whitelabel->id, $currentLoggedInCrmUser->id)) {
            return $this->authorizationError();
        }

        $new_affiliate = Input::json('affiliate');

        $validation = Validation::forge();
        $validation->add_callable('Helpers_Crm_Validation');

        $validation->set_message('check_affiliate', _('Invalid affiliate.'));
        $validation->add("affiliate_id", _('Affiliate'))
            ->add_rule("required")
            ->add_rule("trim")
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 1)
            ->add_rule("check_affiliate");

        if (!$validation->run(['affiliate_id' => $new_affiliate])) {
            $error = Lotto_Helper::generate_errors($validation->error());
            return [
                'code' => 400,
                'error' => $error['affiliate_id']
            ];
        }

        $old_affiliate = Model_Whitelabel_User_Aff::find_by_whitelabel_user_id($user->id);
        if ($old_affiliate && count($old_affiliate) > 0) {
            $old_affiliate[0]->delete();
        }
        $affiliate = Model_Whitelabel_User_Aff::forge();

        $affiliate->set([
            "whitelabel_id" => $user->whitelabel_id,
            "whitelabel_user_id" => $user->id,
            "whitelabel_aff_id" => $new_affiliate,
            "whitelabel_aff_medium_id" => null,
            "whitelabel_aff_campaign_id" => null,
            "whitelabel_aff_content_id" => null,
            "is_deleted" => 0,
            "is_accepted" => 1
        ]);

        $affiliate->save();

        return [
            'code' => 200,
            'message' => _('User affiliate updated successfully.')
        ];
    }

    /**
     *
     * @access public
     * @return array
     */
    public function post_user_edit_balance()
    {
        $fullToken = Input::json('id');
        if (!$this->middleware_auth() || FullTokenHelper::isNotValid($fullToken)) {
            return $this->authorizationError();
        }

        $userToken = FullTokenHelper::getToken($fullToken);
        $whitelabelRepository = Container::get(WhitelabelRepository::class);
        $whitelabel = $whitelabelRepository->getWhitelabelByFullToken($fullToken);
        $whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);
        $user = $whitelabelUserRepository->findByTokenAndWhitelabelId($userToken, $whitelabel->id);
        $currentLoggedInCrmUser = $this->current_user();

        $bonus = Input::json('isBonus');
        $isBonus = $bonus === true;
        $casino = Input::json('isCasino');
        $isCasino = $casino === true;

        $moduleNameNeededToAccess = self::getEditUserBalanceModuleName($isBonus, $isCasino);

        $user_is_not_able_to_edit_balance = $currentLoggedInCrmUser->role_id != 1 && $currentLoggedInCrmUser->role_id != 2 &&
            (!Model_Admin_Modules::check_user_module($moduleNameNeededToAccess, $whitelabel->id, $currentLoggedInCrmUser->id) ||
                !$this->check_whitelabel_type_V2($whitelabel->id));

        if ($user_is_not_able_to_edit_balance) {
            return $this->authorizationError();
        }

        $balance = Input::json('balance');
        $new_balance = trim($balance);

        $validation = Validation::forge();
        $validation->add("balance", _('Balance'))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 0)
            ->add_rule("numeric_max", 9999999);

        if (!$validation->run(['balance' => $new_balance])) {
            $error = Lotto_Helper::generate_errors($validation->error());
            return [
                'code' => 400,
                'error' => $error['balance']
            ];
        }

        if ($isBonus) {
            $previousBalance = $user->bonus_balance;
            $balanceName = 'bonus balance';
        } elseif ($isCasino) {
            $previousBalance = $user->casino_balance;
            $balanceName = 'casino balance';
        } else {
            $previousBalance = $user->balance;
            $balanceName = 'balance';
        }

        Model_Whitelabel_User::update_balance_by_crm($user->id, $new_balance, $isBonus, $isCasino);

        $this->crmLoggerService->log(
            $this->user,
            $whitelabel->id,
            $moduleNameNeededToAccess,
            "User $balanceName was updated",
            [
                'whitelabel_user_email' => $user->email,
                'whitelabel_user_login' => $user->login,
                'previous_balance' => "{$previousBalance} {$user->currency->code}",
                'new_balance' => "{$new_balance} {$user->currency->code}",
            ]
        );

        return [
            'code' => 200,
            'message' => _('User balance updated successfully.')
        ];
    }

    /**
     *
     * @access public
     * @return array
     */
    public function post_user_manual_deposit()
    {
        $fullToken = Input::json('id');
        if (!$this->middleware_auth() || FullTokenHelper::isNotValid($fullToken)) {
            return $this->authorizationError();
        }

        $userToken = FullTokenHelper::getToken($fullToken);
        $whitelabelRepository = Container::get(WhitelabelRepository::class);
        $whitelabel = $whitelabelRepository->getWhitelabelByFullToken($fullToken);
        $whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);
        $user = $whitelabelUserRepository->findByTokenAndWhitelabelId($userToken, $whitelabel->id);
        $currentLoggedInCrmUser = $this->current_user();

        if (empty($user)) {
            return [
                'code' => 404,
                'message' => _("Incorrect user.")
            ];
        }

        $bonus = Input::json('isBonus');
        $isBonus = $bonus === true;

        $casino = Input::json('isCasino');
        $isCasino = $casino === true;

        $moduleNameNeededToAccess = self::getAddManualDepositModuleName($isBonus, $isCasino);

        $user_is_not_able_to_manual_deposit = $currentLoggedInCrmUser->role_id != 1 && $currentLoggedInCrmUser->role_id != 2 &&
            (!Model_Admin_Modules::check_user_module($moduleNameNeededToAccess, $whitelabel->id, $currentLoggedInCrmUser->id) ||
                !$this->check_whitelabel_type_V2($whitelabel->id));


        if ($user_is_not_able_to_manual_deposit) {
            return $this->authorizationError();
        }

        $amount = Input::json('amount');
        $method = Input::json('method');
        $methodName = Input::json('methodName');

        $validator = new Helpers_Crm_Validation();
        $errors = $validator->check_for_errors_manual_deposit(['amount' => $amount, 'method' => $method]);

        if ($errors) {
            return [
                'code' => 400,
                'errors' => $errors
            ];
        }

        $deprecatedWhitelabelModel = Model_Whitelabel::find_by_pk($whitelabel->id)->to_array();
        $deprecatedUserModel = Model_Whitelabel_User::find_one_by(['id' => $user-> id]);
        $user_deposit = new Forms_Whitelabel_User_Deposit($deprecatedWhitelabelModel, $deprecatedUserModel);
        $result = $user_deposit->process_manual_deposit_from_crm($method, $amount, $isBonus, $isCasino);
        $transactionToken = $user_deposit->getTransactionToken();

        $logMessage = 'Deposit was added to regular balance';
        if ($isCasino) {
            $logMessage = 'Casino deposit was added';
        }
        if ($isBonus) {
            $logMessage = 'Deposit was added to bonus balance';
        }

        switch ($result) {
            case Forms_Whitelabel_User_Deposit::RESULT_OK:
                $this->crmLoggerService->log(
                    $this->user,
                    $whitelabel->id,
                    $moduleNameNeededToAccess,
                    $logMessage,
                    [
                        'whitelabel_user_email' => $user->email,
                        'whitelabel_user_login' => $user->login,
                        'method_name' => $methodName,
                        'amount' => "{$amount} {$user->currency->code}",
                        'transaction_token' => $transactionToken
                    ]
                );
                return [
                    'code' => 200,
                    'message' => _('Deposit has been added!')
                ];
            case Forms_Whitelabel_User_Deposit::RESULT_MAX_DEPOSIT_REACHED:
                return [
                    'code' => 400,
                    'message' => _('Maximum deposit has been reached!')
                ];
                break;
            case Forms_Whitelabel_User_Deposit::RESULT_SECURITY_ERROR:
                return [
                    'code' => 400,
                    'message' => _("Security error! Please try again.")
                ];
                break;
        }
    }

    /**
     *
     * @access public
     * @return array
     */
    public function post_update_whitelabel_user_details()
    {
        $fullToken = Input::json('token');
        if (!$this->middleware_auth() || FullTokenHelper::isNotValid($fullToken)) {
            return $this->authorizationError();
        }

        $userToken = FullTokenHelper::getToken($fullToken);
        $whitelabelRepository = Container::get(WhitelabelRepository::class);
        $whitelabel = $whitelabelRepository->getWhitelabelByFullToken($fullToken);
        $whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);
        $user = $whitelabelUserRepository->findByTokenAndWhitelabelId($userToken, $whitelabel->id);
        $currentLoggedInCrmUser = $this->current_user();

        if ($currentLoggedInCrmUser->role_id != 1 && !Model_Admin_Modules::check_user_module(CrmModuleHelper::MODULE_USERS_EDIT_ACCOUNT_PERSONAL_DATA, $user->whitelabel_id, $currentLoggedInCrmUser->id)) {
            return $this->authorizationError();
        }
        $updatedUser = Input::json('updatedUser');

        $selectedGroups = Input::json('selectedGroups');
        $selectedGroupIds = [];
        foreach ($selectedGroups as $group) {
            $selectedGroupIds[] = $group['id'];
        }

        $actual_prize_payout_group = null;
        if (isset($updatedUser['prize_payout_whitelabel_user_group_id'])) {
            $actual_prize_payout_group = $updatedUser['prize_payout_whitelabel_user_group_id'];
        } else {
            $actual_prize_payout_group = $user->prize_payout_whitelabel_user_group_id;
        }

        Model_Whitelabel_User_Group::delete_all_for_users([$user->id]);

        $groups_changed = true;
        if (count($selectedGroupIds) > 0) {
            $groups_changed = Model_Whitelabel_User_Group::add_users_to_groups([$user->id], $selectedGroupIds);
        } else {
            $updatedUser['prize_payout_whitelabel_user_group_id'] = null;
        }

        if (!$groups_changed) {
            return [
                'code' => 400,
                'message' => _("User groups cannot be updated.")
            ];
        }

        $validator = new Helpers_Crm_Validation();
        $errors = $validator->check_for_errors_whitelabel_user($updatedUser, $selectedGroupIds, $actual_prize_payout_group);

        if ($errors) {
            return [
                'code' => 400,
                'errors' => $errors,
                'message' => _("User not updated - the form contains errors.")
            ];
        }
        if (isset($updatedUser['birthdate'])) {
            try {
                $date = new DateTime($updatedUser['birthdate']);
                $updatedUser['birthdate'] = $date->format('Y-m-d');
            } catch (Exception $e) {
                return [
                    'code' => 400,
                    'errors' => ['birthdate' => _("Invalid date format.")],
                    'message' => _("User not updated - the form contains errors.")
                ];
            }
        }

        if (!empty($updatedUser)) {
            $res = Model_Whitelabel_User::update_user_by_crm($user->id, $updatedUser);
            if (!$res) {
                return [
                    'code' => 404,
                    'message' => _("User not found.")
                ];
            }
        }

        return [
            'code' => 200,
            'message' => _("User updated successfully.")
        ];
    }

    /**
     *
     * @access public
     * @return array
     */
    public function post_delete_whitelabel_user()
    {
        $fullToken = Input::json('token');
        if (!$this->middleware_auth() || FullTokenHelper::isNotValid($fullToken)) {
            return $this->authorizationError();
        }

        $userToken = FullTokenHelper::getToken($fullToken);
        $whitelabelRepository = Container::get(WhitelabelRepository::class);
        $whitelabel = $whitelabelRepository->getWhitelabelByFullToken($fullToken);
        $whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);
        $user = $whitelabelUserRepository->findByTokenAndWhitelabelId($userToken, $whitelabel->id);
        $currentLoggedInCrmUser = $this->current_user();

        if ($currentLoggedInCrmUser->role_id != 1 && !Model_Admin_Modules::check_user_module('users-delete', $user->whitelabel_id, $currentLoggedInCrmUser->id)) {
            return $this->authorizationError();
        }

        Model_Whitelabel_User::delete_user_for_id($user->id);
        Model_Whitelabel_User_Group::delete_all_for_users([$user->id]);

        return [
            'code' => 200,
            'message' => 'User deleted successfully.'
        ];
    }

    /**
     *
     * @access public
     * @return array
     */
    public function post_restore_whitelabel_user()
    {
        $fullToken = Input::json('token');
        if (!$this->middleware_auth() || FullTokenHelper::isNotValid($fullToken)) {
            return $this->authorizationError();
        }

        $userToken = FullTokenHelper::getToken($fullToken);
        $whitelabelRepository = Container::get(WhitelabelRepository::class);
        $whitelabel = $whitelabelRepository->getWhitelabelByFullToken($fullToken);
        $whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);
        $user = $whitelabelUserRepository->findByTokenAndWhitelabelId($userToken, $whitelabel->id);
        $currentLoggedInCrmUser = $this->current_user();

        if ($currentLoggedInCrmUser->role_id != 1 && !Model_Admin_Modules::check_user_module(CrmModuleHelper::MODULE_USERS_EDIT_ACCOUNT_PERSONAL_DATA, $user->whitelabel_id, $currentLoggedInCrmUser->id)) {
            return $this->authorizationError();
        }

        Model_Whitelabel_User::user_restore_for_id($user->id);

        return [
            'code' => 200,
            'message' => 'User restored successfully.'
        ];
    }

    /**
     *
     * @access public
     * @return array
     */
    public function post_activate_whitelabel_user()
    {
        $fullToken = Input::json('token');
        if (!$this->middleware_auth() || FullTokenHelper::isNotValid($fullToken)) {
            return $this->authorizationError();
        }

        $userToken = FullTokenHelper::getToken($fullToken);
        $whitelabelRepository = Container::get(WhitelabelRepository::class);
        $whitelabel = $whitelabelRepository->getWhitelabelByFullToken($fullToken);
        $whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);
        $user = $whitelabelUserRepository->findByTokenAndWhitelabelId($userToken, $whitelabel->id);
        $currentLoggedInCrmUser = $this->current_user();

        if ($currentLoggedInCrmUser->role_id != 1 && !Model_Admin_Modules::check_user_module(CrmModuleHelper::MODULE_USERS_EDIT_ACCOUNT_PERSONAL_DATA, $user->whitelabel_id, $currentLoggedInCrmUser->id)) {
            return $this->authorizationError();
        }

        Model_Whitelabel_User::activate_user_for_id($user->id);

        return [
            'code' => 200,
            'message' => 'User activated and confirmed successfully.'
        ];
    }

    /**
     *
     * @access public
     * @return array
     */
    public function post_confirm_whitelabel_user()
    {
        $fullToken = Input::json('token');
        if (!$this->middleware_auth() || FullTokenHelper::isNotValid($fullToken)) {
            return $this->authorizationError();
        }

        $userToken = FullTokenHelper::getToken($fullToken);
        $whitelabelRepository = Container::get(WhitelabelRepository::class);
        $whitelabel = $whitelabelRepository->getWhitelabelByFullToken($fullToken);
        $whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);
        $user = $whitelabelUserRepository->findByTokenAndWhitelabelId($userToken, $whitelabel->id);
        $currentLoggedInCrmUser = $this->current_user();

        if ($currentLoggedInCrmUser->role_id != 1 && !Model_Admin_Modules::check_user_module(CrmModuleHelper::MODULE_USERS_EDIT_ACCOUNT_PERSONAL_DATA, $user->whitelabel_id, $currentLoggedInCrmUser->id)) {
            return $this->authorizationError();
        }

        Model_Whitelabel_User::user_confirm_for_id($user->id);

        return [
            'code' => 200,
            'message' => 'User confirmed successfully.'
        ];
    }

    /**
     *
     * @access public
     * @return array
     */
    public function post_whitelabel_users_stats()
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }
        $start_date = Input::json('start_date');
        $end_date = Input::json('end_date');
        $whitelabel_id = Input::json('whitelabel_id');
        $current_user = $this->current_user();
        if ($current_user->role_id != 1 && !Model_Admin_Modules::check_admin_whitelabel($whitelabel_id, $current_user->id)) {
            return $this->authorizationError();
        }
        if ($whitelabel_id == 0) {
            $whitelabel_id = null;
        }

        $registered = Model_Whitelabel_User::get_registered_count_for_crm($start_date, $end_date, $whitelabel_id);
        $deposit = Model_Whitelabel_User::get_first_deposit_count_for_crm($start_date, $end_date, $whitelabel_id);
        return [
            'code' => 200,
            'registered' => $registered,
            'deposit' => $deposit
        ];
    }

    public function post_dashboard_data(): array
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }

        $language_code = Input::json('language_code');
        $range = Input::json('range');
        $start_date = Input::json('start_date');
        $end_date = Input::json('end_date');

        $whitelabel_id = Input::json('whitelabel_id');
        $current_user = $this->current_user();
        if (($current_user->role_id != 1 && $current_user->role_id != 2) && !Model_Admin_Modules::check_admin_whitelabel($whitelabel_id, $current_user->id)) {
            return $this->authorizationError();
        }
        if ($whitelabel_id == 0) {
            $whitelabel_id = null;
        }

        $curr_conf = $current_user->config_data;
        if (empty($curr_conf) || $curr_conf !== $range) {
            $user_obj = Model_Admin_User::find_by_pk($current_user->id);
            $user_obj->set(['config_data' => $range]);
            $user_obj->save();
        }

        $registered_data = Model_Whitelabel_User::get_registered_active_users_count_for_crm_last_seven_days($whitelabel_id);
        $registered_count = Model_Whitelabel_User::get_registered_active_users_count_for_crm($whitelabel_id, $start_date, $end_date);
        $ftd_data = Model_Whitelabel_User::get_ftd_count_for_crm_last_seven_days($whitelabel_id);
        $ftd_count = Model_Whitelabel_User::get_ftd_count_for_crm($whitelabel_id, $start_date, $end_date);

        $whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);
        $ftpData = $whitelabelUserRepository->getFTPDataForCRMChartForLastSevenDays($whitelabel_id);
        $ftpCount = $whitelabelUserRepository->getFTPCountForCRM($whitelabel_id, $start_date, $end_date);

        $deposits_data_last_seven_days = Model_Whitelabel_Transaction::get_deposits_count_for_crm_last_seven_days($whitelabel_id);
        $deposits_count = Model_Whitelabel_Transaction::get_deposits_count_for_crm_date_range($whitelabel_id, $start_date, $end_date);
        $sold_tickets_lines_last_seven_days_ago = Model_Whitelabel_User_Ticket::get_sold_tickets_lines_count_for_crm_last_seven_days($whitelabel_id);
        $sold_tickets_lines_count = Model_Whitelabel_User_Ticket::get_sold_tickets_lines_count_for_crm($whitelabel_id, $start_date, $end_date);
        $sold_tickets_amount = Model_Whitelabel_User_Ticket::get_sold_tickets_amount_for_crm($whitelabel_id, $start_date, $end_date);
        $won_tickets_data = Model_Whitelabel_User_Ticket::get_won_tickets_count_for_crm_last_seven_days($whitelabel_id);
        $won_tickets_count = Model_Whitelabel_User_Ticket::get_won_tickets_count_for_crm($whitelabel_id, $start_date, $end_date);
        $amount_data_last_seven_days = Model_Whitelabel_User_Ticket::get_amount_for_crm_last_seven_days($whitelabel_id);
        $amount_date_range = Model_Whitelabel_User_Ticket::get_amount_for_crm_date_range($whitelabel_id, $start_date, $end_date);
        $total_amount = Model_Whitelabel_User_Ticket::get_total_amount_for_crm($whitelabel_id, $start_date, $end_date);
        $cost_data_last_seven_days = Model_Whitelabel_User_Ticket::get_cost_for_crm_last_seven_days($whitelabel_id);
        $cost_date_range = Model_Whitelabel_User_Ticket::get_cost_for_crm_date_range($whitelabel_id, $start_date, $end_date);
        $total_cost = Model_Whitelabel_User_Ticket::get_total_cost_for_crm($whitelabel_id, $start_date, $end_date);
        $income_data = Model_Whitelabel_User_Ticket::get_income_for_crm_last_seven_days($whitelabel_id);
        $income_date_range = Model_Whitelabel_User_Ticket::get_income_for_crm_date_range($whitelabel_id, $start_date, $end_date);
        $total_income = Model_Whitelabel_User_Ticket::get_total_income_for_crm($whitelabel_id, $start_date, $end_date);
        $top_lotteries = Model_Whitelabel_User_Ticket::get_top_seller_lotteries_for_crm($whitelabel_id, $start_date, $end_date);
        $ftp_tickets_amount = Model_Whitelabel_User_Ticket::get_ftp_tickets_amount_for_crm($whitelabel_id, $start_date, $end_date);
        $stp_tickets_amount = Model_Whitelabel_User_Ticket::get_stp_tickets_amount_for_crm($whitelabel_id, $start_date, $end_date);
        $top_languages = Model_Whitelabel_User_Ticket::get_top_seller_languages_for_crm($whitelabel_id, $start_date, $end_date);
        $registrations = Model_Whitelabel_User::get_registered_count_for_crm($start_date, $end_date, $whitelabel_id);
        $first_deposits = Model_Whitelabel_User::get_first_deposit_count_for_crm($start_date, $end_date, $whitelabel_id);
        $payment_gateways = [];
        $payment_gateways_sum = $this->whitelabelTransactionRepository->getPaymentGatewayPurchaseAndDepositSumForCrmDateRange($whitelabel_id, $start_date, $end_date);
        $payment_gateways_details = $this->whitelabelTransactionRepository->getGatewayPurchaseAndDepositSumForCrmDateRangeByGateway($whitelabel_id, $start_date, $end_date);
        $requested_withdrawal = [];
        $requested_withdrawal_sum = $this->whitelabelWithdrawalRepository->getWithdrawalSumForCrmDateRange($whitelabel_id, $start_date, $end_date, Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_PENDING);
        $accepted_withdrawal = [];
        $accepted_withdrawal_sum = $this->whitelabelWithdrawalRepository->getWithdrawalSumForCrmDateRange($whitelabel_id, $start_date, $end_date, Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_APPROVED);

        $whitelabels = [];
        if ($current_user->role_id == 1) {
            $whitelabels = Model_Whitelabel_User_Ticket::get_whitelabels_amount_income_date_range($whitelabel_id, $start_date, $end_date);
        }

        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $interval = $end->diff($start);
        $days = $interval->d;
        $analogical_start_date = $start->modify('-' . $days . 'days')->format('Y-m-d');

        $top_countries = Model_Whitelabel_User_Ticket::get_top_seller_countries_for_crm($whitelabel_id, $start_date, $analogical_start_date, $end_date);

        foreach ($top_countries as &$country_data) {
            $code = $country_data['country'];
            if ($code) {
                $countries = json_decode(file_get_contents(APPPATH . 'vendor/iso/countries.json'), true);
                foreach ($countries as $country) {
                    if ($country['country_code'] == $code) {
                        $country_data['coordinates'] = $country['latlng'];
                    }
                }
                $country_data['country_name'] = Locale::getDisplayRegion('-' . $code, $language_code);
            } else {
                $country_data['country_name'] = _('Not specified');
                $country_data['coordinates'] = null;
            }

            $previous_amount = $country_data['previous_amount'];
            if ($previous_amount > 0) {
                $current_amount = floatval($country_data['amount']);
                $difference = $current_amount - $previous_amount;
                $percent = ($difference / $previous_amount * 100);
                $country_data['balance'] = round($percent);
            } else {
                $country_data['balance'] = 100;
            }
        }
        foreach ($top_languages as &$language) {
            $code = $language['code'];
            $language['name'] = Locale::getDisplayLanguage($code, $language_code);
        }

        $isTemporaryRestrictedUser = $current_user->id == 86 || $current_user->id == 89;
        if ($isTemporaryRestrictedUser) { // temporary restriction
            foreach ($top_countries as &$topCountry) {
                $topCountry['amount'] = 0;
                $topCountry['previous_amount'] = 0;
            }
            foreach ($top_lotteries as &$topLottery) {
                $topLottery['amount'] = 0;
            }
            foreach ($top_languages as &$language) {
                $language['amount'] = 0;
            }
            $income_data = [];
            $income_date_range = [];
            $cost_data_last_seven_days = [];
            $cost_date_range = [];
            $amount_last_seven_days = [];
            $amount_date_range = [];
            $total_income = 0;
            $total_cost = 0;
            $total_amount = 0;
        }

        return [
            'code' => 200,
            'registered' => $registered_data,
            'registered_count' => $registered_count,
            'ftd' => $ftd_data,
            'ftd_count' => $ftd_count,
            'ftp' => $ftpData,
            'ftp_count' => $ftpCount,
            'deposits_last_seven_days' => $deposits_data_last_seven_days,
            'deposits_count' => $deposits_count,
            'sold_tickets_lines' => $sold_tickets_lines_last_seven_days_ago,
            'sold_tickets_lines_count' => $sold_tickets_lines_count,
            'sold_tickets_amount' => $sold_tickets_amount,
            'won_tickets' => $won_tickets_data,
            'won_tickets_count' => $won_tickets_count,
            'amount_last_seven_days' => $amount_data_last_seven_days,
            'amount_date_range' => $amount_date_range,
            'total_amount' => $total_amount,
            'cost_data_last_seven_days' => $cost_data_last_seven_days,
            'cost_date_range' => $cost_date_range,
            'total_cost' => $total_cost,
            'income' => $income_data,
            'income_date_range' => $income_date_range,
            'total_income' => $total_income,
            'top_lotteries' => $top_lotteries,
            'ftp_tickets_amount' => $ftp_tickets_amount,
            'stp_tickets_amount' => $stp_tickets_amount,
            'top_countries' => $top_countries,
            'top_languages' => $top_languages,
            'whitelabels' => $whitelabels,
            'registrations' => $registrations,
            'first_deposits' => $first_deposits,
            'payment_gateways' => $payment_gateways,
            'payment_gateways_sum' => $payment_gateways_sum,
            'payment_gateways_details' => $payment_gateways_details,
            'requested_withdrawal' => $requested_withdrawal,
            'requested_withdrawal_sum' => $requested_withdrawal_sum,
            'accepted_withdrawal' => $accepted_withdrawal,
            'accepted_withdrawal_sum' => $accepted_withdrawal_sum,
        ];
    }

    public function post_transactions_data_date_range(): array
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }

        $whitelabelId = Input::json('whitelabel_id');
        $isCasino = Input::json('isCasino');
        $isDeposit = Input::json('isDeposit');

        $moduleName = CrmModuleHelper::MODULE_TRANSACTIONS_VIEW;

        if ($isDeposit) {
            $moduleName = $isCasino ? CrmModuleHelper::MODULE_CASINO_DEPOSITS_VIEW : CrmModuleHelper::MODULE_DEPOSITS_VIEW;
        }

        $currentUser = $this->current_user();
        if ($currentUser->role_id != 1 && !Model_Admin_Modules::check_user_module($moduleName, $whitelabelId, $currentUser->id)) {
            return $this->authorizationError();
        }
        $startDate = Input::json('start_date');
        $endDate = Input::json('end_date');

        if ($whitelabelId == 0) {
            $whitelabelId = null;
        }

        $pending = Model_Whitelabel_Transaction::get_transactions_pending_for_crm_date_range(
            $whitelabelId,
            $startDate,
            $endDate,
            $isCasino,
            $isDeposit
        );
        $approved = Model_Whitelabel_Transaction::get_transactions_approved_for_crm_date_range(
            $whitelabelId,
            $startDate,
            $endDate,
            $isCasino,
            $isDeposit
        );
        $error = Model_Whitelabel_Transaction::get_transactions_error_for_crm_date_range(
            $whitelabelId,
            $startDate,
            $endDate,
            $isCasino,
            $isDeposit
        );

        return [
            'code' => 200,
            'sd' => $startDate,
            'ed' => $endDate,
            'pending' => $pending,
            'approved' => $approved,
            'error' => $error
        ];
    }

    public function post_transactions_table_data(): array
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }

        $whitelabelId = Input::json('whitelabel_id');
        $isCasino = Input::json('isCasino');
        $isDeposit = Input::json('isDeposit');

        $moduleName = CrmModuleHelper::MODULE_TRANSACTIONS_VIEW;

        if ($isDeposit) {
            $moduleName = $isCasino ? CrmModuleHelper::MODULE_CASINO_DEPOSITS_VIEW : CrmModuleHelper::MODULE_DEPOSITS_VIEW;
        }

        $currentUser = $this->current_user();
        if ($currentUser->role_id != 1 && !Model_Admin_Modules::check_user_module($moduleName, $whitelabelId, $currentUser->id)) {
            return $this->authorizationError();
        }
        if ($whitelabelId == 0) {
            $whitelabelId = null;
        }
        $activeTab = Input::json('activeTab');
        $filters = Input::json('filters');
        $page = Input::json('page');
        $itemsPerPage = Input::json('itemsPerPage');
        $sortBy = Input::json('sortBy');
        $order = Input::json('order');

        $transactions = Model_Whitelabel_Transaction::get_transactions_data_for_crm(
            $whitelabelId,
            $activeTab,
            $filters,
            $page,
            $itemsPerPage,
            $sortBy,
            $order,
            false,
            $isCasino,
            $isDeposit
        );

        foreach ($transactions as &$transaction) {
            $this->prepare_whitelabel_transaction_data($transaction, $whitelabelId, $currentUser->code);
        }

        $allCount = Model_Whitelabel_Transaction::get_transactions_count_for_crm($whitelabelId, $filters, $isCasino, $isDeposit);
        $purchasesCount = Model_Whitelabel_Transaction::get_purchases_count_for_crm($whitelabelId, $filters, $isCasino, $isDeposit);
        $pendingCount = Model_Whitelabel_Transaction::get_pending_count_for_crm($whitelabelId, $filters, $isCasino, $isDeposit);
        $approvedCount = Model_Whitelabel_Transaction::get_approved_count_for_crm($whitelabelId, $filters, $isCasino, $isDeposit);
        $errorCount = Model_Whitelabel_Transaction::get_error_count_for_crm($whitelabelId, $filters, $isCasino, $isDeposit);
        $methods = $this->paymentMethodRepository->getAllMethods();

        return [
            'code' => 200,
            'table_data' => $transactions,
            'all_count' => $allCount,
            'purchases_count' => $purchasesCount,
            'pending_count' => $pendingCount,
            'approved_count' => $approvedCount,
            'error_count' => $errorCount,
            'methods' => $methods
        ];
    }

    public function post_transaction_details(): array
    {
        $fullToken = Input::json('token');
        if (!$this->middleware_auth() || FullTokenHelper::isNotValid($fullToken)) {
            return $this->authorizationError();
        }

        $whitelabelId = Input::json('whitelabel_id');
        $isCasino = Input::json('isCasino');
        $isDeposit = Input::json('isDeposit');

        $moduleName = CrmModuleHelper::MODULE_TRANSACTIONS_VIEW;

        if ($isDeposit) {
            $moduleName = $isCasino ? CrmModuleHelper::MODULE_CASINO_DEPOSITS_VIEW : CrmModuleHelper::MODULE_DEPOSITS_VIEW;
        }

        if ($whitelabelId == 0) {
            $whitelabelId = null;
        } else {
            $whitelabelRepository = Container::get(WhitelabelRepository::class);
            $whitelabel = $whitelabelRepository->getWhitelabelByFullToken($fullToken);
            $whitelabelId = $whitelabel->id;
        }

        $currentUser = $this->current_user();
        if ($currentUser->role_id != 1 && !Model_Admin_Modules::check_user_module($moduleName, $whitelabelId, $currentUser->id)) {
            return $this->authorizationError();
        }

        $token = FullTokenHelper::getToken($fullToken);
        $whitelabelRepository = Container::get(WhitelabelRepository::class);
        $whitelabel = $whitelabelRepository->getWhitelabelByFullToken($fullToken);
        $whitelabelTransactionRepository = Container::get(WhitelabelTransactionRepository::class);
        $details = $whitelabelTransactionRepository->getTransactionDetailsForCrmByTokenAndWhitelabelId($token, $whitelabel->id);
        $this->prepare_whitelabel_transaction_data($details, $whitelabel->id, $currentUser->code);


        return [
            'code' => 200,
            'details' => $details
        ];
    }

    public function post_withdrawals_data(): array
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }

        $whitelabelId = Input::json('whitelabel_id');
        $isCasino = Input::json('isCasino');
        $moduleName = $isCasino ? CrmModuleHelper::MODULE_CASINO_WITHDRAWALS_VIEW : CrmModuleHelper::MODULE_WITHDRAWALS_VIEW;

        $currentUser = $this->current_user();
        if ($currentUser->role_id != 1 && !Model_Admin_Modules::check_user_module($moduleName, $whitelabelId, $currentUser->id)) {
            return $this->authorizationError();
        }
        if ($whitelabelId == 0) {
            $whitelabelId = null;
        }

        $withdrawals = Model_Withdrawal_Request::get_withdrawals_count_for_crm_last_month($whitelabelId, $isCasino);

        $methods = WhitelabelWithdrawal::find("all");

        if (isset($whitelabelId)) {
            $methods = WhitelabelWithdrawal::find('all', [
                'where' => [
                    'whitelabel_id' => $whitelabelId
                ]
            ]);
        }

        $whitelabelWithdrawals = [];

        if (!empty($methods)) {
            /** @var WhitelabelWithdrawal $method */
            foreach ($methods as $method) {
                $whitelabelWithdrawal = $method->withdrawal;
                $whitelabelWithdrawal->name = _($whitelabelWithdrawal->name);

                $whitelabelWithdrawals[] = $whitelabelWithdrawal;
            }
        }

        return [
            'code' => 200,
            'withdrawals' => $withdrawals,
            'methods' => $whitelabelWithdrawals
        ];
    }

    public function post_withdrawals_table_data(): array
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }

        $whitelabelId = Input::json('whitelabel_id');
        $isCasino = Input::json('isCasino');
        $moduleName = $isCasino ? CrmModuleHelper::MODULE_CASINO_WITHDRAWALS_VIEW : CrmModuleHelper::MODULE_WITHDRAWALS_VIEW;

        $currentUser = $this->current_user();
        if ($currentUser->role_id != 1 && !Model_Admin_Modules::check_user_module($moduleName, $whitelabelId, $currentUser->id)) {
            return $this->authorizationError();
        }
        if ($whitelabelId == 0) {
            $whitelabelId = null;
        }
        $filters = Input::json('filters');
        $activeTab = Input::json('activeTab');
        $page = Input::json('page');
        $itemsPerPage = Input::json('itemsPerPage');
        $sortBy = Input::json('sortBy');
        $order = Input::json('order');

        $withdrawals = Model_Withdrawal_Request::get_withdrawals_data_for_crm(
            $whitelabelId,
            $activeTab,
            $filters,
            $page,
            $itemsPerPage,
            $sortBy,
            $order,
            false,
            $isCasino
        );

        foreach ($withdrawals as &$withdrawal) {
            $this->prepare_whitelabel_withdrawal_data($withdrawal, $whitelabelId, $currentUser->code);
        }

        $allCount = Model_Withdrawal_Request::get_withdrawals_count_for_crm($whitelabelId, $filters, $isCasino);
        $pendingCount = Model_Withdrawal_Request::get_withdrawals_pending_count_for_crm($whitelabelId, $filters, $isCasino);
        $approvedCount = Model_Withdrawal_Request::get_withdrawals_approved_count_for_crm($whitelabelId, $filters, $isCasino);
        $declinedCount = Model_Withdrawal_Request::get_withdrawals_declined_count_for_crm($whitelabelId, $filters, $isCasino);
        $canceledCount = Model_Withdrawal_Request::get_withdrawals_canceled_count_for_crm($whitelabelId, $filters, $isCasino);

        return [
            'code' => 200,
            'table_data' => $withdrawals,
            'all' => $allCount,
            'pending' => $pendingCount,
            'approved' => $approvedCount,
            'declined' => $declinedCount,
            'canceled' => $canceledCount
        ];
    }

    public function post_withdrawals_data_date_range(): array
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }

        $whitelabelId = Input::json('whitelabel_id');
        $isCasino = Input::json('isCasino');
        $moduleName = $isCasino ? CrmModuleHelper::MODULE_CASINO_WITHDRAWALS_VIEW : CrmModuleHelper::MODULE_WITHDRAWALS_VIEW;

        $currentUser = $this->current_user();
        if ($currentUser->role_id != 1 && !Model_Admin_Modules::check_user_module($moduleName, $whitelabelId, $currentUser->id)) {
            return $this->authorizationError();
        }
        $startDate = Input::json('start_date');
        $endDate = Input::json('end_date');

        if ($whitelabelId == 0) {
            $whitelabelId = null;
        }

        $pending = Model_Withdrawal_Request::get_pending_for_crm_date_range($whitelabelId, $startDate, $endDate, $isCasino);
        $approved = Model_Withdrawal_Request::get_approved_for_crm_date_range($whitelabelId, $startDate, $endDate, $isCasino);
        $declined = Model_Withdrawal_Request::get_declined_for_crm_date_range($whitelabelId, $startDate, $endDate, $isCasino);
        $canceled = Model_Withdrawal_Request::get_canceled_for_crm_date_range($whitelabelId, $startDate, $endDate, $isCasino);

        return [
            'code' => 200,
            'pending' => $pending,
            'approved' => $approved,
            'declined' => $declined,
            'canceled' => $canceled
        ];
    }

    public function post_withdrawal_details(): array
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }

        $whitelabelId = Input::json('whitelabel_id');
        $isCasino = Input::json('isCasino');
        $moduleName = $isCasino ? CrmModuleHelper::MODULE_CASINO_WITHDRAWALS_VIEW : CrmModuleHelper::MODULE_WITHDRAWALS_VIEW;

        if ($whitelabelId == 0) {
            $whitelabelId = null;
        }

        $currentUser = $this->current_user();
        if ($currentUser->role_id != 1 && !Model_Admin_Modules::check_user_module($moduleName, $whitelabelId, $currentUser->id)) {
            return $this->authorizationError();
        }

        $token = Input::json('token');

        $details = Model_Withdrawal_Request::get_single_for_crm($token, $isCasino);
        $this->prepare_whitelabel_withdrawal_data($details, $whitelabelId, $currentUser->code);

        return [
            'code' => 200,
            'details' => $details
        ];
    }

    public function post_approve_withdrawal(): array
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }

        $whitelabelId = Input::json('whitelabel_id');
        $isCasino = Input::json('isCasino');
        $moduleName = $isCasino ? CrmModuleHelper::MODULE_WITHDRAWALS_EDIT : CrmModuleHelper::MODULE_CASINO_WITHDRAWALS_EDIT;

        if ($whitelabelId == 0) {
            $whitelabelId = null;
        }
        $currentUser = $this->current_user();
        if ($currentUser->role_id != 1 && !Model_Admin_Modules::check_user_module($moduleName, $whitelabelId, $currentUser->id)) {
            return $this->authorizationError();
        }

        $whitelabelId = Input::json('whitelabel');
        $token = Input::json('token');
        $whitelabel = Model_Whitelabel::find_by_pk($whitelabelId)->to_array();

        $withdrawalApprove = new Forms_Whitelabel_Withdrawal_Approve(
            $token,
            $whitelabel
        );
        $result = $withdrawalApprove->process_form();

        switch ($result) {
            case Forms_Whitelabel_Withdrawal_Approve::RESULT_OK:
                return [
                    'code' => 200,
                    'message' => _("Withdrawal has been approved!")
                ];
            case Forms_Whitelabel_Withdrawal_Approve::RESULT_INCORRECT_WITHDRAWAL:
                return [
                    'code' => 400,
                    'message' => _("Incorrect withdrawal!")
                ];
        }
    }

    public function post_decline_withdrawal(): array
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }

        $whitelabelId = Input::json('whitelabel_id');
        $isCasino = Input::json('isCasino');
        $moduleName = $isCasino ? CrmModuleHelper::MODULE_CASINO_WITHDRAWALS_EDIT : CrmModuleHelper::MODULE_WITHDRAWALS_EDIT;

        if ($whitelabelId == 0) {
            $whitelabelId = null;
        }

        $currentUser = $this->current_user();
        if ($currentUser->role_id != 1 && !Model_Admin_Modules::check_user_module($moduleName, $whitelabelId, $currentUser->id)) {
            return $this->authorizationError();
        }

        $whitelabelId = Input::json('whitelabel');
        $token = Input::json('token');
        $whitelabel = Model_Whitelabel::find_by_pk($whitelabelId)->to_array();

        $withdrawalDecline = new Forms_Whitelabel_Withdrawal_Decline(
            $token,
            $whitelabel
        );
        $result = $withdrawalDecline->process_form();

        switch ($result) {
            case Forms_Whitelabel_Withdrawal_Decline::RESULT_OK:
                return [
                    'code' => 200,
                    'message' => _("Withdrawal has been declined!")
                ];
            case Forms_Whitelabel_Withdrawal_Decline::RESULT_INCORRECT_WITHDRAWAL:
                return [
                    'code' => 400,
                    'message' => _("Incorrect withdrawal!")
                ];
        }
    }

    public function post_export_withdrawals()
    {
        set_time_limit(600);

        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }

        $whitelabelId = Input::json('whitelabel_id');
        $isCasino = Input::json('isCasino');
        $moduleName = $isCasino ? CrmModuleHelper::MODULE_CASINO_WITHDRAWALS_VIEW : CrmModuleHelper::MODULE_WITHDRAWALS_VIEW;

        $currentUser = $this->current_user();
        if ($currentUser->role_id != 1 && !Model_Admin_Modules::check_user_module($moduleName, $whitelabelId, $currentUser->id)) {
            return $this->authorizationError();
        }

        $activeTab = Input::json('activeTab');
        $filters = Input::json('filters');
        $showLogin = $this->showLogin($whitelabelId);

        $recordsNum = 0;
        $headers = [_('Token'), _('User ID'), _('User name'), _('User e-mail')];
        if ($showLogin) {
            array_push($headers, _('User login'));
        }
        array_push(
            $headers,
            _('Method'),
            _('User balance'),
            _('Amount'),
            _('Date'),
            _('Date approved'),
            _('Prize payout group'),
            _('Status'),
            _('Request details')
        );

        switch ($activeTab) {
            case 'all':
                $recordsNum = Model_Withdrawal_Request::get_withdrawals_count_for_crm($whitelabelId, $filters, $isCasino);
                break;
            case 'pending':
                $recordsNum = Model_Withdrawal_Request::get_withdrawals_pending_count_for_crm($whitelabelId, $filters, $isCasino);
                break;
            case 'approved':
                $recordsNum = Model_Withdrawal_Request::get_withdrawals_approved_count_for_crm($whitelabelId, $filters, $isCasino);
                break;
            case 'declined':
                $recordsNum = Model_Withdrawal_Request::get_withdrawals_declined_count_for_crm($whitelabelId, $filters, $isCasino);
                break;
            case 'canceled':
                $recordsNum = Model_Withdrawal_Request::get_withdrawals_canceled_count_for_crm($whitelabelId, $filters, $isCasino);
        }
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment');

        $this->closeSession();

        $output = fopen('php://output', 'w');

        fputcsv($output, $headers);

        $items = 10000;
        $pages = ceil($recordsNum / $items);

        for ($i = 1; $i <= $pages; $i++) {
            $dataArray = Model_Withdrawal_Request::get_withdrawals_data_for_crm(
                $whitelabelId,
                $activeTab,
                $filters,
                $i,
                $items,
                null,
                null,
                true,
                $isCasino
            );
            foreach ($dataArray as &$data) {
                $this->prepare_whitelabel_withdrawal_data($data, $whitelabelId, $currentUser->code);
                $values = [];
                array_push(
                    $values,
                    $data['full_token'],
                    $data['user_token_full'],
                    $data['user_fullname'],
                    $data['user_email']
                );
                if ($showLogin) {
                    array_push($values, $data['user_login']);
                }
                array_push(
                    $values,
                    $data['method_name'],
                    $data['user_balance_display'],
                    $data['amount_display'],
                    $data['date'],
                    $data['date_confirmed'],
                    $data['user_prize_group_name'],
                    $data['status_display'],
                    $data['request_details']
                );
                fputcsv($output, $values);
                ob_flush();
                flush();
            }
        }
        fclose($output);
    }

    /**
     *
     * @access public
     * @return array
     */
    public function post_export_transactions()
    {
        set_time_limit(600);

        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }
        $whitelabel_id = Input::json('whitelabel_id');
        $isDeposit = Input::json('isDeposit') ?? false;
        $isCasino = Input::json('isCasino') ?? false;

        $current_user = $this->current_user();
        if ($current_user->role_id != 1 && !Model_Admin_Modules::check_user_module('transactions-view', $whitelabel_id, $current_user->id)) {
            return $this->authorizationError();
        }

        $show_login = $this->showLogin($whitelabel_id);

        $active_tab = Input::json('activeTab');
        $filters = Input::json('filters');
        $records_num = 0;
        $headers = [_('Token'), _('User ID'), _('User name'), _('User e-mail')];
        if ($show_login) {
            array_push($headers, _('User login'));
        }

        switch ($active_tab) {
            case 'all':
                $records_num = Model_Whitelabel_Transaction::get_transactions_count_for_crm($whitelabel_id, $filters, $isCasino, $isDeposit);
                break;
            case 'purchases':
                $records_num = Model_Whitelabel_Transaction::get_purchases_count_for_crm($whitelabel_id, $filters, $isCasino, $isDeposit);
                break;
            case 'pending':
                $records_num = Model_Whitelabel_Transaction::get_pending_count_for_crm($whitelabel_id, $filters, $isCasino, $isDeposit);
                break;
            case 'approved':
                $records_num = Model_Whitelabel_Transaction::get_approved_count_for_crm($whitelabel_id, $filters, $isCasino, $isDeposit);
                break;
            case 'error':
                $records_num = Model_Whitelabel_Transaction::get_error_count_for_crm($whitelabel_id, $filters, $isCasino, $isDeposit);
        }

        array_push($headers, _('Method'), _('Amount'), _('Bonus amount'), _('Date'), _('Date confirmed'), _('Tickets/Processed'), _('Status'));

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment');

        $this->closeSession();

        $output = fopen('php://output', 'w');

        fputcsv($output, $headers);

        $items = 10000;
        $pages = ceil($records_num / $items);

        for ($i = 1; $i <= $pages; $i++) {
            $data_array = Model_Whitelabel_Transaction::get_transactions_data_for_crm(
                $whitelabel_id,
                $active_tab,
                $filters,
                $i,
                $items,
                null,
                null,
                true,
                $isCasino,
                $isDeposit
            );
            foreach ($data_array as $data) {
                $this->prepare_whitelabel_transaction_data($data, $whitelabel_id, $current_user->code);
                $values = [];
                array_push(
                    $values,
                    $data['full_token'],
                    $data['user_token_full'],
                    $data['user_name'] . ' ' . $data['user_surname'],
                    $data['user_email'],
                    $data['user_login'],
                    $data['method'],
                    $data['amount_display'],
                    $data['bonus_amount_display'],
                    $data['date'],
                    $data['date_confirmed'] ?? 'confirmation required',
                    $data['tickets_count'] . '/' . $data['tickets_processed_count'],
                    $data['status_display']
                );
                fputcsv($output, $values);
                ob_flush();
                flush();
            }
        }
        fclose($output);
    }

    /**
     *
     * @access public
     * @return array
     * @throws Throwable when $lottery|$ticket is null
     */
    public function post_tickets_table_data(): array
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }

        $start_date = Input::json('start');
        $end_date = Input::json('end');

        $whitelabel_id = Input::json('whitelabel');
        $current_user = $this->current_user();
        if ($current_user->role_id != 1 && !Model_Admin_Modules::check_user_module('tickets-view', $whitelabel_id, $current_user->id)) {
            return $this->authorizationError();
        }
        if ($whitelabel_id == 0) {
            $whitelabel_id = null;
        }

        $active_tab = Input::json('activeTab');
        $filters = Input::json('filters');
        $page = Input::json('page');
        $items_per_page = Input::json('itemsPerPage');
        $sort_by = Input::json('sortBy');
        $order = Input::json('order');
        $multidraw = Input::json('multi_draw_id');

        $table_data = Model_Whitelabel_User_Ticket::get_full_data_for_crm(
            $whitelabel_id,
            $active_tab,
            $filters,
            $multidraw,
            $page,
            $items_per_page,
            $sort_by,
            $order
        );

        $all_count = Model_Whitelabel_User_Ticket::get_tickets_counts_for_crm($whitelabel_id, $filters, $multidraw, null);
        $pending_count = Model_Whitelabel_User_Ticket::get_tickets_counts_for_crm($whitelabel_id, $filters, $multidraw, Helpers_General::TICKET_STATUS_PENDING);
        $win_count = Model_Whitelabel_User_Ticket::get_tickets_counts_for_crm($whitelabel_id, $filters, $multidraw, Helpers_General::TICKET_STATUS_WIN);
        $no_winnings_count = Model_Whitelabel_User_Ticket::get_tickets_counts_for_crm($whitelabel_id, $filters, $multidraw, Helpers_General::TICKET_STATUS_NO_WINNINGS);
        $canceled_count = Model_Whitelabel_User_Ticket::get_tickets_counts_for_crm($whitelabel_id, $filters, $multidraw, Helpers_General::TICKET_STATUS_CANCELED);

        $all_lotteries = Model_Lottery::get_all_lotteries();
        foreach ($table_data as &$ticket) {
            $lottery = $all_lotteries["__by_id"][$ticket['lottery_id']];
            try {
                $this->prepare_whitelabel_user_ticket_data($ticket, $whitelabel_id, $lottery, $current_user->code);
            } catch (Throwable $exception) {
                $this->fileLoggerService->error(
                    "Missing argument. DEBUG LOG: ticket[id]:" . $ticket['id'] . " ticket[lottery_id]: " .
                        $ticket['lottery_id'] . " lottery[timezone]: " . $lottery['timezone']
                );
                throw $exception;
            }
        }

        return [
            'code' => 200,
            'data' => $table_data,
            'all_count' => $all_count,
            'pending_count' => $pending_count,
            'win_count' => $win_count,
            'no_winnings_count' => $no_winnings_count,
            'canceled_count' => $canceled_count
        ];
    }

    public function post_tickets_lotteries_data(): array
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }

        $start_date = Input::json('start');
        $end_date = Input::json('end');

        $whitelabel_id = Input::json('whitelabel');
        $current_user = $this->current_user();
        if ($current_user->role_id != 1 && !Model_Admin_Modules::check_user_module('tickets-view', $whitelabel_id, $current_user->id)) {
            return $this->authorizationError();
        }
        if ($whitelabel_id == 0) {
            $whitelabel_id = null;
        }

        $lotteries = Model_Whitelabel_User_Ticket::get_tickets_lotteries_for_crm($whitelabel_id, $start_date, $end_date);

        return ['code' => 200, 'lotteries' => $lotteries];
    }

    public function post_tickets_lines(): array
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }

        $start_date = Input::json('start');
        $end_date = Input::json('end');

        $whitelabel_id = Input::json('whitelabel');
        $current_user = $this->current_user();
        if ($current_user->role_id != 1 && !Model_Admin_Modules::check_user_module('tickets-view', $whitelabel_id, $current_user->id)) {
            return $this->authorizationError();
        }
        if ($whitelabel_id == 0) {
            $whitelabel_id = null;
        }

        $tickets = Model_Whitelabel_User_Ticket::get_tickets_lines_count_for_crm($start_date, $end_date, $whitelabel_id);

        return [
            'code' => 200,
            'tickets' => $tickets
        ];
    }

    public function post_ticket_details(): array
    {
        $fullToken = Input::json('token');
        if (!$this->middleware_auth() || FullTokenHelper::isNotValid($fullToken)) {
            return $this->authorizationError();
        }

        $whitelabel_id = Input::json('whitelabel_id');
        $whitelabelRepository = Container::get(WhitelabelRepository::class);
        $whitelabel = $whitelabelRepository->getWhitelabelByFullToken($fullToken);
        if ($whitelabel_id == 0) {
            $whitelabel_id = null;
        } else {
            $whitelabel_id = $whitelabel->id;
        }
        $current_user = $this->current_user();
        if ($current_user->role_id != 1 && !Model_Admin_Modules::check_user_module('tickets-view', $whitelabel_id, $current_user->id)) {
            return $this->authorizationError();
        }

        $token = FullTokenHelper::getToken($fullToken);
        $ticket = Model_Whitelabel_User_Ticket::get_single_for_crm($token, $whitelabel->id);
        if (empty($ticket)) {
            return $this->authorizationError();
        }
        $ticket_lines = Model_Whitelabel_User_Ticket_Line::get_with_slip_by_ticket_id($ticket['id']);
        $lottery = Model_Lottery::find_by_pk($ticket['lottery_id'])->to_array();
        $lottery_types = Model_Lottery_Type::get_lottery_type_for_date($lottery, $ticket['draw_date']);
        $slips = Model_Whitelabel_User_Ticket_Slip::find([
            "where" => [
                "whitelabel_user_ticket_id" => $ticket['id']
            ],
            "order_by" => [
                "id" => "asc"
            ]
        ]);

        Config::load("platform", true);

        $images = [];
        /** @var WhitelabelLotteryRepository $whitelabelLotteryRepository */
        $whitelabelLotteryRepository = Container::get(WhitelabelLotteryRepository::class);
        $whitelabelLottery = $whitelabelLotteryRepository->getOneByLotteryIdForWhitelabel(
            $ticket['lottery_id'],
            $ticket['whitelabel_id']
        );

        if ($slips !== null && $whitelabelLottery->isScanInCrmEnabled) {
            foreach ($slips as $slip) {
                if (!empty($slip->ticket_scan_url)) {
                    $images[] = $slip->ticket_scan_url;
                }
            }

            $images = $this->getGgWorldScanImages($images, $ticket['id']);
        }

        $all_lotteries = Model_Lottery::get_all_lotteries();
        $lottery = $all_lotteries["__by_id"][$ticket['lottery_id']];
        $this->prepare_whitelabel_user_ticket_data($ticket, $whitelabel_id, $lottery, $current_user->code);
        list(
            $lines_data,
            $ticket['is_payout']
        ) = $this->prepare_line_data($whitelabel_id, $current_user->code, $ticket, $ticket_lines, $lottery_types);

        return [
            'code' => 200,
            'lines' => $lines_data,
            'details' => $ticket,
            'images' => $images
        ];
    }

    public function post_tickets_mark_paid_out(): array
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }
        $whitelabel_id = Input::json('whitelabel');

        $current_user = $this->current_user();
        if ($current_user->role_id != 1 && !Model_Admin_Modules::check_user_module('tickets-edit', $whitelabel_id, $current_user->id)) {
            return $this->authorizationError();
        }
        $token = Input::json('token');
        $whitelabel = Model_Whitelabel::find_by_pk($whitelabel_id)->to_array();

        $ticket_paidout = new Forms_Whitelabel_Ticket_Paidout(
            $token,
            $whitelabel
        );
        $message = "";
        $code = 200;
        $result = $ticket_paidout->process_form();

        switch ($result) {
            case Forms_Whitelabel_Ticket_Paidout::RESULT_OK:
                $message = _("The ticket has been marked as paid out!");
                // no break
            case Forms_Whitelabel_Ticket_Paidout::RESULT_PAIDOUT_PARTIALLY:
                $message = _(
                    "The ticket has been marked as paid out, " .
                        "however there are still some lines that will have to " .
                        "be paid automatically!"
                );
                break;
            case Forms_Whitelabel_Ticket_Paidout::RESULT_INCORRECT_TICKET:
                $message = _("Incorrect ticket!");
                $code = 400;
                break;
        }

        return [
            'code' => $code,
            'message' => $message
        ];
    }

    public function post_ticket_line_payout(): array
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }
        $whitelabel_id = Input::json('whitelabel');

        $current_user = $this->current_user();
        if ($current_user->role_id != 1 && !Model_Admin_Modules::check_user_module('tickets-edit', $whitelabel_id, $current_user->id)) {
            return $this->authorizationError();
        }
        $token = Input::json('token');
        $offset = Input::json('offset');
        $whitelabel = Model_Whitelabel::find_by_pk($whitelabel_id)->to_array();

        $ticket_payout = new Forms_Whitelabel_Ticket_Payout(
            $token,
            $whitelabel,
            $offset
        );
        $result = $ticket_payout->process_form();

        switch ($result) {
            case Forms_Whitelabel_Ticket_Payout::RESULT_OK:
                return [
                    'code' => 200,
                    'message' => _("Line prize has been paid out to the user account balance!")
                ];
                break;
            case Forms_Whitelabel_Ticket_Payout::RESULT_DB_ERROR:
                return [
                    'code' => 400,
                    'message' => _("Bad request!")
                ];
                break;
            case Forms_Whitelabel_Ticket_Payout::RESULT_WITH_ERRORS:
                return [
                    'code' => 400,
                    'message' => _("Incorrect line!")
                ];
                break;
        }
    }

    public function post_export_tickets()
    {
        set_time_limit(600);

        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }
        $whitelabel_id = Input::json('whitelabel');
        if ($whitelabel_id == 0) {
            $whitelabel_id = null;
        }

        $current_user = $this->current_user();
        if ($current_user->role_id != 1 && !Model_Admin_Modules::check_user_module('tickets-view', $whitelabel_id, $current_user->id)) {
            return $this->authorizationError();
        }

        $show_login = $this->showLogin($whitelabel_id);

        $active_tab = Input::json('activeTab');
        $filters = Input::json('filters');
        $multidraw = Input::json('multi_draw_id');

        $records_num = 0;
        $headers = [_('ID'), _('Transaction ID'), _('Lottery'), _('User ID'), _('User name'), _('User e-mail')];

        if ($show_login) {
            $headers[] = _('User login');
        }

        array_push($headers, _('Model'), _('Cost'), _('Income'), _('Royalties'), _('Bonus cost'), _('Amount'), _('Bonus amount'), _('Date'), _('Draw date'));

        switch ($active_tab) {
            case 'all':
                array_push($headers, _('Status'));
                $records_num = Model_Whitelabel_User_Ticket::get_tickets_counts_for_crm($whitelabel_id, $filters, $multidraw, null);
                break;
            case 'pending':
                $records_num = Model_Whitelabel_User_Ticket::get_tickets_counts_for_crm($whitelabel_id, $filters, $multidraw, Helpers_General::TICKET_STATUS_PENDING);
                break;
            case 'win':
                $records_num = Model_Whitelabel_User_Ticket::get_tickets_counts_for_crm($whitelabel_id, $filters, $multidraw, Helpers_General::TICKET_STATUS_WIN);
                break;
            case 'nowinnings':
                $records_num = Model_Whitelabel_User_Ticket::get_tickets_counts_for_crm($whitelabel_id, $filters, $multidraw, Helpers_General::TICKET_STATUS_NO_WINNINGS);
                break;
            case 'canceled':
                $records_num = Model_Whitelabel_User_Ticket::get_tickets_counts_for_crm($whitelabel_id, $filters, $multidraw, Helpers_General::TICKET_STATUS_CANCELED);
                break;
        }
        array_push($headers, _('Prize'), _('Prize Net'), _('Paid out'), _('Lines'), _('Prize payout'), _('Lines numbers'));

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment');

        $this->closeSession();

        $output = fopen('php://output', 'w');

        fputcsv($output, $headers);

        $items = 1000;
        $pages = ceil($records_num / $items);
        $all_lotteries = Model_Lottery::get_all_lotteries();

        for ($i = 1; $i <= $pages; $i++) {
            $data_array = Model_Whitelabel_User_Ticket::get_full_data_for_crm(
                $whitelabel_id,
                $active_tab,
                $filters,
                $multidraw,
                $i,
                $items,
                null,
                null,
                true
            );
            $ids = array_column($data_array, 'id');
            $all_lines_for_batch = Model_Whitelabel_User_Ticket_Line::get_all_for_crm($ids);

            foreach ($data_array as &$data) {
                $lines_data = [];
                if (isset($all_lines_for_batch[$data['id']])) {
                    $lines_data = $all_lines_for_batch[$data['id']];
                }
                $lines_data = $all_lines_for_batch[$data['id']];
                $lottery = $all_lotteries["__by_id"][$data['lottery_id']];
                $this->prepare_whitelabel_user_ticket_data($data, $whitelabel_id, $lottery, $current_user->code);
                $lines = $this->prepare_lines_data_for_export($whitelabel_id, $data, $lines_data, $current_user->code);
                $values = [];
                array_push(
                    $values,
                    $data['full_token'],
                    $data['transaction_full_token'],
                    $data['lname'],
                    $data['user_full_token'],
                    $data['user_fullname'],
                    $data['email']
                );
                if ($show_login) {
                    array_push($values, $data['user_login']);
                }
                array_push(
                    $values,
                    $data['model_name'],
                    $data['cost_display'],
                    $data['income_display'],
                    $data['margin_display'],
                    $data['bonus_cost_display'] ?? "",
                    $data['amount_display'],
                    $data['bonus_amount_display'],
                    $data['date'],
                    $data['draw_date_display'] ?? ""
                );
                if ($active_tab == 'all') {
                    array_push(
                        $values,
                        $data['status_display']
                    );
                }
                array_push(
                    $values,
                    $data['prize_display'] ?? "",
                    $data['prize_net_display'] ?? "",
                    $data['payout_display'],
                    $data['line_count'],
                    $data['prize_payout_display'],
                    $lines
                );
                fputcsv($output, $values);
                ob_flush();
                flush();
            }
        }
        fclose($output);
    }

    public function post_multidraw_tickets_table_data(): array
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }

        $start_date = Input::json('start');
        $end_date = Input::json('end');

        $whitelabel_id = Input::json('whitelabel');
        $current_user = $this->current_user();
        if ($current_user->role_id != 1 && !Model_Admin_Modules::check_user_module('tickets-view', $whitelabel_id, $current_user->id)) {
            return $this->authorizationError();
        }
        if ($whitelabel_id == 0) {
            $whitelabel_id = null;
        }

        $filters = Input::json('filters');
        $page = Input::json('page');
        $items_per_page = Input::json('itemsPerPage');
        $sort_by = Input::json('sortBy');
        $order = Input::json('order');

        $table_data = Model_Multidraw::get_full_data_for_crm(
            $whitelabel_id,
            $filters,
            $page,
            $items_per_page,
            $sort_by,
            $order
        );

        $all_count = Model_Multidraw::get_tickets_count_for_crm($whitelabel_id, $filters, null);

        foreach ($table_data as &$ticket) {
            $this->prepare_multidraw_ticket_data($ticket, $whitelabel_id, $current_user->code);
        }

        return [
            'code' => 200,
            'data' => $table_data,
            'all_count' => $all_count,
        ];
    }

    public function post_export_multidraw_tickets()
    {
        set_time_limit(600);

        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }
        $whitelabel_id = Input::json('whitelabel');
        if ($whitelabel_id == 0) {
            $whitelabel_id = null;
        }

        $current_user = $this->current_user();
        if ($current_user->role_id != 1 && !Model_Admin_Modules::check_user_module('tickets-view', $whitelabel_id, $current_user->id)) {
            return $this->authorizationError();
        }

        $show_login = $this->showLogin($whitelabel_id);

        $filters = Input::json('filters');

        $records_num = 0;
        $headers = [_('ID'), _('Lottery'), _('User ID'), _('User name'), _('User e-mail')];

        if ($show_login) {
            $headers[] = _('User login');
        }

        array_push($headers, _('Tickets'), _('First draw'), _('Valid to draw'), _('Current draw'), _('Date'));

        $records_num = Model_Multidraw::get_tickets_count_for_crm($whitelabel_id, $filters, null);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment');

        $this->closeSession();

        $output = fopen('php://output', 'w');

        fputcsv($output, $headers);

        $items = 10000;
        $pages = ceil($records_num / $items);
        for ($i = 1; $i <= $pages; $i++) {
            $data_array = Model_Multidraw::get_full_data_for_crm(
                $whitelabel_id,
                $filters,
                $i,
                $items,
                null,
                null,
                true
            );
            foreach ($data_array as &$data) {
                $this->prepare_multidraw_ticket_data($data, $whitelabel_id, $current_user->code);
                $values = [];
                array_push(
                    $values,
                    $data['full_token'],
                    $data['lname'],
                    $data['user_full_token'],
                    $data['user_fullname'],
                    $data['email']
                );

                if ($show_login) {
                    array_push($values, $data['user_login']);
                }

                array_push(
                    $values,
                    $data['tickets'],
                    $data['first_draw'],
                    $data['valid_to_draw'],
                    $data['current_draw'],
                    $data['date']
                );
                fputcsv($output, $values);
                ob_flush();
                flush();
            }
        }
        fclose($output);
    }

    /**
     *
     * @access private
     * @param array $user
     * @param array $columns
     * @return array
     */
    private function prepare_export_data($user, $columns)
    {
        $user_export = [];
        if (in_array('full_token', $columns)) {
            array_push($user_export, $user['whitelabel_prefix'] . 'U' . $user['token']);
        }
        if (in_array('name', $columns)) {
            array_push($user_export, $user['name']);
        }
        if (in_array('surname', $columns)) {
            array_push($user_export, $user['surname']);
        }
        if (in_array('birthdate', $columns)) {
            array_push($user_export, $user['birthdate']);
        }
        if (in_array('gender', $columns)) {
            switch ($user['gender']) {
                case "1":
                    array_push($user_export, _("Male"));
                    break;
                case "2":
                    array_push($user_export, _("Female"));
                    break;
                default:
                    array_push($user_export, '-');
            }
        }
        if (in_array('email', $columns)) {
            array_push($user_export, $user['email']);
        }
        if (in_array('login', $columns)) {
            $login = "";
            if (isset($user['login'])) {
                $login = $user['login'];
            }
            array_push($user_export, $login);
        }
        if (in_array('phone', $columns)) {
            array_push($user_export, $user['phone']);
            array_push($user_export, $user['phone_country']);
        }
        if (in_array('language', $columns)) {
            array_push($user_export, $user['language_code']);
        }
        if (in_array('timezone', $columns)) {
            array_push($user_export, $user['timezone']);
        }
        if (in_array('country_name', $columns)) {
            array_push($user_export, $user['country']);
        }
        if (in_array('state', $columns)) {
            array_push($user_export, $user['state']);
        }
        if (in_array('city', $columns)) {
            array_push($user_export, $user['city']);
        }
        if (in_array('address_1', $columns)) {
            array_push($user_export, $user['address_1']);
        }
        if (in_array('address_2', $columns)) {
            array_push($user_export, $user['address_2']);
        }
        if (in_array('zip', $columns)) {
            array_push($user_export, $user['zip']);
        }
        if (in_array('national_id', $columns)) {
            array_push($user_export, $user['national_id']);
        }
        if (in_array('user_currency_code', $columns)) {
            array_push($user_export, $user['code']);
        }
        if (in_array('affiliate', $columns)) {
            $affiliate = "";
            $aff_email = "";
            if (isset($user['aff_name']) || isset($user['aff_surname'])) {
                if (isset($user['aff_name'])) {
                    $affiliate = $user['aff_name'] . " ";
                }
                if (isset($user['aff_surname'])) {
                    $affiliate .= $user['aff_surname'];
                }
            }
            if (isset($user['aff_email'])) {
                $aff_email = $user['aff_email'];
            }
            array_push($user_export, $affiliate);
            array_push($user_export, $aff_email);
        }
        if (in_array('balance', $columns)) {
            array_push($user_export, $user['balance']);
        }
        if (in_array('bonus_balance', $columns)) {
            array_push($user_export, $user['bonus_balance']);
        }
        if (in_array('date_register', $columns)) {
            array_push($user_export, $user['date_register']);
        }
        if (in_array('register_ip', $columns)) {
            array_push($user_export, $user['register_ip']);
        }
        if (in_array('register_country_name', $columns)) {
            array_push($user_export, $user['register_country']);
        }
        if (in_array('last_ip', $columns)) {
            array_push($user_export, $user['last_ip']);
        }
        if (in_array('first_deposit', $columns)) {
            array_push($user_export, $user['first_deposit']);
        }
        if (in_array('second_deposit', $columns)) {
            array_push($user_export, $user['second_deposit']);
        }
        if (in_array('last_active', $columns)) {
            array_push($user_export, $user['last_active']);
        }
        if (in_array('last_country_name', $columns)) {
            array_push($user_export, $user['last_country']);
        }
        if (in_array('first_purchase', $columns)) {
            array_push($user_export, $user['first_purchase']);
        }
        if (in_array('second_purchase', $columns)) {
            array_push($user_export, $user['second_purchase']);
        }
        if (in_array('last_purchase_date', $columns)) {
            array_push($user_export, $user['last_purchase_date']);
        }
        if (in_array('purchaseCountForDate', $columns)) {
            array_push($user_export, $user['purchaseCountForDate']);
        }
        if (in_array('first_deposit_amount_manager', $columns)) {
            array_push($user_export, $user['first_deposit_amount_manager']);
        }
        if (in_array('last_deposit_amount_manager', $columns)) {
            array_push($user_export, $user['last_deposit_amount_manager']);
        }
        if (in_array('total_deposit_manager', $columns)) {
            array_push($user_export, $user['total_deposit_manager']);
        }
        if (in_array('total_withdrawal_manager', $columns)) {
            array_push($user_export, $user['total_withdrawal_manager']);
        }
        if (in_array('total_purchases_manager', $columns)) {
            array_push($user_export, $user['total_purchases_manager']);
        }
        if (in_array('last_purchase_amount_manager', $columns)) {
            array_push($user_export, $user['last_purchase_amount_manager']);
        }
        if (in_array('total_net_income_manager', $columns)) {
            array_push($user_export, $user['total_net_income_manager']);
        }
        if (in_array('net_winnings_manager', $columns)) {
            array_push($user_export, $user['net_winnings_manager']);
        }
        if (in_array('sale_status', $columns)) {
            $sale_status = "-";
            switch ($user['sale_status']) {
                case "1":
                    $sale_status = _("Started deposit");
                    break;
                case "2":
                    $sale_status = _("Deposited");
                    break;
                case "3":
                    $sale_status = _("Started purchase");
                    break;
                case "4":
                    $sale_status = _("Purchased");
                    break;
            }
            array_push($user_export, $sale_status);
        }
        if (in_array('pnl_manager', $columns)) {
            array_push($user_export, $user['pnl_manager']);
        }
        if (in_array('system_type', $columns)) {
            array_push($user_export, $user['system_type']);
        }
        if (in_array('browser_type', $columns)) {
            array_push($user_export, $user['browser_type']);
        }
        if (in_array('last_update', $columns)) {
            array_push($user_export, $user['last_update']);
        }
        if (in_array('whitelabel_name', $columns)) {
            array_push($user_export, $user['whitelabel_name']);
        }
        if (in_array('group', $columns)) {
            $group = "";
            if (isset($user['group'])) {
                $group = $user['group'];
            }
            array_push($user_export, $group);
        }
        if (in_array('date_delete', $columns)) {
            array_push($user_export, $user['date_delete']);
        }
        if (in_array('player_lifetime', $columns)) {
            array_push($user_export, $user['player_lifetime']);
        }
        return $user_export;
    }

    /**
     *
     * @access private
     * @param array $columns
     * @return array
     */
    private function prepare_export_columns($columns)
    {
        $export_columns = [];
        $columns_headers = [];
        if (in_array('full_token', $columns)) {
            array_push($export_columns, ['whitelabel.prefix', 'whitelabel_prefix']);
            array_push($export_columns, 'whitelabel_user.token');
            array_push($columns_headers, _('User ID'));
        }
        if (in_array('name', $columns)) {
            array_push($export_columns, 'whitelabel_user.name');
            array_push($columns_headers, _('Name'));
        }
        if (in_array('surname', $columns)) {
            array_push($export_columns, 'whitelabel_user.surname');
            array_push($columns_headers, _('Surname'));
        }
        if (in_array('birthdate', $columns)) {
            array_push($export_columns, 'whitelabel_user.birthdate');
            array_push($columns_headers, _('Birthdate'));
        }
        if (in_array('gender', $columns)) {
            array_push($export_columns, 'whitelabel_user.gender');
            array_push($columns_headers, _('Gender'));
        }
        if (in_array('email', $columns)) {
            array_push($export_columns, 'whitelabel_user.email');
            array_push($columns_headers, _('E-mail'));
        }
        if (in_array('login', $columns)) {
            array_push($export_columns, 'whitelabel_user.login');
            array_push($columns_headers, _('Login'));
        }
        if (in_array('phone', $columns)) {
            array_push($export_columns, 'whitelabel_user.phone');
            array_push($export_columns, 'whitelabel_user.phone_country');
            array_push($columns_headers, _('Phone'));
            array_push($columns_headers, _('Phone Country'));
        }
        if (in_array('language', $columns)) {
            array_push($export_columns, ['language.code', 'language_code']);
            array_push($columns_headers, _('Language'));
        }
        if (in_array('timezone', $columns)) {
            array_push($export_columns, 'whitelabel_user.timezone');
            array_push($columns_headers, _('Timezone'));
        }
        if (in_array('country_name', $columns)) {
            array_push($export_columns, 'whitelabel_user.country');
            array_push($columns_headers, _('Country'));
        }
        if (in_array('state', $columns)) {
            array_push($export_columns, 'whitelabel_user.state');
            array_push($columns_headers, _('Region'));
        }
        if (in_array('city', $columns)) {
            array_push($export_columns, 'whitelabel_user.city');
            array_push($columns_headers, _('City'));
        }
        if (in_array('address_1', $columns)) {
            array_push($export_columns, 'whitelabel_user.address_1');
            array_push($columns_headers, _('Address #1'));
        }
        if (in_array('address_2', $columns)) {
            array_push($export_columns, 'whitelabel_user.address_2');
            array_push($columns_headers, _('Address #2'));
        }
        if (in_array('zip', $columns)) {
            array_push($export_columns, 'whitelabel_user.zip');
            array_push($columns_headers, _('Postal/ZIP Code'));
        }
        if (in_array('national_id', $columns)) {
            array_push($export_columns, 'whitelabel_user.national_id');
            array_push($columns_headers, _('National ID'));
        }
        if (in_array('user_currency_code', $columns)) {
            array_push($export_columns, 'currency.code');
            array_push($columns_headers, _('Currency'));
        }
        if (in_array('affiliate', $columns)) {
            array_push($export_columns, ['whitelabel_aff.name', 'aff_name']);
            array_push($export_columns, ['whitelabel_aff.surname', 'aff_surname']);
            array_push($export_columns, ['whitelabel_aff.email', 'aff_email']);
            array_push($columns_headers, _('Affiliate name'));
            array_push($columns_headers, _('Affiliate e-mail'));
        }
        if (in_array('balance', $columns)) {
            array_push($export_columns, 'whitelabel_user.balance');
            array_push($columns_headers, _('Balance'));
        }
        if (in_array('bonus_balance', $columns)) {
            array_push($export_columns, 'whitelabel_user.bonus_balance');
            array_push($columns_headers, _('Bonus_balance'));
        }
        if (in_array('date_register', $columns)) {
            array_push($export_columns, 'whitelabel_user.date_register');
            array_push($columns_headers, _('Register Date'));
        }
        if (in_array('register_ip', $columns)) {
            array_push($export_columns, 'whitelabel_user.register_ip');
            array_push($columns_headers, _('Register IP'));
        }
        if (in_array('register_country_name', $columns)) {
            array_push($export_columns, 'whitelabel_user.register_country');
            array_push($columns_headers, _('Register country'));
        }
        if (in_array('last_ip', $columns)) {
            array_push($export_columns, 'whitelabel_user.last_ip');
            array_push($columns_headers, _('Last IP'));
        }
        if (in_array('first_deposit', $columns)) {
            array_push($export_columns, 'whitelabel_user.first_deposit');
            array_push($columns_headers, _('First Deposit'));
        }
        if (in_array('second_deposit', $columns)) {
            array_push($export_columns, 'whitelabel_user.second_deposit');
            array_push($columns_headers, _('Second Deposit'));
        }
        if (in_array('last_active', $columns)) {
            array_push($export_columns, 'whitelabel_user.last_active');
            array_push($columns_headers, _('Last Active'));
        }
        if (in_array('last_country_name', $columns)) {
            array_push($export_columns, 'whitelabel_user.last_country');
            array_push($columns_headers, _('Last country'));
        }
        if (in_array('first_purchase', $columns)) {
            array_push($export_columns, 'whitelabel_user.first_purchase');
            array_push($columns_headers, _('First Purchase'));
        }
        if (in_array('second_purchase', $columns)) {
            array_push($export_columns, 'whitelabel_user.second_purchase');
            array_push($columns_headers, _('Second Purchase'));
        }
        if (in_array('last_purchase_date', $columns)) {
            array_push($export_columns, 'whitelabel_user.last_purchase_date');
            array_push($columns_headers, _('Last Purchase'));
        }
        if (in_array('purchaseCountForDate', $columns)) {
            array_push($export_columns, 'whitelabel_transaction.purchaseCountForDate');
            array_push($columns_headers, _('Purchase count for date'));
        }
        if (in_array('first_deposit_amount_manager', $columns)) {
            array_push($export_columns, 'whitelabel_user.first_deposit_amount_manager');
            array_push($columns_headers, _('First Deposit Amount'));
        }
        if (in_array('last_deposit_amount_manager', $columns)) {
            array_push($export_columns, 'whitelabel_user.last_deposit_amount_manager');
            array_push($columns_headers, _('Last Deposit Amount'));
        }
        if (in_array('total_deposit_manager', $columns)) {
            array_push($export_columns, 'whitelabel_user.total_deposit_manager');
            array_push($columns_headers, _('Total Deposit'));
        }
        if (in_array('total_withdrawal_manager', $columns)) {
            array_push($export_columns, 'whitelabel_user.total_withdrawal_manager');
            array_push($columns_headers, _('Total Withdrawal'));
        }
        if (in_array('total_purchases_manager', $columns)) {
            array_push($export_columns, 'whitelabel_user.total_purchases_manager');
            array_push($columns_headers, _('Total Purchases'));
        }
        if (in_array('last_purchase_amount_manager', $columns)) {
            array_push($export_columns, 'whitelabel_user.last_purchase_amount_manager');
            array_push($columns_headers, _('Last Purchase'));
        }
        if (in_array('total_net_income_manager', $columns)) {
            array_push($export_columns, 'whitelabel_user.total_net_income_manager');
            array_push($columns_headers, _('Total net income'));
        }
        if (in_array('net_winnings_manager', $columns)) {
            array_push($export_columns, 'whitelabel_user.net_winnings_manager');
            array_push($columns_headers, _('Net winnings'));
        }
        if (in_array('sale_status', $columns)) {
            array_push($export_columns, 'whitelabel_user.sale_status');
            array_push($columns_headers, _('Sale status'));
        }
        if (in_array('pnl_manager', $columns)) {
            array_push($export_columns, 'whitelabel_user.pnl_manager');
            array_push($columns_headers, _('PnL'));
        }
        if (in_array('system_type', $columns)) {
            array_push($export_columns, 'whitelabel_user.system_type');
            array_push($columns_headers, _('System'));
        }
        if (in_array('browser_type', $columns)) {
            array_push($export_columns, 'whitelabel_user.browser_type');
            array_push($columns_headers, _('Browser'));
        }
        if (in_array('last_update', $columns)) {
            array_push($export_columns, 'whitelabel_user.last_update');
            array_push($columns_headers, _('Last update'));
        }
        if (in_array('whitelabel_name', $columns)) {
            array_push($export_columns, ['whitelabel.name', 'whitelabel_name']);
            array_push($columns_headers, _('Whitelabel'));
        }
        if (in_array('group', $columns)) {
            array_push($export_columns, ['whitelabel_user_group.name', 'group']);
            array_push($columns_headers, _('Prize payout group'));
        }
        if (in_array('date_delete', $columns)) {
            array_push($export_columns, 'whitelabel_user.date_delete');
            array_push($columns_headers, _('Delete date'));
        }
        if (in_array('player_lifetime', $columns)) {
            array_push($export_columns, [DB::expr('DATEDIFF(NOW(), whitelabel_user.date_register)'), 'player_lifetime']);
            array_push($columns_headers, _('Player_Lifetime (days)'));
        }
        return [$export_columns, $columns_headers];
    }

    /**
     *
     * @return array
     */
    public function post_whitelabel_user_groups()
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }
        $current_user = $this->current_user();

        $whitelabel_id = Input::json('whitelabel_id');

        if ($current_user->role_id != 1 && !Model_Admin_Modules::check_user_module('user-groups-view', $whitelabel_id, $current_user->id)) {
            return $this->authorizationError();
        }
        $groups = Model_Whitelabel_User_Group::get_all_groups_for_whitelabel($whitelabel_id);
        $default_groups = Model_Whitelabel::get_default_user_groups_ids($whitelabel_id);
        $locale = $current_user->code;

        foreach ($groups as &$group) {
            $this->prepare_groups_data($group, $default_groups, $locale);
        }

        return [
            'code' => 200,
            'groups' => $groups
        ];
    }

    /**
     *
     * @param array &$group
     * @param array $default_groups
     * @param string $locale
     */
    private function prepare_groups_data(&$group, $default_groups, $locale)
    {
        if (in_array($group['id'], $default_groups)) {
            $group['is_default'] = true;
        } else {
            $group['is_default'] = false;
        }
        $prize_payout_percent = $group['prize_payout_percent'] / 100;
        $formatter = new NumberFormatter(
            $locale,
            NumberFormatter::PERCENT
        );
        $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, 2);
        $group['prize_payout_percent_display'] = $formatter->format($prize_payout_percent);
    }

    /**
     *
     * @return array
     */
    public function post_default_user_group_change()
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }
        $current_user = $this->current_user();

        $whitelabel_id = Input::json('whitelabel_id');

        if ($current_user->role_id != 1 && ($current_user->role_id != 3 || !Model_Admin_Modules::check_admin_whitelabel($whitelabel_id, $current_user->id))) {
            return $this->authorizationError();
        }

        $group_id = Input::json('new_default');

        Model_Whitelabel::update_default_whitelabel_user_group($whitelabel_id, $group_id);
        Lotto_Helper::clear_cache(['model_whitelabel']);

        return [
            'code' => 200,
            'message' => "Default group for whitelabel has been changed."
        ];
    }

    /**
     *
     * @return array
     */
    public function post_user_group_details()
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }
        $whitelabel_id = Input::json('whitelabel_id');
        $current_user = $this->current_user();
        if ($current_user->role_id != 1 && !Model_Admin_Modules::check_user_module('user-groups-view', $whitelabel_id, $current_user->id)) {
            return $this->authorizationError();
        }
        $id = Input::json('id');
        $locale = $current_user->code;

        $group = Model_Whitelabel_User_Group::find_by_pk($id);
        $this->prepare_groups_data($group, [], $locale);

        return [
            'code' => 200,
            'group' => $group
        ];
    }

    /**
     *
     * @return array
     */
    public function post_user_group_update()
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }

        $id = Input::json('id');
        $group = Model_Whitelabel_User_Group::find_by_pk($id);
        $current_user = $this->current_user();
        if ($current_user->role_id != 1 && !Model_Admin_Modules::check_user_module('user-groups-edit', $group->whitelabel_id, $current_user->id)) {
            return $this->authorizationError();
        }
        $updated_group = Input::json('updatedGroup');
        if (count($updated_group) > 0) {
            $validator = new Helpers_Crm_Validation();
            $errors = $validator->check_for_errors_user_group($updated_group, $group->whitelabel_id);

            if (count($errors) > 0) {
                return [
                    'code' => 400,
                    'errors' => $errors
                ];
            }

            $res = Model_Whitelabel_User_Group::update_user_group($group['id'], $updated_group);
            if (!$res) {
                return [
                    'code' => 404
                ];
            }
        }

        return [
            'code' => 200,
            'message' => _("User group updated successfully.")
        ];
    }

    /**
     *
     * @return array
     */
    public function post_new_user_group()
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }

        $whitelabel_id = Input::json('id');

        $current_user = $this->current_user();
        if ($current_user->role_id != 1 && !Model_Admin_Modules::check_user_module('user-groups-edit', $whitelabel_id, $current_user->id)) {
            return $this->authorizationError();
        }
        $group = Input::json('group');
        $validator = new Helpers_Crm_Validation();
        $errors = $validator->check_for_errors_user_group($group, $whitelabel_id);

        if (count($errors) > 0) {
            return [
                'code' => 400,
                'errors' => $errors
            ];
        }
        $new_group = Model_Whitelabel_User_Group::forge();
        $new_group->set([
            "name" => $group['name'],
            "whitelabel_id" => $whitelabel_id,
            "prize_payout_percent" => $group['prize_payout_percent'],
            "is_selectable_by_user" => $group['is_selectable_by_user']
        ]);

        $group_obj = $new_group->save();

        return [
            'code' => 200,
            'message' => _("User group added successfully.")
        ];
    }

    /**
     *
     * @return array
     */
    public function post_delete_user_group()
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }

        $group_id = Input::json('id');
        $group = Model_Whitelabel_User_Group::find_by_pk($group_id);

        if (!$group) {
            return [
                'code' => 400,
                'message' => _("Incorrect group!")
            ];
        }

        $current_user = $this->current_user();
        if ($current_user->role_id != 1 && !Model_Admin_Modules::check_user_module('user-groups-delete', $group->whitelabel_id, $current_user->id)) {
            return $this->authorizationError();
        }

        $has_members = Model_Whitelabel_User_Group::check_users_for_group($group_id);

        if ($has_members) {
            return [
                'code' => 400,
                'message' => _("Group cannot be removed - there are users assigned to it. Move users to another group before proceeding!")
            ];
        }

        $group->delete();

        return [
            'code' => 200,
            'message' => _('Group has been removed successfully.')
        ];
    }

    /**
     *
     * @return array
     */
    public function post_update_user_groups()
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }

        $whitelabel_id = Input::json('whitelabel_id');
        $current_user = $this->current_user();
        if ($current_user->role_id != 1 && !Model_Admin_Modules::check_user_module(CrmModuleHelper::MODULE_USERS_EDIT_ACCOUNT_PERSONAL_DATA, $whitelabel_id, $current_user->id)) {
            return $this->authorizationError();
        }

        $users = Input::json('choosedUsers');
        /** Random full token is chosen because it can update users group logged in single whitelabel */
        $randomUserFullToken = $users[0];
        if (FullTokenHelper::isNotValid($randomUserFullToken)) {
            return $this->authorizationError();
        }
        $groups = Input::json('selectedGroups');
        $groupsId = [];
        foreach ($groups as $group) {
            $groupsId[] = $group['value'];
        }
        $whitelabelRepository = Container::get(WhitelabelRepository::class);
        $whitelabel = $whitelabelRepository->getWhitelabelByFullToken($randomUserFullToken);
        foreach ($users as $index => $fullToken) {
            $users[$index] = FullTokenHelper::getToken($fullToken);
        }

        $users_ids_objs = DB::select('id', 'token', 'whitelabel_id')
            ->from('whitelabel_user')
            ->where('token', 'IN', $users)
            ->and_where('whitelabel_id', '=', $whitelabel->id)
            ->execute()->as_array();

        $users_ids = array_column($users_ids_objs, 'id');
        $users_tokens = array_column($users_ids_objs, 'token');

        foreach ($users as $token) {
            if (!in_array($token, $users_tokens)) {
                return [
                    'code' => 400,
                    'message' => 'Wrong user: ' . $token
                ];
            }
        }

        $whitelabel_groups = Model_Whitelabel_User_Group::get_all_groups_keys_for_whitelabel($whitelabel_id);
        foreach ($groupsId as $groupId) {
            if (!in_array($groupId, $whitelabel_groups)) {
                return [
                    'code' => 400,
                    'message' => 'Wrong group!'
                ];
            }
        }

        $result = true;
        if (count($groupsId) > 0) {
            $result = Model_Whitelabel_User_Group::add_users_to_groups($users_ids, $groupsId);
        }

        if (!$result) {
            return [
                'code' => 400,
                'message' => 'Groups can not be updated!'
            ];
        }

        return [
            'code' => 200,
            'message' => 'User groups updated successfully.'
        ];
    }

    public function post_raffle_tickets_table_data(): array
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }

        $start_date = Input::json('start');
        $end_date = Input::json('end');

        $whitelabel_id = Input::json('whitelabel');
        $current_user = $this->current_user();
        if ($current_user->role_id != 1 && !Model_Admin_Modules::check_user_module('raffle-tickets-view', $whitelabel_id, $current_user->id)) {
            return $this->authorizationError();
        }
        if ($whitelabel_id == 0) {
            $whitelabel_id = null;
        }

        $filters = Input::json('filters');
        $active_tab = Input::json('activeTab');
        $page = Input::json('page');
        $items_per_page = Input::json('itemsPerPage');
        $sort_by = Input::json('sortBy');
        $order = Input::json('order');

        $table_data = Model_Whitelabel_Raffle_Ticket::get_full_data_for_crm(
            $whitelabel_id,
            $active_tab,
            $filters,
            $page,
            $items_per_page,
            $sort_by,
            $order
        );

        $all_count = Model_Whitelabel_Raffle_Ticket::get_raffle_tickets_counts_for_crm($whitelabel_id, $filters, null);
        $pending_count = Model_Whitelabel_Raffle_Ticket::get_raffle_tickets_counts_for_crm($whitelabel_id, $filters, Model_Whitelabel_Raffle_Ticket::RAFFLE_TICKET_STATUS_PENDING);
        $win_count = Model_Whitelabel_Raffle_Ticket::get_raffle_tickets_counts_for_crm($whitelabel_id, $filters, Model_Whitelabel_Raffle_Ticket::RAFFLE_TICKET_STATUS_WIN);
        $nowinnings_count = Model_Whitelabel_Raffle_Ticket::get_raffle_tickets_counts_for_crm($whitelabel_id, $filters, Model_Whitelabel_Raffle_Ticket::RAFFLE_TICKET_STATUS_NO_WINNINGS);

        foreach ($table_data as &$ticket) {
            $this->prepare_raffle_ticket_data($ticket, $whitelabel_id, $current_user->code);
        }

        return [
            'code' => 200,
            'data' => $table_data,
            'all_count' => $all_count,
            'pending_count' => $pending_count,
            'win_count' => $win_count,
            'nowinnings_count' => $nowinnings_count,
        ];
    }

    public function post_raffle_ticket_details(): array
    {
        $fullToken = Input::json('token');
        if (!$this->middleware_auth() || FullTokenHelper::isNotValid($fullToken)) {
            return $this->authorizationError();
        }

        $whitelabel_id = Input::json('whitelabel_id');
        $whitelabelRepository = Container::get(WhitelabelRepository::class);
        $whitelabel = $whitelabelRepository->getWhitelabelByFullToken($fullToken);
        if ($whitelabel_id == 0) {
            $whitelabel_id = null;
        } else {
            $whitelabel_id = $whitelabel->id;
        }

        $current_user = $this->current_user();
        if ($current_user->role_id != 1 && !Model_Admin_Modules::check_user_module('raffle-tickets-view', $whitelabel_id, $current_user->id)) {
            return $this->authorizationError();
        }

        $token = FullTokenHelper::getToken($fullToken);
        $ticket = Model_Whitelabel_Raffle_Ticket::get_single_for_crm($token, $whitelabel->id);
        if (empty($ticket)) {
            return $this->authorizationError();
        }
        $ticket_lines = Model_Whitelabel_Raffle_Ticket_Line::get_by_ticket_id($ticket['id']);

        $this->prepare_raffle_ticket_data($ticket, $whitelabel_id, $current_user->code);
        $lines_data = $this->prepare_raffle_line_data($whitelabel_id, $current_user->code, $ticket, $ticket_lines);

        return [
            'code' => 200,
            'lines' => $lines_data,
            'details' => $ticket
        ];
    }

    public function post_export_raffle_tickets()
    {
        set_time_limit(600);

        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }
        $whitelabel_id = Input::json('whitelabel');
        if ($whitelabel_id == 0) {
            $whitelabel_id = null;
        }

        $current_user = $this->current_user();
        if ($current_user->role_id != 1 && !Model_Admin_Modules::check_user_module('raffle-tickets-view', $whitelabel_id, $current_user->id)) {
            return $this->authorizationError();
        }

        $show_login = $this->showLogin($whitelabel_id);

        $active_tab = Input::json('activeTab');
        $filters = Input::json('filters');

        $records_num = 0;
        $headers = [_('ID'), _('Transaction ID'), _('Raffle'), _('User ID'), _('User name'), _('User e-mail')];

        if ($show_login) {
            $headers[] = _('User login');
        }

        array_push($headers, _('Amount'), _('Bonus amount'), _('Date'), _('Draw date'));

        switch ($active_tab) {
            case 'all':
                array_push($headers, _('Status'));
                $records_num = Model_Whitelabel_Raffle_Ticket::get_raffle_tickets_counts_for_crm($whitelabel_id, $filters, null);
                break;
            case 'pending':
                $records_num = Model_Whitelabel_Raffle_Ticket::get_raffle_tickets_counts_for_crm($whitelabel_id, $filters, Model_Whitelabel_Raffle_Ticket::RAFFLE_TICKET_STATUS_PENDING);
                break;
            case 'win':
                $records_num = Model_Whitelabel_Raffle_Ticket::get_raffle_tickets_counts_for_crm($whitelabel_id, $filters, Model_Whitelabel_Raffle_Ticket::RAFFLE_TICKET_STATUS_WIN);
                break;
            case 'nowinnings':
                $records_num = Model_Whitelabel_Raffle_Ticket::get_raffle_tickets_counts_for_crm($whitelabel_id, $filters, Model_Whitelabel_Raffle_Ticket::RAFFLE_TICKET_STATUS_NO_WINNINGS);
                break;
        }
        array_push($headers, _('Prize'), _('Paid out'), _('Line number'));

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment');

        $this->closeSession();

        $output = fopen('php://output', 'w');

        fputcsv($output, $headers);

        $items = 10000;
        $pages = ceil($records_num / $items);
        for ($i = 1; $i <= $pages; $i++) {
            $data_array = Model_Whitelabel_Raffle_Ticket::get_full_data_for_crm(
                $whitelabel_id,
                $active_tab,
                $filters,
                $i,
                $items,
                null,
                null,
                true
            );
            $ids = array_column($data_array, 'id');
            $all_lines_for_batch = Model_Whitelabel_Raffle_Ticket_Line::get_all_batched_for_crm($ids);
            foreach ($data_array as &$data) {
                $lines = [];
                if (isset($all_lines_for_batch[$data['id']])) {
                    $lines_data = $all_lines_for_batch[$data['id']];
                }
                $this->prepare_raffle_ticket_data($data, $whitelabel_id, $current_user->code);
                $lines = $this->prepare_raffle_lines_data_for_export($whitelabel_id, $data, $lines_data, $current_user->code);
                $values = [];
                array_push(
                    $values,
                    $data['full_token'],
                    $data['transaction_full_token'],
                    $data['rname'],
                    $data['user_full_token'],
                    $data['user_fullname'],
                    $data['email']
                );
                if ($show_login) {
                    array_push($values, $data['user_login']);
                }
                array_push(
                    $values,
                    $data['amount_display'],
                    $data['bonus_amount_display'],
                    $data['created_at'],
                    $data['draw_date'] ?? ""
                );
                if ($active_tab == 'all') {
                    array_push(
                        $values,
                        $data['status_display']
                    );
                }
                array_push(
                    $values,
                    $data['prize_display'] ?? "",
                    $data['payout_display'],
                    $lines
                );
                fputcsv($output, $values);
                ob_flush();
                flush();
            }
        }
        fclose($output);
    }

    /** @return array{code: int, tableData: array, itemsCountPerTab: array, currencies: array, total: array} */
    public function post_casino_report(): array
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }

        /** @var CrmConfig $crmTableConfig */
        $crmTableConfig = $this->crmTableService->getConfig();
        $whitelabelId = $crmTableConfig->whitelabelId;

        $isNotSuperadmin = $this->user->isNotSuperadmin();
        $denyAccess = $isNotSuperadmin &&
            !Model_Admin_Modules::check_user_module('casino-reports-view', $whitelabelId, $this->user->id);
        if ($denyAccess) {
            return $this->authorizationError();
        }

        $perWhitelabelTab = 'per_whitelabel';
        $perGameTab = 'per_game';
        $perGameProviderTab = 'per_game_provider';
        $limitsTab = 'limits';

        $fetchDataFunction = null;
        $activeTab = $crmTableConfig->activeTab;

        $currencyUsdRate = 1;
        $isNotSuperadminView = $isNotSuperadmin || $whitelabelId !== 0;
        $crmTableConfig->isNotSuperadminView = $isNotSuperadminView;

        if ($isNotSuperadminView) {
            /** @var Whitelabel $whitelabel */
            $whitelabel = $this->whitelabelRepository->findOneById($whitelabelId);
            $currencyUsdRate = $whitelabel->currency->rate;
            $currencyCode = $whitelabel->currency->code;
            $whitelabelsCount = count($whitelabel->whitelabelSlotProviders) > 0 ? 1 : 0;
        } else {
            $whitelabelsCount = $this->whitelabelRepository->countWithEnabledSlots();
            // In this currency, data is taken from database
            $currencyCode = 'USD';
        }

        $currencyCodes = [
            'bets' => $currencyCode,
            'wins' => $currencyCode,
            'ggr' => $currencyCode,
            'left_limit' => $currencyCode
        ];

        switch ($activeTab) {
            case $perWhitelabelTab:
                $fetchDataFunction = fn (...$args) => $this->slotTransactionRepository->findGgrPerWhitelabel(...$args);
                break;
            case $perGameTab:
                $fetchDataFunction = fn (...$args) => $this->slotTransactionRepository->findGgrPerGame(...$args);
                break;
            case $perGameProviderTab:
                $fetchDataFunction = fn (...$args) => $this->slotTransactionRepository->findGgrPerGameProvider(...$args);
                break;
            case $limitsTab:
                $fetchDataFunction = fn (...$args) => $this->whitelabelSlotProviderRepository->findLimitsPerV2WhitelabelWithPagination(...$args);
                break;
        }

        $data = $fetchDataFunction($crmTableConfig, $currencyUsdRate);

        if (key_exists('total', $data)) {
            $total = ['total' => $data['total'][0]];
        } else {
            $total = [];
        }

        return array_merge([
            'code' => 200,
            'tableData' => $data['data'],
            'itemsCountPerTab' => [
                $perWhitelabelTab => $data['fullDataCount'] ?? $whitelabelsCount,
                $perGameTab => $this->slotGameRepository->countWithAnyBetTransaction($crmTableConfig, $whitelabelId),
                $perGameProviderTab => $this->slotGameRepository->countProvidersWithAnyBetTransaction($crmTableConfig, $whitelabelId),
                $limitsTab => $isNotSuperadminView ? 1 : $this->whitelabelRepository->countV2(),
            ],
            'currencies' => $currencyCodes
        ], $total);
    }

    /** @return array{code: int, tableData: array, itemsCountPerTab: array} */
    public function post_acceptance_rate_report(): array
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }

        $whitelabelId = Input::json('whitelabelId');
        $denyAccess = $this->user->isNotSuperadmin() && !Model_Admin_Modules::check_user_module('acceptance-rate-report-view', $whitelabelId, $this->user->id);
        if ($denyAccess) {
            return $this->authorizationError();
        }


        /** @var CrmConfig $crmTableConfig */
        $crmTableConfig = $this->crmTableService->getConfig();

        switch ($crmTableConfig->activeTab) {
            case 'per_method':
                $acceptanceRateRows = $this->paymentMethodRepository->getCrmAcceptanceRateReportDataGroupedByMethodName($crmTableConfig, $whitelabelId);
                break;
            default:
                $acceptanceRateRows = $this->paymentMethodRepository->getCrmAcceptanceRateReportData($crmTableConfig, $whitelabelId);
                $countryCodes = CountryHelper::COUNTRY_CODES;
                foreach ($acceptanceRateRows as $index => $row) {
                    $countryCode = $row['register_country_code'];
                    if (!array_key_exists($countryCode, $countryCodes)) {
                        $acceptanceRateRows[$index]['register_country'] = '';
                        continue;
                    }

                    $acceptanceRateRows[$index]['register_country'] = $countryCodes[$countryCode];
                }
                break;
        }

        return [
            'code' => 200,
            'tableData' => $acceptanceRateRows,
            'itemsCountPerTab' => [
                'per_method' => $this->paymentMethodRepository->countAcceptanceRateReportGroupedByMethodName($crmTableConfig, $whitelabelId),
                'per_country' => $this->paymentMethodRepository->countAcceptanceRateReport($crmTableConfig, $whitelabelId)
            ],
        ];
    }

    /** @return array{code: int, tableData: array, itemsCountPerTab: array} */
    public function post_transaction_per_method(): array
    {
        $whitelabelId = (int) SanitizerHelper::sanitizeString(Input::json('whitelabelId'));
        $isCasino = Input::get('isCasino') === 'true';
        $isDeposit = Input::get('isDeposit') === 'true';
        $startDate = SanitizerHelper::sanitizeString(Input::get('startDate', Helpers_Time::getFirstDateTimeOfCurrentMonth()));
        $endDate = SanitizerHelper::sanitizeString(Input::get('endDate', Helpers_Time::getLastDateTimeOfCurrentMonth()));
        $whitelabelTransactionRepository = Container::get(WhitelabelTransactionRepository::class);
        /** @var CrmConfig $crmTableConfig */
        $crmTableConfig = $this->crmTableService->getConfig();

        $tableData = $whitelabelTransactionRepository->getTransactionGroupedPerMethod(
            $isCasino,
            $isDeposit,
            $startDate,
            $endDate,
            $whitelabelId,
            $crmTableConfig,
        );

        $this->addCurrencyAndBalance($tableData, $whitelabelId);

        $allItemsCountPerTab = $whitelabelTransactionRepository->getTransactionNumbersGroupedPerMethod(
            $isCasino,
            $isDeposit,
            $startDate,
            $endDate,
            $whitelabelId,
            $crmTableConfig,
        );

        return [
            'code' => 200,
            'itemsCountPerTab' => [
                'all_per_method' => $allItemsCountPerTab,
            ],
            'tableData' => $tableData,
        ];
    }

    /** @return array{code: int, tableData: array, itemsCountPerTab: array} */
    public function post_withdrawal_report_per_method(): array
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }

        $whitelabelId = Input::json('whitelabelId');
        $startDate = SanitizerHelper::sanitizeString(Input::get('startDate', Helpers_Time::getFirstDateTimeOfCurrentMonth()));
        $endDate = SanitizerHelper::sanitizeString(Input::get('endDate', Helpers_Time::getLastDateTimeOfCurrentMonth()));
        $isCasino = Input::get('isCasino') === 'true';
        /** @var CrmConfig $crmTableConfig */
        $crmTableConfig = $this->crmTableService->getConfig();
        $whitelabelWithdrawalRepository = Container::get(WhitelabelWithdrawalRepository::class);

        $tableData = $whitelabelWithdrawalRepository->getWithdrawalRequestGroupedPerMethod(
            $whitelabelId,
            $isCasino,
            $crmTableConfig,
            $startDate,
            $endDate
        );

        $this->addCurrencyAndBalance($tableData, $whitelabelId);

        $allItemsCountPerTab = $whitelabelWithdrawalRepository->getWithdrawalRequestNumbersGroupedPerMethod(
            $whitelabelId,
            $isCasino,
            $crmTableConfig,
            $startDate,
            $endDate
        );

        return [
            'code' => 200,
            'itemsCountPerTab' => [
                'all_per_method' => $allItemsCountPerTab,
            ],
            'tableData' => $tableData,
        ];
    }


    public function get_available_currencies(): array
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }

        $whitelabelId = (int)Input::get('whitelabel_id');
        $adminHasNotCorrectPrivileges = $this->user->isNotSuperadmin() &&
            !Model_Admin_Modules::check_user_module(CrmModuleHelper::MODULE_SEO_WIDGETS_GENERATOR, $whitelabelId, $this->user->id);
        if ($adminHasNotCorrectPrivileges) {
            return $this->authorizationError();
        }

        return $this->currencyRepository->getResults();
    }

    public function get_available_languages(): array
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }        $whitelabelId = (int)Input::get('whitelabel_id');
        $denyAccess = $this->user->isNotSuperadmin() &&
            !Model_Admin_Modules::check_user_module(CrmModuleHelper::MODULE_SEO_WIDGETS_GENERATOR, $whitelabelId, $this->user->id);
        if ($denyAccess) {
            return $this->authorizationError();
        }

        $whitelabelId = $whitelabelId === 0 ?
            $this->whitelabelRepository->findOneByTheme(Whitelabel::LOTTOPARK_THEME)->id :
            $whitelabelId;
        $whitelabel = $this->whitelabelRepository->findOneById($whitelabelId);
        return $this->whitelabelLanguageRepository->getAll($whitelabel);
    }

    public function get_enabled_lotteries(): array
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }

        $whitelabelId = (int)Input::get('whitelabel_id');
        $denyAccess = $this->user->isNotSuperadmin() &&
            !Model_Admin_Modules::check_user_module(CrmModuleHelper::MODULE_SEO_WIDGETS_GENERATOR, $whitelabelId, $this->user->id);
        if ($denyAccess) {
            return $this->authorizationError();
        }

        $whitelabelId = $whitelabelId === 0 ?
            $this->whitelabelRepository->findOneByTheme(Whitelabel::LOTTOPARK_THEME)->id :
            $whitelabelId;
        $whitelabel['id'] = $whitelabelId;

        return Model_Lottery::get_all_lotteries_for_whitelabel($whitelabel);
    }

    public function post_lotteriesWaitingForDraw(): array
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }

        if ($this->user->isNotSuperadmin()) {
            return $this->authorizationError();
        }

        return $this->ltechManualDrawService->getLotteriesForManualDraw();
    }

    /**
     * ONLY FOR MAIN SUPERADMIN IN CRM!
     * It's used for manually adding draw.
     */
    public function post_addLtechManualDraw(): array
    {
        if (!$this->middleware_auth()) {
            return $this->authorizationError();
        }

        if ($this->user->isNotSuperadmin()) {
            return $this->authorizationError();
        }

        $ltechManualDrawValidator = Container::get(LtechManualDrawValidator::class);
        $isRequestInvalid = $ltechManualDrawValidator->isNotValid();
        if ($isRequestInvalid) {
            $errors = json_encode($ltechManualDrawValidator->getErrors());
            return [
                'code' => 400,
                'message' => "Request is invalid. $errors"
            ];
        }

        [
            $lotterySlug,
            $nextDrawDate,
            $nextJackpot,
            $additionalNumber
        ] = $ltechManualDrawValidator->getValidatedProperties([
            'currentLottery.slug',
            'nextDrawDate',
            'nextJackpot',
            'additionalNumber',
        ]);

        $prizes = $ltechManualDrawValidator->getPrizes();
        $winners = $ltechManualDrawValidator->getWinners();

        $lotteryRepository = Container::get(LotteryRepository::class);
        $lottery = $lotteryRepository->findOneBySlug($lotterySlug);

        $lotteryTicketNumbersValidator = Container::get(LotteryTicketNumbersValidator::class);
        $lotteryTicketNumbersValidator->setBuildArguments($lottery);
        $lotteryTicketNumbersValidator->setExtraCheckArguments($lottery);
        $lotteryTicketNumbersValidator->setCustomInput([
            'normalNumbers' => Input::json('normalNumbers', []),
            'bonusNumbers' => Input::json('bonusNumbers', []),
        ]);
        $areNumbersInvalid = $lotteryTicketNumbersValidator->isNotValid();
        if ($areNumbersInvalid) {
            $errors = json_encode($lotteryTicketNumbersValidator->getErrors());
            return [
                'code' => 400,
                'message' => "Request is invalid. $errors"
            ];
        }

        [
            'normalNumbers' => $normalNumbers,
            'bonusNumbers' => $bonusNumbers,
        ] = $lotteryTicketNumbersValidator->getValidatedProperties();

        $ltechManualDrawService = Container::get(LtechManualDrawService::class);
        $ltechManualDrawService->add(
            $lottery,
            $nextDrawDate,
            $normalNumbers,
            $bonusNumbers,
            $nextJackpot,
            $lottery->currency,
            $prizes,
            $winners,
            $additionalNumber,
        );

        return ['code' => 200];
    }

    private function addCurrencyAndBalance(&$tableData, ?int $whitelabelId): void
    {
        $whitelabelRepository = Container::get(WhitelabelRepository::class);
        $whitelabel = $whitelabelRepository->findOneById($whitelabelId);
        foreach ($tableData as &$singleRow) {
            foreach ($singleRow as $key => &$value) {
                if (str_contains($key, 'method_name') && is_null($value)) {
                    $value = _('Balance');
                }

                if (str_contains($key, 'amount')) {
                    $value = Lotto_View::format_currency(
                        $value,
                        $whitelabel->currency->code ?? 'USD', // $whitelabel not exists when super admin is logged in
                        true
                    );
                }
            }
        }
    }

    private function showLogin(?int $whitelabelId): bool
    {
        $showLogin = true;
        $isNotSuperAdmin = !empty($whitelabelId);
        if ($isNotSuperAdmin) {
            $whitelabelRepository = Container::get(WhitelabelRepository::class);
            /** @var Whitelabel $whitelabelModel */
            $whitelabelModel = $whitelabelRepository->findOneById($whitelabelId);
            $showLogin = $whitelabelModel->loginForUserIsUsedDuringRegistration();
        }

        return $showLogin;
    }

    /**
     * Returns module name to check access against based on balance, bonus balance or casino balance
     */
    private static function getEditUserBalanceModuleName(bool $isBonus, bool $isCasino): string
    {
        $moduleName = CrmModuleHelper::MODULE_USERS_BALANCE_EDIT;
        if ($isBonus) {
            $moduleName = CrmModuleHelper::MODULE_USERS_BONUS_BALANCE_EDIT;
        }

        if ($isCasino) {
            $moduleName = CrmModuleHelper::MODULE_USERS_BALANCE_CASINO_EDIT;
        }

        return $moduleName;
    }

    /**
     * Returns module name to check access against based on balance, bonus balance or casino balance
     */
    private static function getAddManualDepositModuleName(bool $isBonus, bool $isCasino): string
    {
        $moduleName = CrmModuleHelper::MODULE_USERS_MANUAL_DEPOSIT_ADD;
        if ($isBonus) {
            $moduleName = CrmModuleHelper::MODULE_USERS_BONUS_BALANCE_MANUAL_DEPOSIT_ADD;
        }

        if ($isCasino) {
            $moduleName = CrmModuleHelper::MODULE_USERS_CASINO_BALANCE_MANUAL_DEPOSIT_ADD;
        }

        return $moduleName;
    }

    /**
     * Use it everywhere in generating report methods, where ob_flush is used
     * In other case fuel will try to rotate session and save cookie after ob_flush
     * And we receive: "Cannot modify header information" error
     */
    private function closeSession(): void
    {
        Session::close();
    }
}
