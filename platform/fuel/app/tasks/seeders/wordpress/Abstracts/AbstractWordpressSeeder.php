<?php

namespace Fuel\Tasks\Seeders\Wordpress\Abstracts;

use Fuel\Core\Fuel;
use GuzzleHttp\Client;
use DB;
use Carbon\Carbon;
use Repositories\WhitelabelRepository;
use Container;
use Throwable;
use GuzzleHttp\Exception\RequestException;

abstract class AbstractWordpressSeeder
{
    private const SEED_DOMAINS_FROM_FILE = 'from-file';

    private bool $seedDomainsFromFile = false;
    private string $domainFromCli = '';
    private WhitelabelRepository $whitelabelRepository;
    abstract protected function parameters(): array;

    public function __construct(
        string $domainFromCli = ''
    ) {
        if (!empty($domainFromCli)) {
            $this->domainFromCli = $domainFromCli;
            $this->seedDomainsFromFile = $domainFromCli === self::SEED_DOMAINS_FROM_FILE;
        }

        $this->whitelabelRepository = Container::get(WhitelabelRepository::class);
    }

    public function execute(): void
    {
        $isDevEnv = false;

        switch (Fuel::$env) {
            case Fuel::DEVELOPMENT:
                $domainBasedOnEnv = '.loc';
                $isDevEnv = true;
                break;
            case Fuel::STAGING:
                $domainBasedOnEnv = '.work';
                $isDevEnv = true;
                break;
            case Fuel::PRODUCTION:
                $domainBasedOnEnv = '.com';
                break;
            default:
                $domainBasedOnEnv = '';
        }

        $params = $this->parameters();
        $params['TOKEN'] = $_ENV['WP_SEEDER_TOKEN'];
        $params['SOURCE'] = 'cli';
        $isSeederWithBody = $params['TYPE'] !== 'menu';

        if ($isSeederWithBody) {
            $params['TITLES_AND_BODIES_PER_LANGUAGE'] = static::TITLES_AND_BODIES_PER_LANGUAGE;
        }

        $params['WP_DOMAIN_NAME_WITHOUT_PORT'] = static::WP_DOMAIN_NAME_WITHOUT_PORT;

        $seedOneDomainFromCli = !$this->seedDomainsFromFile && !empty($this->domainFromCli);
        if ($seedOneDomainFromCli) {
            $params['WP_DOMAIN_NAME_WITHOUT_PORT'] = [$this->domainFromCli];
        }

        $params = $this->checkAndCorrectDomainName($params, $domainBasedOnEnv, $isDevEnv);
        $params = $this->checkIfOverridesExist($params, $domainBasedOnEnv);
        $seederName = static::class;

        foreach ($params['WP_DOMAIN_NAME_WITHOUT_PORT'] as $domain) {
            if (str_contains($domain, 'lottohoy')) {
                $domain = "www.$domain";
            }
            $params['WP_DOMAIN_NAME_WITHOUT_PORT'] = $domain;

            $wasSeeded = $this->checkIfSeederWasSeededBefore($seederName, $domain);

            if ($wasSeeded) {
                continue;
            }

            echo "Seeding $seederName \n";

            $url = 'https://' . $domain;

            $client = new Client([
                'base_uri' => $url,
                'verify' => !$isDevEnv ? true : false,
            ]);

            $response = '';

            try {
                $response = $client->post(
                    'WordpressSeederService.php',
                    [
                        'form_params' => $params
                    ]
                );

                $statusCode = $response->getStatusCode();
                $response = $response->getBody()->getContents();
                echo $response;
            } catch (RequestException $e) {
                if ($e->hasResponse()) {
                    $statusCode = $e->getCode();
                    $response =  $e->getResponse()->getBody()->getContents();
                } else {
                    $statusCode = 503;
                }
                echo $response;
            } catch (Throwable $throwable) {
                $statusCode = 0;
                $response = '';
            }

            $isFatalError = str_contains($response, 'bad request token') || str_contains($response, 'forbidden');
            $statusCode = !empty($statusCode) ? $statusCode : 0;
            $response = !empty($response) ? strtolower($response) : '';
            $isSetWrongToken = str_contains($response, 'bad request token');
            $isSetWrongTokenString = $isSetWrongToken ? 'true' : 'false';
            $isWrongStatusCode = $statusCode != 200;
            $isFatalError = (empty($response) && $isWrongStatusCode) || $isSetWrongToken || $isWrongStatusCode;

            if ($isFatalError) {
                echo "Something wrong happened .. \n
                    url: $url,
                    response: $response,
                    status code: $statusCode,
                    wrong token: $isSetWrongTokenString
                ";
                die;
            }
            $isNotError = !str_contains($response, 'error');

            if ($isNotError) {
                $this->addSeedLogToDb($seederName, $domain);
            }
        }
    }

