<?php

namespace Services\Plugin;

use Container;
use Exception;
use GuzzleHttp\Client;
use Helpers_General;
use Models\Whitelabel;
use Models\WhitelabelPlugin;
use Repositories\WhitelabelPluginLogRepository;

trait SendPostbackTrait
{

    /**
     * @return boolean only true. otherwise exit with exception.
     * @throws Exception if postback was not received.
     */
    private function sendPostback(string $url, Client $client, string $affSystemName, ?WhitelabelPluginLogRepository $whitelabelPluginLog = null): bool
    {
        $request = $client->request('GET', $url, ['timeout' => 5]);
        $resultCode = $request->getStatusCode();
        $requestNotSuccessful = !in_array($resultCode, [200, 201], true);
        if ($requestNotSuccessful && $this->isPrimeadsPlugin($affSystemName) && !empty($whitelabelPluginLog)) {
            $message = json_encode(['message' => "Unsuccessful request to $affSystemName with status $resultCode, response: {$request->getBody()}", 'url' => $url]);
            $whitelabelPluginLog->addErrorLog($affSystemName, $message);
            return false;
        }

        if ($requestNotSuccessful) {
            throw new Exception("Unsuccessful request to url: {$url} with status $resultCode, response: {$request->getBody()}");
        }

        return true;
    }

    private function isTrafficBar($userAffToken): bool
    {
        return $userAffToken === '537861c913';
    }

    private function shouldSendToPrimeads($userAffToken): bool
    {
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        return $whitelabel->isTheme(Whitelabel::LOTTOPARK_THEME) && $this->isPrimeadsAffToken($userAffToken);
    }

    private static function isPrimeadsAffToken(string $userAffToken): bool
    {
        return strtolower($userAffToken) === '919b3b1e2c';
    }

    private function isPrimeadsPlugin(string $pluginName): bool
    {
        return $pluginName === WhitelabelPlugin::PRIMEADS_NAME;
    }

    public function generatePrimeadsTransactionUrl(string $userClickId, string $commission, string $secure): string
    {
        return "https://s.primeads.io/postback?clickid={$userClickId}&status=2&goal=rs&sum={$commission}&secure={$secure}";
    }

    public function generatePrimeadsRegisterUrl(string $userClickId, string $secure): string
    {
        return "https://pb.primeads.io/postback?clickid={$userClickId}&status=2&goal=reg&secure={$secure}";
    }
}