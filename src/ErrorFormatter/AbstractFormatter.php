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
    /** @var ResponseFactoryInterface */
    protected $responseFactory;

    /** @var string[] */
    protected $contentTypes = [];

    public function __construct(
        ResponseFactoryInterface $responseFactory = null
    ) {
        $this->responseFactory = $responseFactory ?? Factory::getResponseFactory();
    }

    public function isValid(Throwable $error, ServerRequestInterface $request): bool
    {
        return $this->getContentType($request) ? true : false;
    }

    abstract protected function format(Throwable $error): string;

    public function handle(Throwable $error, ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($this->errorStatus($error));
        $response->getBody()->write($this->format($error));

        $contentType = $this->getContentType($request);

        return $response->withHeader('Content-Type', $contentType ? $contentType : $this->contentTypes[0]);
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

    /**
     * @return string|null
     */
    protected function getContentType(ServerRequestInterface $request)
    {
        $accept = $request->getHeaderLine('Accept');

        foreach ($this->contentTypes as $type) {
            if (stripos($accept, $type) !== false) {
                return $type;
            }
        }
    }
}
