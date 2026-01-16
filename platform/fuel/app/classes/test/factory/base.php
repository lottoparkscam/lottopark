<?php

use Carbon\Carbon;
use Fuel\Core\DB;
use Fuel\Core\Str;

/**
 * @deprecated - use new fixtures instead
 * Base class for test factories.
 *
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2019-07-06
 * Time: 15:32:58
 */
abstract class Test_Factory_Base
{
    /**
     * True if subsequent with calls should reuse last used values.
     *
     * @var bool
     */
    private static $use_reusable = true;

    // TODO: {Vordis 2019-07-17 16:52:07} probably overkill with disabling, since passing explicit will override reusables
    public static function disable_reusable(): void
    {
        self::$use_reusable = false;
    }

    public static function enable_reusable(): void
    {
        self::$use_reusable = true;
    }

    /**
     * Result of the create.
     *
     * @var array
     */
    protected static $result = [];

    /**
     * Variable for calculation of id.
     *
     * @var int
     */
    private $id; // it is used by reflection

    /**
     * Values used in this chain, they will be tried by subsequent links
     * - so you will not need to pass the same values all over again.
     *
     * @var array
     */
    protected static $reusable_values;

    /**
     * Define name of the columns which should be checked for reusability.
     *
     * @var array
     */
    protected $reusable_columns = [];

    /**
     * Do before creation.
     * You can overwrite this method to implement logic.
     *
     * @param integer $count
     * @param array   $values
     *
     * @return void
     */
    protected function before(int $count, array $values): void
    {
    }

    /**
     * Do after creation.
     * You can overwrite this method to implement logic.
     *
     * @param integer $count
     * @param array   $values
     *
     * @return void
     */
    protected function after(int $count, array $values): void
    {
    }

    protected function next(string $table = null, string $column = 'id'): int
    {
        $table = $table ?: $this->table();
        if ($this->{$column} === null) {
            // fetch from db, since field was not fetched yet
            $this->{$column} =
                DB::select([DB::expr("MAX(`$column`)"), "max_$column"])
                    ->from($table)
                    ->execute()
                    ->as_array()[0]["max_$column"]
                ?? 0;
        }

        return ++$this->{$column};
    }

    private function next_id(): int
    {
        return $this->next();
    }

    private function reusable_values(): array
    {
        $reusables = [];
        if (self::$use_reusable) {
            foreach ($this->reusable_columns as $column) {
                if (isset(self::$reusable_values[$column])) { // NOTE: it will not include nulls (and its fine since they should be defaulted)
                    $reusables[$column] = self::$reusable_values[$column];
                }
            }
        }

        return $reusables;
    }

    public function reusable_unset_last(): void
    {
        array_pop($this->reusable_columns);
        array_pop(self::$reusable_values);
    }

    public static function create(array $values = [], int $count = 1, bool $start_of_chain = true): self
    {
        // clear values cache if it start of new chain
        if ($start_of_chain) {
            self::$reusable_values = $values;
        } else {
            self::$reusable_values = $values + self::$reusable_values; // new values have priority
        }
        $factory = new static;
        $factory->before($count, $values);
        // retrieve values and merge them
        $row_values = $values + $factory->values($values) + $factory->reusable_values();
        $columns = array_keys($row_values);
        // add id if it's not defined 
        $is_id_not_defined = array_search('id', $columns, true) === false;
        if ($is_id_not_defined) {
            $columns[] = 'id';
        }
        $rows = [];
        $define_id = $is_id_not_defined
            ?
            function (int $i) use ($factory, &$rows): void { // add new id
                $rows[$i]['id'] = $factory->next_id();
            }
            : function (): void {
            } // id already define - do nothing
        ;
        // generate values
        for ($i = 0; $i < $count; $i++) {
            foreach ($row_values as $column => $row_value) {
                if ($row_value instanceof \Closure) { // this could be optimized with closures outside of loop but would need slight change of architecture
                    $rows[$i][$column] = $row_value();
                } else {
                    $rows[$i][$column] = $row_value;
                }
            }
            $define_id($i);
        }

        // seed data in database
        DB::insert($factory->table(), $columns)
            ->values($rows)
            ->execute();

        // store created data (rows)
        self::$result[$factory->table()] = isset(self::$result[$factory->table()]) ?
            array_merge(self::$result[$factory->table()], $rows)
            :
            $rows;
        $factory->after($count, $values);

        return $factory;
    }

    /**
     * Create items for current factory (table).
     * NOTE: it will create item for every row of current factory
     *
     * @param string  $class
     * @param array   $values
     * @param integer $count
     *
     * @return static child instance
     */
    public function with(string $class, array $values = [], int $count = 1): self
    {
        foreach (self::$result[$this->table()] as $row) {
            $merged_values = $values + [
                    $this->key() => $row['id']
                ];
            $child_factory = $class::create($merged_values, $count, false);
        }

        return $child_factory; // NOTE: I assume that count is 1 or greater
    }

