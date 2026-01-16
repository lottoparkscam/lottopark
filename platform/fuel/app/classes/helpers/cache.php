<?php

use Fuel\Core\Cache;
use Fuel\Core\CacheNotFoundException;
use Fuel\Core\Database_Query;
use Helpers\StringHelper;
use Services\Logs\FileLoggerService;

/** @deprecated use CacheService instead */
final class Helpers_Cache
{
    const EXPIRATION_TIME = 86400; // 60s * 60m * 24h

    private static function delete_item(string $identifier, bool $call_delete_all): void
    {
        Cache::delete($identifier);
        if ($call_delete_all) {
            Cache::delete_all($identifier);
        }
    }

    /**
     * Reset cache for model.
     *
     * @param string $model e.g. Model_Lottery::class
     * @param boolean $call_delete_all true if Cache::delete_all($identifier) should be called after Cache::delete($identifier)
     * @return string
     */
    public static function reset_model(string $model, bool $call_delete_all = false): string
    {
        $identifier = strtolower($model);
        self::delete_item($identifier, $call_delete_all);
        return $identifier;
    }

    /**
     * Reset cache for table.
     *
     * @param string $table e.g. lottery
     * @param boolean $call_delete_all true if Cache::delete_all($identifier) should be called after Cache::delete($identifier)
     * @return string
     */
    public static function reset_table(string $table, bool $call_delete_all = false): string
    {
        $identifier = "model_$table";
        self::delete_item($identifier, $call_delete_all);
        return $identifier;
    }

    public static function reset_models(string ...$models): void
    {
        foreach ($models as $model) {
            self::reset_model($model);
        }
    }

    public static function reset_models_using_all(string ...$models): void
    {
        foreach ($models as $model) {
            self::reset_model($model, true);
        }
    }

    private static function query_to_unique_seed(Database_Query &$database_query): string
    {
        $sql = $database_query->compile();
        $asci_items = unpack("C*", $sql);
        $counter = 0.0001;
        $seed = 0;
        foreach ($asci_items as $asci_item) {
            $seed += $asci_item * $counter;
            $counter += 0.0001;
        }
        return str_replace('.', '_', sprintf('%.4F', $seed)); // cast and round float to string, replace dot with underscore
    }

    /**
     * If value is not found in cache then it will be fetched from database and stored in cache.
     *
     * @param Database_Query $database_query
     * @param Closure $process_before_set optional function (array &$query_result): void
     * @param int $expiration_time
     *
     * @return array|null null only if failed to read from cache AND failed to read from database.
     */
    public static function read_or_create(Database_Query $database_query, Closure $process_before_set = null, int $expiration_time = self::EXPIRATION_TIME): ?array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        try {
            $caller_info = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
            $model_name = strtolower($caller_info['class']);
            $function_name = strtolower($caller_info['function']);
            $unique_seed = self::query_to_unique_seed($database_query);
            $key = StringHelper::dot_notate($model_name, $function_name, $unique_seed);
            $value_read = Cache::get($key);
            if ($value_read === null) throw new \Exception("Invalid value found"); // NOTE: just in case, it normally shouldn't happen (unless null was saved to cache)
            return $value_read;
        } catch (CacheNotFoundException $e) {
        } catch (\Throwable $e) { // FATALS, should be logged.
            $fileLoggerService->error(
                $e->getMessage() . " key=$key"
            );
        }

        $value_from_database = Model_Model::execute_raw($database_query);
        $failed_to_read_from_database = $value_from_database === null;
        if ($failed_to_read_from_database) {
            return null;
        }
        try {
            $value_from_database = $value_from_database->as_array();
            $should_process_before_set = $process_before_set !== null;
            if ($should_process_before_set) {
                $process_before_set($value_from_database);
            }
            Cache::set($key, $value_from_database, $expiration_time); // NOTE: throws at least InvalidArgumentException (invalid timeout)
        } catch (\Throwable $e) {
            $fileLoggerService->error(
                $e->getMessage() . " key=$key"
            );
        }

        return $value_from_database;
    }

    /** CacheKey cannot contain numbers so it has to be changed */
    public static function changeNumbersInCacheKeyToLetters(string $cacheKey){
        $valuesToChange = [
            '0' => 'a',
            '1' => 'b',
            '2' => 'c',
            '3' => 'd',
            '4' => 'e',
            '5' => 'f',
            '6' => 'g',
            '7' => 'h',
            '8' => 'i',
            '9' => 'j'
        ];
        
        return strtr($cacheKey, $valuesToChange);
    }
}
