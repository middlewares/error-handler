<?php
declare(strict_types = 1);

namespace Middlewares\ErrorFormatter;

use Middlewares\Utils\Factory;
use Middlewares\Utils\HttpErrorException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

abstract class AbstractFormatter implements FormatterInterface
{
    protected $responseFactory;
    protected $contentTypes = [];

    public function __construct(
        ResponseFactoryInterface $responseFactory = null
    ) {
        $this->responseFactory = $responseFactory ?? Factory::getResponseFactory();
    }

    public function isValid(Throwable $error, ServerRequestInterface $request): bool
    {
        $accept = $request->getHeaderLine('Accept');

        foreach ($this->contentTypes as $type) {
            if (stripos($accept, $type) !== false) {
                return true;
            }
        }

        return false;
    }

    abstract protected function format(Throwable $error): string;

    public function handle(Throwable $error, ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($this->errorStatus($error));
        $response = $response->withHeader('Content-Type', $this->contentTypes[0]);
        $response->getBody()->write($this->format($error));

        return $response;
    }

    protected function errorStatus(Throwable $e): int
    {
        if ($e instanceof HttpErrorException) {
            return $e->getCode();
        }

        if (method_exists($e, 'getStatusCode')) {
            return $e->getStatusCode();
        }

        return 500;
    }
}
