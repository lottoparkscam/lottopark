<?php


namespace Wrappers;

use Exception;
use Fuel\Core\Database_Expression;
use Fuel\Core\Database_Query;
use Fuel\Core\Database_Query_Builder;
use Fuel\Core\Database_Query_Builder_Delete;
use Fuel\Core\Database_Query_Builder_Select;
use Fuel\Core\Database_Query_Builder_Update;
use Fuel\Core\Database_Query_Builder_Where;

/**
 * @codeCoverageIgnore
 */
class Db
{
    /**
     * @param null $table
     *
     * @return Database_Query_Builder_Update
     */
    public function update($table = null): Database_Query_Builder_Update
    {
        return \Fuel\Core\DB::update($table);
    }

    public function delete($table = null): Database_Query_Builder_Delete
    {
        return \Fuel\Core\DB::delete($table);
    }

    /**
     * Create a new [Database_Query_Builder_Select]. Each argument will be
     * treated as a column. To generate a `foo AS bar` alias, use an array.
     *
     *     // SELECT id, username
     *     $query = DB::select('id', 'username');
     *
     *     // SELECT id AS user_id
     *     $query = DB::select(array('id', 'user_id'));
     *
     * @param mixed $args column name or array($column, $alias) or object
     *
     * @return Database_Query_Builder_Select
     */
    public function select($args = null): Database_Query_Builder_Select
    {
        $argsArray = func_get_args();
        $class = '\\Fuel\\Core\\DB';
        $method = 'select';
        return call_user_func_array("$class::$method", $argsArray);
    }


    /**
     * Create a new [Database_Query_Builder_Select] from an array of columns.
     *
     *     // SELECT id, username
     *     $query = DB::select_array(array('id', 'username'));
     *
     * @param array|null $columns
     * @return  Database_Query_Builder_Select
     */
    public function selectArray(array $columns = null): Database_Query_Builder_Select
    {
        return \Fuel\Core\DB::select_array($columns);
    }

    public function expr(string $expr): Database_Expression
    {
        return \Fuel\Core\DB::expr($expr);
    }

    /**
     * @param string $name
     * @param array $arguments
     *
     * @throws Exception
     */
    public static function __callStatic(string $name, array $arguments)
    {
        throw new Exception('Use only non-static methods');
    }

    public function inTransaction(callable $callback, callable $errorCallback = null): bool
    {
        $this->start_transaction();
        try {
            $callback();
            $this->commit_transaction();
        } catch (Exception $exception) {
            $this->rollback_transaction();
            if (!empty($errorCallback)) {
                $errorCallback($exception);
            }
            return false;
        }
        return true;
    }

    public function start_transaction($db = null): void
    {
        \Fuel\Core\DB::start_transaction($db);
    }

    public function rollback_transaction($db = null, $rollback_all = true): void
    {
        \Fuel\Core\DB::rollback_transaction($db, $rollback_all);
    }

    public function commit_transaction($db = null): void
    {
        \Fuel\Core\DB::commit_transaction($db);
    }

    /**
     * Create a new [Database_Query] of the given type.
     *
     *     // Create a new SELECT query
     *     $query = DB::query('SELECT * FROM users');
     *
     *     // Create a new DELETE query
     *     $query = DB::query('DELETE FROM users WHERE id = 5');
     *
     * Specifying the type changes the returned result. When using
     * `DB::SELECT`, a [Database_Query_Result] will be returned.
     * `DB::INSERT` queries will return the insert id and number of rows.
     * For all other queries, the number of affected rows is returned.
     *
     * @param string $sql   SQL statement
     * @param int|null $type
     *
     * @return Database_Query
     */
    public function query(string $sql, ?int $type = null): Database_Query
    {
        return \Fuel\Core\DB::query($sql, $type);
    }

    public function lastQuery($db = null): mixed
    {
        return \Fuel\Core\DB::last_query($db);
    }
}
