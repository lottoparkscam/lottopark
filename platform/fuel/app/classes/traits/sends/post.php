<?php

use Services\Logs\FileLoggerService;

/**  This trait allow to send messages POST requests via curl. */
trait Traits_Sends_Post
{
    /**
     * SAFELY send POST request with data attached in the url.
     * @param string $url address, where data will be send.
     * @return bool true on success.
     */
    protected function send_message(string $url): bool
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        // do in try catch, basic IO logic
        try {
            //create a new cURL resource
            $curl = curl_init($url);

            // define curl options
            $options = [
                CURLOPT_URL => $url, // set url with data attached
                CURLOPT_RETURNTRANSFER => true, //return response, instead of outputting
                CURLOPT_TIMEOUT => 10,
            ];
            // set curl options
            curl_setopt_array($curl, $options);
            //execute request
            $result = curl_exec($curl);

            //close cURL resource
            curl_close($curl);

            // return result
            return $result !== false;
        } catch (Exception $ex) {
            $fileLoggerService->error(
                $ex->getMessage()
            );
            return false;
        }
    }
}
