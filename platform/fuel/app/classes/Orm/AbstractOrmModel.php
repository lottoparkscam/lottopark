<?php

namespace Classes\Orm;

use Container;
use Exception;
use Helpers\CaseHelper;
use Orm\Model;
use Orm\Query;
use Orm\HasOne;
use Orm\HasMany;
use Orm\ManyMany;
use Carbon\Carbon;
use Orm\BelongsTo;
use ReflectionClass;
use RuntimeException;
use Orm\RecordNotFound;
use BadMethodCallException;
use Doctrine\Inflector\Inflector;
use Classes\Orm\Traits\Model_Orm_Traits_Castable;
use Classes\Orm\Criteria\By\Model_Orm_Criteria_By_Id;
use Classes\Orm\Criteria\With\Model_Orm_Criteria_With_Relation;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Classes\Orm\Criteria\Model_Orm_Criteria_Order;

/**
 * @property-read mixed $tiers
 * @property-read int $raffle_id
 * @property-read int $id
 * @property-read int $currency_id
 */
abstract class AbstractOrmModel extends Model implements OrmModelInterface
{
    use Model_Orm_Traits_Castable;

    public const BELONGS_TO = 'belongsTo';
    public const HAS_ONE = 'haveOne';
    public const HAS_MANY = 'haveMany';

    const CAST_INT = 'integer';
    const CAST_BOOL = 'boolean';
    const CAST_ARRAY = 'array';
    const CAST_STRING = 'string';
    const CAST_CARBON = 'carbon';
    const CAST_DATETIME = 'datetime';
    const CAST_FLOAT = 'float';

    public const CAST_TYPES = [
        self::CAST_INT,
        self::CAST_BOOL,
        self::CAST_ARRAY,
        self::CAST_STRING,
        self::CAST_CARBON,
        self::CAST_DATETIME,
        self::CAST_FLOAT,
    ];

    /** @var Query */
    protected $query;

    protected static bool $_cache_disabled = false;
    protected static bool $_global_cache_enabled = true;
    protected static array $_belongs_to = [];
    protected static array $_has_many = [];
    protected static array $_has_one = [];

    final public function __construct($data = [], $new = true, $view = null, $cache = true)
    {
        if (!empty($this->relations)) {
            self::createRelations($this->relations);
        }

        parent::__construct($data, $new, $view, $cache);
        $this->create_base_query();
        if (empty($this->query)) {
            throw new RuntimeException('No base query initialized. Probably you overwritten "create_base_query" method and didnt reassigned query isntance.');
        }
    }

    public function __isset($propertyName)
    {
        $propertyName = CaseHelper::camelToSnake($propertyName);
        return parent::__isset($propertyName);
    }

    public function getTimezoneForField(string $fieldName): string
    {
        $defaultTimezone = 'UTC';

        if (empty($this->timezones[$fieldName])) {
            return $defaultTimezone;
        }

        if (is_string($this->timezones[$fieldName])) {
            return $this->timezones[$fieldName];
        }

        if (!is_array($this->timezones[$fieldName])) {
            return $defaultTimezone;
        }

        $foundTimezone = $this->findTimezone($this->timezones[$fieldName], $this);

        if (empty($foundTimezone)) {
            return $defaultTimezone;
        }

        return $foundTimezone;
    }

    /**
     * Get the class's relations
     *
     * @param bool $specific
     * @return  HasOne|HasMany|ManyMany|BelongsTo|HasOne[]|HasMany[]|ManyMany[]|BelongsTo[]
     */
    public static function relations($specific = false)
    {
        $reflection = new ReflectionClass(static::class);
        $relationsPropertyExists = $reflection->hasProperty('relations');

        if ($relationsPropertyExists) {
            $relations = $reflection->getDefaultProperties()['relations'];
            self::createRelations($relations);
        }

        return parent::relations($specific);
    }

    public static function isCachingEnabled(): bool
    {
        return !static::$_cache_disabled;
    }

    public static function setGlobalCaching($enabled = true): void
    {
        static::$_global_cache_enabled = $enabled;
    }

