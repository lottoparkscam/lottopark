<?php

class Services_Lcs_Auth_Credentials
{
    private string $key;
    private string $signature;
    private string $nonce;
    private string $secret;

    public function __construct(string $key, string $secret, string $signature, string $nonce)
    {
        $this->key = $key;
        $this->signature = $signature;
        $this->nonce = $nonce;
        $this->secret = $secret;
    }

    public function get_key(): string
    {
        return $this->key;
    }

    public function get_signature(): string
    {
        return $this->signature;
    }

    public function get_nonce(): string
    {
        return $this->nonce;
    }

    public function get_secret(): string
    {
        return $this->secret;
    }

    public function to_array(): array
    {
        return [
            'api-key' => $this->get_key(),
            'api-signature' => $this->get_signature(),
            'api-nonce' => $this->get_nonce(),
        ];
    }
}
