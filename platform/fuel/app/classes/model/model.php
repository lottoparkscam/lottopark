<?php

use Fuel\Core\Database_Query;
use Fuel\Core\Database_Query_Builder_Select;
use Fuel\Core\Database_Result;
use Fuel\Core\DB;
use Fuel\Core\Model_Crud;
use Services\Logs\FileLoggerService;

/**
 * @deprecated
 */
abstract class Model_Model extends Model_Crud
{ // TODO: @Vordis - this object needs more work (I'm not satisfied with it's shape).

    /** @var array */
    private $_changed_data;

    /** @var array */
    private $_temporary_data;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->_changed_data = [];
    }

    /**
     * After each find method _changed_data are cleared
     * @param Model_Model[]|null $results
     * @return Model_Model[]|null
     */
    protected static function post_find($results)
    {
        if (is_array($results))
        {
            foreach($results as $result)
            {
                $result->_changed_data = [];
            }
        }

        return parent::post_find($results);
    }

    private function prepare_temporary_data(): void
    {
        $this->_temporary_data = [];

        foreach ($this->_data as $property => $value)
        {
            $this->_temporary_data[$property] = $value;
        }
    }

    private function change_data_to_changed_only(): void
    {
        $this->prepare_temporary_data();

        $id = $this->_data['id'];

        $changed_data = [];

        foreach ($this->_changed_data as $property)
        {
            $changed_data[$property] = $this->_data[$property];
        }

        $this->_data = $changed_data;
        $this->_data['id'] = $id;
    }

    private function rollback_data(): void
    {

        $this->_data = [];

        foreach ($this->_temporary_data as $property => $value)
        {
            $this->_data[$property] = $value;
        }

        $this->_temporary_data = [];
    }

    private function clear_changes(): void
    {
        $this->_changed_data = [];
    }

    private function prepare_diffs(string $property, $value): void
    {
        $differences_exists = !isset($this->_data[$property]) || $this->_data[$property] !== $value;

        if ($differences_exists)
        {
            $this->_changed_data[] = $property;
        }
    }

    public function __set($property, $value)
    {
        $this->prepare_diffs($property, $value);

        parent::__set($property, $value);
    }

    public function set(array $data)
    {
        foreach ($data as $property => $value)
        {
            $this->prepare_diffs($property, $value);
        }

        return parent::set($data);
    }

    public function save($validate = true)
    {
        if ($this->is_new())
        {
            $this->clear_changes();
            return parent::save($validate);
        }

        $this->change_data_to_changed_only();

        $result = parent::save($validate);

        $this->rollback_data();

        if ($result)
        {
            $this->clear_changes();
        }

        return $result;
    }

    /**
     * TODO: cache handling needs improvement - unlimited lifetime and add method for removal + call it where necessary.
     * Execute cached query.
     * @param string $query query.
     * @param string $key cache key.
     * @param array $params params of query. Note: Keys should correspond to query. e.g. array ("id" => 1) for query with :id.
     * @return array query result from cache or database or NULL on error.
     */
    protected static function execute_cached_query($query, $key, $params)
    {
        // set time for cache
        $expiration_time = 86400; // 60 * 60 * 24
        // create database object with query
        $db = DB::query($query);
        // add to database object params
        foreach ($params as $key => $value) {
            $db->param(":$key", $value);
        }

        // read from cache
        $result = self::read_from_cache($key);
        // validate
        if ($result === null) {
            // cache is not found - need to read from db.
            $result = self::fetch_from_database($db);
            // validate query result
            if ($result === null) {
                return null;
            }
            // query ok, set it to cache
            Lotto_Helper::set_cache($key, $result, $expiration_time);
        }
        // return value
        return $result;
    }

    /**
     * Read from cache.
     * @param string $key key, under which query result was stored.
     * @return array query result stored in cache or null if there is no cached result.
     */
    private static function read_from_cache($key)
    {
        try {
            // try to get value from cache
            return Lotto_Helper::get_cache($key);
        } catch (\CacheNotFoundException $e) {
            // cache not found e.g expired
            return null;
        }
    }

    /**
     * Fetch query result from database.
     * @param DB $db DB::query object.
     * @return array query result or null if there was error.
     */
    private static function fetch_from_database($db)
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        try {
            // try to fetch from db.
            /** @var object $db */
            return $db->execute()->as_array();
        } catch (\Exception $e) {
            // log db error
            $fileLoggerService->error(
                $e->getMessage()
            );
        }
        return [];
    }

    /**
     * Create key for cached query.
     * @param string $general_key general key, should be from array in model.
     * @param mixed $params query params, format them if necessary.
     * @return string key.
     */
    protected static function create_key($general_key, $params)
    {
        // add all params preceded by dot to general key.
        foreach ($params as $param) {
            $general_key .= '.' . $param;
        }
        return $general_key;
    }

    /**
     * Execute query. Note method is safe - it won't produce exceptions.
     * @param string $query_string sql query.
     * @param array $params params to be attached to query array(key, value).
     * @return Database_Query|null query result or null.
     */
    protected static function execute_query(string $query_string, array $params)
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        // create db object.
        $query = DB::query($query_string);

        // attach params.
        foreach ($params as $param) { // filters
            $query->param($param[0], $param[1]);
        }

        // try to execute query.
        try {
            return $query->execute();
        } catch (\Exception $e) { // save log and return null
            $fileLoggerService->error(
                $e->getMessage()
            );
            return null;
        }
    }

    /**
     * Safely read array from query result.
     * @param Database_Query|null $result result of the query.
     * @param mixed $default value to be returned, when result is invalid or empty.
     * @return mixed array or default.
     */
    protected static function get_array_result($result, $default)
    {
        // validate
        /** @var object $result */
        if (!isset($result) || empty($result->as_array())) {
            // return default value on query error/empty.
            return $default;
        }
        // return array if valid.
        return $result->as_array();
    }

    /**
     * Safely read array item from query result.
     * @param Database_Query|null $result result of the query.
     * @param mixed $default value to be returned, when row is invalid or empty.
     * @param mixed $key key of the row.
     * @return mixed array or default.
     */
    protected static function get_array_result_row($result, $default, $key)
    {
        // validate
        /** @var object $result */
        if (!isset($result) || empty($result->as_array()[$key])) { // empty works fine for undefined keys
            // return default value on query error/empty.
            return $default;
        }
        // return row if valid.
        return $result->as_array()[$key];
    }

    /**
     * Safely read array item from query result.
     * @param Database_Query|null $result result of the query.
     * @param mixed $default value to be returned, when row is invalid or empty.
     * @param mixed $key_row key of the row.
     * @param mixed $key_item key of the item.
     * @return mixed array or default.
     */
    protected static function get_array_result_item($result, $default, $key_row, $key_item)
    {
        // validate
        /** @var object $result */
        if (!isset($result) || empty($result->as_array()[$key_row][$key_item])) { // empty works fine for undefined keys
            // return default value on query error/empty.
            return $default;
        }
        // return row if valid.
        return $result->as_array()[$key_row][$key_item];
    }

    /**
     * Get next batch from select query.
     *
     * @param Database_Query_Builder_Select $query
     * @param integer $batch_size
     * @param integer $offset
     * @return Database_Result
     */
    private static function next_batch(Database_Query_Builder_Select $query, int $batch_size, int $offset): Database_Result
    {
        return $query
            ->limit($batch_size)
            ->offset($offset)
            ->execute();
    }

    /**
     * Chunk select query - execute in batches.
     *
     * @param Database_Query_Builder_Select $query query to execute in batches.
     * @param integer $batch_size size of the batch.
     * @param \Closure $process process logic function (Database_Result $batch, bool $is_last_batch): void
     * @return void
     */
    public static function chunk(Database_Query_Builder_Select $query, int $batch_size, \Closure $process): void
    { // TODO: {Vordis 2019-07-03 13:11:44} for perfect performance we could write second method dedicated for last_batch checks, but it's probably overkill
        for (
            $offset = 0, $batch = self::next_batch($query, $batch_size, $offset);
            ($count = $batch->count()) > 0; // proceed with not empty results
            $offset += $batch_size, $batch = self::next_batch($query, $batch_size, $offset)
        ) {
            $is_last_batch = $count !== $batch_size;
            $process($batch, $is_last_batch);
            if ($is_last_batch) {
                break; // we can finish faster here, if this is the last batch (instead of querying database)
            }
        }
    }

    /**
     * Chunk select query - execute in batches.
     * WARNING! infinity loop possibility
     * NOTE: this is custom method, should be used with care (it assume that your batch processing is removing last batch from database)
     * e.g. we are synchronizing tickets 100 at a time, query for it is based on is_synchronized = 0, but batch processing interferes with
     * it by setting is_synchronized to 1, which means that normal chunking would process every second batch only.
     *
     * @param Database_Query_Builder_Select $query query to execute in batches.
     * @param integer $batch_size size of the batch.
     * @param \Closure $process process logic function (Database_Result $batch, bool $is_last_batch): void
     * @param bool $check_for_last_batch true if chunking should automatically break on last batch (saves 1 query). IMPORTANT should be used only when you process whole batch.
     * @return void
     */
    public static function chunk_no_offset(Database_Query_Builder_Select $query, int $batch_size, \Closure $process, bool $check_for_last_batch = true): void
    { // TODO: {Vordis 2019-07-03 13:11:44} for perfect performance we could write second method dedicated for last_batch checks, but it's probably overkill

        $set_is_last_batch = $check_for_last_batch ?
            function (int $count) use ($batch_size): bool {
                return $count !== $batch_size;
            } : function (): bool {
                return false; // check disabled
            };
        for (
            $batch = self::next_batch($query, $batch_size, 0);
            ($count = $batch->count()) > 0; // proceed with not empty results
            $batch = self::next_batch($query, $batch_size, 0)
        ) {
            $is_last_batch = $set_is_last_batch($count);
            $process($batch, $is_last_batch);
            if ($is_last_batch) {
                break; // we can finish faster here, if this is the last batch (instead of querying database)
            }
        }
    }

    /**
     * @throws \Throwable database exception or unexpected fatals.
     */
    public static function execute_raw_or_fail(Database_Query $database_query): Database_Result
    {
        return $database_query->execute();
    }

    /**
     * @return Database_Result|null instead of throwing exception db failures and fatals will be logged and null will be returned.
     */
    public static function execute_raw(Database_Query $database_query): ?Database_Result
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        try {
            return self::execute_raw_or_fail($database_query);
        } catch (\Throwable $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
            return null;
        }
    }

    public static function getTableName(): string
    {
        return static::$_table_name;
    }
}
