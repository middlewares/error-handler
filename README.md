# middlewares/error-handler

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Build Status][ico-travis]][link-travis]
[![Quality Score][ico-scrutinizer]][link-scrutinizer]
[![Total Downloads][ico-downloads]][link-downloads]
[![SensioLabs Insight][ico-sensiolabs]][link-sensiolabs]

Middleware to execute a handler if the response returned by the next middlewares has any error (status code 400-599). It can also catch the exceptions.

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
    new Middlewares\ErrorHandler(),

    function ($request, $next) {
        $response = $next($request);
        return $response->withStatus(404);
    }
]);

$response = $dispatcher->dispatch(new ServerRequest());
```

## Options

#### `__construct(string|callable $handler = null)`

Assign the callable used to handle the error. It can be a callable or a string with the format `Class::method`. The signature of the handler is the following:

```php
use Psr\Http\Message\ServerRequestInterface;

$handler = function (ServerRequestInterface $request) {
    //Get the error info using the "error" attribute
    $error = $request->getAttribute('error');

    //Any output is captured and added to the body stream
    if ($error['exception']) {
        echo $error['exception']->getMessage();
    } else {
        echo sprintf('Oops, a "%s" erro ocurried', $error['status_code']);
    }

    return (new Response())->withStatus($error['status_code']);
};

$dispatcher = new Dispatcher([
    new Middlewares\ErrorHandler($handler)
]);

$response = $dispatcher->dispatch(new ServerRequest());
```

If it's not provided, use [the default](src/ErrorHandlerDefault.php) that provides different outputs for different formats.

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

#### `attribute(string $attribute)`

The attribute name used to store the array with the error info in the server request. By default is `error`.

#### `arguments(...$args)`

Extra arguments to pass to the error handler. This is useful to inject, for example a logger:

```php
$handler = function (ServerRequestInterface $request, $logger) {
    $error = $request->getAttribute('error');
    $message = sprintf('Oops, a "%s" erro ocurried', $error['status_code']);

    //Log the error
    $logger->error($message);

    //Build the response
    $response = (new Response())->withStatus($error['status_code']);
    $response->getBody()->write($message);

    return $response;
};

$dispatcher = new Dispatcher([
    (new Middlewares\ErrorHandler($handler))
        ->arguments($logger)
]);
```

## The `Middlewares\HttpErrorException`

With `Middlewares\HttpErrorException` you can send context-related data to the error handler. It provides the `setContext(array $data)` and `getContext()` methods to assign and retrieve the error context data that you can use in the error handler. Just have to throw the exception and it will be captured by `ErrorHandler`:

```php
use Psr\Http\Message\ServerRequestInterface;

$handler = function (ServerRequestInterface $request) use ($logger) {
    $exception = $request->getAttribute('error')['exception'];

    //Include the data in the log
    $data = $exception->getContext();
    $logger->error("There's an error", $data);

    return (new Response())->withStatus($exception->getCode());
};

$dispatcher = new Dispatcher([
    new Middlewares\ErrorHandler($handler),

    function ($request, $next) {
        $user = Session::signup($request);

        if ($user->isNotAllowed()) {
            //Send an exception adding context data
            throw Middlewares\HttpErrorException::create(401, [
                'user' => $user,
                'request' => $request
            ]);
        }

        return $next($request);
    }
]);

$response = $dispatcher->dispatch(new ServerRequest());
```

Note: `Middlewares\HttpErrorException` is captured **always,** `catchExceptions(false)` has not effect here.
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
