<?php

/**
 * This trait translates insured database field into human readable form.
 * @author Marcin Klimek <marcin.klimek at gg.international>
 */
trait Traits_Formats_Insured
{
    /**
     * Translate is insured database field into human readable form.
     * @param int|string $is_insured loose binding int - may be in form of castable string.
     * @return string human readable is_insured.
     */
    private function translate_is_insured($is_insured): string
    {
        return $is_insured == "1" ? "insured" : "not insured";
    }
}
