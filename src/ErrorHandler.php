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

    /** @var FormatterInterface */
    private $defaultFormatter;

    /**
     * Configure the error formatters
     *
     * @param FormatterInterface[] $formatters
     */
    public function __construct(array $formatters = [])
    {
        $this->addFormatters(...$formatters);
        $this->defaultFormatter(new PlainFormatter());
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

    /**
     * Set the default formatter
     */
    public function defaultFormatter(FormatterInterface $defaultFormatter): self
    {
        $this->defaultFormatter = $defaultFormatter;

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

            return $this->defaultFormatter->handle($error, $request);
        }
    }
}
