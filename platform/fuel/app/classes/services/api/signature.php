<?php

/**
 * Use to generate cubit-like signature.
 */
trait Services_Api_Signature
{

    /**
     * Build signature for provided request.
     * @param string $secret our secret, should be retrieved from config or database.
     * @param string $nonce valid nonce, at this point it should be stored in database.
     * @param string $uri_path path part of our end-point address e.g. we pass /route/do-something instead of server.com/route/do-something.
     * @param string $message_content not decoded content of the message.
     * @return string built signature.
     */
    private function build_signature(string $secret, string $nonce, string $uri_path, string $message_content): string
    {
        // create message from uri path, nonce and encoded message content
        $message = $uri_path . $nonce . hash('sha256', $message_content);
        return hash_hmac('sha512', $message, $secret); // encode and return signature.
    }

}
