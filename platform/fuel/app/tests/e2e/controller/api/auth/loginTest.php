<?php


class Tests_E2e_Controller_Api_Auth_Login extends Test_E2e_Controller_Api
{
    public function test_post_index_correct_credentials()
    {
        $method = "POST";
        $endpoint = "/api/auth/login";

        $email = 'test@user.loc';

        $body = [
            'email' => $email,
            'password' => 'asdqwe',
        ];

        $options = [
            'form_params' => $body
        ];

        $response = $this->get_response_with_security_check($method, $endpoint, $options);

        $expected_keys = [
          "autologin_url"
        ];

        foreach ($expected_keys as $key) {
            $this->assertArrayHasKey($key, $response['data']);
        }
    }

    public function test_post_index_wrong_credentials()
    {
        $method = "POST";
        $endpoint = "/api/auth/login";

        $email = 'test@user.loc';

        $body = [
            'email' => $email,
            'password' => 'asdqwe1',
        ];

        $options = [
            'form_params' => $body
        ];

        $response = $this->get_response_with_security_check($method, $endpoint, $options);

        $expected_response = [
            'status' => 'error',
            'errors' =>
                [
                    'title' => 'Bad request',
                    'message' => ['Wrong credentials'],
                ]
        ];

        $this->assertSame($expected_response, $response);
    }
}