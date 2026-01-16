<?php

declare(strict_types=1);

namespace Tests\Feature\Classes\Repositories;

use Repositories\Orm\CurrencyRepository;
use Test_Feature;

final class CurrencyRepositoryTest extends Test_Feature
{
    private CurrencyRepository $currencyRepositoryUnderTest;

    public function setUp(): void
    {
        parent::setUp();

        $this->currencyRepositoryUnderTest = $this->container->get(CurrencyRepository::class);
    }

    /** @test */
    public function getAllCodes(): void
    {
        // When
        $codes = $this->currencyRepositoryUnderTest->getAllCodes();

        $this->assertIsArray($codes);
        $this->assertContains('PLN', $codes);
        $this->assertContains('EUR', $codes);
        $this->assertContains('USD', $codes);
    }
}
