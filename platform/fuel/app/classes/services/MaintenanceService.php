<?php

declare(strict_types=1);

namespace Services;

class MaintenanceService
{
    private string $maintenanceDomainFilename = '';

    public function setMaintenanceDomainFilename(string $maintenanceDomainFilename): void
    {
        $this->maintenanceDomainFilename = $maintenanceDomainFilename;
    }

    public function checkIfMaintenanceDomainFileExists(): bool
    {
        return file_exists($this->maintenanceDomainFilename);
    }

    public function getHostDomain(): string
    {
        return str_replace('www.', '', $_SERVER['HTTP_HOST'] ?? '');
    }

    public function getDomainFromString(string $maintenanceDomain): string
    {
        $domain = str_replace('www.', '', $maintenanceDomain);
        $domain = explode('.', $domain);
        if (count($domain) > 2) {
            $domain = $domain[count($domain) - 2] . '.' . $domain[count($domain) - 1];
        } else {
            $domain = implode('.', $domain);
        }
        return $domain;
    }

    public function getDomainsUnderMaintenance(): array
    {
        if (!$this->checkIfMaintenanceDomainFileExists()) {
            return [];
        }

        $maintenanceDomainContents = trim(file_get_contents($this->maintenanceDomainFilename));
        $maintenanceDomains = preg_split('/\s+/', $maintenanceDomainContents);

        return array_unique(array_filter($maintenanceDomains));
    }

    public function isDomainUnderMaintenance(): bool
    {
        if (!$this->checkIfMaintenanceDomainFileExists()) {
            return false;
        }

        $domain = $this->getHostDomain();
        $maintenanceDomains = $this->getDomainsUnderMaintenance();
        return in_array($domain, $maintenanceDomains);
    }

    public function isWhitelabelDomainUnderMaintenance(string $domain): bool
    {
        if (!$this->checkIfMaintenanceDomainFileExists()) {
            return false;
        }

        $maintenanceDomains = $this->getDomainsUnderMaintenance();

        foreach ($maintenanceDomains as $maintenanceDomain) {
            if ($this->getDomainFromString($maintenanceDomain) === $domain) {
                return true;
            }
        }
        return false;
    }
}
