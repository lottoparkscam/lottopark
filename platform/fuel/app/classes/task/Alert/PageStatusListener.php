<?php

namespace Task\Alert;

use Container;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Repositories\WhitelabelRepository;
use Services\Logs\FileLoggerService;
use Throwable;

class PageStatusListener extends AbstractAlertListener
{
    protected string $message;
    protected string $type = self::TYPE_PAGE_STATUS;
    private WhitelabelRepository $whitelabelRepository;
    private Client $client;
    private FileLoggerService $logger;

    public function __construct()
    {
        parent::__construct();
        $this->whitelabelRepository = Container::get(WhitelabelRepository::class);
        $this->client = Container::get(Client::class);
        $this->logger = Container::get(FileLoggerService::class);
    }

    public function shouldSendAlert(): bool
    {
        /** @var string[] $domains */
        $domains = $this->whitelabelRepository->getAllActiveWhitelabelDomains();
        foreach ($domains as $domain) {
            $url = "https://$domain";
            try {
                $response = $this->client->get($url);
                $statusCode = $response->getStatusCode();
                $isError = $statusCode !== 200;
                if ($isError) {
                    $this->setMessage("Domain: $url is not responding. 
                    Response body: {$response->getBody()}, status: $statusCode");
                    return true;
                }
            } catch (RequestException $exception) {
                $this->message = "Request error. Domain: $url is not responding. {$exception->getMessage()}";
                return true;
            } catch (Throwable $exception) {
                $this->setMessage("Undefined error. Domain: $url is not responding. {$exception->getMessage()}");
                $this->logger->error("Cannot check if site is available. Guzzle exception: {$exception->getMessage()}");
                return true;
            }
        }

        return false;
    }
}
