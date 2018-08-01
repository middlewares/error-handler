<?php
declare(strict_types = 1);

namespace Middlewares\Tests;

use Exception;
use Middlewares\ErrorHandler;
use Middlewares\HttpErrorException;
use Middlewares\Utils\CallableHandler;
use Middlewares\Utils\Dispatcher;
use Middlewares\Utils\Factory;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ErrorHandlerTest extends TestCase
{
    public function testError()
    {
        $response = Dispatcher::run([
            new ErrorHandler(new CallableHandler(function ($request) {
                echo 'Page not found';

                return Factory::createResponse($request->getAttribute('error')->getCode());
            })),
            function () {
                return Factory::createResponse(404);
            },
        ]);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('Page not found', (string) $response->getBody());
    }

    public function testInvalidHttpErrorException()
    {
        $this->expectException(RuntimeException::class);

        HttpErrorException::create(0);
    }

    public function testHttpErrorException()
    {
        $response = Dispatcher::run([
            new ErrorHandler(new CallableHandler(function ($request) {
                $error = $request->getAttribute('error');

                echo $error->getCode();
                echo '-'.$error->getMessage();
                echo '-'.$error->getContext()['foo'];

                return Factory::createResponse($error->getCode());
            })),
            function () {
                throw HttpErrorException::create(500, ['foo' => 'bar']);
            },
        ]);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('500-Internal Server Error-bar', (string) $response->getBody());
    }

    public function testAttribute()
    {
        $response = Dispatcher::run([
            (new ErrorHandler(new CallableHandler(function ($request) {
                echo 'Page not found';

                return Factory::createResponse($request->getAttribute('foo')->getCode());
            })))->attribute('foo'),
            function () {
                return Factory::createResponse(404);
            },
        ]);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('Page not found', (string) $response->getBody());
    }

    public function testException()
    {
        $exception = new Exception('Error Processing Request');

        $response = Dispatcher::run([
            (new ErrorHandler(new CallableHandler(function ($request) {
                echo $request->getAttribute('error')->getPrevious();

                return Factory::createResponse($request->getAttribute('error')->getCode());
            })))->catchExceptions(),
            function ($request) use ($exception) {
                echo 'not showed text';
                throw $exception;
            },
        ]);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals((string) $exception, (string) $response->getBody());
    }

    public function testNotCatchedException()
    {
        $this->expectException(Exception::class);

        Dispatcher::run([
            new ErrorHandler(),
            function ($request) {
                throw new Exception();
            },
        ]);
    }

    public function formatsProvider(): array
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
    public function testFormats(string $type)
    {
        $request = Factory::createServerRequest('GET', '/')->withHeader('Accept', $type);

        $response = Dispatcher::run([
            new ErrorHandler(),
            function () {
                return Factory::createResponse(500);
            },
        ], $request);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals($type, $response->getHeaderLine('Content-Type'));
    }

    public function testDefaultFormat()
    {
        $response = Dispatcher::run([
            new ErrorHandler(),
            function () {
                return Factory::createResponse(500);
            },
        ]);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('text/html', $response->getHeaderLine('Content-Type'));
    }

    public function testValidators()
    {
        $validator = function ($code) {
            return $code === 404;
        };

        $response = Dispatcher::run([
            (new ErrorHandler())->statusCode($validator),
            function () {
                echo 'Content';

                return Factory::createResponse(500);
            },
        ]);

        $this->assertEquals('Content', (string) $response->getBody());

        $response = Dispatcher::run([
            (new ErrorHandler())->statusCode($validator),
            function () {
                echo 'Content';

                return Factory::createResponse(404);
            },
        ]);

        $this->assertNotEquals('Content', (string) $response->getBody());
    }
}
