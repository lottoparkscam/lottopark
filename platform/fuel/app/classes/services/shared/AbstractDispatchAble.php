<?php


namespace Services\Shared;

use Closure;
use Container;
use Exception;
use Fuel\Core\Fuel;
use RuntimeException;

/**
 * This class allows to do some delayed tasks after service is finished.
 * For example when we change user account balance on WL
 * it is none sense to do LCS call each time it is changed and we can move it outside
 * and after other iterations finished, we can simply call dispatch.
 *
 * VERY IMPORTANT NOTE!
 * In __destruct we have some test to checking if enqueued task has been dispatched,
 * but this magic method is late, so it will throws exception probably outside
 * DB::transaction. It should never happens in real life and treat it as logical error.
 */
abstract class AbstractDispatchAble implements DispatchAble
{
    private bool $is_dispatched = false;
    protected Closure $task;

    public function is_enqueued(): bool
    {
        return !empty($this->task);
    }

    public function dispatch(): void
    {
        if ($this->is_enqueued() === false) {
            throw new RuntimeException(sprintf('Class <%s> can not be dispatched until it is not enqueued', get_called_class()));
        }
        $this->is_dispatched = true;
        call_user_func($this->task);
        $this->reset();
    }

    final public function __destruct()
    {
        if ($this->is_enqueued() === false) {
            return;
        }
        if ($this->is_dispatched === false) {
            $message = sprintf('Class <%s> has pending tasks but it was never dispatched', get_called_class());

            /** @var System $system */
            $system = Container::get(System::class);

            if ($system->env() === Fuel::TEST) {
                echo $message;
                return;
            }

            throw new Exception();
        }
    }

    /**
     * Should call set_task() method and do some specific logic.
     *
     * @param mixed ...$args
     */
    abstract protected function enqueue(...$args): void;

    final protected function set_task(Closure $task): void
    {
        $this->task = $task;
    }

    public function reset(): void
    {
        // overwrite this method, when any changes need to be restored
    }
}