    /**
     * The same as with, but executing multiple factories with option to pass data between them (from first to last).
     *
     * @param array $data            for multiple with, format = 'Class_Name' => [values => [], count => 1,
     *                               'passed_fields' => []]...
     * @param bool  $pass_id         if set then subsequent factories will pass their ids to the next one. NOTE: that
     *                               3rd factory will have both ids from first and second.
     * @param bool  $last_item_index if you call subsequently this method than set counter to last value (result is
     *                               stored statically)
     *
     * @return static child instance
     */
    public function with_multiple(array $data, bool $pass_id = true, int $last_item_index = 0): self
    {
        $i = $last_item_index;
        foreach (self::$result[$this->table()] as $row) {
            $passed_fields = [];
            foreach ($data as $class => $item) {
                $values = $item['values'] ?? [];
                $count = $item['count'] ?? 1;
                $merged_values = $values + [
                        $this->key() => $row['id']
                    ] + $passed_fields;
                $child_factory = $class::create($merged_values, $count, false);
                if ($pass_id) {
                    $passed_fields[$child_factory->key()] = self::$result[$child_factory->table()][$i]['id'];
                }
                foreach ($item['passed_fields'] ?? [] as $field_name) {
                    $passed_fields[$field_name] = self::$result[$child_factory->table()][$i][$field_name];
                }
            }
            $i++;
        }

        return $child_factory; // NOTE: I assume that count is 1 or greater
    }

    public function with_multiple_without_reusable(array $data, bool $pass_id = true, int $last_item_index = 0): self
    {
        Test_Factory_Base::disable_reusable();
        $factory = $this->with_multiple($data, $pass_id, $last_item_index);
        Test_Factory_Base::enable_reusable();

        return $factory;
    }

    /**
     * Create items for current factory (table).
     * NOTE: it will create item for every row of current factory
     *
     * @param string  $class
     * @param array   $values
     * @param integer $count
     *
     * @return self it differ from with that it will return base factory instead of a child.
     */
    public function with_abstract(string $class, array $values = [], int $count = 1): self
    {
        $this->with($class, $values, $count);

        return $this;
    }

    /**
     * Define values that will be used as base for data mocking.
     *
     * @param array &$values passed by caller.
     *
     * @return array format: ['column1' => value1...'column2' => value2]
     */
    abstract protected function values(array &$values): array;

    private $table = null; // cache

    public function table(): string
    {
        if ($this->table !== null) {
            return $this->table;
        }

        return $this->table = strtolower(substr(static::class, 13)); // omit Test_Factory_ part and cast to lowercase
    }

    public function key(): string
    {
        return $this->table() . '_id';
    }

    // TODO: {Vordis 2019-07-16 18:26:26} string types should be in constants

    /**
     * Random string
     *
     * @param integer $length
     * @param string  $type 'alnum', 'numeric', 'nozero', 'alpha', 'distinct', 'hexdec', 'basic', 'unique', 'sha1',
     *                      'uuid'
     *
     * @return string
     */
    protected function random_string(int $length = 16, string $type = 'alnum'): string
    {
        return Str::random($type, $length);
    }

    protected function random_string_uppercase(int $length = 16, string $type = 'alnum'): string
    {
        return Str::upper(
            $this->random_string($length, $type)
        );
    }

    protected function random_bool(): bool
    {
        return (bool)random_int(0, 1);
    }

    private $timezones = null; // cache

    protected function random_timezone(): string
    {
        $this->timezones = $this->timezones ?: DateTimeZone::listIdentifiers();

        return $this->timezones[random_int(0, count($this->timezones) - 1)];
    }

    protected function random_integer_logic(int $max = 100, int $min = 0): \Closure
    {
        return function () use ($min, $max): int {
            return random_int($min, $max);
        };
    }

    protected function random_items(\Closure $logic, int $count = 1, bool $unique = false, bool $ordered = false, string $order = 'asc'): array
    {
        $items = [];
        for ($i = 0; $i < $count; $i++) {
            $items[] = $logic();
        }
        if ($unique) {
            $items = array_unique($items);
        }
        if ($ordered) {
            if ($order === 'asc') {
                sort($items);
            } else {
                rsort($items);
            }
        }

        return $items;
    }

    protected function random_items_string(\Closure $logic, int $count = 1, bool $unique = false, bool $ordered = false, string $order = 'asc'): string
    {
        return implode(',', $this->random_items($logic, $count, $unique, $ordered, $order));
    }

    protected function random_time($format = "H:i:s"): string
    {
        $date = Carbon::createFromTimeString('last Monday 00:00:00');

        $day = random_int(0, 6);
        $hour = random_int(0, 23);
        $minute = random_int(0, 59);
        $second = random_int(0, 59);

        $date->subDays($day);
        $date->subHours($hour);
        $date->subMinutes($minute);
        $date->subSeconds($second);


        return $date->format($format);
    }

    protected function random_decimal(int $max = 100, int $max_decimal = 99, int $min = 0): string
    {
        return random_int($min, $max) . '.' . random_int(0, $max_decimal);
    }

    /**
     * Get result of the create.
     *
     * @return  array
     */
    public function get_result(): array
    {
        // prune result
        $result = self::$result;
        self::$result = [];

        return $result;
    }
}
