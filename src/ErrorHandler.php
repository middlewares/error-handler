<?php
declare(strict_types=1);

namespace Middlewares;

use Middlewares\ErrorFormatter\FormatterInterface;
use Middlewares\ErrorFormatter\PlainFormatter;
use Middlewares\Utils\Factory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class ErrorHandler implements MiddlewareInterface
{
    /** @var ResponseFactoryInterface */
    private $responseFactory;

    /** @var StreamFactoryInterface */
    private $streamFactory;

    /** @var FormatterInterface[] */
    private $formatters = [];

    public function __construct(
        ResponseFactoryInterface $responseFactory = null,
        StreamFactoryInterface $streamFactory = null
    ) {
        $this->responseFactory = $responseFactory ?? Factory::getResponseFactory();
        $this->streamFactory = $streamFactory ?? Factory::getStreamFactory();
    }

    /**
     * Add additional error formatters
     */
    public function addFormatters(FormatterInterface ...$formatters): self
    {
        foreach ($formatters as $formatter) {
            foreach ($formatter->contentTypes() as $contentType) {
                $this->formatters[$contentType] = $formatter;
            }
        }

        return $this;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $e) {
            list($contentType, $formatter) = $this->errorFormatter($request);
            return $this->errorResponse($formatter, $e, $contentType);
        }
    }

    protected function errorFormatter(ServerRequestInterface $request): array
    {
        $accept = $request->getHeaderLine('Accept');

        foreach ($this->formatters as $type => $formatter) {
            if (stripos($accept, $type) !== false) {
                return [$type, $formatter];
            }
        }

        return ['text/plain', new PlainFormatter()];
    }

    protected function errorResponse(
        FormatterInterface $formatter,
        Throwable $e,
        string $contentType
    ): ResponseInterface {
        $responseBody = $this->streamFactory->createStream($formatter->format($e));

        $response = $this->responseFactory->createResponse($this->errorStatus($e));
        $response = $response->withHeader('Content-Type', $contentType);
        $response = $response->withBody($responseBody);

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
