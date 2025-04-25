# middlewares/error-handler

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
![Testing][ico-ga]
[![Total Downloads][ico-downloads]][link-downloads]

Middleware to catch and format errors encountered while handling the request.

## Requirements

* PHP >= 7.2
* A [PSR-7 http library](https://github.com/middlewares/awesome-psr15-middlewares#psr-7-implementations)
* A [PSR-15 middleware dispatcher](https://github.com/middlewares/awesome-psr15-middlewares#dispatcher)

## Installation

This package is installable and autoloadable via Composer as [middlewares/error-handler](https://packagist.org/packages/middlewares/error-handler).

```shell
composer require middlewares/error-handler
```

## Example

```php
use Middlewares\ErrorFormatter;
use Middlewares\ErrorHandler;
use Middlewares\Utils\Dispatcher;

// Create a new ErrorHandler instance
// Any number of formatters can be added. One will be picked based on the Accept
// header of the request. If no formatter matches, the first formatter in the array
// will be used.
$errorHandler = new ErrorHandler([
    new ErrorFormatter\HtmlFormatter(),
    new ErrorFormatter\ImageFormatter(),
    new ErrorFormatter\JsonFormatter(),
    new ErrorFormatter\PlainFormatter(),
    new ErrorFormatter\SvgFormatter(),
    new ErrorFormatter\XmlFormatter(),
]);

// ErrorHandler should always be the first middleware in the stack!
$dispatcher = new Dispatcher([
    $errorHandler,
    // ...
    function ($request) {
        throw HttpErrorException::create(404);
    }
]);

$request = $serverRequestFactory->createServerRequest('GET', '/');
$response = $dispatcher->dispatch($request);
```

## Usage

Add the [formatters](src/ErrorFormatter) to be used (instances of `Middlewares\ErrorFormatter\FormatterInterface`). If no formatters are provided, use all available.

```php
$errorHandler = new ErrorHandler([
    new ErrorFormatter\HtmlFormatter(),
    new ErrorFormatter\JsonFormatter()
]);
```

**Note:** If no formatter is found, the first value of the array will be used. In the example above, `HtmlFormatter`.

### How to log the error and delegate and delegate the formatting to the middleware

Please note that the following snippet must go even before error-hander's middleware.

```php
public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
{
    try {
        return $handler->handle($request);
    } catch (Throwable $exception) {
        $this->logger->critical('Uncaught {error}', [
            'error' => $exception->getMessage(),
            'exception' => $exception, // If you use Monolog, this is correct
        ]);

        // leave it for the middleware
        throw $exception;
    }
}
```

### How to use a custom response for Production

This snippet might come handy when you want to customize your response in production.

```php
class PrettyPage implements StreamFactoryInterface
{
    public function createStream(string $content = ''): StreamInterface
    {
        return Factory::createStream('<strong>Pretty page</strong>');
    }

    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        // This is safe as the Middleware only uses createStream()
        throw new Exception('Not implemented');
    }

    public function createStreamFromResource($resource): StreamInterface
    {
        // This is safe as the Middleware only uses createStream()
        throw new Exception('Not implemented');
    }
}


$errorHandler = new ErrorHandler([
    new HtmlFormatter(
        null,
        new PrettyPage,
    ),
]);
```

---

Please see [CHANGELOG](CHANGELOG.md) for more information about recent changes and [CONTRIBUTING](CONTRIBUTING.md) for contributing details.

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.

[ico-version]: https://img.shields.io/packagist/v/middlewares/error-handler.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-ga]: https://github.com/middlewares/error-handler/workflows/testing/badge.svg
[ico-downloads]: https://img.shields.io/packagist/dt/middlewares/error-handler.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/middlewares/error-handler
[link-downloads]: https://packagist.org/packages/middlewares/error-handler
