<?php

namespace Middlewares\Tests;

use Middlewares\ErrorHandler;
use Middlewares\Utils\Dispatcher;
use Middlewares\Utils\Factory;
use Exception;

class ErrorHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testError()
    {
        $request = Factory::createServerRequest();

        $response = (new Dispatcher([
            new ErrorHandler(function ($request) {
                echo 'Page not found';

                return Factory::createResponse($request->getAttribute('error')['status_code']);
            }),
            function () {
                return Factory::createResponse(404);
            },
        ]))->dispatch($request);

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('Page not found', (string) $response->getBody());
    }

    public function testAttribute()
    {
        $request = Factory::createServerRequest();

        $response = (new Dispatcher([
            (new ErrorHandler(function ($request) {
                echo 'Page not found';

                return Factory::createResponse($request->getAttribute('foo')['status_code']);
            }))->attribute('foo'),
            function () {
                return Factory::createResponse(404);
            },
        ]))->dispatch($request);

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('Page not found', (string) $response->getBody());
    }

    public function testException()
    {
        $exception = new Exception('Error Processing Request');
        $request = Factory::createServerRequest();

        $response = (new Dispatcher([
            (new ErrorHandler(function ($request) {
                echo $request->getAttribute('error')['exception'];

                return Factory::createResponse($request->getAttribute('error')['status_code']);
            }))->catchExceptions(),
            function ($request) use ($exception) {
                echo 'not showed text';
                throw $exception;
            },
        ]))->dispatch($request);

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
        $request = Factory::createServerRequest()->withHeader('Accept', $type);

        $response = (new Dispatcher([
            new ErrorHandler(),
            function () {
                return Factory::createResponse(500);
            },
        ]))->dispatch($request);

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals($type, $response->getHeaderLine('Content-Type'));
    }

    public function testDefaultFormat()
    {
        $request = Factory::createServerRequest();

        $response = (new Dispatcher([
            new ErrorHandler(),
            function () {
                return Factory::createResponse(500);
            },
        ]))->dispatch($request);

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('text/html', $response->getHeaderLine('Content-Type'));
    }

    public function testArguments()
    {
        $request = Factory::createServerRequest();

        $response = (new Dispatcher([
            (new ErrorHandler(function ($request, $message) {
                echo $message;

                return Factory::createResponse($request->getAttribute('error')['status_code']);
            }))->arguments('Hello world'),
            function () {
                return Factory::createResponse(500);
            },
        ]))->dispatch($request);

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('Hello world', (string) $response->getBody());
    }

    public function testValidators()
    {
        $validator = function ($code) {
            return $code === 404;
        };

        $request = Factory::createServerRequest();

        $response = (new Dispatcher([
            (new ErrorHandler())->statusCode($validator),
            function () {
                echo 'Content';

                return Factory::createResponse(500);
            },
        ]))->dispatch($request);

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
        $this->assertEquals('Content', (string) $response->getBody());

        $response = (new Dispatcher([
            (new ErrorHandler())->statusCode($validator),
            function () {
                echo 'Content';

                return Factory::createResponse(404);
            },
        ]))->dispatch($request);

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
        $this->assertNotEquals('Content', (string) $response->getBody());
    }
}
