<?php

/**
 * Use to generate cubit-like nonce.
 */
trait Services_Api_Nonce
{

    /**
     * Generate nonce number.
     * @return int nonce number.
     */
    private function generate_nonce(): int
    {
        return microtime(true)*10000; // Push microsecond into integer part.
    }
}
