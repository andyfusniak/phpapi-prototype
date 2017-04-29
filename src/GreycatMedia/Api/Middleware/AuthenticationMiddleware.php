<?php declare(strict_types=1);
namespace GreycatMedia\Api\Middleware;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Monolog\Logger;
use GreycatMedia\Api\Crypto\SignatureSigner;

class AuthenticationMiddleware
{
    /**
     * @var SignatureSigner
     */
    protected $signatureSigner;

    /**
     * @var Logger
     */
    protected $logger;

    public function __construct(SignatureSigner $signatureSigner,
                                Logger $logger)
    {
        $this->signatureSigner = $signatureSigner;
        $this->logger = $logger;
    }

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
        $this->signatureSigner->setRequest($request)
                              ->setResponse($response);
        var_dump($this->signatureSigner->test());
        die();
        if (true) {
            return $next($request, $response);
        }
        
        return $response->withStatus(403)->write('Unauthorized');
    }
}
