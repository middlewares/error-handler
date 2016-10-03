<?php

namespace Middlewares;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Http\Middleware\ServerMiddlewareInterface;
use Interop\Http\Middleware\DelegateInterface;
use Exception;
use Throwable;

class ErrorHandler implements ServerMiddlewareInterface
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
     *
     * @param bool $catch
     *
     * @return self
     */
    public function catchExceptions($catch = true)
    {
        $this->catchExceptions = (bool) $catch;

        return $this;
    }

    /**
     * Configure the status code validator.
     *
     * @param callable $statusCodeValidator
     *
     * @return self
     */
    public function statusCode(callable $statusCodeValidator)
    {
        $this->statusCodeValidator = $statusCodeValidator;

        return $this;
    }

    /**
     * Extra arguments passed to the handler.
     *
     * @return self
     */
    public function arguments()
    {
        $this->arguments = func_get_args();

        return $this;
    }

    /**
     * Process a server request and return a response.
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface      $delegate
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        ob_start();
        $level = ob_get_level();

        try {
            $response = $delegate->process($request);

            if ($this->isError($response->getStatusCode())) {
                return $this->handleError($request, $response->getStatusCode(), null);
            }

            return $response;
        } catch (Throwable $exception) {
            if (!$this->catchExceptions) {
                throw $exception;
            }

            return $this->handleError($request, 500, $exception);
        } catch (Exception $exception) {
            if (!$this->catchExceptions) {
                throw $exception;
            }

            return $this->handleError($request, 500, $exception);
        } finally {
            while (ob_get_level() >= $level) {
                ob_end_clean();
            }
        }
    }

    /**
     * Execute the error handler.
     *
     * @param ServerRequestInterface   $request
     * @param int                      $statusCode
     * @param Exception|Throwable|null $exception
     *
     * @return ResponseInterface
     */
    private function handleError(ServerRequestInterface $request, $statusCode, $exception)
    {
        $arguments = array_merge([$request, $statusCode, $exception], $this->arguments);
        $callable = Utils\CallableHandler::resolve($this->handler, $arguments);

        return Utils\CallableHandler::execute($callable, $arguments);
    }

    /**
     * Check whether the status code represents an error or not.
     *
     * @param int $statusCode
     *
     * @return bool
     */
    private function isError($statusCode)
    {
        if ($this->statusCodeValidator) {
            return call_user_func($this->statusCodeValidator, $statusCode);
        }

        return $statusCode >= 400 && $statusCode < 600;
    }
}
