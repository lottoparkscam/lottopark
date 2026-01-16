<?php

use Services\Logs\FileLoggerService;

/**
 * @deprecated
 * Class responsible for interactions with database - table whitelabel_user_aff.
 */
class Model_Whitelabel_User_Aff extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_user_aff';

    /**
     *
     * @var array
     */
    public static $cache_list = [];

    /**
     *
     * @param array $user
     * @return null|array
     */
    public static function get_data_for_aff_links($user)
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $res = null;
        $query = "SELECT 
                wa.*, 
                medium, 
                campaign, 
                content 
            FROM whitelabel_user_aff wua 
            LEFT JOIN whitelabel_aff wa ON wa.id = wua.whitelabel_aff_id 
            LEFT JOIN whitelabel_aff_medium wam ON wam.id = wua.whitelabel_aff_medium_id 
            LEFT JOIN whitelabel_aff_campaign wacg ON wacg.id = wua.whitelabel_aff_campaign_id 
            LEFT JOIN whitelabel_aff_content waco ON waco.id = wua.whitelabel_aff_content_id 
            WHERE wua.whitelabel_user_id = :user 
                AND wua.is_deleted = 0 
                AND wua.is_accepted = 1 
                AND wa.is_deleted = 0 
                AND wa.is_active = 1 
                AND wa.is_accepted = 1";

        try {
            /** @var object $db */
            $db = DB::query($query);
            $db->param(":user", $user['id']);
            $res = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        if ($res !== null && count($res) > 0 && !empty($res[0])) {
            $result = $res[0];
        }

        return $result;
    }

    /**
     * Delete lead for specified user.
     * @param int $user_id id of the user.
     */
    public static function expire_lead($user_id)
    {
        $query = "UPDATE whitelabel_user_aff 
            SET is_expired = 1 
            WHERE whitelabel_user_id = :user_id";

        // execute safe query
        parent::execute_query($query, [[":user_id", $user_id]]);
    }

    public static function fetch_count_for_leads(
        array $add,
        array $params,
        ?int $userId,
        ?int $parentId
    ): int {
        $userAdd = '';
        if (!empty($userId)) {
            $params[] = [":user_id", $userId];
            $userAdd = " AND whitelabel_user_aff.whitelabel_aff_id = :user_id ";
        }
        $parentAdd = '';
        if (!empty($parentId)) {
            $params[] = [":parent_id", $parentId];
            $parentAdd = " AND whitelabel_aff.whitelabel_aff_parent_id = :parent_id ";
        }

        $query_string = "SELECT 
            COUNT(*) AS count 
        FROM whitelabel_user_aff 
        LEFT JOIN whitelabel_aff ON whitelabel_user_aff.whitelabel_aff_id = whitelabel_aff.id
        WHERE 1=1
            $userAdd 
            $parentAdd
            AND whitelabel_user_aff.is_deleted = 0 
            AND whitelabel_user_aff.is_accepted = 1 " .
        implode(" ", $add);

        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result_item($result, 0, 0, 'count');
    }

    /**
     * Fetch count of leads for whitelabel filtered
     *
     * @param string $add filter adds
     * @param array $params filter params
     * @param int $whitelabel_id whitelabel id
     * @return int count of the leads for specified whitelabel.
     */
    public static function fetch_count_for_leads_for_whitelabel(
        string $add,
        array $params,
        int $whitelabel_id
    ): int {
        // add unique params
        $params[] = [":whitelabel_id", $whitelabel_id];

        $query_string = "SELECT 
                COUNT(*) AS count 
            FROM whitelabel_user_aff 
            LEFT JOIN whitelabel_aff ON whitelabel_aff.id = whitelabel_user_aff.whitelabel_aff_id 
            LEFT JOIN whitelabel_user ON whitelabel_user_aff.whitelabel_user_id = whitelabel_user.id 
            WHERE whitelabel_user_aff.whitelabel_id = :whitelabel_id 
            AND whitelabel_user_aff.is_deleted = 0 
            AND whitelabel_aff.is_deleted = 0 
            AND whitelabel_aff.is_active = 1 
            AND whitelabel_aff.is_accepted = 1 " .
            $add;

        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result_item($result, 0, 0, 'count');
    }

    /**
     * Get regcount query without add and order.
     * @return string regcount query.
     */
    private static function get_leads_query(): string
    {
        $query = "SELECT 
            whitelabel_user.token, 
            whitelabel_user_aff.id, 
            whitelabel_user.whitelabel_id, 
            whitelabel_user.country, 
            whitelabel_user.name, 
            whitelabel_user.surname, 
            whitelabel_user_aff.whitelabel_aff_id, 
            whitelabel_user_aff.is_accepted, 
            whitelabel_user.email, 
            whitelabel_user.is_active, 
            whitelabel_user.is_confirmed, 
            whitelabel_user.date_register, 
            whitelabel_user.register_country, 
            whitelabel_user.last_country, 
            whitelabel_user.first_purchase, 
            whitelabel_user_aff.is_expired,
            whitelabel_aff.name AS aff_name,
            whitelabel_aff.surname AS aff_surname,
            whitelabel_aff.login AS aff_login,
            whitelabel_aff.is_confirmed AS aff_is_confirmed,
            whitelabel_aff.token AS aff_token,
            whitelabel_aff.email AS aff_email
        FROM whitelabel_user_aff 
        LEFT JOIN whitelabel_user ON whitelabel_user.id = whitelabel_user_id 
        LEFT JOIN whitelabel_aff ON whitelabel_aff.id = whitelabel_user_aff.whitelabel_aff_id 
        WHERE whitelabel_user_aff.whitelabel_id = :whitelabel_id 
        AND whitelabel_user_aff.is_deleted = 0 
        AND whitelabel_aff.is_deleted = 0 
        AND whitelabel_aff.is_active = 1 
        AND whitelabel_aff.is_accepted = 1 ";

        return $query;
    }

    /**
     * Fetch regcount for reports.
     *
     * @param string $filter_add filter adds.
     * @param array $params filter params.
     * @param int $whitelabel_id user id
     * @param string $add_limits additional string consists limit and offset
     * @param int $offset offset for start fetching
     * @param int $limit limit of rows returned. Default 0
     * @return array array of regcount or null. Default 5
     */
    public static function fetch_leads_data(
        string $filter_add,
        array $params,
        int $whitelabel_id,
        string $add_limits = "",
        int $offset = 0,
        int $limit = 5
    ): ?array {
        // add non global params
        $params[] = [":whitelabel_id", $whitelabel_id];
        if (!empty($add_limits)) {
            $params[] = [":offset", $offset];
            $params[] = [":limit", $limit];
        }

        $query_string = self::get_leads_query() .
            $filter_add . " 
            ORDER BY date_register DESC " .
            $add_limits;

        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result($result, []);
    }

    /**
     * Get regcount query without add and order.
     *
     * @param array $joins Consist values for medium, campaign, content
     * filters (LEFT or INNER)
     * @return string regcount query.
     */
    private static function get_regcount_query(array $joins): string
    {
        $query = "SELECT 
            whitelabel_user.connected_aff_id,
            whitelabel_user_aff.whitelabel_aff_id,
            whitelabel_user.token, 
            whitelabel_user.is_active, 
            whitelabel_user.is_confirmed, 
            whitelabel_user.date_register, 
            whitelabel_user.register_country, 
            whitelabel_user.last_country, 
            whitelabel_user.first_purchase, 
            medium, 
            campaign, 
            content, 
            whitelabel_user_aff.is_expired, 
            whitelabel_user.name AS lead_name, 
            whitelabel_user.surname AS lead_surname, 
            whitelabel_user.email AS lead_email
        FROM whitelabel_user_aff 
        LEFT JOIN whitelabel_user ON whitelabel_user.id = whitelabel_user_id  
        JOIN whitelabel_aff ON whitelabel_user_aff.whitelabel_aff_id = whitelabel_aff.id " .
            $joins["medium"] .
            " JOIN whitelabel_aff_medium ON whitelabel_aff_medium.id = whitelabel_user_aff.whitelabel_aff_medium_id " .
            $joins["campaign"] .
            " JOIN whitelabel_aff_campaign ON whitelabel_aff_campaign.id = whitelabel_user_aff.whitelabel_aff_campaign_id " .
            $joins["content"] .
            " JOIN whitelabel_aff_content ON whitelabel_aff_content.id = whitelabel_user_aff.whitelabel_aff_content_id ";

        return $query;
    }

    /**
     * Get regcount query without add and order for whitelabel.
     *
     * @return string regcount query.
     */
    private static function get_regcount_query_for_whitelabel(): string
    {
        $query = "SELECT 
                whitelabel_user.token, 
                whitelabel_user.is_active, 
                whitelabel_user.is_confirmed, 
                whitelabel_user.date_register, 
                whitelabel_user.register_country, 
                whitelabel_user.last_country, 
                whitelabel_user.first_purchase,
                whitelabel_user.first_deposit,
                whitelabel_user_aff.whitelabel_aff_id, 
                whitelabel_user.email, 
                whitelabel_user.name, 
                whitelabel_user.surname, 
                whitelabel_user_aff.is_expired,
                whitelabel_aff.name AS aff_name,
                whitelabel_aff.surname AS aff_surname,
                whitelabel_aff.login AS aff_login,
                whitelabel_aff.is_confirmed AS aff_is_confirmed,
                whitelabel_aff.email AS aff_email,
                whitelabel_aff.token AS aff_token
            FROM whitelabel_user_aff 
            LEFT JOIN whitelabel_user ON whitelabel_user.id = whitelabel_user_id 
            LEFT JOIN whitelabel_aff ON whitelabel_aff.id = whitelabel_user_aff.whitelabel_aff_id
            WHERE whitelabel_user_aff.whitelabel_id = :whitelabel_id 
                AND whitelabel_user_aff.is_deleted = 0 
                AND whitelabel_user_aff.is_accepted = 1 ";

        return $query;
    }

    /**
     * Get first time purchase query without add and order for user.
     *
     * @param array $joins Consist values for medium, campaign, content
     * filters (LEFT or INNER)
     * @return string regcount query.
     */
    private static function get_ftp_query(array $joins): string
    {
        $query =  "SELECT DISTINCT
            whitelabel_user.token, 
            whitelabel_user.is_active, 
            whitelabel_user.is_confirmed, 
            whitelabel_user.date_register, 
            whitelabel_user.register_country, 
            whitelabel_user.last_country, 
            whitelabel_user.first_purchase, 
            medium, 
            campaign, 
            content, 
            whitelabel_user.name as lead_name, 
            whitelabel_user.surname as lead_surname, 
            whitelabel_user.email as lead_email 
        FROM whitelabel_user_aff 
        JOIN whitelabel_aff ON whitelabel_user_aff.whitelabel_aff_id = whitelabel_aff.id 
        LEFT JOIN whitelabel_user ON whitelabel_user.id = whitelabel_user_id
        LEFT JOIN whitelabel_aff_commission ON whitelabel_aff_commission.whitelabel_aff_id = whitelabel_user_aff.whitelabel_aff_id " .
            $joins["medium"] .
            " JOIN whitelabel_aff_medium ON whitelabel_aff_medium.id = whitelabel_user_aff.whitelabel_aff_medium_id " .
            $joins["campaign"] .
            " JOIN whitelabel_aff_campaign ON whitelabel_aff_campaign.id = whitelabel_user_aff.whitelabel_aff_campaign_id " .
            $joins["content"] .
            " JOIN whitelabel_aff_content ON whitelabel_aff_content.id = whitelabel_user_aff.whitelabel_aff_content_id ";

        return $query;
    }

    /**
     * Get first time deposit query without add and order for user.
     *
     * @param array $joins Consist values for medium, campaign, content
     * filters (LEFT or INNER)
     * @return string regcount query.
     */
    private static function get_ftd_query(array $joins): string
    {
        $query =  "SELECT DISTINCT
            whitelabel_user.token, 
            whitelabel_user.is_active, 
            whitelabel_user.is_confirmed, 
            whitelabel_user.date_register, 
            whitelabel_user.register_country, 
            whitelabel_user.last_country, 
            whitelabel_user.first_deposit, 
            medium, 
            campaign, 
            content, 
            whitelabel_user.name as lead_name, 
            whitelabel_user.surname as lead_surname, 
            whitelabel_user.email as lead_email 
        FROM whitelabel_user_aff 
        JOIN whitelabel_aff ON whitelabel_user_aff.whitelabel_aff_id = whitelabel_aff.id 
        LEFT JOIN whitelabel_user ON whitelabel_user.id = whitelabel_user_id
        LEFT JOIN whitelabel_aff_commission ON whitelabel_aff_commission.whitelabel_aff_id = whitelabel_user_aff.whitelabel_aff_id " .
            $joins["medium"] .
            " JOIN whitelabel_aff_medium ON whitelabel_aff_medium.id = whitelabel_user_aff.whitelabel_aff_medium_id " .
            $joins["campaign"] .
            " JOIN whitelabel_aff_campaign ON whitelabel_aff_campaign.id = whitelabel_user_aff.whitelabel_aff_campaign_id " .
            $joins["content"] .
            " JOIN whitelabel_aff_content ON whitelabel_aff_content.id = whitelabel_user_aff.whitelabel_aff_content_id ";

        return $query;
    }

    /**
     * Get first time purchase query without add and order for whitelabel.
     * @return string regcount query.
     */
    private static function get_ftp_query_for_whitelabel(): string
    {
        $query =  "SELECT 
                whitelabel_user.token, 
                whitelabel_user.is_active, 
                whitelabel_user.is_confirmed, 
                whitelabel_user.date_register, 
                whitelabel_user.register_country, 
                whitelabel_user.last_country, 
                whitelabel_user.first_purchase, 
                whitelabel_user_aff.whitelabel_aff_id, 
                whitelabel_user.email, 
                whitelabel_user.name, 
                whitelabel_user.surname,
                whitelabel_aff.name AS aff_name,
                whitelabel_aff.surname AS aff_surname,
                whitelabel_aff.login AS aff_login,
                whitelabel_aff.is_confirmed AS aff_is_confirmed,
                whitelabel_aff.email AS aff_email,
                whitelabel_aff.token AS aff_token
            FROM whitelabel_user_aff 
            LEFT JOIN whitelabel_user ON whitelabel_user.id = whitelabel_user_id 
            LEFT JOIN whitelabel_aff ON whitelabel_aff.id = whitelabel_user_aff.whitelabel_aff_id
            WHERE whitelabel_user_aff.whitelabel_id = :whitelabel_id 
                AND whitelabel_user_aff.is_accepted = 1 ";

        return $query;
    }

    /**
     * Get first time deposit query without add and order for whitelabel.
     */
    private static function get_ftd_query_for_whitelabel(): string
    {
        $query =  'SELECT 
                whitelabel_user.token, 
                whitelabel_user.is_active, 
                whitelabel_user.is_confirmed, 
                whitelabel_user.date_register, 
                whitelabel_user.register_country, 
                whitelabel_user.last_country, 
                whitelabel_user.first_deposit, 
                whitelabel_user_aff.whitelabel_aff_id, 
                whitelabel_user.email, 
                whitelabel_user.name, 
                whitelabel_user.surname,
                whitelabel_aff.name AS aff_name,
                whitelabel_aff.surname AS aff_surname,
                whitelabel_aff.login AS aff_login,
                whitelabel_aff.is_confirmed AS aff_is_confirmed,
                whitelabel_aff.email AS aff_email,
                whitelabel_aff.token AS aff_token
            FROM whitelabel_user_aff 
            LEFT JOIN whitelabel_user ON whitelabel_user.id = whitelabel_user_id 
            LEFT JOIN whitelabel_aff ON whitelabel_aff.id = whitelabel_user_aff.whitelabel_aff_id
            WHERE whitelabel_user_aff.whitelabel_id = :whitelabel_id 
                AND whitelabel_user_aff.is_accepted = 1 ';

        return $query;
    }

    /**
     * @param array $add filter adds.
     * @param array $params filter params.
     * @param array $joins SQL joins strings.
     * @param int[] $subAffiliateIds
     * @return array array of regcount or null.
     */
    public static function fetch_leads_for_reports(
        array $add,
        array $params,
        array $joins,
        ?array $subAffiliateIds,
        ?int $parentId,
    ): array {
        $userAdd = '';
        if (!empty($subAffiliateIds)) {
            $subAffiliateIds = join(',', array_map('intval', $subAffiliateIds));
            $userAdd = " AND whitelabel_user_aff.whitelabel_aff_id IN ($subAffiliateIds) ";
        }
        $parentAdd = '';
        if (!empty($parentId)) {
            $params[] = [":parent_id", $parentId];
            $parentAdd = " AND whitelabel_aff.whitelabel_aff_parent_id = :parent_id ";
        }

        $query_string = self::get_regcount_query($joins) .
            "WHERE 1=1 
                $userAdd
                $parentAdd
                AND whitelabel_user_aff.is_deleted = 0 
                AND whitelabel_user_aff.is_accepted = 1 " .
                implode(" ", $add) . " 
                AND date_register >= :date_start 
                AND date_register <= :date_end 
            ORDER BY date_register";

        // execute safe query
        $result = parent::execute_query($query_string, $params);

        // safely retrieve value
        return parent::get_array_result($result, []);
    }

    /**
     * Fetch regcount for reports for whitelabel.
     *
     * @param array $add filter adds.
     * @param array $params filter params.
     * @param int $whitelabel_id
     * @return array array of regcount or null.
     */
    public static function fetch_regcount_for_reports_for_whitelabel(
        array $add,
        array $params,
        int $whitelabel_id
    ): array {
        // add non global params
        $params[] = [":whitelabel_id", $whitelabel_id];

        $query_string = self::get_regcount_query_for_whitelabel() .
            implode(" ", $add) . " 
            AND date_register >= :date_start 
            AND date_register <= :date_end 
            ORDER BY date_register";

        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result($result, []);
    }

    /**
     * Fetch first time purchases for reports.
     *
     * @param array $add filter adds.
     * @param array $params filter params.
     * @param array $joins SQL joins strings.
     * @param array $subAffiliateIds
     * @param int $parentId
     * @return array array of ftp or null.
     */
    public static function fetch_ftp_for_reports(
        array $add,
        array $params,
        array $joins,
        array $subAffiliateIds,
        ?int $parentId
    ): ?array {
        // add non global params
        $userAdd = '';
        if (!empty($subAffiliateIds)) {
            $subAffiliateIds = join(',', array_map('intval', $subAffiliateIds));
            $userAdd = " AND whitelabel_user_aff.whitelabel_aff_id IN ($subAffiliateIds) ";
        }
        $parentAdd = '';
        if (!empty($parentId)) {
            $params[] = [":parent_id", $parentId];
            $parentAdd = " AND whitelabel_aff.whitelabel_aff_parent_id = :parent_id ";
        }

        $query_string = self::get_ftp_query($joins) .
            "WHERE 1=1 
                $userAdd
                $parentAdd
                AND whitelabel_user_aff.is_accepted = 1 " .
            implode(" ", $add) . " 
                AND first_purchase IS NOT NULL 
                AND first_purchase >= :date_start 
                AND first_purchase <= :date_end 
            ORDER BY first_purchase";

        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result($result, []);
    }

    /**
     * Fetch first time deposits for reports.
     *
     * @param array $add filter adds.
     * @param array $params filter params.
     * @param array $joins SQL joins strings.
     * @param array $subAffiliateIds
     * @param int $parentId
     * @return array array of ftd or null.
     */
    public static function fetch_ftd_for_reports(
        array $add,
        array $params,
        array $joins,
        array $subAffiliateIds,
        ?int $parentId
    ): ?array {
        // add non global params
        $userAdd = '';
        if (!empty($subAffiliateIds)) {
            $subAffiliateIds = join(',', array_map('intval', $subAffiliateIds));
            $userAdd = " AND whitelabel_user_aff.whitelabel_aff_id IN ($subAffiliateIds) ";
        }
        $parentAdd = '';
        if (!empty($parentId)) {
            $params[] = [":parent_id", $parentId];
            $parentAdd = " AND whitelabel_aff.whitelabel_aff_parent_id = :parent_id ";
        }

        $query_string = self::get_ftd_query($joins) .
            "WHERE 1=1 
                $userAdd
                $parentAdd
                AND whitelabel_user_aff.is_accepted = 1 " .
            implode(" ", $add) . " 
                AND first_deposit IS NOT NULL 
                AND first_deposit >= :date_start 
                AND first_deposit <= :date_end
                ORDER BY first_deposit";

        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result($result, []);
    }

    /**
     * Fetch first time purchases for reports for whitelabel.
     *
     * @param array $add filter adds.
     * @param array $params filter params.
     * @param int $whitelabel_id user id
     * @return array array of ftp or null.
     */
    public static function fetch_ftp_for_reports_for_whitelabel(
        array $add,
        array $params,
        int $whitelabel_id
    ): ?array {
        // add non global params
        $params[] = [":whitelabel_id", $whitelabel_id];

        $query_string = self::get_ftp_query_for_whitelabel() .
            implode(" ", $add) . " 
            AND first_purchase IS NOT NULL 
            AND first_purchase >= :date_start 
            AND first_purchase <= :date_end 
            ORDER BY first_purchase";

        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result($result, []);
    }

    /**
     * Fetch first time deposits for reports for whitelabel.
     *
     * @param array $add filter adds.
     * @param array $params filter params.
     * @param int $whitelabel_id user id
     * @return array array of ftd or null.
     */
    public static function fetch_ftd_for_reports_for_whitelabel(
        array $add,
        array $params,
        int $whitelabel_id
    ): ?array {
        // add non global params
        $params[] = [":whitelabel_id", $whitelabel_id];

        $query_string = self::get_ftd_query_for_whitelabel() .
            implode(" ", $add) . " 
            AND first_deposit IS NOT NULL 
            AND first_deposit >= :date_start 
            AND first_deposit <= :date_end 
            ORDER BY first_deposit";

        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result($result, []);
    }

    public static function fetch_leads(
        array $add,
        array $params,
        array $joins,
        object $pagination,
        ?int $userId,
        ?int $parentId
    ): ?array {
        $userAdd = '';
        if (!empty($userId)) {
            $params[] = [":user_id", $userId];
            $userAdd = " AND whitelabel_user_aff.whitelabel_aff_id = :user_id ";
        }
        $parentAdd = '';
        if (!empty($parentId)) {
            $params[] = [":parent_id", $parentId];
            $parentAdd = " AND whitelabel_aff.whitelabel_aff_parent_id = :parent_id ";
        }

        // add non global params
        $params[] = [":offset", $pagination->offset];
        $params[] = [":limit", $pagination->per_page];

        $query_string = self::get_regcount_query($joins) . " WHERE 1=1
            $userAdd
            $parentAdd
            AND whitelabel_user_aff.is_deleted = 0 
            AND whitelabel_user_aff.is_accepted = 1 " .
            implode(" ", $add) . " 
            ORDER BY date_register DESC LIMIT :offset, :limit";

        // execute safe query
        $result = parent::execute_query($query_string, $params);

        // safely retrieve value
        return parent::get_array_result($result, []);
    }

    public static function fetch_count_for_ftps(
        array $add,
        array $params,
        ?int $userId,
        ?int $parentId
    ): int {
        $userAdd = '';
        if (!empty($userId)) {
            $params[] = [":user_id", $userId];
            $userAdd = " AND whitelabel_user_aff.whitelabel_aff_id = :user_id ";
        }
        $parentAdd = '';
        if (!empty($parentId)) {
            $params[] = [":parent_id", $parentId];
            $parentAdd = " AND whitelabel_aff.whitelabel_aff_parent_id = :parent_id ";
        }

        $query_string = "SELECT 
            COUNT(*) AS count 
            FROM whitelabel_user_aff 
            LEFT JOIN whitelabel_user ON whitelabel_user.id = whitelabel_user_id 
            JOIN whitelabel_aff ON whitelabel_user_aff.whitelabel_aff_id = whitelabel_aff.id
            WHERE 1 = 1
            $userAdd
            $parentAdd
            AND whitelabel_user_aff.is_accepted = 1 " .
            implode(" ", $add) . " 
            AND first_purchase IS NOT NULL";

        // execute safe query
        $result = parent::execute_query($query_string, $params);

        // safely retrieve value
        return parent::get_array_result_item($result, 0, 0, 'count');
    }

    public static function fetch_ftp(
        array $add,
        array $params,
        array $joins,
        object $pagination,
        ?int $userId,
        ?int $parentId
    ): ?array {
        $userAdd = '';
        if (!empty($userId)) {
            $params[] = [":user_id", $userId];
            $userAdd = " AND whitelabel_user_aff.whitelabel_aff_id = :user_id ";
        }
        $parentAdd = '';
        if (!empty($parentId)) {
            $params[] = [":parent_id", $parentId];
            $parentAdd = " AND whitelabel_aff.whitelabel_aff_parent_id = :parent_id ";
        }

        $params[] = [":offset", $pagination->offset];
        $params[] = [":limit", $pagination->per_page];

        $query_string = self::get_ftp_query($joins) .
            "WHERE 1=1 
                $userAdd
                $parentAdd
                AND whitelabel_user_aff.is_accepted = 1 " .
            implode(" ", $add) . " 
                AND first_purchase IS NOT NULL 
            ORDER BY first_purchase DESC LIMIT :offset, :limit";

        // execute safe query
        $result = parent::execute_query($query_string, $params);

        // safely retrieve value
        return parent::get_array_result($result, []);
    }
}
