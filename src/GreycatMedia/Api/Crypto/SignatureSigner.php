<?php declare(strict_types=1);
namespace GreycatMedia\Api\Crypto;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Monolog\Logger;

class SignatureSigner
{
    const ALGO = 'sha256';
    const NEWLINE = "\n";

    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $request;

    /**
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $response;

    /**
     * @var Logger
     */
    protected $logger;

    public function  __construct(Logger $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    public function setRequest(Request $request) : SignatureSigner
    {
        $this->request = $request;
        return $this;
    }

    public function setResponse(Response $response) : SignatureSigner
    {
        $this->response = $response;
        return $this;
    }

    private function debugShowNewlineChars($value)
    {
        return str_replace(self::NEWLINE, "↵", $value);
    }

    public function test()
    {
        var_dump($this->request);
        $uri = $this->request->getUri();

        $httpRequestMethod = $this->request->getMethod() . self::NEWLINE;
        $canonicalUri = $uri->getPath() . self::NEWLINE;
        $canonicalQueryString = $uri->getQuery() . self::NEWLINE;
        $canonicalHeaders = '' . self::NEWLINE;
        $signedHeaders = '' . self::NEWLINE;

        $this->logger->debug(sprintf(
            'httpRequestMethod=%s',
            $this->debugShowNewlineChars($httpRequestMethod)
        ));
        $this->logger->debug(sprintf(
            'canonicalUri=%s',
            $this->debugShowNewlineChars($canonicalUri)
        ));
        $this->logger->debug(sprintf(
            'canonicalQueryString=%s',
            $this->debugShowNewlineChars($canonicalQueryString)
        ));
        $this->logger->debug(sprintf(
            'canonicalHeaders=%s',
            $this->debugShowNewlineChars($canonicalHeaders)
        ));
        $this->logger->debug(sprintf(
            'signedHeaders=%s',
            $this->debugShowNewlineChars($signedHeaders)
        ));

        $kSecret = 'secret';

        var_dump($signature = hash_hmac(self::ALGO, 'test string', $kSecret, true));
        var_dump(hash_hmac(self::ALGO, 'test string', $kSecret, false));
        var_dump(bin2hex($signature));
        die('test crypto');
    }
}
