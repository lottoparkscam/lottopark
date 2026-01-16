<?php

namespace fixtures\Exceptions;

use ArrayAccess;
use OutOfBoundsException;

final class MissingRelation extends OutOfBoundsException
{
    public static function verify(ArrayAccess $model, string ...$relations): void
    {
        $missing = [];
        foreach ($relations as $relation) {
            if (empty($model[$relation])) {
                $missing[] = $relation;
            }
        }

        if (!empty($missing)) {
            $relations = implode(', ', $missing);
            throw new self(
                "Missing relations: $relations in model: " . get_class($model) . " in: " . __FILE__
            );
        }
    }
}
