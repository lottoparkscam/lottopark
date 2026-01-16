<?php

use Fuel\Core\DB;
use Classes\Orm\AbstractOrmModel;

/**
 * @deprecated - use new fixtures instead
 *
 * Class Factory_Orm_Abstract
 * Proof of concept, Laravel's style factory. Need more work.
 * @see https://gginternational.slite.com/app/channels/4mtH3PN_5R/notes/XSLLZ5Bym8
 *
 * @Author Sebastian Twaróg <sebastian.twarog@gg.international>
 *
 * @UnitTest not required
 * @FeatureTest not required
 * @E2ETest not required
 */
abstract class Factory_Orm_Abstract
{
    // these Models are not initialized as ORM yet
    protected const HARDCODED_WHITELABEL_USER_ID = 1;
    protected const HARDCODED_WHITELABEL_ID = 1;

    /** @var array */
    protected $props;

    /**
     * Called to generate random/minimal data (without any DB interactions!)
     * to be used in build() method.
     *
     * @param array $props
     */
    abstract public function __construct(array $props = []);

    /**
     * Fetches all prepared data from $props, generates related entities (if necessary)
     * and stores record in DB.
     *
     * @param bool $save
     *
     * @return AbstractOrmModel
     */
    abstract public function build(bool $save = true): AbstractOrmModel;

    /**
     * @deprecated - use new fixtures instead
     * Simply initialize this class with it's constructor default values.
     *
     * @param array $props
     *
     * @return static
     */
    public static function forge(array $props = []): self
    {
        return new static($props);
    }

    /**
     * @deprecated - use new fixtures instead
     *
     * @param int $amount
     * @param array $props
     * @param Closure|null $closure
     *
     * @return AbstractOrmModel[]
     * @throws Throwable
     */
    final public static function create(int $amount, array $props = [], ?Closure $closure = null): array
    {
        return self::generate($amount, $props, true, $closure);
    }

    /**
     * @deprecated - use new fixtures instead
     *
     * @param int $amount
     * @param array $props
     * @param Closure|null $closure
     *
     * @return AbstractOrmModel[]
     * @throws Throwable
     */
    final public static function make(int $amount, array $props = [], ?Closure $closure = null): array
    {
        return self::generate($amount, $props, false, $closure);
    }

    /**
     * @param int $amount
     * @param array $props
     * @param bool $save
     * @param Closure|null $closure
     *
     * @return AbstractOrmModel[]
     * @throws Throwable
     */
    private static function generate(int $amount, array $props, bool $save = true, ?Closure $closure = null): array
    {
        DB::start_transaction();
        try {
            $results = [];
            for ($c = 0; $c !== $amount; $c++) {
                $instance = static::forge($props);
                if ($closure) {
                    $instance = call_user_func($closure, $instance);
                }
                $results[] = $instance->build($save);
            }
        } catch (Throwable $throwable) {
            DB::rollback_transaction();
            throw $throwable;
        }
        DB::commit_transaction();
        return $results;
    }

    /**
     * @deprecated - use new fixtures instead
     *
     * @param bool $save
     *
     * @return AbstractOrmModel
     */
    public function __invoke($save = true)
    {
        return static::forge()->build($save);
    }
}
