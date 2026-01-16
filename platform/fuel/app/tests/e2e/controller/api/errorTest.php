<?php


class Tests_E2e_Controller_Api_Error extends Test_E2e_Controller_Api
{
    public function test_action_404()
    {
        $method = 'GET';
        $endpoint = "/api/error/404";
        $_SERVER['REQUEST_URI'] = "/api/error/404";

        $expected_response = [
            'status' => 'error',
            'errors' =>
                [
                    'title' => 'Bad request',
                    'message' => ['Bad API endpoint'],
                ]
        ];

        $response = $this->get_response_with_security_check($method, $endpoint);

        $this->assertSame($expected_response, $response);
    }
}