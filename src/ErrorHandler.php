<?php
declare(strict_types = 1);

namespace Middlewares;

use Middlewares\ErrorFormatter\FormatterInterface;
use Middlewares\ErrorFormatter\PlainFormatter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class ErrorHandler implements MiddlewareInterface
{
    /** @var FormatterInterface[] */
    private $formatters = [];

    /**
     * Configure the error formatters
     *
     * @param FormatterInterface[] $formatters
     */
    public function __construct(array $formatters = [])
    {
        $this->addFormatters(...$formatters);
    }

    /**
     * Add additional error formatters
     */
    public function addFormatters(FormatterInterface ...$formatters): self
    {
        foreach ($formatters as $formatter) {
            $this->formatters[] = $formatter;
        }

        return $this;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $error) {
            foreach ($this->formatters as $formatter) {
                if ($formatter->isValid($error, $request)) {
                    return $formatter->handle($error, $request);
                }
            }

            $default = current($this->formatters);

            return $default->handle($error, $request);
        }
    }
}
