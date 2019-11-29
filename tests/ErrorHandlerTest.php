<?php
declare(strict_types = 1);

namespace Middlewares\Tests;

use Exception;
use Middlewares\ErrorFormatter;
use Middlewares\ErrorHandler;
use Middlewares\Utils\Dispatcher;
use Middlewares\Utils\Factory;
use Middlewares\Utils\HttpErrorException;
use PHPUnit\Framework\TestCase;

class ErrorHandlerTest extends TestCase
{
    public function testMiddleware()
    {
        $response = Dispatcher::run([
            new ErrorHandler(),
            function ($request) {
                throw new Exception('Something went wrong');
            },
        ]);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('text/plain', $response->getHeaderLine('Content-Type'));
        $this->assertStringContainsString('Something went wrong', (string) $response->getBody());
    }

    public function testHttpException()
    {
        $response = Dispatcher::run([
            new ErrorHandler(),
            function ($request) {
                throw HttpErrorException::create(404);
            },
        ]);

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testHttpStatusException()
    {
        $response = Dispatcher::run([
            new ErrorHandler(),
            function ($request) {
                throw new class() extends Exception {
                    public function getStatusCode(): int
                    {
                        return 418;
                    }
                };
            },
        ]);

        $this->assertEquals(418, $response->getStatusCode());
    }

    public function testGifFormatter()
    {
        $request = Factory::createServerRequest('GET', '/');
        $request = $request->withheader('Accept', 'image/gif');

        $response = Dispatcher::run([
            (new ErrorHandler())->addFormatters(new ErrorFormatter\ImageFormatter()),
            function ($request) {
                throw new Exception('Something went wrong');
            },
        ], $request);

        $this->assertEquals('image/gif', $response->getHeaderLine('Content-Type'));
    }

    public function testHtmlFormatter()
    {
        $request = Factory::createServerRequest('GET', '/');
        $request = $request->withheader('Accept', 'text/html');

        $response = Dispatcher::run([
            (new ErrorHandler())->addFormatters(new ErrorFormatter\HtmlFormatter()),
            function ($request) {
                throw new Exception('Something went wrong');
            },
        ], $request);

        $this->assertEquals('text/html', $response->getHeaderLine('Content-Type'));
    }

    public function testJpegFormatter()
    {
        $request = Factory::createServerRequest('GET', '/');
        $request = $request->withheader('Accept', 'image/jpeg');

        $response = Dispatcher::run([
            (new ErrorHandler())->addFormatters(new ErrorFormatter\ImageFormatter()),
            function ($request) {
                throw new Exception('Something went wrong');
            },
        ], $request);

        $this->assertEquals('image/jpeg', $response->getHeaderLine('Content-Type'));
    }

    public function testJsonFormatter()
    {
        $request = Factory::createServerRequest('GET', '/');
        $request = $request->withheader('Accept', 'application/json');

        $response = Dispatcher::run([
            (new ErrorHandler())->addFormatters(new ErrorFormatter\JsonFormatter()),
            function ($request) {
                throw new Exception('Something went wrong');
            },
        ], $request);

        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
    }

    public function testPlainFormatter()
    {
        $request = Factory::createServerRequest('GET', '/');
        $request = $request->withheader('Accept', 'text/plain');

        $response = Dispatcher::run([
            (new ErrorHandler())->addFormatters(new ErrorFormatter\PlainFormatter()),
            function ($request) {
                throw new Exception('Something went wrong');
            },
        ], $request);

        $this->assertEquals('text/plain', $response->getHeaderLine('Content-Type'));
    }

    public function testPngFormatter()
    {
        $request = Factory::createServerRequest('GET', '/');
        $request = $request->withheader('Accept', 'image/png');

        $response = Dispatcher::run([
            (new ErrorHandler())->addFormatters(new ErrorFormatter\ImageFormatter()),
            function ($request) {
                throw new Exception('Something went wrong');
            },
        ], $request);

        $this->assertEquals('image/png', $response->getHeaderLine('Content-Type'));
    }

    public function testSvgFormatter()
    {
        $request = Factory::createServerRequest('GET', '/');
        $request = $request->withheader('Accept', 'image/svg+xml');

        $response = Dispatcher::run([
            (new ErrorHandler())->addFormatters(new ErrorFormatter\SvgFormatter()),
            function ($request) {
                throw new Exception('Something went wrong');
            },
        ], $request);

        $this->assertEquals('image/svg+xml', $response->getHeaderLine('Content-Type'));
    }

    public function testXmlFormatter()
    {
        $request = Factory::createServerRequest('GET', '/');
        $request = $request->withheader('Accept', 'text/xml');

        $response = Dispatcher::run([
            (new ErrorHandler())->addFormatters(new ErrorFormatter\XmlFormatter()),
            function ($request) {
                throw new Exception('Something went wrong');
            },
        ], $request);

        $this->assertEquals('text/xml', $response->getHeaderLine('Content-Type'));
    }
}
