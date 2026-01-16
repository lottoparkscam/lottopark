<?php

/**
 * Allow model to set jackpot values.
 */
trait Model_Traits_Set_Jackpot
{

    /**
     * Set jackpot value.
     *
     * @param string $value value in normal decimal notation
     * @param string $prefix optional prefix of the field name
     * @param string $suffix optional suffix of the field name
     * 
     * @return self
     */
    public function set_jackpot(string $value, string $prefix = '', string $suffix = ''): self
    {
        parent::__set("{$prefix}jackpot{$suffix}", Helper_Lottery::calculate_jackpot_value($value));

        return $this;
    }
}
