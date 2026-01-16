<?php

use Psr\Http\Message\ResponseInterface;

/**
 * Simple wrapper for API responses to get access to commonly used methods.
 */
class Services_Lcs_Client_Response
{
    private ResponseInterface $response;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function is_success(): bool
    {
        return in_array($this->response->getStatusCode(), range(200, 299));
    }

    public function get_status_code(): int
    {
        return $this->response->getStatusCode();
    }

    public function get_body(): array
    {
        return json_decode($this->response->getBody(), true);
    }

    /**
     * Method tries to determine data answer.
     * If result is a single array then we treat it as response.
     * When is more results, then it is a collection.
     *
     * @return array
     */
    public function get_data(): array
    {
        $body = $this->get_body();
        if (isset($body['data'])) {
            if (count($body['data']) === 1) {
                return reset($body['data']);
            }
            return $body['data'];
        }
        if (count($body) === 1) {
            return reset($body);
        }
        throw new InvalidArgumentException('Unable to determine response data in LCS Client response wrapper');
    }

    public function get_original_response(): ResponseInterface
    {
        return $this->response;
    }
}
