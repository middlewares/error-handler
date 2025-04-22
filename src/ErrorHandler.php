<?php
declare(strict_types = 1);

namespace Middlewares;

use Middlewares\ErrorFormatter\FormatterInterface;
use Middlewares\ErrorFormatter\HtmlFormatter;
use Middlewares\ErrorFormatter\ImageFormatter;
use Middlewares\ErrorFormatter\JsonFormatter;
use Middlewares\ErrorFormatter\PlainFormatter;
use Middlewares\ErrorFormatter\SvgFormatter;
use Middlewares\ErrorFormatter\XmlFormatter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class ErrorHandler implements MiddlewareInterface
{
    /** @var FormatterInterface[] */
    private $formatters = [];

    /** @var LoggerInterface|null */
    private $logger = null;

    /** @var callable|null */
    private $logCallback = null;

    /**
     * Configure the error formatters
     *
     * @param FormatterInterface[] $formatters
     */
    public function __construct(?array $formatters = null, ?LoggerInterface $logger = null)
    {
        $this->logger = $logger;

        if (empty($formatters)) {
            $formatters = [
                new PlainFormatter(),
                new HtmlFormatter(),
                new ImageFormatter(),
                new JsonFormatter(),
                new SvgFormatter(),
                new XmlFormatter(),
            ];
        }

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

    /**
     * @param callable $callback
     */
    public function logCallback(callable $callback): self
    {
        $this->logCallback = $callback;

        return $this;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $error) {
            if ($this->logger) {
                if ($this->logCallback) {
                    ($this->logCallback)($this->logger, $error, $request);
                } else {
                    $this->logger->critical('Uncaught exception', [
                        'message' => $error->getMessage(),
                        'file' => $error->getFile(),
                        'line' => $error->getLine(),
                    ]);
                }
            }

            foreach ($this->formatters as $formatter) {
                if ($formatter->isValid($error, $request)) {
                    return $formatter->handle($error, $request);
                }
            }

            /** @var FormatterInterface $default */
            $default = reset($this->formatters);

            return $default->handle($error, $request);
        }
    }
}
