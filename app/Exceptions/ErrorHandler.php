<?php
namespace app\Exceptions;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Handles a custom 500 response
 */
class ErrorHandler
{

    public function __invoke(Request $request, Response $response, \Exception $exception)
    {
        $error = array(
            'error' => $exception->getMessage()
        );
        return $response->withJson($error, 500);
    }
}