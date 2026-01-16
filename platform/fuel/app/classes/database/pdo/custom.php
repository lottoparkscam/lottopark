<?php

use Services\Logs\FileLoggerService;

/**
 * Redirects Fuel Database_Query into custom pdo.
 */
class Database_Pdo_Custom
{
    /**
     * Get unbuffered pdo instance (minimised memory usage).
     * @param \Fuel\Core\Database_Query $database_query Fuel query object.
     * @return PDOStatement|null null on failure (exception).
     */
    public static function unbuffered(\Fuel\Core\Database_Query $database_query): ?PDOStatement
    {
        return self::pdo($database_query, [PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false]);
    }

    /**
     * Get custom pdo instance, based on prepared fuel query.
     * @param \Fuel\Core\Database_Query $database_query Fuel query object.
     * @param array $options config for pdo instance.
     * @return PDOStatement|null null on failure (exception).
     */
    public static function pdo(\Fuel\Core\Database_Query $database_query, array $options = []): ?PDOStatement
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        try {
            // create pdo instance based on config
            $connection_data = \Fuel\Core\Config::get('db')['default']['connection'];
            $pdo = new PDO($connection_data["dsn"], $connection_data["username"], $connection_data["password"], $options);

            // return statement built upon provided query.
            return $pdo->query($database_query);
        } catch (\Exception $ex) {
            $fileLoggerService->error($ex->getMessage());
            return null;
        }
    }
}
