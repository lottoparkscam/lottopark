<?php

use Fuel\Core\DB;
use Services\Logs\FileLoggerService;

/**
 * Model class of Whitelabel_User to manage users
 */
class Model_Whitelabel_User extends Model_Model implements Forms_Status
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_user';

    /**
     *
     * @var array
     */
    public static $cache_list = [];

    /**
     * This is in fact token
     *
     * @var int
     */
    private $param_id;

    /**
     *
     * @var array
     */
    private $whitelabel = [];

    /**
     *
     * @var string
     */
    private $domain = "";

    /**
     * @param int $param_id
     * @param array $whitelabel
     */
    public function __construct($param_id = null, $whitelabel = [])
    {
        parent::__construct();
        $this->param_id = $param_id;
        $this->whitelabel = $whitelabel;

        if (!empty($param_id) && empty($whitelabel)) {      // This is only in the case of admin page
            $domain = Model_Whitelabel::get_domain_for_user($this->param_id);
            if (is_null($domain)) {     // This could be a problem if it will be null!!!
                return;
            }
            $this->domain = $domain;

            $whitelabel = Model_Whitelabel::get_by_domain($this->domain);
            if (is_null($whitelabel)) {     // This could be a problem if it will be null!!!
                return;
            }
            $this->whitelabel = $whitelabel;
        }
    }

    /**
     *
     * @return string|null
     */
    public function get_domain_for_user(): ?string
    {
        if (empty($this->domain) && !empty($this->get_param_id())) {
            $domain = Model_Whitelabel::get_domain_for_user($this->get_param_id());
            if (is_null($domain)) {     // This could be a problem!
                return null;
            }
            $this->domain = $domain;
        }

        return $this->domain;
    }

    /**
     *
     * @return null|array
     */
    public function get_whitelabel(): ?array
    {
        if (empty($this->whitelabel) && !empty($this->get_param_id())) {
            $domain = Model_Whitelabel::get_domain_for_user($this->get_param_id());
            if (is_null($domain)) {     // This could be a problem if it will be null!!!
                return null;
            }
            $this->domain = $domain;

            $whitelabel = Model_Whitelabel::get_by_domain($domain);
            if (is_null($whitelabel)) {     // This could be a problem if it will be null!!!
                return null;
            }
            $this->whitelabel = $whitelabel;
        }

        return $this->whitelabel;
    }

    /**
     *
     * @param array $whitelabel
     */
    public function set_whitelabel($whitelabel): void
    {
        $this->whitelabel = $whitelabel;
    }

    /**
     *
     * @return int
     */
    public function get_param_id(): int
    {
        return $this->param_id;
    }

    /**
     *
     * @param int $param_id
     */
    public function set_param_id($param_id): void
    {
        $this->param_id = $param_id;
    }

    /**
     *
     * @param array $whitelabel
     * @param string $email
     * @return null|array
     */
    public static function get_count_for_whitelabel_and_email(
        array $whitelabel,
        string $email
    ): ?array {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;

        $query = "SELECT 
                COUNT(*) AS count 
            FROM whitelabel_user 
            WHERE 1=1 ";

        if (!empty($whitelabel) && !empty($whitelabel["id"])) {
            $query .= " AND whitelabel_id = :whitelabel_id";
        }

        $query .= " AND is_deleted = 0";

        if (!empty($email)) {
            $query .= " AND email = :email";
        }

        try {
            $db = DB::query($query);

            if (!empty($whitelabel) && !empty($whitelabel["id"])) {
                $db->param(":whitelabel_id", $whitelabel["id"]);
            }

            if (!empty($email)) {
                $db->param(":email", $email);
            }

            $result = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        return $result;
    }

    /**
     *
     * @param array $whitelabel If it is set the results will be for that whitelabel otherwise for all whitelabels
     * @param array $params
     * @param array $sort
     * @param int $deleted
     * @param string $add
     * @param string $filter_add
     * @return null|array
     */
    public static function get_filtered_data_full(
        $whitelabel,
        $params,
        $sort,
        $deleted = 0,
        $add = "",
        $filter_add = ""
    ): ?array {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $query = "SELECT * 
            FROM whitelabel_user 
            WHERE 1=1 ";

        if (!empty($whitelabel) && !empty($whitelabel["id"])) {
            $query .= " AND whitelabel_id = :whitelabel_id";
        }

        $query .= " AND is_deleted = " . $deleted . $add . $filter_add;

        if (!empty($sort) && !empty($sort["db"])) {
            $query .= " ORDER BY :order ";
        }

        try {
            $db = DB::query($query);

            if (!empty($whitelabel) && !empty($whitelabel["id"])) {
                $db->param(":whitelabel_id", $whitelabel["id"]);
            }

            if (!empty($sort) && !empty($sort["db"])) {
                $db->param(":order", DB::expr($sort["db"]));
            }

            foreach ($params as $param) {
                $db->param($param[0], $param[1]);
            }

            $result = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        return $result;
    }

    /**
     *
     * @see get_filtered_data_full_for_admin
     * @return \Fuel\Core\Database_Query
     */
    public static function query_get_filtered_data_full_for_admin(
        $whitelabel,
        $params,
        $sort,
        $deleted = 0,
        $add = "",
        $filter_add = ""
    ): \Fuel\Core\Database_Query {
        $query = "SELECT 
                whitelabel.prefix AS pref, 
                whitelabel.name AS w_name,
                whitelabel.domain AS w_domain,
                whitelabel_user.*,
                whitelabel_aff.name AS aff_name,
                whitelabel_aff.surname AS aff_surname,
                whitelabel_aff.login AS aff_login,
                whitelabel_aff.email AS aff_email,
                whitelabel_aff.token AS aff_token,
                whitelabel_aff.is_confirmed AS aff_is_confirmed,
                whitelabel_aff.is_accepted AS aff_is_accepted,
                whitelabel_aff.is_deleted AS aff_is_deleted
            FROM whitelabel_user 
            INNER JOIN whitelabel ON whitelabel.id = whitelabel_user.whitelabel_id 
            LEFT JOIN whitelabel_user_aff ON whitelabel_user_aff.whitelabel_user_id = whitelabel_user.id
            LEFT JOIN whitelabel_aff ON whitelabel_aff.id = whitelabel_user_aff.whitelabel_aff_id
            WHERE 1=1 ";

        if (!empty($whitelabel) && !empty($whitelabel['id'])) {
            $query .= " AND whitelabel.id = :whitelabel_id";
        }

        $query .= " AND whitelabel_user.is_deleted = " . $deleted . $add . $filter_add;
        $query .= " ORDER BY whitelabel_user.id DESC ";

        $database_query = DB::query($query);

        foreach ($params as $param) {
            $database_query->param($param[0], $param[1]);
        }

        return $database_query;
    }

    /**
     *
     * @see get_filtered_data_full_for_admin
     * @return PDOStatement|null
     */
    public static function get_filtered_data_full_for_admin_pdo(
        $whitelabel,
        $params,
        $sort,
        $deleted = 0,
        $add = "",
        $filter_add = ""
    ): ?PDOStatement {
        return Database_Pdo_Custom::unbuffered(self::query_get_filtered_data_full_for_admin(
            $whitelabel,
            $params,
            $sort,
            $deleted,
            $add,
            $filter_add
        ));
    }

    /**
     *
     * @param array $params
     * @param array $sort
     * @param int $deleted
     * @param string $add
     * @param string $filter_add
     * @return null|array
     */
    public static function get_filtered_data_full_for_admin(
        $whitelabel,
        $params,
        $sort,
        $deleted = 0,
        $add = "",
        $filter_add = ""
    ): ?array {
        $fileLoggerService = Container::get(FileLoggerService::class);

        try {
            return self::query_get_filtered_data_full_for_admin(
                $whitelabel,
                $params,
                $sort,
                $deleted,
                $add,
                $filter_add
            )->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
            return null;
        }
    }

    /**
     *
     * @param array $user
     * @return null|array
     */
    public static function get_full_data_for_user_rodo($user): ?array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $query = "SELECT 
                CONCAT(whitelabel.prefix, 'U', whitelabel_user.token) AS user_prefix_token,
                whitelabel.name AS w_name,
                whitelabel.domain AS w_domain,
                currency.code AS c_code,
                whitelabel_user.* 
            FROM whitelabel_user 
            INNER JOIN whitelabel ON whitelabel.id = whitelabel_user.whitelabel_id 
            LEFT JOIN currency ON whitelabel_user.currency_id = currency.id 
            WHERE 1=1 ";

        if (!empty($user) && !empty($user['id'])) {
            $query .= " AND whitelabel_user.id = :user_id";
        }

        $query .= " AND whitelabel_user.is_deleted = 0";
        $query .= " ORDER BY whitelabel_user.id DESC ";

        try {
            $db = DB::query($query);

            if (!empty($user) && !empty($user["id"])) {
                $db->param(":user_id", $user["id"]);
            }

            $result = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        return $result;
    }

    /**
     *
     * @param array $whitelabel
     * @param array $params
     * @param int $deleted
     * @param string $add
     * @param string $filter_add
     * @return null|array
     */
    public static function get_filtered_data_count(
        $whitelabel,
        $params,
        $deleted = 0,
        $add = "",
        $filter_add = ""
    ): ?array {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;

        $query = "SELECT 
            COUNT(*) AS count 
        FROM whitelabel_user 
        WHERE 1=1 ";

        if (!empty($whitelabel) && !empty($whitelabel["id"])) {
            $query .= " AND whitelabel_id = :whitelabel_id ";
        }

        $query .= " AND is_deleted = " . $deleted . $add . $filter_add;

        try {
            $db = DB::query($query);

            if (!empty($whitelabel) && !empty($whitelabel["id"])) {
                $db->param(":whitelabel_id", $whitelabel["id"]);
            }

            foreach ($params as $param) {
                $db->param($param[0], $param[1]);
            }

            $result = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        return $result;
    }

    /**
     * @see get_data_joined_with_aff
     * @return \Fuel\Core\Database_Query
     */
    private static function query_data_joined_with_aff(
        $whitelabel,
        $pagination,
        $sort,
        $params,
        $deleted = 0,
        $add = "",
        $filter_add = ""
    ): \Fuel\Core\Database_Query {
        $query = "SELECT 
            whitelabel.id as w_id, 
            whitelabel.type as w_type, 
            whitelabel.prefix as w_prefix, 
            whitelabel.user_activation_type as w_user_activation_type, 
            whitelabel_user.*, 
            user_currency.id AS user_currency_id,
            user_currency.code AS user_currency_code,
            user_currency.rate AS user_currency_rate,
            manager_currency.code AS manager_currency_code, 
            whitelabel_user_aff.whitelabel_aff_id,
            whitelabel_aff.name AS aff_name,
            whitelabel_aff.surname AS aff_surname,
            whitelabel_aff.login AS aff_login,
            whitelabel_aff.email AS aff_email,
            whitelabel_aff.token AS aff_token,
            whitelabel_aff.is_confirmed AS aff_is_confirmed,
            whitelabel_aff.is_accepted AS aff_is_accepted,
            whitelabel_aff.is_deleted AS aff_is_deleted
        FROM whitelabel_user 
        INNER JOIN whitelabel ON whitelabel.id = whitelabel_user.whitelabel_id 
        LEFT JOIN whitelabel_user_aff ON whitelabel_user_aff.whitelabel_user_id = whitelabel_user.id 
        LEFT JOIN currency user_currency ON whitelabel_user.currency_id = user_currency.id 
        LEFT JOIN currency manager_currency ON whitelabel.manager_site_currency_id = manager_currency.id 
        LEFT JOIN whitelabel_aff ON whitelabel_aff.id = whitelabel_user_aff.whitelabel_aff_id
        WHERE 1=1 ";

        if (!empty($whitelabel) && !empty($whitelabel["id"])) {
            $query .= " AND whitelabel_user.whitelabel_id = :whitelabel_id ";
        }

        $query .= " AND whitelabel_user.is_deleted = " . $deleted . $add . $filter_add;

        if (!empty($sort) && !empty($sort["db"])) {
            $query .= " ORDER BY :order ";
        }

        if (!empty($pagination)) {
            $query .= " LIMIT :offset, :limit";
        }

        $database_query = DB::query($query);

        if (!empty($whitelabel) && !empty($whitelabel["id"])) {
            $database_query->param(":whitelabel_id", $whitelabel["id"]);
        }

        if (!empty($pagination)) {
            $database_query->param(":offset", $pagination->offset);
            $database_query->param(":limit", $pagination->per_page);
        }

        if (!empty($sort) && !empty($sort["db"])) {
            $database_query->param(":order", DB::expr($sort["db"]));
        }

        foreach ($params as $param) {
            $database_query->param($param[0], $param[1]);
        }

        return $database_query;
    }

    /**
     *
     * @param array $whitelabel
     * @param array $pagination
     * @param array $sort
     * @param array $params
     * @param int $deleted
     * @param string $add
     * @param string $filter_add
     * @return PDOStatement|null
     */
    public static function get_data_joined_with_aff_pdo(
        $whitelabel,
        $pagination,
        $sort,
        $params,
        $deleted = 0,
        $add = "",
        $filter_add = ""
    ): ?PDOStatement {
        return Database_Pdo_Custom::unbuffered(self::query_data_joined_with_aff(
            $whitelabel,
            $pagination,
            $sort,
            $params,
            $deleted,
            $add,
            $filter_add
        ));
    }

    /**
     *
     * @param array $whitelabel
     * @param array $pagination
     * @param array $sort
     * @param array $params
     * @param int $deleted
     * @param string $add
     * @param string $filter_add
     * @return null|array
     */
    public static function get_data_joined_with_aff(
        $whitelabel,
        $pagination,
        $sort,
        $params,
        $deleted = 0,
        $add = "",
        $filter_add = ""
    ): ?array {
        $fileLoggerService = Container::get(FileLoggerService::class);

        try {
            return self::query_data_joined_with_aff(
                $whitelabel,
                $pagination,
                $sort,
                $params,
                $deleted,
                $add,
                $filter_add
            )->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
            return null;
        }
    }

    /**
     *
     * @param array $whitelabel
     * @param array $pagination
     * @param array $sort
     * @param array $params
     * @param int $deleted
     * @param string $add
     * @param string $filter_add
     * @return null|array
     */
    public static function get_data_joined_with_aff_for_admin(
        $whitelabel,
        $pagination,
        $sort,
        $params,
        $deleted = 0,
        $add = "",
        $filter_add = ""
    ): ?array {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $query = "SELECT 
                whitelabel.id AS w_id, 
                whitelabel.type AS w_type, 
                whitelabel.prefix AS w_prefix, 
                whitelabel.user_activation_type AS w_user_activation_type, 
                whitelabel.name AS w_name, 
                whitelabel.domain AS w_domain, 
                whitelabel_user.*, 
                user_currency.id AS user_currency_id,
                user_currency.code AS user_currency_code,
                user_currency.rate AS user_currency_rate,
                manager_currency.code AS manager_currency_code, 
                whitelabel_user_aff.whitelabel_aff_id,
                whitelabel_aff.name AS aff_name,
                whitelabel_aff.surname AS aff_surname,
                whitelabel_aff.login AS aff_login,
                whitelabel_aff.email AS aff_email,
                whitelabel_aff.token AS aff_token,
                whitelabel_aff.is_confirmed AS aff_is_confirmed,
                whitelabel_aff.is_accepted AS aff_is_accepted,
                whitelabel_aff.is_deleted AS aff_is_deleted
            FROM whitelabel_user 
            INNER JOIN whitelabel ON whitelabel.id = whitelabel_user.whitelabel_id 
            LEFT JOIN whitelabel_user_aff ON whitelabel_user_aff.whitelabel_user_id = whitelabel_user.id 
            LEFT JOIN currency user_currency ON whitelabel_user.currency_id = user_currency.id 
            LEFT JOIN currency manager_currency ON whitelabel.manager_site_currency_id = manager_currency.id 
            LEFT JOIN whitelabel_aff ON whitelabel_aff.id = whitelabel_user_aff.whitelabel_aff_id
            WHERE 1=1 ";

        if (!empty($whitelabel) && !empty($whitelabel["id"])) {
            $query .= " AND whitelabel_user.whitelabel_id = :whitelabel_id ";
        }

        $query .= " AND whitelabel_user.is_deleted = " . $deleted . $add . $filter_add;

        if (!empty($sort) && !empty($sort["db"])) {
            $query .= " ORDER BY :order ";
        }

        $query .= " LIMIT :offset, :limit";

        try {
            $db = DB::query($query);

            if (!empty($whitelabel) && !empty($whitelabel["id"])) {
                $db->param(":whitelabel_id", $whitelabel["id"]);
            }

            /** @var mixed $pagination */
            $db->param(":offset", $pagination->offset);
            $db->param(":limit", $pagination->per_page);

            if (!empty($sort) && !empty($sort["db"])) {
                $db->param(":order", DB::expr($sort["db"]));
            }

            foreach ($params as $param) {
                $db->param($param[0], $param[1]);
            }

            $result = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        return $result;
    }

    /**
     *
     * @return bool
     */
    public function delete_user(): bool
    {
        $result = true;
        $whitelabel = $this->get_whitelabel();

        $user = self::find_by([
            "whitelabel_id" => $whitelabel['id'],
            "token" => $this->get_param_id(),
            "is_deleted" => 0
        ]);

        if ($user !== null && count($user) > 0) {
            $user = $user[0];
            $user->set([
                "is_deleted" => 1,
                'date_delete' => DB::expr("NOW()"),
                'last_update' => DB::expr("NOW()")
            ]);
            $user->save();
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * @param int $id
     * @return bool
     */
    public static function delete_user_for_id($id): bool
    {
        $result = true;
        $user = Model_Whitelabel_User::find_by_pk($id);

        if ($user !== null) {
            $user->set([
                "is_deleted" => 1,
                'date_delete' => DB::expr("NOW()"),
                'last_update' => DB::expr("NOW()")
            ]);
            $user->save();
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     *
     * @return bool
     */
    public function activate_user(): bool
    {
        $result = true;
        $whitelabel = $this->get_whitelabel();

        $args = [
            "whitelabel_id" => $whitelabel['id'],
            "token" => $this->get_param_id(),
            "is_deleted" => 0,
            "is_active" => 0
        ];

        $user = self::find_by($args);

        if ($user !== null && count($user) > 0) {
            $user = $user[0];
            $user->set([
                "is_active" => 1,
                "is_confirmed" => 1,
                'last_update' => DB::expr("NOW()")
            ]);
            $user->save();

            if (!empty($user->connected_aff_id)) {
                $aff = Model_Whitelabel_Aff::find_by_pk($user->connected_aff_id);

                if (
                    $aff !== null &&
                    (int)$aff->is_deleted === 0 &&
                    ((int)$aff->is_active === 0 ||
                        ((int)$whitelabel['aff_activation_type'] === Helpers_General::ACTIVATION_TYPE_REQUIRED &&
                            (int)$aff->is_confirmed === 0))
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
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * @param int $id
     * @return bool
     */
    public static function activate_user_for_id($id): bool
    {
        $result = true;
        $user = Model_Whitelabel_User::find_by_pk($id);
        $whitelabel = Model_Whitelabel::get_single_by_id($user->whitelabel_id);

        if ($user !== null) {
            $user->set([
                "is_active" => 1,
                "is_confirmed" => 1,
                'last_update' => DB::expr("NOW()")
            ]);
            $user->save();

            if (!empty($user->connected_aff_id)) {
                $aff = Model_Whitelabel_Aff::find_by_pk($user->connected_aff_id);

                if (
                    $aff !== null &&
                    (int)$aff->is_deleted === 0 &&
                    ((int)$aff->is_active === 0 ||
                        ((int)$whitelabel['aff_activation_type'] === Helpers_General::ACTIVATION_TYPE_REQUIRED &&
                            (int)$aff->is_confirmed === 0))
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
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     *
     * @return int
     */
    public function user_restore(): int
    {
        $result = self::RESULT_OK;
        $whitelabel = $this->get_whitelabel();

        $users = self::find_by([
            "whitelabel_id" => $whitelabel['id'],
            "token" => $this->get_param_id(),
            "is_deleted" => 1
        ]);

        if ($users !== null && count($users) > 0) {
            $user = $users[0];

            $res = self::get_count_for_whitelabel_and_email($whitelabel, $user->email);

            if (is_null($res)) {    // If that situation happen it means that there is a problem with DB
                return self::RESULT_DB_ERROR;
            }

            $userscnt = $res[0]['count'];

            if ((int)$userscnt === 0) {
                $user->set([
                    "is_deleted" => 0,
                    'last_update' => DB::expr("NOW()")
                ]);
                $user->save();
            } else {
                $result = self::RESULT_USER_EXIST;
            }
        } else {
            $result = self::RESULT_INCORRECT_USER;
        }

        return $result;
    }

    /**
     * @param int $id
     * @return int
     */
    public static function user_restore_for_id($id): int
    {
        $result = self::RESULT_OK;
        $user = Model_Whitelabel_User::find_by_pk($id);

        if ($user !== null) {
            $whitelabel = Model_Whitelabel::get_single_by_id($user->whitelabel_id);
            $res = Model_Whitelabel_User::get_count_for_whitelabel_and_email($whitelabel, $user->email);

            if (is_null($res)) {    // If that situation happen it means that there is a problem with DB
                return self::RESULT_DB_ERROR;
            }

            $userscnt = $res[0]['count'];

            if ((int)$userscnt === 0) {
                $user->set([
                    "is_deleted" => 0,
                    'last_update' => DB::expr("NOW()")
                ]);
                $user->save();
            } else {
                $result = self::RESULT_USER_EXIST;
            }
        } else {
            $result = self::RESULT_INCORRECT_USER;
        }

        return $result;
    }

    /**
     *
     * @return bool
     */
    public function user_confirm(): bool
    {
        $result = true;
        $whitelabel = $this->get_whitelabel();

        if (
            !empty($whitelabel) &&
            (int)$whitelabel['user_activation_type'] !== Helpers_General::ACTIVATION_TYPE_REQUIRED
        ) {
            $user = self::find_by([
                "whitelabel_id" => $whitelabel['id'],
                "token" => $this->get_param_id(),
                "is_deleted" => 0,
                "is_confirmed" => 0,
                "is_active" => 1,
            ]);

            if ($user !== null && count($user) > 0) {
                $user = $user[0];
                $user->set([
                    "is_confirmed" => 1,
                    'last_update' => DB::expr("NOW()")
                ]);
                $user->save();

                if (!empty($user->connected_aff_id)) {
                    $aff = Model_Whitelabel_Aff::find_by_pk($user->connected_aff_id);

                    if (
                        $aff !== null &&
                        (int)$aff->is_deleted === 0 &&
                        (int)$aff->is_confirmed === 0 &&
                        (int)$aff->is_active === 1 &&
                        (int)$whitelabel['aff_activation_type'] !== Helpers_General::ACTIVATION_TYPE_REQUIRED
                    ) {
                        $aff_set = [
                            "is_confirmed" => 1
                        ];
                        $aff->set($aff_set);
                        $aff->save();
                    }
                }
            } else {
                $result = false;
            }
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * @param int $id
     * @return bool
     */
    public static function user_confirm_for_id($id): bool
    {
        $result = true;
        $user = Model_Whitelabel_User::find_by_pk($id);
        $whitelabel = Model_Whitelabel::get_single_by_id($user->whitelabel_id);

        if (
            !empty($whitelabel) &&
            (int)$whitelabel['user_activation_type'] !== Helpers_General::ACTIVATION_TYPE_REQUIRED
        ) {
            if ($user !== null) {
                $user->set([
                    "is_confirmed" => 1,
                    'last_update' => DB::expr("NOW()")
                ]);
                $user->save();

                if (!empty($user->connected_aff_id)) {
                    $aff = Model_Whitelabel_Aff::find_by_pk($user->connected_aff_id);

                    if (
                        $aff !== null &&
                        (int)$aff->is_deleted === 0 &&
                        (int)$aff->is_confirmed === 0 &&
                        (int)$aff->is_active === 1 &&
                        (int)$whitelabel['aff_activation_type'] !== Helpers_General::ACTIVATION_TYPE_REQUIRED
                    ) {
                        $aff_set = [
                            "is_confirmed" => 1
                        ];
                        $aff->set($aff_set);
                        $aff->save();
                    }
                }
            } else {
                $result = false;
            }
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     *
     * @return null|array
     */
    public function get_active_user(): ?array
    {
        $result = null;
        $whitelabel = $this->get_whitelabel();

        $params = [
            "whitelabel_id" => $whitelabel['id'],
            "token" => $this->get_param_id(),
            "is_deleted" => 0,
            "is_active" => 1
        ];

        if (!empty($whitelabel) && !empty($whitelabel['user_activation_type'])) {
            if ((int)$whitelabel['user_activation_type'] === Helpers_General::ACTIVATION_TYPE_REQUIRED) {
                $params["is_confirmed"] = 1;
            }
        }

        $user = Model_Whitelabel_User::find_by($params);

        if ($user !== null && count($user) > 0) {
            $result = $user;
        }

        return $result;
    }

    /**
     * @param int $token
     * @param array $whitelabel
     * @return null|array
     */
    public static function get_user_with_currencies_by_token(
        $token,
        $whitelabel
    ): ?array {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $res = null;

        $query = "SELECT 
                whitelabel_user.*, 
                user_currency.id AS user_currency_id,
                user_currency.code AS user_currency_code,
                user_currency.rate AS user_currency_rate,
                manager_currency.code AS manager_currency_code
            FROM whitelabel_user 
            INNER JOIN whitelabel ON whitelabel.id = whitelabel_user.whitelabel_id 
            LEFT JOIN currency user_currency ON whitelabel_user.currency_id = user_currency.id 
            LEFT JOIN currency manager_currency ON whitelabel.manager_site_currency_id = manager_currency.id 
            WHERE 1=1 ";

        if (!empty($whitelabel) && !empty($whitelabel["id"])) {
            $query .= " AND whitelabel_user.whitelabel_id = :whitelabel_id ";
        }

        if (!empty($token)) {
            $query .= " AND whitelabel_user.token = :token ";
        }

        $query .= " LIMIT 1";

        try {
            $db = DB::query($query);

            if (!empty($whitelabel) && !empty($whitelabel["id"])) {
                $db->param(":whitelabel_id", $whitelabel["id"]);
            }

            if (!empty($token)) {
                $db->param(":token", $token);
            }

            $res = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        if ($res === null && !empty($res[0])) {
            return $result;
        }

        $result = $res[0];

        return $result;
    }

    /**
     * @param int $id
     * @return null|array
     */
    public static function get_user_with_currencies_by_id($id): ?array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $res = null;

        $query = "SELECT 
                whitelabel_user.*, 
                user_currency.id AS user_currency_id,
                user_currency.code AS user_currency_code,
                user_currency.rate AS user_currency_rate,
                manager_currency.code AS manager_currency_code
            FROM whitelabel_user 
            INNER JOIN whitelabel ON whitelabel.id = whitelabel_user.whitelabel_id 
            LEFT JOIN currency user_currency ON whitelabel_user.currency_id = user_currency.id 
            LEFT JOIN currency manager_currency ON whitelabel.manager_site_currency_id = manager_currency.id 
            WHERE 1=1 ";

        if (!empty($id)) {
            $query .= " AND whitelabel_user.id = :id ";
        }

        $query .= " LIMIT 1";

        try {
            $db = DB::query($query);

            if (!empty($id)) {
                $db->param(":id", $id);
            }

            $res = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        if ($res === null && !empty($res[0])) {
            return $result;
        }

        $result = $res[0];

        return $result;
    }

    /**
     *
     * @param int $token
     * @param int $whitelabel_id
     * @return array|null
     */
    public static function get_existing_user_by_token(int $token, int $whitelabel_id): ?array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $query = 'SELECT * FROM whitelabel_user WHERE is_active = 1 AND '
            . 'is_deleted = 0 AND whitelabel_id = :whitelabel AND token = :token';
        try {
            $db = DB::query($query);

            $db->param(':token', $token);
            $db->param(':whitelabel', $whitelabel_id);

            $user = $db->execute()->as_array();

            if (empty($user)) {
                return null;
            }
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
            return null;
        }

        return $user[0];
    }

    /**
     * @param int $id
     * @param array $whitelabel
     * @return null|array
     */
    public static function get_user_with_currencies_by_id_and_whitelabel(
        $id,
        $whitelabel
    ): ?array {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $res = null;

        $query = "SELECT 
            whitelabel_user.*, 
            user_currency.id AS user_currency_id,
            user_currency.code AS user_currency_code,
            user_currency.rate AS user_currency_rate,
            manager_currency.code AS manager_currency_code
        FROM whitelabel_user 
        INNER JOIN whitelabel ON whitelabel.id = whitelabel_user.whitelabel_id 
        LEFT JOIN currency user_currency ON whitelabel_user.currency_id = user_currency.id 
        LEFT JOIN currency manager_currency ON whitelabel.manager_site_currency_id = manager_currency.id 
        WHERE 1=1 ";

        if (!empty($whitelabel) && !empty($whitelabel["id"])) {
            $query .= " AND whitelabel_user.whitelabel_id = :whitelabel_id ";
        }

        if (!empty($id)) {
            $query .= " AND whitelabel_user.id = :id ";
        }

        $query .= " LIMIT 1";

        try {
            $db = DB::query($query);

            if (!empty($whitelabel) && !empty($whitelabel["id"])) {
                $db->param(":whitelabel_id", $whitelabel["id"]);
            }

            if (!empty($id)) {
                $db->param(":id", $id);
            }

            $res = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        if ($res === null && !empty($res[0])) {
            return $result;
        }

        $result = $res[0];

        return $result;
    }

    /**
     *
     * @param string $email
     * @param string $password
     * @param string $hash
     * @param int $is_deleted Default 0
     * @return null|array
     */
    public static function check_user_credentials(
        $email,
        $password,
        &$hash,
        $is_deleted = 0
    ): ?array {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $res = null;
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        $query = 'SELECT 
                id, 
                token,
                salt, 
                hash, 
                is_active, 
                is_confirmed 
            FROM whitelabel_user 
            WHERE whitelabel_id = :whitelabel
                AND is_deleted = :is_deleted 
                AND email = :email 
            LIMIT 1';

        try {
            $db = DB::query($query);
            $db->param(":whitelabel", $whitelabel['id']);
            $db->param(":is_deleted", $is_deleted);
            $db->param(":email", $email);
            $res = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        if ($res === null || count($res) == 0) {
            return $result;
        }
        $res = $res[0];

        $hash = Lotto_Security::generate_hash($password, $res['salt']);
        if ($hash === $res['hash']) {
            $result = $res;
        }

        return $result;
    }

    /**
     *
     * @param array $token
     * @param array $whitelabel
     * @return null|array
     */
    public static function get_count_for_whitelabel(
        $token,
        $whitelabel
    ): ?array {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $res = null;
        $query = "SELECT 
                COUNT(*) AS count 
            FROM whitelabel_user 
            WHERE whitelabel_id = :whitelabel_id 
            AND token = :token";

        try {
            $db = DB::query($query);

            $db->param(":whitelabel_id", $whitelabel['id']);
            $db->param(":token", $token);
            $res = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        if ($res === null || count($res) == 0) {
            return $result;
        }
        $result = $res[0]['count'];

        return $result;
    }

    /**
     *
     * @param int $id
     * @return null|array
     */
    public static function get_single_by_id($id): ?array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $res = null;
        $query = "SELECT 
            whitelabel_user.*
        FROM whitelabel_user 
        WHERE 1=1 ";

        if (!empty($id)) {
            $query .= " AND id = :id ";
        }

        $query .= "LIMIT 1";

        try {
            $db = DB::query($query);

            if (!empty($id)) {
                $db->param(":id", $id);
            }

            $res = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        if ($res === null || count($res) == 0) {
            return $result;
        }

        $result = $res[0];

        return $result;
    }

    /**
     * Fetch count of users filtered.
     *
     * @param string $add filter adds.
     * @param array $params filter params.
     * @param int $whitelabel_id whitelabel id could be null (for all whitelabels)
     * @param bool $only_confirmed Could filter only confirmed users
     * @return int count of the registered users for specified whitelabel.
     */
    public static function get_data_count_for_reports(
        string $add,
        array $params,
        int $whitelabel_id = null,
        bool $only_confirmed = false
    ): int {
        // add non global params
        if (!empty($whitelabel_id)) {
            $params[] = [":whitelabel_id", $whitelabel_id];
        }

        $query_string = "SELECT 
            COUNT(*) AS count 
        FROM whitelabel_user 
        WHERE whitelabel_id = :whitelabel_id ";

        if ($only_confirmed) {
            $query_string .= " AND is_confirmed = 1 ";
        }

        $query_string .= $add;

        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result_item($result, 0, 0, 'count');
    }

    /**
     * Fetch count of users filtered.
     *
     * @param string $add filter adds.
     * @param array $params filter params.
     * @param int $whitelabel_type whitelabel type could be null
     * @param int $whitelabel_id whitelabel id could be null
     * @param bool $is_full_report For full report noting changed
     * @param bool $only_confirmed Could filter only confirmed users
     * @return int count of the registered users
     */
    public static function get_counted_data_for_admin_reports(
        string $add,
        array $params,
        int $whitelabel_type = null,
        int $whitelabel_id = null,
        bool $is_full_report = false,
        bool $only_confirmed = false
    ): int {
        if (!empty($whitelabel_type)) {
            $params[] = [":whitelabel_type", $whitelabel_type];
        }

        if (!empty($whitelabel_id)) {
            $params[] = [":whitelabel_id", $whitelabel_id];
        }

        $query_string = "SELECT 
            COUNT(*) AS count 
        FROM whitelabel_user 
        INNER JOIN whitelabel ON whitelabel_user.whitelabel_id = whitelabel.id ";

        if (!empty($whitelabel_type)) {
            $query_string .= " AND whitelabel.type = :whitelabel_type ";
        }

        $query_string .= "WHERE 1=1 ";

        if (!empty($whitelabel_id)) {
            $query_string .= " AND whitelabel_user.whitelabel_id = :whitelabel_id ";
        }

        if (!$is_full_report && is_null($whitelabel_id)) {
            $query_string .= " AND whitelabel.is_report = 1 ";
        }

        if ($only_confirmed) {
            $query_string .= " AND whitelabel_user.is_confirmed = 1 ";
        }

        $query_string .= $add;

        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result_item($result, 0, 0, 'count');
    }

    /**
     * One of possible values for whitelabel_user.gender_field
     */
    const GENDER_UNSET = 0;
    /**
     * One of possible values for whitelabel_user.gender_field
     */
    const GENDER_MALE = 1;
    /**
     * One of possible values for whitelabel_user.gender_field
     */
    const GENDER_FEMALE = 2;

    /**
     * Get possible values for for whitelabel_user.gender_field.
     *
     * @return int[]
     */
    public static function get_gender_keys(): array
    {
        return [
            self::GENDER_UNSET,
            self::GENDER_MALE,
            self::GENDER_FEMALE,
        ];
    }

    /**
     * Get genders mappings for @see get_gender_keys.
     * NOTE: genders are in english (untranslated).
     *
     * @return string[]
     */
    public static function get_genders(): array
    {
        return [
            self::GENDER_UNSET => '',
            self::GENDER_MALE => 'male',
            self::GENDER_FEMALE => 'female',
        ];
    }

    /**
     * Update user balance, add prize value to it.
     *
     * @param array $ids ids for users who should have balance updated.
     * @param string $prize amount over which balance has changed.
     * @return integer how many users were updated
     * @throws \Exception on database error.
     */
    public static function update_balance(array $ids, string $prize): int
    {
        return DB::update(self::$_table_name)
            ->set([
                'balance' => DB::expr("`balance` + $prize")
            ])
            ->where('id', 'IN', $ids)
            ->execute();
    }

    /**
     * This function creates relationship between whitelabel_user and whitelabel_transaction
     * in order to take transactions count per user
     * Filter is here to avoid taking useless data from db and it checks only one (purchaseCountForDate)
     * absolute path because "use Database_Query" might cause problems in other functions
     * @throws \Exception on database error.
     */
    public static function transactionCount(\Fuel\Core\Database_Query_Builder_Select $query, array $filters): object
    {
        $joinType = 'left';
        $purchaseCountSubquery = DB::select(
            'whitelabel_user_id',
            [DB::expr('count(whitelabel_user_id)'), 'purchaseCountForDate']
        )->from('whitelabel_transaction');

        $purchaseFilterKey = array_search('purchaseCountForDate', array_column($filters, 'column'));
        $isPurchaseKeySet = is_int($purchaseFilterKey);

        if ($isPurchaseKeySet) {
            $start = Helpers_Crm_General::prepare_start_date($filters[$purchaseFilterKey]['startDate']);
            $end = Helpers_Crm_General::prepare_end_date($filters[$purchaseFilterKey]['endDate']);
            $purchaseCountSubquery->where('date', 'BETWEEN', [$start, $end]);
            $joinType = 'inner';
        }

        $purchaseCountSubquery->group_by('whitelabel_user_id');

        $query->select('purchaseCountForDate')
            ->join([$purchaseCountSubquery, 'whitelabel_transaction'], $joinType)
            ->on('whitelabel_transaction.whitelabel_user_id', '=', 'whitelabel_user.id');

        return $query;
    }

    /**
     *
     * @access public
     * @param int $whitelabel_id
     * @param string $activity
     * @param array $filters
     * @param int $page
     * @param int $items_per_page
     * @param string $sort_by
     * @param string $order
     * @param int $group_id
     * @return array
     *
     */
    public static function get_data_for_crm($whitelabel_id, $activity, $filters, $page, $items_per_page, $sort_by, $order, $group_id = null)
    {
        $group_subquery = DB::select('whitelabel_user_id')->from('whitelabel_user_whitelabel_user_group')
            ->where('whitelabel_user_group_id', '=', $group_id)
            ->group_by('whitelabel_user_id');

        $query = DB::select(
            'whitelabel_user.address_1',
            'whitelabel_user.whitelabel_id',
            'whitelabel_user.address_2',
            'whitelabel_user.name',
            'whitelabel_user.surname',
            'whitelabel_user.country',
            'whitelabel_user.city',
            'whitelabel_user.state',
            'whitelabel_user.birthdate',
            'whitelabel_user.phone',
            'whitelabel_user.prize_payout_whitelabel_user_group_id',
            'whitelabel_user.date_register',
            'whitelabel_user.currency_id',
            'whitelabel_user.language_id',
            'whitelabel_user.gender',
            'whitelabel_user.national_id',
            'whitelabel_user.first_purchase',
            'whitelabel_user.balance',
            'whitelabel_user.bonus_balance',
            'whitelabel_user.casino_balance',
            'whitelabel_user.token',
            'whitelabel_user.email',
            'whitelabel_user.login',
            'whitelabel_user.phone',
            'whitelabel_user.timezone',
            'whitelabel_user.city',
            'whitelabel_user.state',
            'whitelabel_user.zip',
            'whitelabel_user.connected_aff_id',
            'whitelabel_user.register_ip',
            'whitelabel_user.register_country',
            'whitelabel_user.last_ip',
            'whitelabel_user.first_deposit_amount_manager',
            'whitelabel_user.last_deposit_amount_manager',
            'whitelabel_user.second_deposit',
            'whitelabel_user.last_active',
            'whitelabel_user.last_deposit_date',
            'whitelabel_user.last_country',
            'whitelabel_user.first_deposit',
            'whitelabel_user.first_purchase',
            'whitelabel_user.second_purchase',
            'whitelabel_user.last_purchase_date',
            'whitelabel_user.total_deposit_manager',
            'whitelabel_user.total_withdrawal_manager',
            'whitelabel_user.total_purchases_manager',
            'whitelabel_user.total_net_income_manager',
            'whitelabel_user.last_purchase_amount_manager',
            'whitelabel_user.sale_status',
            'whitelabel_user.pnl_manager',
            'whitelabel_user.system_type',
            'whitelabel_user.browser_type',
            'whitelabel_user.net_winnings_manager',
            'whitelabel_user.last_update',
            'whitelabel_user.prize_payout_whitelabel_user_group_id',
            'whitelabel_user.date_register',
            'whitelabel_user.is_confirmed',
            ['language.code', 'language_code'],
            ['language.id', 'language_id'],
            ['whitelabel.name', 'whitelabel_name'],
            ['whitelabel.prefix', 'whitelabel_prefix'],
            ['c1.code', 'whitelabel_currency_code'],
            ['c1.rate', 'whitelabel_currency_rate'],
            ['c2.id', 'user_currency_id'],
            ['c2.code', 'user_currency_code'],
            ['c2.rate', 'user_currency_rate'],
            ['whitelabel_aff.id', 'aff_id'],
            ['whitelabel_aff.login', 'aff_login'],
            ['whitelabel_aff.name', 'aff_name'],
            ['whitelabel_aff.surname', 'aff_surname'],
            ['whitelabel_aff.email', 'aff_email'],
            ['whitelabel_user_group.name', 'group'],
            [DB::expr('DATEDIFF(NOW(), whitelabel_user.date_register)'), 'player_lifetime']
        )->from('whitelabel_user')
            ->join('whitelabel')->on('whitelabel_user.whitelabel_id', '=', 'whitelabel.id')
            ->join('whitelabel_user_aff', 'LEFT')->on('whitelabel_user.id', '=', 'whitelabel_user_aff.whitelabel_user_id')
            ->join('whitelabel_aff', 'LEFT')->on('whitelabel_aff.id', "=", 'whitelabel_user_aff.whitelabel_aff_id')
            ->join(['currency', 'c1'], 'LEFT')->on('whitelabel.manager_site_currency_id', '=', 'c1.id')
            ->join(['currency', 'c2'], 'LEFT')->on('whitelabel_user.currency_id', '=', 'c2.id')
            ->join('language', 'LEFT')->on('whitelabel_user.language_id', '=', 'language.id')
            ->join('whitelabel_user_group', 'LEFT')->on('whitelabel_user.prize_payout_whitelabel_user_group_id', '=', 'whitelabel_user_group.id');

        if ($group_id) {
            $query->join([$group_subquery, 's'])->on('s.whitelabel_user_id', '=', 'whitelabel_user.id');
        }

        $query = self::transactionCount($query, $filters);

        if ($activity == 'active') {
            $query->where_open();
            $query->where('whitelabel_user.is_active', 1)
                ->and_where(function ($query) {
                    $query->where('whitelabel.user_activation_type', '<>', 2)
                        ->or_where(function ($query) {
                            $query->where('whitelabel.user_activation_type', 2)
                                ->and_where('whitelabel_user.is_confirmed', 1);
                        });
                })
                ->and_where('whitelabel_user.is_deleted', 0);
            $query->where_close();
        } elseif ($activity == 'inactive') {
            $query->where_open();
            $query->where('whitelabel_user.is_active', 0)
                ->or_where(function ($query) {
                    $query->where('whitelabel.user_activation_type', '=', 2)
                        ->and_where('whitelabel_user.is_confirmed', 0);
                })
                ->and_where('whitelabel_user.is_deleted', 0);
            $query->where_close();
        } elseif ($activity == 'deleted') {
            $query->where('whitelabel_user.is_deleted', '=', 1);
        }

        if ($whitelabel_id !== 0) {
            $query->and_where('whitelabel_user.whitelabel_id', '=', $whitelabel_id);
        }

        $query = self::prepare_filters($filters, $query);

        $query->order_by($sort_by, $order);

        if ($items_per_page) {
            $query->limit($items_per_page)->offset($items_per_page * ($page - 1));
        }

        $res = $query->execute()->as_array();

        return $res;
    }

    /**
     *
     * @access public
     * @param int $whitelabel_id
     * @param string $activity
     * @param array $filters
     * @param array $columns
     * @param int $group_id
     * @return array
     *
     */
    public static function get_export_data_for_crm($whitelabel_id, $activity, $filters, $columns, $group_id = null)
    {
        $group_subquery = DB::select('whitelabel_user_id')->from('whitelabel_user_whitelabel_user_group')
            ->where('whitelabel_user_group_id', '=', $group_id)
            ->group_by('whitelabel_user_id');

        $query = DB::select_array(
            $columns
        )->from('whitelabel_user')
            ->join('whitelabel')->on('whitelabel_user.whitelabel_id', '=', 'whitelabel.id')
            ->join('whitelabel_user_aff', 'LEFT')->on('whitelabel_user.id', '=', 'whitelabel_user_aff.whitelabel_user_id')
            ->join('whitelabel_aff', 'LEFT')->on('whitelabel_aff.id', "=", 'whitelabel_user_aff.whitelabel_aff_id')
            ->join('currency', 'LEFT')->on('whitelabel.manager_site_currency_id', '=', 'currency.id')
            ->join('language', 'LEFT')->on('whitelabel_user.language_id', '=', 'language.id')
            ->join('whitelabel_user_group', 'LEFT')->on('whitelabel_user.prize_payout_whitelabel_user_group_id', '=', 'whitelabel_user_group.id');

        if ($group_id) {
            $query->join([$group_subquery, 's'])->on('s.whitelabel_user_id', '=', 'whitelabel_user.id');
        }

        $query = self::transactionCount($query, $filters);

        if ($activity == 'active') {
            $query->where_open();
            $query->where('whitelabel_user.is_active', 1)
                ->and_where(function ($query) {
                    $query->where('whitelabel.user_activation_type', '<>', 2)
                        ->or_where(function ($query) {
                            $query->where('whitelabel.user_activation_type', 2)
                                ->and_where('whitelabel_user.is_confirmed', 1);
                        });
                })
                ->and_where('whitelabel_user.is_deleted', 0);
            $query->where_close();
        } elseif ($activity == 'inactive') {
            $query->where_open();
            $query->where('whitelabel_user.is_active', 0)
                ->or_where(function ($query) {
                    $query->where('whitelabel.user_activation_type', '=', 2)
                        ->and_where('whitelabel_user.is_confirmed', 0);
                })
                ->and_where('whitelabel_user.is_deleted', 0);
            $query->where_close();
        } elseif ($activity == 'deleted') {
            $query->where('whitelabel_user.is_deleted', '=', 1);
        }

        if ($whitelabel_id !== 0) {
            $query->and_where('whitelabel_user.whitelabel_id', '=', $whitelabel_id);
        }

        $query = self::prepare_filters($filters, $query);

        $res = $query->execute()->as_array();

        return $res;
    }
    /**
     *
     * @access public
     * @param int $whitelabel_id
     * @param string $activity
     * @param array $filters
     * @param int $group_id
     * @return array
     *
     */
    public static function get_counts_for_crm($whitelabel_id, $activity, $filters, $group_id = null)
    {
        $group_subquery = DB::select('whitelabel_user_id')->from('whitelabel_user_whitelabel_user_group')
            ->where('whitelabel_user_group_id', '=', $group_id)
            ->group_by('whitelabel_user_id');

        $query = DB::select('whitelabel_user.id')
            ->from('whitelabel_user')
            ->join('whitelabel')->on('whitelabel_user.whitelabel_id', '=', 'whitelabel.id')
            ->join('whitelabel_user_aff', 'LEFT')->on('whitelabel_user.id', '=', 'whitelabel_user_aff.whitelabel_user_id')
            ->join('whitelabel_aff', 'LEFT')->on('whitelabel_aff.id', "=", 'whitelabel_user_aff.whitelabel_aff_id')
            ->join('whitelabel_user_group', 'LEFT')->on('whitelabel_user.prize_payout_whitelabel_user_group_id', '=', 'whitelabel_user_group.id');

        if ($group_id) {
            $query->join([$group_subquery, 's'])->on('s.whitelabel_user_id', '=', 'whitelabel_user.id');
        }

        if ($activity == 'active') {
            $query->where_open();
            $query->where('whitelabel_user.is_active', 1)
                ->and_where(function ($query) {
                    $query->where('whitelabel.user_activation_type', '<>', 2)
                        ->or_where(function ($query) {
                            $query->where('whitelabel.user_activation_type', 2)
                                ->and_where('whitelabel_user.is_confirmed', 1);
                        });
                })
                ->and_where('whitelabel_user.is_deleted', 0);
            $query->where_close();
        } elseif ($activity == 'inactive') {
            $query->where_open();
            $query->where('whitelabel_user.is_active', 0)
                ->or_where(function ($query) {
                    $query->where('whitelabel.user_activation_type', '=', 2)
                        ->and_where('whitelabel_user.is_confirmed', 0);
                })
                ->and_where('whitelabel_user.is_deleted', 0);
            $query->where_close();
        } elseif ($activity == 'deleted') {
            $query->where('whitelabel_user.is_deleted', '=', 1);
        }

        if ($whitelabel_id !== 0) {
            $query->and_where('whitelabel_user.whitelabel_id', '=', $whitelabel_id);
        }

        $query = self::prepare_filters($filters, $query);

        $res = $query->execute();
        $count = 0;
        if (count($res) > 0) {
            $count = DB::count_last_query();
        }
        return $count;
    }

    /**
     * @access private
     * @param array $filters
     * @param Database_Query_Builder_Select $query
     * @return Database_Query_Builder_Select
     */
    private static function prepare_filters($filters, $query)
    {
        foreach ($filters as $filter) {
            if ($filter['column'] == 'token') {
                $value = '%' . $filter['value'] . '%';
                $query->and_where('whitelabel_user.token', 'LIKE', $value);
            }
            if ($filter['column'] == 'name') {
                $value = '%' . $filter['value'] . '%';
                $query->and_where('whitelabel_user.name', 'LIKE', $value);
            }
            if ($filter['column'] == 'surname') {
                $value = '%' . $filter['value'] . '%';
                $query->and_where('whitelabel_user.surname', 'LIKE', $value);
            }
            if ($filter['column'] == 'gender') {
                $query->and_where('whitelabel_user.gender', '=', intval($filter['value']));
            }
            if ($filter['column'] == 'email') {
                $value = '%' . $filter['value'] . '%';
                $query->and_where('whitelabel_user.email', 'LIKE', $value);
            }
            if ($filter['column'] == 'login') {
                $value = '%' . $filter['value'] . '%';
                $query->and_where('whitelabel_user.login', 'LIKE', $value);
            }
            if ($filter['column'] == 'phone') {
                $value = '%' . $filter['value'] . '%';
                $query->and_where('whitelabel_user.phone', 'LIKE', $value);
            }
            if ($filter['column'] == 'language_id') {
                $query->and_where('whitelabel_user.language_id', '=', intval($filter['value']));
            }
            if ($filter['column'] == 'timezone') {
                $query->and_where('whitelabel_user.timezone', '=', $filter['value']);
            }
            if ($filter['column'] == 'country') {
                $query->and_where('whitelabel_user.country', '=', $filter['value']);
            }
            if ($filter['column'] == 'state') {
                $query->and_where('whitelabel_user.state', '=', $filter['value']);
            }
            if ($filter['column'] == 'city') {
                $value = '%' . $filter['value'] . '%';
                $query->and_where('whitelabel_user.city', 'LIKE', $value);
            }
            if ($filter['column'] == 'address_1') {
                $value = '%' . $filter['value'] . '%';
                $query->and_where('whitelabel_user.address_1', 'LIKE', $value);
            }
            if ($filter['column'] == 'address_2') {
                $value = '%' . $filter['value'] . '%';
                $query->and_where('whitelabel_user.address_2', 'LIKE', $value);
            }
            if ($filter['column'] == 'zip') {
                $value = '%' . $filter['value'] . '%';
                $query->and_where('whitelabel_user.zip', 'LIKE', $value);
            }
            if ($filter['column'] == 'national_id') {
                $value = '%' . $filter['value'] . '%';
                $query->and_where('whitelabel_user.national_id', 'LIKE', $value);
            }
            if ($filter['column'] == 'currency_id') {
                $query->and_where('whitelabel_user.currency_id', '=', intval($filter['value']));
            }
            if ($filter['column'] == 'whitelabel_aff_id') {
                $query->and_where('whitelabel_user_aff.whitelabel_aff_id', '=', intval($filter['value']));
            }
            if ($filter['column'] == 'balance') {
                if ($filter['start'] > 0 && $filter['end'] == 0) {
                    $query->and_where('whitelabel_user.balance', '>=', intval($filter['start']));
                } elseif ($filter['start'] > 0 && $filter['end'] > 0) {
                    $query->and_where('whitelabel_user.balance', '>=', intval($filter['start']))
                        ->and_where('whitelabel_user.balance', '<=', intval($filter['end']));
                } elseif ($filter['start'] == 0 && $filter['end'] > 0) {
                    $query->and_where('whitelabel_user.balance', '<=', intval($filter['end']));
                }
            }
            if ($filter['column'] == 'bonus_balance') {
                if ($filter['start'] > 0 && $filter['end'] == 0) {
                    $query->and_where('whitelabel_user.bonus_balance', '>=', intval($filter['start']));
                } elseif ($filter['start'] > 0 && $filter['end'] > 0) {
                    $query->and_where('whitelabel_user.bonus_balance', '>=', intval($filter['start']))
                        ->and_where('whitelabel_user.bonus_balance', '<=', intval($filter['end']));
                } elseif ($filter['start'] == 0 && $filter['end'] > 0) {
                    $query->and_where('whitelabel_user.bonus_balance', '<=', intval($filter['end']));
                }
            }
            if ($filter['column'] == 'register_ip') {
                $value = '%' . $filter['value'] . '%';
                $query->and_where('whitelabel_user.register_ip', 'LIKE', $value);
            }
            if ($filter['column'] == 'register_country') {
                $query->and_where('whitelabel_user.register_country', '=', $filter['value']);
            }
            if ($filter['column'] == 'last_country') {
                $query->and_where('whitelabel_user.last_country', '=', $filter['value']);
            }
            if ($filter['column'] == 'last_ip') {
                $value = '%' . $filter['value'] . '%';
                $query->and_where('whitelabel_user.last_ip', 'LIKE', $value);
            }
            if ($filter['column'] == 'first_deposit_amount_manager') {
                if ($filter['start'] > 0 && $filter['end'] == 0) {
                    $query->and_where('whitelabel_user.first_deposit_amount_manager', '>=', intval($filter['start']));
                } elseif ($filter['start'] > 0 && $filter['end'] > 0) {
                    $query->and_where('whitelabel_user.first_deposit_amount_manager', '>=', intval($filter['start']))
                        ->and_where('whitelabel_user.first_deposit_amount_manager', '<=', intval($filter['end']));
                } elseif ($filter['start'] == 0 && $filter['end'] > 0) {
                    $query->and_where('whitelabel_user.first_deposit_amount_manager', '<=', intval($filter['end']));
                }
            }
            if ($filter['column'] == 'last_deposit_amount_manager') {
                if ($filter['start'] > 0 && $filter['end'] == 0) {
                    $query->and_where('whitelabel_user.last_deposit_amount_manager', '>=', intval($filter['start']));
                } elseif ($filter['start'] > 0 && $filter['end'] > 0) {
                    $query->and_where('whitelabel_user.last_deposit_amount_manager', '>=', intval($filter['start']))
                        ->and_where('whitelabel_user.last_deposit_amount_manager', '<=', intval($filter['end']));
                } elseif ($filter['start'] == 0 && $filter['end'] > 0) {
                    $query->and_where('whitelabel_user.last_deposit_amount_manager', '<=', intval($filter['end']));
                }
            }
            if ($filter['column'] == 'total_deposit_manager') {
                if ($filter['start'] > 0 && $filter['end'] == 0) {
                    $query->and_where('whitelabel_user.total_deposit_manager', '>=', intval($filter['start']));
                } elseif ($filter['start'] > 0 && $filter['end'] > 0) {
                    $query->and_where('whitelabel_user.total_deposit_manager', '>=', intval($filter['start']))
                        ->and_where('whitelabel_user.total_deposit_manager', '<=', intval($filter['end']));
                } elseif ($filter['start'] == 0 && $filter['end'] > 0) {
                    $query->and_where('whitelabel_user.total_deposit_manager', '<=', intval($filter['end']));
                }
            }
            if ($filter['column'] == 'total_withdrawal_manager') {
                if ($filter['start'] > 0 && $filter['end'] == 0) {
                    $query->and_where('whitelabel_user.total_withdrawal_manager', '>=', intval($filter['start']));
                } elseif ($filter['start'] > 0 && $filter['end'] > 0) {
                    $query->and_where('whitelabel_user.total_withdrawal_manager', '>=', intval($filter['start']))
                        ->and_where('whitelabel_user.total_withdrawal_manager', '<=', intval($filter['end']));
                } elseif ($filter['start'] == 0 && $filter['end'] > 0) {
                    $query->and_where('whitelabel_user.total_withdrawal_manager', '<=', intval($filter['end']));
                }
            }
            if ($filter['column'] == 'total_purchases_manager') {
                if ($filter['start'] > 0 && $filter['end'] == 0) {
                    $query->and_where('whitelabel_user.total_purchases_manager', '>=', intval($filter['start']));
                } elseif ($filter['start'] > 0 && $filter['end'] > 0) {
                    $query->and_where('whitelabel_user.total_purchases_manager', '>=', intval($filter['start']))
                        ->and_where('whitelabel_user.total_purchases_manager', '<=', intval($filter['end']));
                } elseif ($filter['start'] == 0 && $filter['end'] > 0) {
                    $query->and_where('whitelabel_user.total_purchases_manager', '<=', intval($filter['end']));
                }
            }
            if ($filter['column'] == 'last_purchase_amount_manager') {
                if ($filter['start'] > 0 && $filter['end'] == 0) {
                    $query->and_where('whitelabel_user.last_purchase_amount_manager', '>=', intval($filter['start']));
                } elseif ($filter['start'] > 0 && $filter['end'] > 0) {
                    $query->and_where('whitelabel_user.last_purchase_amount_manager', '>=', intval($filter['start']))
                        ->and_where('whitelabel_user.last_purchase_amount_manager', '<=', intval($filter['end']));
                } elseif ($filter['start'] == 0 && $filter['end'] > 0) {
                    $query->and_where('whitelabel_user.last_purchase_amount_manager', '<=', intval($filter['end']));
                }
            }
            if ($filter['column'] == 'total_net_income_manager') {
                if ($filter['start'] > 0 && $filter['end'] == 0) {
                    $query->and_where('whitelabel_user.total_net_income_manager', '>=', intval($filter['start']));
                } elseif ($filter['start'] > 0 && $filter['end'] > 0) {
                    $query->and_where('whitelabel_user.total_net_income_manager', '>=', intval($filter['start']))
                        ->and_where('whitelabel_user.total_net_income_manager', '<=', intval($filter['end']));
                } elseif ($filter['start'] == 0 && $filter['end'] > 0) {
                    $query->and_where('whitelabel_user.total_net_income_manager', '<=', intval($filter['end']));
                }
            }
            if ($filter['column'] == 'net_winnings_manager') {
                if ($filter['start'] > 0 && $filter['end'] == 0) {
                    $query->and_where('whitelabel_user.net_winnings_manager', '>=', intval($filter['start']));
                } elseif ($filter['start'] > 0 && $filter['end'] > 0) {
                    $query->and_where('whitelabel_user.net_winnings_manager', '>=', intval($filter['start']))
                        ->and_where('whitelabel_user.net_winnings_manager', '<=', intval($filter['end']));
                } elseif ($filter['start'] == 0 && $filter['end'] > 0) {
                    $query->and_where('whitelabel_user.net_winnings_manager', '<=', intval($filter['end']));
                }
            }
            if ($filter['column'] == 'sale_status') {
                $query->and_where('whitelabel_user.sale_status', '=', $filter['value']);
            }
            if ($filter['column'] == 'pnl_manager') {
                if ($filter['start'] > 0 && $filter['end'] == 0) {
                    $query->and_where('whitelabel_user.pnl_manager', '>=', intval($filter['start']));
                } elseif ($filter['start'] > 0 && $filter['end'] > 0) {
                    $query->and_where('whitelabel_user.pnl_manager', '>=', intval($filter['start']))
                        ->and_where('whitelabel_user.pnl_manager', '<=', intval($filter['end']));
                } elseif ($filter['start'] == 0 && $filter['end'] > 0) {
                    $query->and_where('whitelabel_user.pnl_manager', '<=', intval($filter['end']));
                }
            }
            if ($filter['column'] == 'system_type') {
                $value = '%' . $filter['value'] . '%';
                $query->and_where('whitelabel_user.system_type', 'LIKE', $value);
            }
            if ($filter['column'] == 'browser_type') {
                $value = '%' . $filter['value'] . '%';
                $query->and_where('whitelabel_user.browser_type', 'LIKE', $value);
            }
            if ($filter['column'] == 'birthdate') {
                $start = Helpers_Crm_General::prepare_start_date($filter['startDate']);
                $end = Helpers_Crm_General::prepare_end_date($filter['endDate']);
                $query->and_where('whitelabel_user.birthdate', '>=', $start);
                $query->and_where('whitelabel_user.birthdate', '<=', $end);
            }
            if ($filter['column'] == 'date_register') {
                $start = Helpers_Crm_General::prepare_start_date($filter['startDate']);
                $end = Helpers_Crm_General::prepare_end_date($filter['endDate']);
                $query->and_where('whitelabel_user.date_register', '>=', $start);
                $query->and_where('whitelabel_user.date_register', '<=', $end);
            }
            if ($filter['column'] == 'first_deposit') {
                $start = Helpers_Crm_General::prepare_start_date($filter['startDate']);
                $end = Helpers_Crm_General::prepare_end_date($filter['endDate']);
                $query->and_where('whitelabel_user.first_deposit', '>=', $start);
                $query->and_where('whitelabel_user.first_deposit', '<=', $end);
            }
            if ($filter['column'] == 'second_deposit') {
                $start = Helpers_Crm_General::prepare_start_date($filter['startDate']);
                $end = Helpers_Crm_General::prepare_end_date($filter['endDate']);
                $query->and_where('whitelabel_user.second_deposit', '>=', $start);
                $query->and_where('whitelabel_user.second_deposit', '<=', $end);
            }
            if ($filter['column'] == 'last_deposit_date') {
                $start = Helpers_Crm_General::prepare_start_date($filter['startDate']);
                $end = Helpers_Crm_General::prepare_end_date($filter['endDate']);
                $query->and_where('whitelabel_user.last_deposit_date', '>=', $start);
                $query->and_where('whitelabel_user.last_deposit_date', '<=', $end);
            }
            if ($filter['column'] == 'last_active') {
                $start = Helpers_Crm_General::prepare_start_date($filter['startDate']);
                $end = Helpers_Crm_General::prepare_end_date($filter['endDate']);
                $query->and_where('whitelabel_user.last_active', '>=', $start);
                $query->and_where('whitelabel_user.last_active', '<=', $end);
            }
            if ($filter['column'] == 'first_purchase') {
                $start = Helpers_Crm_General::prepare_start_date($filter['startDate']);
                $end = Helpers_Crm_General::prepare_end_date($filter['endDate']);
                $query->and_where('whitelabel_user.first_purchase', '>=', $start);
                $query->and_where('whitelabel_user.first_purchase', '<=', $end);
            }
            if ($filter['column'] == 'second_purchase') {
                $start = Helpers_Crm_General::prepare_start_date($filter['startDate']);
                $end = Helpers_Crm_General::prepare_end_date($filter['endDate']);
                $query->and_where('whitelabel_user.second_purchase', '>=', $start);
                $query->and_where('whitelabel_user.second_purchase', '<=', $end);
            }
            if ($filter['column'] == 'last_purchase_date') {
                $start = Helpers_Crm_General::prepare_start_date($filter['startDate']);
                $end = Helpers_Crm_General::prepare_end_date($filter['endDate']);
                $query->and_where('whitelabel_user.last_purchase_date', '>=', $start);
                $query->and_where('whitelabel_user.last_purchase_date', '<=', $end);
            }
            if ($filter['column'] == 'last_update') {
                $start = Helpers_Crm_General::prepare_start_date($filter['startDate']);
                $end = Helpers_Crm_General::prepare_end_date($filter['endDate']);
                $query->and_where('whitelabel_user.last_update', '>=', $start);
                $query->and_where('whitelabel_user.last_update', '<=', $end);
            }
            if ($filter['column'] == 'group') {
                $query->and_where('whitelabel_user.prize_payout_whitelabel_user_group_id', '=', intval($filter['value']));
            }
        }

        return $query;
    }

    /**
     *
     * @access public
     * @param string $start_date
     * @param string $end_date
     * @param int $whitelabel_id
     * @return array
     */
    public static function get_registered_count_for_crm($start_date, $end_date, $whitelabel_id)
    {
        $res = [];

        $start = Helpers_Crm_General::prepare_start_date($start_date);
        $end = Helpers_Crm_General::prepare_end_date($end_date);

        $query = DB::select('date_register')
            ->from('whitelabel_user')
            ->and_where('date_register', '>=', $start)
            ->and_where('date_register', '<=', $end);

        if ($whitelabel_id) {
            $query->and_where('whitelabel_id', '=', $whitelabel_id);
        }

        $res = $query->execute()->as_array();

        return $res;
    }

    /**
     *
     * @access public
     * @param string $start_date
     * @param string $end_date
     * @param int $whitelabel_id
     * @return array
     */
    public static function get_first_deposit_count_for_crm($start_date, $end_date, $whitelabel_id)
    {
        $res = [];

        $start = Helpers_Crm_General::prepare_start_date($start_date);
        $end = Helpers_Crm_General::prepare_end_date($end_date);

        $query = DB::select('first_deposit')
            ->from('whitelabel_user')
            ->and_where('first_deposit', '>=', $start)
            ->and_where('first_deposit', '<=', $end);

        if ($whitelabel_id) {
            $query->and_where('whitelabel_id', '=', $whitelabel_id);
        }

        $res = $query->execute()->as_array();

        return $res;
    }

    /**
     *
     * @access public
     * @param int $id
     * @return array
     */
    public static function get_user_affiliate_for_crm($id)
    {
        $res = null;
        $query = DB::select(
            'whitelabel_user.whitelabel_id',
            ['whitelabel_aff.id', 'affiliate']
        )->from('whitelabel_user')
            ->join('whitelabel_user_aff', 'LEFT')->on('whitelabel_user.id', '=', 'whitelabel_user_aff.whitelabel_user_id')
            ->join('whitelabel_aff', 'LEFT')->on('whitelabel_aff.id', '=', 'whitelabel_user_aff.whitelabel_aff_id');

        if (!empty($id)) {
            $query->where('whitelabel_user.id', '=', $id);
        }

        $result = $query->execute()->as_array();

        if (!empty($result[0])) {
            $res = $result[0];
        }

        return $res;
    }

    /**
     * @param int $id
     * @param string $email
     * @return bool
     */
    public static function update_email_by_crm($id, $email): bool
    {
        $result = true;
        $user = Model_Whitelabel_User::find_by_pk($id);

        if ($user !== null) {
            $user->set([
                "email" => $email,
                'last_update' => DB::expr("NOW()")
            ]);
            $user->save();
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * @param int $id
     * @param string $password
     * @return bool
     */
    public static function update_password_by_crm($id, $password): bool
    {
        $result = true;
        $user = Model_Whitelabel_User::find_by_pk($id);

        if ($user !== null) {
            $salt = Lotto_Security::generate_salt();
            $hash = Lotto_Security::generate_hash($password, $salt);

            $user->set([
                "salt" => $salt,
                "hash" => $hash,
                'last_update' => DB::expr("NOW()")
            ]);
            $user->save();
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * @param int $id
     * @param array $updated_user
     * @return bool
     */
    public static function update_user_by_crm($id, $updated_user)
    {
        if (count($updated_user) > 0) {
            $res = 0;

            $query = DB::update('whitelabel_user')->set($updated_user)->where('id', '=', $id);

            $res = $query->execute();

            if ((int)$res === 0) {
                return false;
            }
        }

        return true;
    }

    /**
     *
     * @param int $admin_id
     * @return bool
     */
    public static function default_admin_user_visible_columns_crm($admin_id)
    {
        $slug = "full_token,country_name,balance,first_deposit,first_purchase,total_deposit_manager,total_purchases_manager";

        DB::insert('admin_user_table_column')
            ->columns([
                'admin_id',
                'slug'
            ])
            ->values([
                $admin_id,
                $slug
            ])
            ->execute();

        return true;
    }

    /**
     *
     * @param int $admin_id
     * @param int $slug
     * @return bool
     */
    public static function save_admin_user_visible_columns_crm($admin_id, $slug)
    {
        DB::insert('admin_user_table_column')
            ->columns([
                'admin_id',
                'slug'
            ])
            ->values([
                $admin_id,
                $slug
            ])
            ->execute();

        return true;
    }

    public static function get_admin_user_visible_columns_crm(int $admin_id): ?string
    {
        $query = DB::select('slug')->from('admin_user_table_column')->where('admin_id', $admin_id)
            ->execute();

        if (empty($query[0])) {
            return null;
        }

        return $query[0]['slug'];
    }

    /**
     *
     * @param int $admin_id
     * @return bool
     */
    public static function delete_admin_user_visible_columns_crm($admin_id)
    {
        DB::delete('admin_user_table_column')
            ->where('admin_id', '=', $admin_id)
            ->execute();

        return true;
    }

    /**
     *
     * @param int $admin_id
     * @param string $slug
     * @return bool
     */
    public static function update_admin_user_visible_columns_crm($admin_id, $slug)
    {
        DB::update('admin_user_table_column')
            ->set([
                'slug' => $slug
            ])
            ->where('admin_id', '=', $admin_id)
            ->execute();

        return true;
    }

    /**
     *
     * @access public
     * @param int $whitelabel_id
     * @return array
     */
    public static function get_registered_active_users_count_for_crm_last_seven_days($whitelabel_id): array
    {
        $res = [];

        $query = DB::select(DB::expr('COUNT(*) AS count'), DB::expr('DATE(date_register) AS date'))->from('whitelabel_user')
            ->join('whitelabel')->on('whitelabel_user.whitelabel_id', '=', 'whitelabel.id')
            ->where('whitelabel_user.is_active', 1)
            ->and_where(function ($query) {
                $query->where('whitelabel.user_activation_type', '<>', 2)
                    ->or_where(function ($query) {
                        $query->where('whitelabel.user_activation_type', 2)
                            ->and_where('whitelabel_user.is_confirmed', 1);
                    });
            })
            ->and_where('whitelabel_user.is_deleted', 0)
            ->and_where('whitelabel_user.date_register', '>=', DB::expr('DATE(NOW()) - INTERVAL 6 DAY'));

        if ($whitelabel_id) {
            $query->and_where('whitelabel_user.whitelabel_id', '=', $whitelabel_id);
        }

        $query->group_by(DB::expr('DATE(date_register)'));
        $res = $query->execute()->as_array();

        return $res;
    }

    /**
     *
     * @access public
     * @param int $whitelabel_id
     * @param string $start_date
     * @param string $end_date
     * @return int
     */
    public static function get_registered_active_users_count_for_crm($whitelabel_id, $start_date, $end_date): int
    {
        $res = 0;

        $start = Helpers_Crm_General::prepare_start_date($start_date);
        $end = Helpers_Crm_General::prepare_end_date($end_date);

        $query = DB::select(DB::expr('COUNT(*) AS count'))->from('whitelabel_user')
            ->join('whitelabel')->on('whitelabel_user.whitelabel_id', '=', 'whitelabel.id')
            ->where('whitelabel_user.is_active', 1)
            ->and_where(function ($query) {
                $query->where('whitelabel.user_activation_type', '<>', 2)
                    ->or_where(function ($query) {
                        $query->where('whitelabel.user_activation_type', 2)
                            ->and_where('whitelabel_user.is_confirmed', 1);
                    });
            })
            ->and_where('whitelabel_user.is_deleted', 0)
            ->and_where('whitelabel_user.date_register', '>=', $start)
            ->and_where('whitelabel_user.date_register', '<=', $end);

        if ($whitelabel_id) {
            $query->and_where('whitelabel_user.whitelabel_id', '=', $whitelabel_id);
        }

        $result = $query->execute();
        if (!empty($result[0]['count'])) {
            $res = $result[0]['count'];
        }

        return $res;
    }

    /**
     *
     * @access public
     * @param int $whitelabel_id
     * @return array
     */
    public static function get_ftd_count_for_crm_last_seven_days($whitelabel_id): array
    {
        $res = [];

        $query = DB::select(DB::expr('COUNT(*) AS count'), DB::expr('DATE(first_deposit) AS date'))->from('whitelabel_user')
            ->and_where('first_deposit', '>=', DB::expr('DATE(NOW()) - INTERVAL 7 DAY'));

        if ($whitelabel_id) {
            $query->and_where('whitelabel_id', '=', $whitelabel_id);
        }

        $query->group_by(DB::expr('DATE(first_deposit)'));
        $res = $query->execute()->as_array();

        return $res;
    }

    /**
     *
     * @access public
     * @param int $whitelabel_id
     * @param string $start_date
     * @param string $end_date
     * @return int
     */
    public static function get_ftd_count_for_crm($whitelabel_id, $start_date, $end_date): int
    {
        $res = 0;

        $start = Helpers_Crm_General::prepare_start_date($start_date);
        $end = Helpers_Crm_General::prepare_end_date($end_date);

        $query = DB::select(DB::expr('COUNT(*) as count'))->from('whitelabel_user')
            ->where('first_deposit', '!=', null)
            ->and_where('first_deposit', '>=', $start)
            ->and_where('first_deposit', '<=', $end);

        if ($whitelabel_id) {
            $query->and_where('whitelabel_id', '=', $whitelabel_id);
        }

        $result = $query->execute();
        if (!empty($result[0]['count'])) {
            $res = $result[0]['count'];
        }

        return $res;
    }

    /**
     *
     * @param int $id
     * @param string $balance
     * @param bool $is_bonus
     * @return bool
     */
    public static function update_balance_by_crm(int $id, string $balance, bool $is_bonus = false, bool $isCasino = false): bool
    {
        $user = Model_Whitelabel_User::find_by_pk($id);

        if (empty($user)) {
            return false;
        }

        $column_name = 'balance';

        if ($is_bonus) {
            $column_name = 'bonus_balance';
        }

        if ($isCasino) {
            $column_name = 'casino_balance';
        }

        $user->set([
            $column_name => round($balance, 2),
            'last_update' => DB::expr("NOW()")
        ]);
        $user->save();

        return true;
    }

    public static function update_balance_by_login(string $user, string $balance, bool $isBonus, bool $useEmail): int
    {
        $field = 'balance';
        if ($isBonus) {
            $field = 'bonus_balance';
        }

        $updateQuery = DB::update(self::$_table_name)
        ->set([$field => DB::expr("`$field` + $balance")]);

        if ($useEmail) {
            $updateQuery->where('email', '=', $user);
        } else {
            $updateQuery->where('login', '=', $user);
        }

        $updateQuery->and_where('whitelabel_id', '=', $user['whitelabel_id']);

        return $updateQuery->execute();
    }

    /**
     *
     * @param array $users
     * @return bool
     */
    public static function set_null_prize_payout_group_for_users($users)
    {
        DB::update('whitelabel_user')
            ->value('prize_payout_whitelabel_user_group_id', null)
            ->where('id', 'IN', $users)
            ->execute();

        return true;
    }

    public function isSocialConnected(int $userId): bool
    {
        return !empty(DB::select(DB::expr('1'))
            ->from('whitelabel_user_social')
            ->where('whitelabel_user_id', $userId)
            ->limit(1)
            ->execute()
            ->as_array());
    }
}
