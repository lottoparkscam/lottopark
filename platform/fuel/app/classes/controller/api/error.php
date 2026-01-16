<?php

use Fuel\Core\Response;
use Services\Api\Controller;
use Services\Api\Reply;

class Controller_Api_Error extends Controller
{
    public function action_404(): Response
    {
        return $this->returnResponse(
            ["Bad API endpoint"],
            Reply::BAD_REQUEST
        );
    }
}
