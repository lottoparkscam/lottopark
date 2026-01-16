<?php

namespace Tests\Unit\Classes\Services;

use Services\MaintenanceService;
use Test_Unit;

class MaintenanceServiceTest extends Test_Unit
{
    private MaintenanceService $maintenanceServiceUnderTest;

    private string $maintenanceDomainFilename;

    public function setUp(): void
    {
        parent::setUp();

        $this->maintenanceDomainFilename = APPPATH . '.maintenance-domain-test';

        if (file_exists($this->maintenanceDomainFilename)) {
            unlink($this->maintenanceDomainFilename);
        }

        $this->maintenanceServiceUnderTest = new MaintenanceService();
        $this->maintenanceServiceUnderTest->setMaintenanceDomainFilename($this->maintenanceDomainFilename);
    }

    /** @test */
    public function checkIfMaintenanceDomainFileExists_FileDoesNotExist_ShouldReturnFalse(): void
    {
        // When
        $actual = $this->maintenanceServiceUnderTest->checkIfMaintenanceDomainFileExists();

        // Then
        $this->assertFalse($actual);
    }

    /** @test */
    public function checkIfMaintenanceDomainFileExists_FileExists_ShouldReturnTrue(): void
    {
        // When
        file_put_contents($this->maintenanceDomainFilename, '');
        $actual = $this->maintenanceServiceUnderTest->checkIfMaintenanceDomainFileExists();

        // Then
        $this->assertTrue($actual);
    }

    /** @test */
    public function getHostDomain_ShouldReturnActualDomainNameWithoutWww(): void
    {
        // Given
        $_SERVER['HTTP_HOST'] = 'www.casino.lottopark.com';

        // When
        $actual = $this->maintenanceServiceUnderTest->getHostDomain();

        // Then
        $this->assertSame('casino.lottopark.com', $actual);
    }

    /** @test */
    public function getDomainFromString_ShouldReturnActualDomainNameWithoutWww(): void
    {
        // Given
        $domain = 'www.casino.lottopark.com';

        // When
        $actual = $this->maintenanceServiceUnderTest->getDomainFromString($domain);

        // Then
        $this->assertSame('lottopark.com', $actual);
    }

    /** @test */
    public function getDomainsUnderMaintenance_whenMaintenanceDomainFileDoesNotExist_ShouldReturnEmptyArray(): void
    {
        // When
        $actual = $this->maintenanceServiceUnderTest->getDomainsUnderMaintenance();

        // Then
        $this->assertIsArray($actual);
        $this->assertEmpty($actual);
    }

    /** @test */
    public function getDomainsUnderMaintenance_withEmptyMaintenanceDomainFile_ShouldReturnArrayWithoutDomain(): void
    {
        // Given
        $expected = [];

        // When
        file_put_contents($this->maintenanceDomainFilename, '');
        $actual = $this->maintenanceServiceUnderTest->getDomainsUnderMaintenance();

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getDomainsUnderMaintenance_withOneDomain_ShouldReturnArrayWithDomain(): void
    {
        // Given
        $domains = 'casino.lottopark.com';
        $expected = [$domains];

        // When
        file_put_contents($this->maintenanceDomainFilename, $domains);
        $actual = $this->maintenanceServiceUnderTest->getDomainsUnderMaintenance();

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getDomainsUnderMaintenance_withMultipleDomains_ShouldReturnArrayWithAllSetDomains(): void
    {
        // Given
        $domains = "casino.lottopark.com\ncasino.faireum.win";
        $expected = ['casino.lottopark.com', 'casino.faireum.win'];

        // When
        file_put_contents($this->maintenanceDomainFilename, $domains);
        $actual = $this->maintenanceServiceUnderTest->getDomainsUnderMaintenance();

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function isDomainUnderMaintenance_FileDoesNotExist_ShouldReturnFalse(): void
    {
        // Given
        $_SERVER['HTTP_HOST'] = 'casino.lottopark.com';

        // When
        $actual = $this->maintenanceServiceUnderTest->isDomainUnderMaintenance();

        // Then
        $this->assertFalse($actual);
    }

    /** @test */
    public function isDomainUnderMaintenance_withEmptyMaintenanceDomainFile_ShouldReturnFalse(): void
    {
        // Given
        $_SERVER['HTTP_HOST'] = 'casino.lottopark.com';

        // When
        file_put_contents($this->maintenanceDomainFilename, '');
        $actual = $this->maintenanceServiceUnderTest->isDomainUnderMaintenance();

        // Then
        $this->assertFalse($actual);
    }

    /** @test */
    public function isDomainUnderMaintenance_ActualSubdomainIsNotAdded_ShouldReturnFalse(): void
    {
        // Given
        $_SERVER['HTTP_HOST'] = 'casino.lottopark.com';
        $domains = "casino.faireum.win";

        // When
        file_put_contents($this->maintenanceDomainFilename, $domains);
        $actual = $this->maintenanceServiceUnderTest->isDomainUnderMaintenance();

        // Then
        $this->assertFalse($actual);
    }

    /** @test */
    public function isDomainUnderMaintenance_ActualSubdomainAdded_ShouldReturnTrue(): void
    {
        // Given
        $_SERVER['HTTP_HOST'] = 'casino.lottopark.com';
        $domains = "casino.lottopark.com\ncasino.faireum.win";

        // When
        file_put_contents($this->maintenanceDomainFilename, $domains);
        $actual = $this->maintenanceServiceUnderTest->isDomainUnderMaintenance();

        // Then
        $this->assertTrue($actual);
    }

    /** @test */
    public function isDomainUnderMaintenance_ActualSubdomainAddedButHttpHostContainsOnlyDomain_ShouldReturnFalse(): void
    {
        // Given
        $_SERVER['HTTP_HOST'] = 'lottopark.com';
        $domains = "casino.lottopark.com\ncasino.faireum.win";

        // When
        file_put_contents($this->maintenanceDomainFilename, $domains);
        $actual = $this->maintenanceServiceUnderTest->isDomainUnderMaintenance();

        // Then
        $this->assertFalse($actual);
    }

    /** @test */
    public function isWhitelabelDomainUnderMaintenance_ShouldReturnTrue(): void
    {
        $whitelabelDomain = 'lottopark.com';
        $domains = "casino.lottopark.com\ncasino.faireum.win";

        file_put_contents($this->maintenanceDomainFilename, $domains);

        $actual = $this->maintenanceServiceUnderTest->isWhitelabelDomainUnderMaintenance($whitelabelDomain);

        $this->assertTrue($actual);
    }

    /** @test */
    public function isWhitelabelDomainUnderMaintenance_ShouldReturnFalse(): void
    {
        // Given
        $whitelabelDomain = 'lottopark.com';
        $domains = "casino.faireum.win";

        // When
        file_put_contents($this->maintenanceDomainFilename, $domains);
        $actual = $this->maintenanceServiceUnderTest->isWhitelabelDomainUnderMaintenance($whitelabelDomain);

        // Then
        $this->assertFalse($actual);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        if (file_exists($this->maintenanceDomainFilename)) {
            unlink($this->maintenanceDomainFilename);
        }
    }
}
