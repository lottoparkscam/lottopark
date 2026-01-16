<?php

use Helpers\ArrayHelper;

/**
 * Allow model to easily mutate it's numbers field.
 */
trait Model_Traits_Mutate_Numbers
{
    /**
     * Mutate numbers
     *
     * @param string $prefix optional prefix for field name.
     * @return void
     */
    private function mutate_numbers(string $prefix = ''): void
    {
        $numbers_name = "{$prefix}numbers";
        $bonus_name = "{$prefix}bnumbers";
        $numbers = $this->{$numbers_name};
        $bonus_numbers = $this->{$bonus_name};
        $this->{$numbers_name} = is_array($numbers) ?
            ArrayHelper::implode_loosely($numbers)
            : $numbers;
        $this->{$bonus_name} = is_array($bonus_numbers) ?
            ArrayHelper::implode_loosely($bonus_numbers)
            : $bonus_numbers;
    }
}
