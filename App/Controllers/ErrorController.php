<?php

namespace App\Controllers;

class ErrorController extends AbstractController
{
    public function error404Action()
    {
        http_response_code(404);
    }

    public function error500Action()
    {
        http_response_code(500);
    }

    public function error401Action()
    {
        http_response_code(401);
    }
}
