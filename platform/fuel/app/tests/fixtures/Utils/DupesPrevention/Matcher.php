<?php

declare(strict_types=1);

namespace Tests\Fixtures\Utils\DupesPrevention;

use Orm\Model as Model;

final class Matcher
{
    private Overridable $overridableRules;
    private array $done = [];

    public function __construct(Overridable $rules)
    {
        $this->overridableRules = $rules;
    }

    public function execute(Model $original, bool $shouldClearDone = true): void
    {
        foreach ($original as $field => $iterableValue) {
            if (is_array($iterableValue)) {
                foreach ($iterableValue as $iterableValueNested) {
                    $this->execute($iterableValueNested, false);
                }
            }

            $hash = $this->hash($original, $field);

            if ($this->hasBeenProceeded($hash)) {
                continue;
            }

            if (!($iterableValue instanceof Model)) {
                continue;
            }

            if (!$iterableValue->is_new()) {
                continue;
            }

            if ($existingModel = $this->overridableRules->findReplacementInDb($iterableValue)) {
                $original->$field = $existingModel;
            }

            $this->markAsProcessed($hash);

            $this->execute($iterableValue, false);
        }

        if ($shouldClearDone) {
            $this->done = [];
        }
    }

    private function hash(Model $original, $field): string
    {
        return sprintf('%s-%s', spl_object_hash($original), $field);
    }

    private function hasBeenProceeded(string $hash): bool
    {
        return isset($this->done[$hash]);
    }

    private function markAsProcessed(string $hash): void
    {
        $this->done[$hash] = $hash;
    }
}
