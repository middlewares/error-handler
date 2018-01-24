# middlewares/error-handler

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Build Status][ico-travis]][link-travis]
[![Quality Score][ico-scrutinizer]][link-scrutinizer]
[![Total Downloads][ico-downloads]][link-downloads]
[![SensioLabs Insight][ico-sensiolabs]][link-sensiolabs]

Middleware to execute a handler if the response returned by the next middlewares has any error (status code 400-599) or throws a `Middlewares\HttpErrorException`.

This package provides the `Middlewares\HttpErrorException` that you can use to send context-related data to the error handler. The methods `setContext(array $data)` and `getContext()` methods allows to assign and retrieve the error context data used in the error handler.

## Requirements

* PHP >= 7.0
* A [PSR-7](https://packagist.org/providers/psr/http-message-implementation) http message implementation ([Diactoros](https://github.com/zendframework/zend-diactoros), [Guzzle](https://github.com/guzzle/psr7), [Slim](https://github.com/slimphp/Slim), etc...)
* A [PSR-15](https://github.com/http-interop/http-middleware) middleware dispatcher ([Middleman](https://github.com/mindplay-dk/middleman), etc...)

## Installation

This package is installable and autoloadable via Composer as [middlewares/error-handler](https://packagist.org/packages/middlewares/error-handler).

```sh
composer require middlewares/error-handler
```

## Example

```php
use Interop\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ErrorRequestHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        //Get the error info as an instance of Middlewares\HttpErrorException
        $error = $request->getAttribute('error');

        //The error can contains context data that you can use, for example for PSR-3 loggin
        Logger::error("There's an error", $error->getContext());

        //Any output is captured and added to the response's body
        echo $error->getMessage();

        return (new Response())->withStatus($error->getCode());
    }
}

$dispatcher = new Dispatcher([
    new Middlewares\ErrorHandler(new ErrorRequestHandler()),

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

## Options

#### `__construct(Interop\Http\Server\RequestHandlerInterface $handler = null)`

The request handler used to generate the response. If it's not provided, use [the default](src/ErrorHandlerDefault.php) that provides different outputs for different formats.

#### `catchExceptions(true)`

Used to catch also other exceptions than `Middlewares\HttpErrorException` and create `500` error responses. Disabled by default.

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

The attribute name used to store the instance of `Middlewares\HttpErrorException` with the error info in the server request. By default is `error`.

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
