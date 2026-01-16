<?php

namespace Classes\Orm\Traits;

use Carbon\Carbon;
use Orm\Model;
use Throwable;
use Orm\HasMany;
use Fuel\Core\Date;
use ReflectionClass;
use Helpers\CaseHelper;
use BadMethodCallException;
use Classes\Orm\AbstractOrmModel;
use Services\Logs\FileLoggerService;
use Container;
use Exception;

trait Model_Orm_Traits_Castable
{
    /**
     * Called on __get or __set methods.
     * Key (property) => function name (this class method, native function or built in - special cases).
     *
     * @var array
     */
    protected $casts = [];

    /**
     * Allows to disable casting (small hack to allow ORM __set function works).
     *
     * @var bool
     */
    private $is_casting_enabled = true;

    /**
     * Adds mutator's for Model in Laravel's way.
     *
     * @param mixed $property
     *
     * @return mixed
     */
    public function &__get($property)
    {
        if (!array_key_exists($property, get_object_vars($this))) {
            $property = CaseHelper::camelToSnake($property);
        }

        # Laravel's style attributes
        $method = sprintf('get_%s_attribute', $property);
        if (method_exists($this, $method)) {
            $fieldValue = $this->{$method}();
            return $fieldValue;
        }

        $relationExists = !empty(static::$_belongs_to[$property]) ||
            !empty(static::$_has_many[$property]) ||
            !empty(static::$_has_one[$property]);

        // get previous downloaded relation if exists
        if (
            $relationExists &&
            !empty(self::$_global_cache_enabled) &&
            method_exists($this, 'relations') &&
            ($rel = static::relations($property)) &&
            !($rel instanceof HasMany)
        ) {
            $objectFromCache = Model::cached_object($this->{$rel->key_from[0]}, $rel->model_to);
            $relationReflectionClass = new ReflectionClass($rel->model_to);
            if ($relationReflectionClass->newInstanceWithoutConstructor()::isCachingEnabled() && !empty($objectFromCache)) {
                $this->_data_relations[$property] = $objectFromCache;
                $this->_update_original_relations(array($property));
                $result = &$this->_data_relations[$property];

                // do we need to clean before returning the result?
                if ($this->_sanitization_enabled) {
                    $result = $this->_sanitize($property, $result);
                }

                return $result;
            }
        }

        $fieldValue = parent::__get($property);

        if ($this->is_casting_enabled === false) {
            return $fieldValue;
        }

        if (is_null($fieldValue)) {
            return $fieldValue;
        }

        # property casting
        if (array_key_exists($property, $this->casts)) {
            $propertyType = $this->casts[$property];

            if (method_exists($this, $propertyType)) {
                $fieldValue = $this->{$propertyType}($fieldValue, $property);
                return $fieldValue;
            }

            if (in_array($propertyType, ['date', AbstractOrmModel::CAST_DATETIME])) {
                $format = $propertyType === 'datetime' ? 'mysql' : 'mysql_date';
                $fieldValue = Date::create_from_string($fieldValue, $format);
                return $fieldValue;
            }

            switch ($propertyType) {
                case AbstractOrmModel::CAST_ARRAY:
                    try {
                        $fieldValue = !empty($fieldValue) ? json_decode($fieldValue, true, 512, JSON_THROW_ON_ERROR) : [];
                    } catch (Throwable $e) {
                        $modelClassName = static::class;

                        // if $fieldValue cannot be decoded, we want it to return the original value
                        // but let's create a log that something went wrong and we have invalid json
                        // we don't want logs from anonymous model from tests
                        $isNotAnonymousModel = !str_contains($modelClassName, '@anonymous');

                        if ($isNotAnonymousModel) {
                            /** @var FileLoggerService $fileLoggerService */
                            $fileLoggerService = Container::get(FileLoggerService::class);

                            $fileLoggerService->warning(
                                "Problem with json decode. Model: $modelClassName Property: $property, Value: $fieldValue",
                            );
                        }
                    }

                    return $fieldValue;
                case AbstractOrmModel::CAST_INT:
                    $fieldValue = (int)$fieldValue;
                    return $fieldValue;
                case AbstractOrmModel::CAST_FLOAT:
                    $fieldValue = (float)$fieldValue;
                    return $fieldValue;
                case AbstractOrmModel::CAST_BOOL:
                    $fieldValue = empty($fieldValue) ? false : (bool)$fieldValue;
                    return $fieldValue;
                default:
                    break;
            }

            if (function_exists($propertyType)) {
                $fieldValue = call_user_func($propertyType, $fieldValue);
                return $fieldValue;
            }

            throw new BadMethodCallException("<$propertyType> was not found in cast prop of <$property>");
        }

        return $fieldValue;
    }

    /**
     * @param string $property
     * @param mixed $value
     *
     * @return Model|$this
     */
    public function __set($property, $value)
    {
        /** @var FileLoggerService $fileLoggerService */
        $fileLoggerService = Container::get(FileLoggerService::class);

        if (!array_key_exists($property, get_object_vars($this))) {
            $property = CaseHelper::camelToSnake($property);
        }

        if ($this->is_casting_enabled === false) {
            return parent::set($property, $value);
        }

        if (array_key_exists($property, $this->casts)) {
            $propertyType = $this->casts[$property];
            $modelClassName = static::class;
            $valueType = gettype($value);

            foreach (AbstractOrmModel::CAST_TYPES as $castType) {
                $isCastTypeAnArray = $castType === AbstractOrmModel::CAST_ARRAY;
                $isValueTypeAnArray = is_array($value);

                if ($isCastTypeAnArray && $isValueTypeAnArray) {
                    $value = json_encode($value);
                }

                $isCastTypeAnCarbon = $propertyType === AbstractOrmModel::CAST_CARBON;
                $isNotValueTypeAnCarbon = !($value instanceof Carbon);

                if ($isCastTypeAnCarbon && $isNotValueTypeAnCarbon) {
                    try {
                        if ($value !== null) {
                            $value = Carbon::parse($value);
                        }
                    } catch (Exception $e) {
                        $errorMessage = $e->getMessage();
                        $fileLoggerService->error(
                            "Cannot parse to Carbon object. Model: $modelClassName Property: $property ($propertyType),
                        Value: $value ($valueType), Message: $errorMessage",
                        );
                    }
                }
            }
        }

        return parent::set($property, $value);
    }
}
