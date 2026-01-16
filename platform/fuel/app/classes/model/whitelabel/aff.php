<?php

use Services\Logs\FileLoggerService;

/**
 * @deprecated
 * Class responsible for interactions with database - table whitelabel_aff.
 */
class Model_Whitelabel_Aff extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_aff';

    /**
     *
     * @var array
     */
    public static $cache_list = [
        "model_whitelabel_aff.fetchsubaffsactive",
    ];

    /**
     *
     * @param string $login
     * @param string $hash
     * @param bool $check_is_active
     * @param bool $check_is_confirmed
     * @return null|array
     */
    public static function check_aff_credentials_hashed(
        string $login = null,
        string $hash = null,
        bool $check_is_active = false,
        bool $check_is_confirmed = false
    ):? array {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $res = null;
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        $query = "SELECT 
            * 
        FROM whitelabel_aff 
        WHERE whitelabel_id = :whitelabel
            AND is_deleted = 0 
            AND (login = :login OR email = :login) ";
        
        if ($check_is_active) {
            $query .= " AND is_active = 1 ";
        }
        
        if ($check_is_confirmed) {
            $query .= " AND is_confirmed = 1 ";
        }
        
        $query .= " LIMIT 1";

        try {
            $db = DB::query($query);
            $db->param(":whitelabel", $whitelabel['id']);
            $db->param(":login", $login);
            $res = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        if ($res !== null && count($res) > 0 && $hash === $res[0]['hash']) {
            $result = $res[0];
        }

        return $result;
    }

    /**
     *
     * @param array $whitelabel
     * @param string $email
     * @param string $login Default empty - in that case that will not be taken
     * @param int|string $user_id Default empty - in that case that will not be taken
     * @return null|array
     */
    public static function get_count_for_whitelabel(
        $whitelabel,
        $email,
        $login = "",
        $user_id = ""
    ) {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $query = "SELECT 
            COUNT(*) AS count 
        FROM whitelabel_aff 
        WHERE is_deleted = 0 ";

        if (!empty($whitelabel) && !empty($whitelabel["id"])) {
            $query .= " AND whitelabel_id = :whitelabel_id ";
        }

        if (!empty($login)) {
            $query .= " AND (login = :login OR email = :email) ";
        } else {
            $query .= " AND email = :email ";
        }

        if (!empty($user_id)) {
            $query .= " AND id != :id";
        }

        try {
            $db = DB::query($query);

            if (!empty($whitelabel) && !empty($whitelabel["id"])) {
                $db->param(":whitelabel_id", $whitelabel["id"]);
            }

            if (!empty($login)) {
                $db->param(":login", $login);
                $db->param(":email", $email);
            } else {
                $db->param(":email", $email);
            }

            if (!empty($user_id)) {
                $db->param(":id", $user_id);
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
     * Fetch not empty, not deleted, active and accepted aff for specified whitelabel_id and token.
     * @param mixed $whitelabel_id int or string (castable to int).
     * @param string $token token (ref).
     */
    public static function fetch_aff($whitelabel_id, $token): null|array|Model_Whitelabel_Aff
    {
        // fetch aff
        /** @var Model_Whitelabel_Aff[] $aff */
        $aff = Model_Whitelabel_Aff::find([
            "where" => [
                "whitelabel_id" => $whitelabel_id,
                "is_deleted" => 0,
                "is_active" => 1,
                "is_accepted" => 1,
                "token" => mb_strtolower($token)
            ]]);
        // validate aff
        if ($aff !== null && count($aff) > 0) {
            return $aff[0];
        }
        // return null if invalid
        return null;
    }
    
    /**
     * Fetch not empty, not deleted, active and accepted affinites for specified parent.
     * NOTE, this function use cache - since it will be called multiple times in before.
     * @param int $parentId
     * @return array null on failure, model on success (array).
     */
    public static function fetch_subaffs_active(int $parentId):? array
    {
        if (empty($parentId)) {
            return null;
        }
          
        $query_string = "SELECT 
            id, 
            name, 
            surname,
            login 
        FROM whitelabel_aff
        WHERE whitelabel_aff_parent_id = :parent_id
        AND is_deleted = 0
        AND is_active = 1
        AND is_accepted = 1";

        // add non global params
        $params[] = [":parent_id", $parentId];
        
        // execute safe query
        $result = parent::execute_query($query_string, $params);
        
        // safely retrieve value
        return parent::get_array_result($result, []); // todo: it may be a good idea to add function with basic behaviour - result array() on error (it's multiple times used like this).
    }
    
    /**
     * Fetch details for specified subaffiliate.
     * @param int $id id of the subaffiliate.
     * @return array|null array of subaffiliates (can be empty) or null on failure.
     */
    public static function fetch_subaffiliate_details($id)
    {
        $query_string = "SELECT 
            l.code as lcode,  
            if (wa.last_ip IS NULL, null, CONCAT(wa.last_ip, if(wa.last_country IS NULL, '', CONCAT(' ', wa.last_country)))) as activity_data,
            wa.*
        FROM whitelabel_aff as wa
        LEFT JOIN language as l ON wa.language_id = l.id
        WHERE wa.id = :id";
        
        // add non global params
        $params[] = [":id", $id];
        
        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result_row($result, null, 0);
    }

    /**
     * Fetch subaffiliates (which are not deleted, active and accepted) for selected user.
     * @param int $id id of the user.
     * @param object $pagination pagination object.
     * @return mixed array of subaffiliates (can be empty) or null on failure.
     */
    public static function fetch_subaffiliates($add, $params, $id, $pagination)
    {
        // add non global params
        $params[] = [":pid", $id];
        $params[] = [":offset", $pagination->offset];
        $params[] = [":limit", $pagination->per_page];
        
        // select only fields which are used
        $query_string = "SELECT 
            wa.token, 
            wa.name, 
            wa.surname, 
            wa.login, 
            wa.email,
            wa.phone, 
            wa.country, 
            l.code as lcode, 
            wa.timezone, 
            wa.date_created, 
            wa.last_active, 
            wa.last_ip, 
            wa.last_country,
            wa.is_confirmed, 
            wa.id
        FROM whitelabel_aff as wa
        LEFT JOIN language as l ON wa.language_id = l.id
        WHERE wa.whitelabel_aff_parent_id = :pid
        AND is_deleted = 0
        AND is_active = 1
        AND is_accepted = 1 " .
        implode(" ", $add) . "
        ORDER BY date_created DESC LIMIT :offset, :limit";  // last fields are helpers - not included in listing
        
        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result($result, []);
    }
    
     /**
     * Fetch count of subaffiliates (which are not deleted, active and accepted).
     * @param array $add filter adds.
     * @param array $params filter params.
     * @param int $parent_id parent id
     * @return int count of the subaffiliates for specified user.
     */
    public static function fetch_count_subaffiliates($add, $params, $parent_id)
    {
        // add non global params
        $params[] = [":pid", $parent_id];
        
        $query_string = "SELECT 
            COUNT(*) AS count 
        FROM whitelabel_aff as wa
        WHERE whitelabel_aff_parent_id = :pid
        AND is_deleted = 0
        AND is_active = 1
        AND is_accepted = 1 " .
        implode(" ", $add);
        
        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result_item($result, 0, 0, 'count');
    }
    
    /**
     *
     * @param Model_Whitelabel_Aff $whitelabel_aff_obj
     * @return null|int
     */
    public static function delete_parent_aff(Model_Whitelabel_Aff $whitelabel_aff_obj):? int
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        
        $query = "UPDATE 
            whitelabel_aff 
        SET 
            whitelabel_aff_parent_id = NULL
        WHERE whitelabel_aff_parent_id = :whitelabel_aff_obj_id ";
        
        try {
            $db = DB::query($query);

            /** @var object $whitelabel_aff_obj */
            $db->param(":whitelabel_aff_obj_id", $whitelabel_aff_obj->id);
            
            $result = $db->execute();
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
     * @param int $is_deleted
     * @param string $add
     * @return null|int
     */
    public static function count_by_whitelabel($whitelabel, $is_deleted, $add):? int
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $res = null;
        
        $query = "SELECT 
            COUNT(*) AS count 
        FROM whitelabel_aff 
        WHERE 1=1 ";

        if (!empty($whitelabel) && !empty($whitelabel["id"])) {
            $query .= " AND whitelabel_id = :whitelabel_id ";
        }

        $query .= " AND is_deleted = " . $is_deleted . " ";
        $query .= " " . $add;

        try {
            $db = DB::query($query);

            if (!empty($whitelabel) && !empty($whitelabel["id"])) {
                $db->param(":whitelabel_id", $whitelabel["id"]);
            }

            $res = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        if (is_null($res) || count($res) == 0) {
            return $result;
        }
        
        $result = intval($res[0]['count']);
        
        return $result;
    }
    
    /**
     *
     * @param array $whitelabel
     * @param string $add
     * @param array $params
     * @return null|int
     */
    public static function count_for_ftps($whitelabel, $add, $params):? int
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $res = null;
        
        $query = "SELECT 
            COUNT(*) AS count 
        FROM whitelabel_user_aff 
        LEFT JOIN whitelabel_user ON whitelabel_user.id = whitelabel_user_id 
        LEFT JOIN whitelabel_aff ON whitelabel_aff.id = whitelabel_user_aff.whitelabel_aff_id 
        WHERE 1=1 ";
        
        if (!empty($whitelabel) && !empty($whitelabel["id"])) {
            $query .= " AND whitelabel_aff.whitelabel_id = :whitelabel_id ";
        }
        
        $query .= " AND whitelabel_aff.is_deleted = 0 
            AND whitelabel_aff.is_active = 1 
            AND whitelabel_aff.is_accepted = 1 
            AND whitelabel_user_aff.is_accepted = 1 ";
            
        $query .= " " . $add;
        $query .= " AND first_purchase IS NOT NULL";

        try {
            $db = DB::query($query);

            if (!empty($whitelabel) && !empty($whitelabel["id"])) {
                $db->param(":whitelabel_id", $whitelabel["id"]);
            }

            foreach ($params as $param) {
                $db->param($param[0], $param[1]);
            }
            
            $res = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        if (is_null($res) || count($res) == 0) {
            return $result;
        }
        
        $result = intval($res[0]['count']);
        
        return $result;
    }
    
    /**
     *
     * @param array $whitelabel
     * @param string $add
     * @param array $params
     * @param string $add_limits
     * @param int $offset
     * @param int $limit
     * @return array|null
     */
    public static function get_data_for_ftps(
        $whitelabel,
        $add,
        $params,
        $add_limits = "",
        $offset = 0,
        $limit = 5
    ):? array {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $query = "SELECT 
            whitelabel_user.token, 
            whitelabel_user.name, 
            whitelabel_user.surname, 
            whitelabel_user.is_active, 
            whitelabel_user.is_confirmed, 
            whitelabel_user.date_register, 
            whitelabel_user.register_country, 
            whitelabel_user.last_country, 
            whitelabel_user.first_purchase, 
            whitelabel_aff_id, 
            whitelabel_user.name, 
            whitelabel_user.surname, 
            whitelabel_user.email, 
            whitelabel_aff.country,
            whitelabel_aff.name AS aff_name,
            whitelabel_aff.surname AS aff_surname,
            whitelabel_aff.login AS aff_login,
            whitelabel_aff.is_confirmed AS aff_is_confirmed,
            whitelabel_aff.email AS aff_email,
            whitelabel_aff.token AS aff_token
        FROM whitelabel_user_aff 
        LEFT JOIN whitelabel_user ON whitelabel_user.id = whitelabel_user_id 
        LEFT JOIN whitelabel_aff ON whitelabel_aff.id = whitelabel_user_aff.whitelabel_aff_id 
        WHERE 1=1 ";
        
        if (!empty($whitelabel) && !empty($whitelabel['id'])) {
            $query .= " AND whitelabel_aff.whitelabel_id = :whitelabel_id";
        }
        
        $query .= " AND whitelabel_aff.is_deleted = 0 
            AND whitelabel_aff.is_active = 1 
            AND whitelabel_aff.is_accepted = 1 
            AND whitelabel_user_aff.is_accepted = 1";
        
        $query .= $add;
        $query .= " AND first_purchase IS NOT NULL ORDER BY first_purchase ";
        
        if (!empty($add_limits)) {
            $query .= $add_limits;
        }
        
        try {
            $db = DB::query($query);
            
            if (!empty($whitelabel) && !empty($whitelabel['id'])) {
                $db->param(":whitelabel_id", $whitelabel['id']);
            }

            foreach ($params as $param) {
                $db->param($param[0], $param[1]);
            }
            
            if (!empty($add_limits)) {
                $db->param(":offset", $offset);
                $db->param(":limit", $limit);
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
     * @return array|null
     */
    public static function get_all_for_whitelabel(array $whitelabel):? array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        
        $query = "SELECT 
            * 
        FROM whitelabel_aff 
        WHERE 1=1 ";
        
        if (!empty($whitelabel) && !empty($whitelabel['id'])) {
            $query .= " AND whitelabel_aff.whitelabel_id = :whitelabel_id ";
        }
        
        try {
            $db = DB::query($query);
            
            if (!empty($whitelabel) && !empty($whitelabel['id'])) {
                $db->param(":whitelabel_id", $whitelabel['id']);
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
     * @param int $whitelabel_aff_id
     * @return array
     */
    public static function fetch_manager_curr_for_user(int $whitelabel_aff_id): array
    {
        $params = [];
        $params[] = [":whitelabel_aff_id", $whitelabel_aff_id];
        
        // select only fields which are used
        $query_string = "SELECT 
            wf.id AS wfid,
            manager_currency.id AS manager_currency_id,
            manager_currency.code AS manager_currency_code,
            manager_currency.rate AS manager_currency_rate 
        FROM whitelabel_aff wf 
        INNER JOIN whitelabel wl ON wl.id = wf.whitelabel_id
        INNER JOIN currency manager_currency ON manager_currency.id = wl.manager_site_currency_id 
        WHERE wf.id = :whitelabel_aff_id 
        LIMIT 1";
        
        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result($result, []);
    }
    
    /**
     * Fetch count by whitelabel filtered
     *
     * @param array $params
     * @param string $accepted_add
     * @param string $add
     * @param string $filter_add
     * @param int $whitelabel_id
     * @param int $is_deleted
     * @return int
     */
    public static function fetch_count_filtered_by_whitelabel(
        array $params,
        string $accepted_add,
        string $add,
        string $filter_add,
        int $whitelabel_id,
        int $is_deleted
    ): int {
        // add non global params
        $params[] = [":whitelabel_id", $whitelabel_id];
        $params[] = [":is_deleted", $is_deleted];
        
        $query_string = "SELECT 
                COUNT(*) AS count 
            FROM whitelabel_aff 
            WHERE whitelabel_id = :whitelabel_id 
            AND is_deleted = :is_deleted " .
            $accepted_add .
            $add .
            $filter_add;
        
        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result_item($result, 0, 0, 'count');
    }
    
    /**
     * Fetch data by whitelabel filtered
     *
     * @param array $params
     * @param string $accepted_add
     * @param string $add
     * @param string $filter_add
     * @param int $whitelabel_id
     * @param int $is_deleted
     * @param string $sort_db
     * @param bool $should_limit
     * @param int $offset
     * @param int $limit
     * @return array|null
     */
    public static function fetch_data_filtered_by_whitelabel(
        array $params,
        string $accepted_add,
        string $add,
        string $filter_add,
        int $whitelabel_id,
        int $is_deleted,
        string $sort_db,
        bool $should_limit = true,
        int $offset = 0,
        int $limit = 5
    ):? array {
        // add non global params
        $params[] = [":whitelabel_id", $whitelabel_id];
        $params[] = [":is_deleted", $is_deleted];
        
        $params[] = [":order", DB::expr($sort_db)];
        
        if ($should_limit) {
            $params[] = [":offset", $offset];
            $params[] = [":limit", $limit];
        }
        
        $query_string = "SELECT 
                * 
            FROM whitelabel_aff 
            WHERE whitelabel_id = :whitelabel_id 
            AND is_deleted = :is_deleted " .
            $accepted_add .
            $add .
            $filter_add .
            " ORDER BY :order ";
        
        if ($should_limit) {
            $query_string .= " LIMIT :offset, :limit";
        }
        
        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result($result, []);
    }
}
