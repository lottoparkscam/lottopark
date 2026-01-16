<?php

namespace Services\Api;

use Fuel\Core\DB;
use Models\Whitelabel;
use Model_Whitelabel;
use Model_Whitelabel_API_Nonce;

final class Logger
{
    /** @var Whitelabel */
    private $whitelabel;

    /** @var string */
    private $nonce;

    /** @var string */
    private $key;

    /** @var string */
    private $signature;

    /** @var array */
    private $get;

    /** @var string */
    private $checksum;

    /** @var array */
    private $headers;

    const STATUS_ERROR = "error";

    const STATUS_SUCCESS = "success";

    /**
     * @param Whitelabel $whitelabel
     * @return $this
     */
    public function setWhitelabel(Whitelabel $whitelabel): self
    {
        $this->whitelabel = $whitelabel;
        return $this;
    }

    /**
     * @param string $nonce
     * @return $this
     */
    public function setNonce(string $nonce): self
    {
        $this->nonce = $nonce;
        return $this;
    }

    /**
     * @param string $signature
     * @return $this
     */
    public function setSignature(string $signature): self
    {
        $this->signature = $signature;
        return $this;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function setKey(string $key): self
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @param array $headers
     * @return $this
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @param string $checksum
     * @return $this
     */
    public function setChecksum(string $checksum): self
    {
        $this->checksum = $checksum;
        return $this;
    }

    /**
     *
     * @param array $status
     * @param array $data_in
     * @return void
     */
    public function logData(array $status, array $data_in = []): void
    {
        $status_code = $status['status'];

        $data = [
            "nonce" => $this->nonce,
            "signature" => $this->signature,
            "key" => $this->key,
            "get" => $this->get,
            "checksum" => $this->checksum,
            "headers" => $this->headers,
            "response" => $data_in,
            "status" => $status_code
        ];

        if (!$this->whitelabel) {
           return;
        }

        if (!$this->nonce) {
            return;
        }

        /** @var Model_Whitelabel_API_Nonce $whitelabel_api_nonce */
        $whitelabel_api_nonce = Model_Whitelabel_API_Nonce::forge();

        $data_set = [
            "whitelabel_id" => $this->whitelabel->id,
            "nonce" => $this->nonce,
            "date" => DB::expr("NOW()"),
            "data" => serialize($data)
        ];

        $whitelabel_api_nonce->set($data_set);
        $whitelabel_api_nonce->save();
    }
}