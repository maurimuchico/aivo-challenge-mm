<?php
namespace app\Exceptions;

/**
 * Handles a custom 404 response
 */
class NotFoundHandler
{

    public function __invoke($request, $response)
    {
        $error = array(
            'error' => 'Service not implemented'
        );
        return $response->withJson($error, 404);
    }
}