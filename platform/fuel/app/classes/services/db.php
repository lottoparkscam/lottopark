<?php


use Fuel\Core\DB;

/**
 * @deprecated - this class is wrongly designed, please refactor and use DB from /wrappers (add your methods)
 *
 * Class Services_DB created for test purpose
 * You can easly mockup __call method but you cannot mockup static methods
 *
 * @see DB Use all static method as non-static
 *
 * @method query()
 * @method last_query()
 * @method error_info()
 * @method instance()
 * @method select()
 * @method select_array()
 * @method insert()
 * @method update()
 * @method delete()
 * @method expr()
 * @method identifier()
 * @method quote()
 * @method quote_identifier()
 * @method quote_table()
 * @method escape()
 * @method table_prefix()
 * @method list_indexes()
 * @method list_columns()
 * @method list_tables()
 * @method datatype()
 * @method count_records()
 * @method count_last_query()
 * @method set_charset()
 * @method in_transaction()
 */
class Services_DB
{
    /**
     * @return mixed
     */
    public function __call(string $method, array $arguments)
    {
        return call_user_func_array([DB::class, $method], $arguments);
    }

    /**
     * @param string $name
     * @param array $arguments
     * @throws Exception
     */
    public static function __callStatic(string $name, array $arguments)
    {
        throw new Exception('Use only non-static methods');
    }

    public function start_transaction($db = null): void
    {
        DB::start_transaction($db);
    }

    public function rollback_transaction($db = null, $rollback_all = true): void
    {
        DB::rollback_transaction($db, $rollback_all);
    }

    public function commit_transaction($db = null): void
    {
        DB::commit_transaction($db);
    }
}