    private static function createRelations(array $relations): void
    {
        /**
         * @var AbstractOrmModel $modelClass
         * @var string $type
         */
        foreach ($relations as $modelClass => $type) {
            $modelTableName = $modelClass::get_table_name();
            $tableName = self::get_table_name();

            switch ($type) {
                case static::BELONGS_TO:
                    if (key_exists($modelTableName, static::$_belongs_to)) {
                        break;
                    }
                    static::$_belongs_to = array_merge(static::$_belongs_to, [
                        $modelTableName => [
                            'key_from' => "{$modelTableName}_id",
                            'model_to' => $modelClass,
                            'key_to'   => 'id'
                        ]
                    ]);
                    break;
                case static::HAS_ONE:
                    if (key_exists($modelTableName, static::$_has_one)) {
                        break;
                    }
                    static::$_has_one = array_merge(static::$_has_one, [
                        $modelTableName => [
                            'key_from' => 'id',
                            'model_to' => $modelClass,
                            'key_to'   => "{$tableName}_id"
                        ]
                    ]);
                    break;
                case static::HAS_MANY:
                    if (key_exists($modelTableName, static::$_has_many)) {
                        break;
                    }
                    // It can be used before initialise an object so we cannot do it by DI
                    $inflector = Container::get(Inflector::class);
                    $pluralModelTableName = $inflector->pluralize($modelTableName);
                    static::$_has_many = array_merge(static::$_has_many, [
                        $pluralModelTableName => [
                            'key_from' => 'id',
                            'model_to' => $modelClass,
                            'key_to'   => "{$tableName}_id"
                        ]
                    ]);
                    break;
            }
        }
    }

    protected function string(string $fieldValue, string $property): string
    {
        return $fieldValue;
    }

    /**
     * @param string|null $fieldValue
     * @param string $property
     * @return Carbon|string|null
     */
    protected function carbon(?string $fieldValue, string $property)
    {
        if (empty($fieldValue)) {
            return null;
        }

        if ($fieldValue === 'NOW()') {
            return $fieldValue;
        }

        $timezone = $this->getTimezoneForField($property);
        return Carbon::parse($fieldValue, $timezone);
    }

    private function findTimezone(array $where, AbstractOrmModel $context): ?string
    {
        $timezone = null;

        if (count($where) < 1) {
            return null;
        }

        $singleFieldName = $where[0];

        if (count($where) === 1 && empty($context->$singleFieldName)) {
            return null;
        }

        if (count($where) === 1) {
            return $context->$singleFieldName;
        }

        $class = $where[0];
        $relationTableName = $class::get_table_name();

        if (empty($context->$relationTableName)) {
            return null;
        }

        $fieldName = $where[1];
        $isNotDeep = count($where) === 2;
        if ($isNotDeep && empty($context->$relationTableName->$fieldName)) {
            return null;
        }

        if ($isNotDeep) {
            return $context->$relationTableName->$fieldName;
        }

        array_shift($where);

        return $this->findTimezone($where, $context->$relationTableName);
    }

    protected function create_base_query(): void
    {
        $this->query = static::query();
    }

    /**
     * Called to fetch current dao instance and access
     * data in more OOP way.
     * The init method should provide default starting point for query builder
     * as for example limitation to given raffle_id.
     *
     * @return static
     */
    public static function dao()
    {
        return new static();
    }

    /**
     * @param Model_Orm_Criteria $criteria
     *
     * @return $this
     */
    final public function push_criteria(Model_Orm_Criteria $criteria): AbstractOrmModel
    {
        $criteria->apply($this->query);
        return $this;
    }

    /**
     * @param Model_Orm_Criteria[] $criterias
     *
     * @return $this
     */
    final public function push_criterias(array $criterias): AbstractOrmModel
    {
        foreach ($criterias as $criteria) {
            $criteria->apply($this->query);
        }
        return $this;
    }

    /**
     * @return static
     * @throws RecordNotFound
     */
    final public function get_one(): AbstractOrmModel
    {
        $result = $this->get_results();
        if (empty($result)) {
            throw new RecordNotFound(sprintf('Record with given criterias for <%s> class has not been found', get_called_class()));
        }
        $this->reset_query();
        return reset($result);
    }

    /**
     * @return static|null
     */
    final public function find_one(): ?AbstractOrmModel
    {
        $result = $this->get_results();
        if (empty($result)) {
            return null;
        }
        $this->reset_query();
        return reset($result);
    }

    /**
     * @param int|null $limit
     * @param int $offset
     *
     * @return static[]|array
     */
    final public function get_results(int $limit = null, int $offset = 0): array
    {
        if ($limit) {
            $this->query->limit($limit)->offset($offset);
        }
        $result = $this->query->get();
        $this->reset_query();
        return array_values($result);
    }

    /**
     * Count the result of a query
     *
     * @param   bool|string  $column   False for random selected column or specific column, only works for main model
     * currently
     * @param   bool  $distinct True if DISTINCT has to be aded to the query
     *
     * @return  int|bool   number of rows OR false
     */
    final public function getCount($column = null, $distinct = true, bool $shouldResetQuery = false)
    {
        $result = $this->query->count($column, $distinct);

        if ($shouldResetQuery) {
            $this->reset_query();
        }

        return $result;
    }

