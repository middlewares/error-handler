<?php
declare(strict_types = 1);

namespace Middlewares;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use Middlewares\Utils\CallableHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class ErrorHandler implements MiddlewareInterface
{
    /**
     * @var callable|string The handler used
     */
    private $handler;

    /**
     * @var array Extra arguments passed to the handler
     */
    private $arguments = [];

    /**
     * @var callable|null The status code validator
     */
    private $statusCodeValidator;

    /**
     * @var bool Whether or not catch exceptions
     */
    private $catchExceptions = false;

    /**
     * @var string The attribute name
     */
    private $attribute = 'error';

    /**
     * Constructor.
     *
     * @param callable|string|null $handler
     */
    public function __construct($handler = 'Middlewares\\ErrorHandlerDefault')
    {
        $this->handler = $handler;
    }

    /**
     * Configure the catchExceptions.
     */
    public function catchExceptions(bool $catch = true): self
    {
        $this->catchExceptions = (bool) $catch;

        return $this;
    }

    /**
     * Configure the status code validator.
     */
    public function statusCode(callable $statusCodeValidator): self
    {
        $this->statusCodeValidator = $statusCodeValidator;

        return $this;
    }

    /**
     * Set the attribute name to store the error info.
     */
    public function attribute(string $attribute): self
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Extra arguments passed to the handler.
     */
    public function arguments(...$arguments): self
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * Process a server request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        ob_start();
        $level = ob_get_level();

        try {
            $response = $handler->handle($request);

            if ($this->isError($response->getStatusCode())) {
                $exception = new HttpErrorException($response->getReasonPhrase(), $response->getStatusCode());
                return $this->handleError($request, $exception);
            }

            return $response;
        } catch (HttpErrorException $exception) {
            return $this->handleError($request, $exception);
        } catch (Throwable $exception) {
            if (!$this->catchExceptions) {
                throw $exception;
            }

            return $this->handleError($request, HttpErrorException::create(500, [], $exception));
        } finally {
            while (ob_get_level() >= $level) {
                ob_end_clean();
            }
        }
    }

    /**
     * Execute the error handler.
     */
    private function handleError(ServerRequestInterface $request, HttpErrorException $exception): ResponseInterface
    {
        $request = $request->withAttribute($this->attribute, $exception);
        $handler = new CallableHandler($this->handler, $this->arguments);

        return $handler->handle($request);
    }

    /**
     * Check whether the status code represents an error or not.
     */
    private function isError(int $statusCode): bool
    {
        if ($this->statusCodeValidator) {
            return call_user_func($this->statusCodeValidator, $statusCode);
        }

        return $statusCode >= 400 && $statusCode < 600;
    }
}
