<?php

declare(strict_types=1);

namespace Tests\Fixtures\Utils\DupesPrevention;

use Orm\Model as Model;

final class Overridable
{
    private array $handlers;

    /**
     * @param array<string, callable<Model>> $handlers
     */
    public function __construct(array $handlers = [])
    {
        $this->handlers = $handlers;
    }

    public function hasHandler(Model $model): bool
    {
        return in_array(get_class($model), array_keys($this->handlers));
    }

    public function findReplacementInDb(Model $model): ?Model
    {
        if (!$this->hasHandler($model)) {
            return null;
        }
        return $this->handlers[get_class($model)]($model);
    }
}
