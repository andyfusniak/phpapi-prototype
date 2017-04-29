<?php declare(strict_types=1);
namespace GreycatMedia\VmApi\Middleware;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class AuthenticationMiddleware
{
    /**
     * Authentication invokable class
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(Request $request, Response $response, $next) : Response
    {
        var_dump($request);
        if (true) {
            return $next($request, $response);
        }
        
        return $response->withStatus(403)->write('Unauthorized');
    }
}
