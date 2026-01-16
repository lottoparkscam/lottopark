<?php

use Services\Logs\FileLoggerService;

class Model_Payment_Log extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'payment_log';

    // ALL THIS CONST ARE MOVED TO Helpers_General!!!!!!!!!!!
    // I left these values here at this moment
    // but it is better to collect all const in one place
    const TYPE_INFO = 0;
    const TYPE_SUCCESS = 1;
    const TYPE_WARNING = 2;
    const TYPE_ERROR = 3;

    /**
     *
     * @param int $type Could not be null (INFO, SUCCESS, WARNING, ERROR)
     * @param int $payment_method_type
     * @param int $payment_method_id
     * @param int $cc_method
     * @param int $whitelabel_id
     * @param int $transaction_id
     * @param string $message
     * @param array $data
     * @param int $whitelabel_payment_method_id For other payment methods should
     *                                          ID from whitelabel_payment_method
     *                                          table should be given (could be null)
     */
    public static function add_log(
        int $type,
        int $payment_method_type = null,
        int $payment_method_id = null,
        int $cc_method = null,
        int $whitelabel_id = null,
        int $transaction_id = null,
        string $message = "",
        array $data = null,
        int $whitelabel_payment_method_id = null
    ): void {
        $date = new DateTime("now", new DateTimeZone("UTC"));

        $set = [
            'payment_method_id' => $payment_method_id,
            'whitelabel_payment_method_id' => $whitelabel_payment_method_id,
            'cc_method' => $cc_method,
            'payment_method_type' => $payment_method_type,
            'whitelabel_id' => $whitelabel_id,
            'whitelabel_transaction_id' => $transaction_id,
            'date' => $date->format('Y-m-d H:i:s'),
            'type' => $type,
            'message' => $message,
            'data' => $data !== null ? serialize($data) : null
        ];

        $var = self::forge();
        $var->set($set);
        $var->save();
    }

    /**
     *
     * @param string $filters_add
     * @param array $params
     * @return int|null
     */
    public static function get_count_filtered($filters_add, $params): ?int
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $res = null;
        $query = "SELECT 
            COUNT(*) AS count 
        FROM payment_log 
        WHERE 1=1 ";

        $query .= $filters_add . " ";

        try {
            $db = DB::query($query);

            foreach ($params as $param => $value) {
                $db->param($param, $value);
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

        $result = $res[0]['count'];

        return $result;
    }

    /**
     *
     * @param string $filters_add
     * @param array $params
     * @param int $offset
     * @param int $limit
     * @return array|null
     */
    public static function get_data_filtered(
        string $filters_add = "",
        array $params = [],
        int $offset = 0,
        int $limit = 0
    ): ?array {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $query = "SELECT 
            payment_log.*, 
            whitelabel_payment_method.name AS whitelabel_payment_method_name, 
            payment_method.name AS payment_method_name, 
            whitelabel_transaction.type AS wt_type, 
            whitelabel.name, 
            whitelabel.prefix, 
            whitelabel_transaction.token 
        FROM payment_log 
        LEFT JOIN whitelabel_payment_method ON payment_log.whitelabel_payment_method_id = whitelabel_payment_method.id 
        LEFT JOIN payment_method ON payment_log.payment_method_id = payment_method.id 
        LEFT JOIN whitelabel ON whitelabel.id = payment_log.whitelabel_id 
        LEFT JOIN whitelabel_transaction ON whitelabel_transaction.id = whitelabel_transaction_id 
        WHERE 1=1 ";

        $query .= $filters_add . " ";

        $query .= "ORDER BY payment_log.id DESC ";
        $query .= "LIMIT :offset, :limit";

        try {
            $db = DB::query($query);

            foreach ($params as $param => $value) {
                $db->param($param, $value);
            }

            $db->param(":offset", $offset);
            $db->param(":limit", $limit);

            $result = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        return $result;
    }
}
