<?php


class OrderTest extends Test_E2e_Controller_Api
{
    /** @test */
    public function getSummary_returnsCorrectResponse()
    {
        $response = $this->getResponse(
            'GET',
            '/api/internal/order/summary'
        );

        $body = $response['body'];
        $this->assertSame(200, $response['status']);
        $this->assertArrayHasKey('sum', $body);
        $this->assertArrayHasKey('count', $body);
    }
}