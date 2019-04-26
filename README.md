# middlewares/error-handler

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Build Status][ico-travis]][link-travis]
[![Quality Score][ico-scrutinizer]][link-scrutinizer]
[![Total Downloads][ico-downloads]][link-downloads]
[![SensioLabs Insight][ico-sensiolabs]][link-sensiolabs]

Middleware to catch and format errors encountered while handling the request.

## Requirements

* PHP >= 7.1
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
$errorHandler = new ErrorHandler();

// Any number of formatters can be added. One will be picked based on the Accept
// header of the request. If no formatter matches, the PlainFormatter will be used.
$errorHandler->addFormatters(
    new ErrorFormatter\GifFormatter(),
    new ErrorFormatter\HtmlFormatter(),
    new ErrorFormatter\JpegFormatter(),
    new ErrorFormatter\JsonFormatter(),
    new ErrorFormatter\PngFormatter(),
    new ErrorFormatter\SvgFormatter(),
    new ErrorFormatter\XmlFormatter(),
);

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

## Options

### `__construct(ResponseFactoryInterface $responseFactory = null, StreamFactoryInterface $streamFactory = null)`

Provide a specific response and stream factory. If not provided, will be detected based on available PSR-17 implementations.

### `addFormatters(FormatterInterface ...$formatters)`

Add additional error [formatters](src/Formatter). Default is `PlainFormatter`.

---

Please see [CHANGELOG](CHANGELOG.md) for more information about recent changes and [CONTRIBUTING](CONTRIBUTING.md) for contributing details.

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.

[ico-version]: https://img.shields.io/packagist/v/middlewares/error-handler.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/middlewares/error-handler/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/g/middlewares/error-handler.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/middlewares/error-handler.svg?style=flat-square
[ico-sensiolabs]: https://img.shields.io/sensiolabs/i/7aa83a5f-8084-4b8f-bbc9-570751440174.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/middlewares/error-handler
[link-travis]: https://travis-ci.org/middlewares/error-handler
[link-scrutinizer]: https://scrutinizer-ci.com/g/middlewares/error-handler
[link-downloads]: https://packagist.org/packages/middlewares/error-handler
[link-sensiolabs]: https://insight.sensiolabs.com/projects/7aa83a5f-8084-4b8f-bbc9-570751440174
