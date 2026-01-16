<?php

namespace Services\Api;

final class Reply
{
    const OK = [
        'status' => 200,
        'title' => 'OK'
    ];

    const UNAUTHORIZED = [
        'status' => 401,
        'title' => 'Unauthorized'
    ];

    const BAD_REQUEST = [
        'status' => 400,
        'title' => 'Bad request'
    ];

    const NOT_FOUND = [
        'status' => 404,
        'title' => 'Not found'
    ];

    const SERVICE_UNAVAILABLE = [
        'status' => 503,
        'title' => 'Service Unavailable. Please try again later. If problem persists contact us.'
    ];

    const STATUS_SUCCESS = "success";
    const STATUS_ERROR = "error";

    /**
     *
     * @param array $response_data
     * @return array
     */
    public function buildResponseOk($response_data = []): array
    {
        $response = [
            "status" => self::STATUS_SUCCESS,
            "data" => $response_data
        ];

        return $response;
    }

    /**
     *
     * @param array $type
     * @param array $data
     * @return array
     */
    public function buildResponseError(array $type, array $data): array
    {
        $title = $type['title'];
        $status = $type['status'];

        $response_data = [
            "status" => self::STATUS_ERROR,
            "errors" => [
                "title" => $title,
                "message" => $data
            ]
        ];

        $response = [
            'response' => $response_data,
            'status' => $status
        ];

        return $response;
    }

}