    private function checkAndCorrectDomainName(array $params, string $domainBasedOnEnv, bool $isDevEnv): array
    {
        $valuesToReplace = [
            "http://" => "",
            "https://" => "",
            "://" => "",
            "texts." => "",
        ];

        if ($isDevEnv) {
            $valuesToReplace['.com'] = "";
        }

        /** Check for review apps */
        $siteIp = dns_get_record($_SERVER['DEFAULT_NETWORK_DOMAIN'], DNS_A);
        $siteIp = $siteIp[0]['ip'];
        if ($siteIp === '145.239.70.48') {
            foreach ($params['WP_DOMAIN_NAME_WITHOUT_PORT'] as $key => $domain) {
                if (str_contains($domain, 'lottopark')) {
                    $params['WP_DOMAIN_NAME_WITHOUT_PORT'][$key] = $_SERVER['RECEIPT_WP_SITE_URL'];
                }
                if (str_contains($domain, 'whitelotto')) {
                    $params['WP_DOMAIN_NAME_WITHOUT_PORT'][$key] = $_SERVER['WP_URL'];
                }
                $correctDomainFormat = str_replace('https://', '', $params['WP_DOMAIN_NAME_WITHOUT_PORT'][$key]);
                if ($this->isWhitelabelAdded($correctDomainFormat)) {
                    $params['WP_DOMAIN_NAME_WITHOUT_PORT'][$key] = $correctDomainFormat;
                } else {
                    unset($params['WP_DOMAIN_NAME_WITHOUT_PORT'][$key]);
                }
            }
            if (empty($params['WP_DOMAIN_NAME_WITHOUT_PORT'])) {
                echo "Cannot find any correct domain. [based on platform.whitelabel table] \n";
                die;
            }
            return $params;
        }
        /** end check for review apps */


        foreach ($params['WP_DOMAIN_NAME_WITHOUT_PORT'] as $key => $domain) {
            $correctDomainFormat = strtolower($params['WP_DOMAIN_NAME_WITHOUT_PORT'][$key]);
            $correctDomainFormat = strtr($correctDomainFormat, $valuesToReplace);
            if ($isDevEnv) {
                $correctDomainFormat .= $domainBasedOnEnv;
            }
            if ($this->isWhitelabelAdded($correctDomainFormat)) {
                $params['WP_DOMAIN_NAME_WITHOUT_PORT'][$key] = $correctDomainFormat;
            } else {
                unset($params['WP_DOMAIN_NAME_WITHOUT_PORT'][$key]);
            }
        }
        if (empty($params['WP_DOMAIN_NAME_WITHOUT_PORT'])) {
            echo "Cannot find any correct domain. [based on platform.whitelabel table] \n";
            die;
        }
        return $params;
    }

    private function isWhitelabelAdded(string $domain): bool
    {
        $isAdded = (bool) $this->whitelabelRepository->findByDomain($domain);
        return $isAdded;
    }

    private function checkIfOverridesExist(array $params, string $domainBasedOnEnv): array
    {
        if (!empty($params['OVERRIDE_SPECIFIC_DOMAINS'])) {
            foreach ($params['OVERRIDE_SPECIFIC_DOMAINS'] as $domain => $v) {
                $params['OVERRIDE_SPECIFIC_DOMAINS'][$domain . $domainBasedOnEnv] = $params['OVERRIDE_SPECIFIC_DOMAINS'][$domain];
                unset($params['OVERRIDE_SPECIFIC_DOMAINS'][$domain]);
            }
        }
        return $params;
    }

    /** Raw select - imo its pointless to create a new model for this
     *
     * @throws Database_Exception
     */
    private function checkIfSeederWasSeededBefore(string $seederName, string $domain): bool
    {
        // Seeders had wrong namespace; this check is for avoid useless call on prod
        $seederNameWithOldNamespace = str_replace('Wordpress\\', '', $seederName);
        $seederNames = [$seederName, $seederNameWithOldNamespace];

        $checkSeederQuery = DB::select('name')->from('seeder_executed')
            ->where('name', 'IN', $seederNames)
            ->and_where('domain', '=', $domain)
            ->execute()->as_array();

        $isSeed = !empty($checkSeederQuery) ? true : false;
        return $isSeed;
    }

    /**
     * @throws Database_Exception
     */
    private function addSeedLogToDb(string $seederName, string $domain): void
    {
        $today = Carbon::now()->format('Y-m-d');
        DB::insert('seeder_executed')
            ->columns(['name', 'domain', 'created_at'])
            ->values([$seederName, $domain, $today])
            ->execute();
    }
}
