<?php

use Services\Logs\FileLoggerService;

/**
 * @deprecated
 */
class Model_Setting extends Model_Model
{

    /**
     *
     * @var string
     */
    protected static $_table_name = 'setting';

    /**
     *
     * @var array
     */
    public static $cache_list = [
        "all" => "model_setting.all",
        "admin" => "model_setting.admin",
        "task" => "model_setting.task"
    ];

    /**
     *
     * @param string $name
     * @param string $value
     * @return null|int
     */
    public static function update_setting($name, $value):? int
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $query = "UPDATE setting SET value = :value WHERE name = :name";

        try {
            $db = DB::query($query);
            $db->param(":value", $value);
            $db->param(":name", $name);
            $result = $db->execute();
            Lotto_Helper::clear_cache('model_setting');
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }
        
        return $result;
    }

    /**
     *
     * @param string $namespace
     * @return array
     */
    public static function get_settings($namespace = null): array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $expiredTime = Helpers_Whitelabel::get_expired_time();

        $index = "all";
        if ($namespace != null) {
            $index = $namespace;
        }

        $key = self::$cache_list[$index];

        $where = "";
        if ($namespace != null) {
            $where = " WHERE name LIKE :name";
        }

        $query = "SELECT * FROM setting".$where;
        $db = DB::query($query);
        if ($namespace != null) {
            $db->param(":name", $namespace.'%');
        }

        $settings_arr = [];

        try {
            try {
                $settings_arr = Lotto_Helper::get_cache($key);
            } catch (\CacheNotFoundException $e) {
                $settings = $db->execute()->as_array();
                $settings_arr = self::generate_settings_arr($settings);
                Lotto_Helper::set_cache($key, $settings_arr, $expiredTime);
            }
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
            $settings = $db->execute()->as_array();
            $settings_arr = self::generate_settings_arr($settings);
        }

        return $settings_arr;
    }

    /**
     *
     * @param array $settings
     * @return array
     */
    private static function generate_settings_arr($settings): array
    {
        $settings_arr = [];
        foreach ($settings as $setting) {
            // do not store password related data in variable and furthermore - cache
            if (strpos($setting['name'], 'salt') === false &&
                strpos($setting['name'], 'hash') === false &&
                substr($setting['name'], -6) !== "_login"
            ) {
                $settings_arr[$setting['name']] = $setting['value'];
            }
        }
        
        return $settings_arr;
    }

    /**
     *
     * @param string $login
     * @param string $password
     * @return bool
     */
    public static function check_admin_credentials($login, $password): bool
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $res = null;
        $query = "SELECT 
            :login = 
                (
                    SELECT s3.value 
                    FROM setting AS s3 
                    WHERE s3.name = 'admin_login'
                ) 
                AND 
                    SHA2(CONCAT(setting.value,:password), 512) = 
                        (
                            SELECT s2.value 
                            FROM setting AS s2 
                            WHERE s2.name = 'admin_hash'
                        ) AS password_check 
            FROM setting 
            WHERE setting.name = 'admin_salt'";

        try {
            $db = DB::query($query);
            $db->param(":login", $login);
            $db->param(":password", $password);
            $res = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        if ($res !== null && $res[0]['password_check'] == "1") {
            return true;
        }

        return false;
    }

    /**
     *
     * @param string $login
     * @param string $hash
     * @return bool
     */
    public static function check_admin_credentials_hashed($login, $hash): bool
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $res = null;
        $query = "SELECT 
            :login = 
                (
                    SELECT s3.value 
                    FROM setting AS s3 
                    WHERE s3.name = 'admin_login'
                ) 
                AND 
                    :hash = 
                        (
                            SELECT s2.value 
                            FROM setting AS s2 
                            WHERE s2.name = 'admin_hash'
                        ) AS hash_check";

        try {
            $db = DB::query($query);
            $db->param(":login", $login);
            $db->param(":hash", $hash);
            $res = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }
        
        if ($res !== null && $res[0]['hash_check'] == "1") {
            return true;
        }

        return false;
    }

    /**
     *
     * @return null|string
     */
    public static function get_admin_salt():? string
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $res = null;
        $query = "SELECT value 
            FROM setting 
            WHERE name = 'admin_salt'";

        try {
            $db = DB::query($query);
            $res = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        if ($res !== null) {
            $result = $res[0]['value'];
        }

        return $result;
    }
}