    final public function getIntCount($column = null, $distinct = true, bool $shouldResetQuery = false): int
    {
        return $this->getCount($column, $distinct, $shouldResetQuery);
    }

    /**
     * Checks if given criteria contains any results
     *
     * @return bool
     */
    final public function has_results(): bool
    {
        $results = $this->get_results();
        $this->reset_query();
        return !empty($results);
    }

    /**
     *  Cleans up query after result is fetched.
     *  We need to ensure that unexpected criteria will
     *  be active in multiple Instance calls.
     */
    public function reset_query(): void
    {
        if (empty(self::$_global_cache_enabled) || !empty(static::$_cache_disabled)) {
            self::flush_cache();
        }

        $this->query = static::query();
    }

    /**
     * @param array $props
     * @param array $where
     *
     * @return self
     */
    final public static function first_or_create(array $props, array $where = []): self
    {
        $query = static::query();
        $model = null;

        if (empty($where) === false) {
            $query->where($where);
            $model = $query->get_one();
        }

        if (empty($model)) {
            $model = new static($props);
        }
        foreach ($props as $prop => $value) {
            $model->$prop = $value;
        }
        return $model;
    }

    /**
     * CRITICAL
     * We have to temporary disable casting due
     * parent save class can't handle real data on save.
     *
     * @param mixed $cascade
     * @param bool $use_transaction
     *
     * @return bool
     * @throws Exception
     */
    final public function save($cascade = null, $use_transaction = false): bool
    {
        $this->disable_casting();
        $result = parent::save($cascade, $use_transaction);
        $this->enable_casting();
        return $result;
    }

    /**
     * Differ from save that it will throw if save returned false (no need for check).
     * Save the object and it's relations, create when necessary
     *
     * @param  mixed  $cascade
     *     null = use default config,
     *     bool = force/prevent cascade,
     *     array cascades only the relations that are in the array
     * @throws Exception on db exception or save returning false
     */
    public function saveOrThrow($cascade = null, bool $use_transaction = false): void
    {
        $notSuccessful = !parent::save($cascade, $use_transaction);
        if ($notSuccessful) {
            throw new \Exception("Unable to save model: " . static::class);
        }
    }

    /**
     * @return bool
     */
    final public function create()
    {
        $this->disable_casting();
        $result = parent::create();
        $this->enable_casting();
        return $result;
    }

    /**
     * @return bool
     */
    final public function update()
    {
        $this->disable_casting();
        $result = parent::update();
        $this->enable_casting();
        return $result;
    }

    public function disable_casting(): self
    {
        $this->is_casting_enabled = false;
        return $this;
    }

    public function enable_casting(): self
    {
        $this->is_casting_enabled = true;
        return $this;
    }

    /**
     * Method tries to reload persisted model by its primary key - from db.
     */
    final public function reload(): void
    {
        $id = static::$_primary_key;
        if (sizeof($id) !== 1) {
            throw new BadMethodCallException('Model should have exactly one primary key to be reloaded');
        }

        $id = reset($id);
        if (!isset($this->$id) || empty($this->$id)) {
            throw new BadMethodCallException('Model must be persisted to access this action');
        }

        $reloaded = static::query()->where(['id', '=', $this->$id])->get();
        $this->_data = $reloaded[$this->$id]->to_array();
    }

    /**
     * Returns one result by id with relations if passed any.
     * Pass just relation name or dot nested values.
     *
     * @param int $id
     * @param array $relations
     *
     * @return static
     */
    public function get_by_id(int $id, array $relations = []): self
    {
        $this->push_criteria(new Model_Orm_Criteria_By_Id($id));
        foreach ($relations as $relation) {
            $this->push_criteria(new Model_Orm_Criteria_With_Relation($relation));
        }
        return $this->get_one();
    }

    public function exists($value, string $field = 'id'): bool
    {
        return !empty($this->push_criteria(new Model_Orm_Criteria_Where($field, $value))->find_one());
    }

    /**
     * This is wrapper for model save method.
     * It's is necessary to make code Unit Testable..
     *
     * @param OrmModelInterface $model
     */
    public function store(OrmModelInterface $model): void
    {
        $model->save();
    }

    public function order_by(string $sort_field = 'draw_date', string $sort_order = 'desc'): self
    {
        return $this->push_criteria(new Model_Orm_Criteria_Order($sort_field, $sort_order));
    }

    public static function get_table_name(): string
    {
        return static::$_table_name;
    }

    /**
     * Use this function to fetch uncasted properties.
     * @return mixed
     */
    public function propertyRaw(string $name)
    {
        $this->disable_casting();
        $value = $this->{$name};
        $this->enable_casting();
        return $value;
    }
}
