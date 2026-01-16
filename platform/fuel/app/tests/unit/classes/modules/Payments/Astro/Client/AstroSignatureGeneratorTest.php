<?php

namespace Unit\Modules\Payments\Astro\Client;

use Modules\Payments\Astro\Client\AstroSignatureGenerator;
use Test_Unit;

class AstroSignatureGeneratorTest extends Test_Unit
{
    /** @test */
    public function issue__generates_hash(): void
    {
        // Given
        $s = new AstroSignatureGenerator();

        // When
        $actual = $s->issue('some-key', ['field' => 'payload']);

        // Then
        $this->assertTrue(strlen($actual) === 64);
    }
}
