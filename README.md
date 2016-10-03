# middlewares/error-handler

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Quality Score][ico-scrutinizer]][link-scrutinizer]
[![Total Downloads][ico-downloads]][link-downloads]
[![SensioLabs Insight][ico-sensiolabs]][link-sensiolabs]

Middleware to execute a handler if the response returned by the next middlewares has any error (status code 400-599). It can also catch the exceptions.

**Note:** This middleware is intended for server side only

## Requirements

* PHP >= 5.6
* A [PSR-7](https://packagist.org/providers/psr/http-message-implementation) http mesage implementation ([Diactoros](https://github.com/zendframework/zend-diactoros), [Guzzle](https://github.com/guzzle/psr7), [Slim](https://github.com/slimphp/Slim), etc...)
* A [PSR-15](https://github.com/http-interop/http-middleware) middleware dispatcher ([Middleman](https://github.com/mindplay-dk/middleman), etc...)

## Installation

This package is installable and autoloadable via Composer as [middlewares/error-handler](https://packagist.org/packages/middlewares/error-handler).

```sh
composer require middlewares/error-handler
```

## Example

```php
$dispatcher = new Dispatcher([
	new Middlewares\ErrorHandler()
]);

$response = $dispatcher->dispatch(new ServerRequest());
```

## Options

#### `__construct(string|callable $handler = null)`

Assign the callable used to handle the error. It can be a callable or a string with the format `Class::method`. The signature of the handler is the following:

```php
use Psr\Http\Message\ServerRequestInterface;

$handler = function (ServerRequestInterface $request, $statusCode, $exception) {
    //Any output is captured and added to the body stream
    if ($exception) {
        echo $exception->getMessage();
    } else {
        echo sprintf('Oops, a "%s" erro ocurried', $statusCode);
    }

    return (new Response())->withStatus($statusCode);
};

$dispatcher = new Dispatcher([
    new Middlewares\ErrorHandler($handler)
]);

$response = $dispatcher->dispatch(new ServerRequest());
```

If it's not provided, use [the default](src/ErrorHandlerTest.php) that provides different outputs for different formats.

#### `catchExceptions(true)`

Used to catch also the exceptions and create `500` error responses. Disabled by default.

#### `statusCode(callable $statusCodeValidator)`

By default, all responses with status code between 400-599 are interpreted as error responses. But it's possible to change this behaviour, to handle, for example, only 404 errors providing a validator:

```php
$dispatcher = new Dispatcher([
    (new Middlewares\ErrorHandler($handler))
        ->statusCode(function ($code) {
            return $code === 404; //handle only 404 errors
        })
]);
```

#### `arguments(...$args)`

Extra arguments to pass to the error handler. This is useful to inject, for example a logger:

```php
$handler = function (ServerRequestInterface $request, $statusCode, $exception, $logger) {
    $message = sprintf('Oops, a "%s" erro ocurried', $statusCode);

    //Log the error
    $logger->error($message);

    //Build the response
    $response = (new Response())->withStatus($statusCode);
    $response->getBody()->write($message);

    return $response;
};

$dispatcher = new Dispatcher([
    (new Middlewares\ErrorHandler($handler))
        ->arguments($logger)
]);
```

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
