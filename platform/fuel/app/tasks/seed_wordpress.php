<?php

namespace Fuel\Tasks;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractWordpressSeeder;
use Container;

final class Seed_Wordpress
{
    /**
     * Execute all seeders.
     * Call with php oil refine seed_wordpress
     */
    public function run(string $domain = '', string $seederName = ''): void
    {
        $seedAll = empty($seederName);
        $this->runSeeders($domain, $seederName, $seedAll);
    }

    public function runSeeders(string $domain, string $seederName, bool $seedAll = false): void
    {
        if ($seedAll) {
            $seeders = Container::get('wpseeders');

            foreach ($seeders as $seederName) {
                $seederName = get_class($seederName);
                $this->instantiateSeeder($seederName, $domain)->execute();
            }
        }

        $seedOne = !$seedAll;
        if ($seedOne) {
            $seederName = "Fuel\Tasks\Seeders\Wordpress\\" . $seederName;
            $this->instantiateSeeder($seederName, $domain)->execute();
        }
    }

    private function instantiateSeeder(string $seederName, string $domain = ''): AbstractWordpressSeeder
    {

        if (empty($domain)) {
            return new $seederName();
        }

        return new $seederName($domain);
    }
}
