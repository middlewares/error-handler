<?php

namespace Middlewares\Tests;

use Middlewares\ErrorHandler;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Response;
use mindplay\middleman\Dispatcher;
use Exception;

class ErrorHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testError()
    {
        $response = (new Dispatcher([
            new ErrorHandler(function ($request) {
                echo 'Page not found';

                return (new Response())->withStatus($request->getAttribute('error')['status_code']);
            }),
            function () {
                return (new Response())->withStatus(404);
            },
        ]))->dispatch(new ServerRequest());

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('Page not found', (string) $response->getBody());
    }

    public function testAttribute()
    {
        $response = (new Dispatcher([
            (new ErrorHandler(function ($request) {
                echo 'Page not found';

                return (new Response())->withStatus($request->getAttribute('foo')['status_code']);
            }))->attribute('foo'),
            function () {
                return (new Response())->withStatus(404);
            },
        ]))->dispatch(new ServerRequest());

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('Page not found', (string) $response->getBody());
    }

    public function testException()
    {
        $exception = new Exception('Error Processing Request');

        $response = (new Dispatcher([
            (new ErrorHandler(function ($request) {
                echo $request->getAttribute('error')['exception'];

                return (new Response())->withStatus($request->getAttribute('error')['status_code']);
            }))->catchExceptions(),
            function ($request) use ($exception) {
                echo 'not showed text';
                throw $exception;
            },
        ]))->dispatch(new ServerRequest());

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals((string) $exception, (string) $response->getBody());
    }

    public function formatsProvider()
    {
        return [
            ['text/plain'],
            ['text/css'],
            ['text/javascript'],
            ['image/jpeg'],
            ['image/gif'],
            ['image/png'],
            ['image/svg+xml'],
            ['application/json'],
            ['text/xml'],
        ];
    }

    /**
     * @dataProvider formatsProvider
     */
    public function testFormats($type)
    {
        $request = (new ServerRequest())->withHeader('Accept', $type);
        $response = (new Dispatcher([
            new ErrorHandler(),
            function () {
                return (new Response())->withStatus(500);
            },
        ]))->dispatch($request);

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals($type, $response->getHeaderLine('Content-Type'));
    }

    public function testDefaultFormat()
    {
        $response = (new Dispatcher([
            new ErrorHandler(),
            function () {
                return (new Response())->withStatus(500);
            },
        ]))->dispatch(new ServerRequest());

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('text/html', $response->getHeaderLine('Content-Type'));
    }

    public function testArguments()
    {
        $response = (new Dispatcher([
            (new ErrorHandler(function ($request, $message) {
                $response = (new Response())->withStatus($request->getAttribute('error')['status_code']);
                $response->getBody()->write($message);

                return $response;
            }))->arguments('Hello world'),
            function () {
                return (new Response())->withStatus(500);
            },
        ]))->dispatch(new ServerRequest());

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('Hello world', (string) $response->getBody());
    }

    public function testValidators()
    {
        $validator = function ($code) {
            return $code === 404;
        };

        $response = (new Dispatcher([
            (new ErrorHandler())->statusCode($validator),
            function () {
                $response = (new Response())->withStatus(500);
                $response->getBody()->write('Content');

                return $response;
            },
        ]))->dispatch(new ServerRequest());

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
        $this->assertEquals('Content', (string) $response->getBody());

        $response = (new Dispatcher([
            (new ErrorHandler())->statusCode($validator),
            function () {
                $response = (new Response())->withStatus(404);
                $response->getBody()->write('Content');

                return $response;
            },
        ]))->dispatch(new ServerRequest());

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
        $this->assertNotEquals('Content', (string) $response->getBody());
    }
}
