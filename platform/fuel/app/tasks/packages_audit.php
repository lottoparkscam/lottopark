<?php

namespace Fuel\Tasks;

use Container;
use Services\Logs\FileLoggerService;

final class Packages_Audit
{
    public function run(): void
    {
        /** @var FileLoggerService $logger */
        $logger = Container::get(FileLoggerService::class);

        $platformPath = APPPATH . '../../';
        $composerAuditResponse = ['[COMPOSER AUDIT]'];
        exec("cd $platformPath && composer audit 2>&1", $composerAuditResponse);
        $npmAuditResponse = shell_exec("cd $platformPath && npm audit --production");

        $composerAuditResponse = implode("\r\n", $composerAuditResponse);

        $foundComposerVulnerabilities = !str_contains($composerAuditResponse, 'No security vulnerability advisories found');
        if ($foundComposerVulnerabilities) {
            $logger->assistant($composerAuditResponse);
        }

        $foundNpmVulnerabilities = !str_contains($npmAuditResponse, 'found 0 vulnerabilities');
        if ($foundNpmVulnerabilities) {
            $logger->assistant("[NPM AUDIT]\r\n$npmAuditResponse");
        }
    }
}
