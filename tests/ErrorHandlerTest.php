<?php
declare(strict_types = 1);

namespace Middlewares\Tests;

use Middlewares\ErrorFormatter\ImageFormatter;
use Middlewares\ErrorFormatter\HtmlFormatter;
use Middlewares\ErrorFormatter\JsonFormatter;
use Middlewares\ErrorFormatter\PlainFormatter;
use Middlewares\ErrorFormatter\SvgFormatter;
use Middlewares\ErrorFormatter\XmlFormatter;
use Exception;
use Middlewares\ErrorFormatter;
use Middlewares\ErrorHandler;
use Middlewares\Utils\Dispatcher;
use Middlewares\Utils\Factory;
use Middlewares\Utils\HttpErrorException;
use PHPUnit\Framework\TestCase;

class ErrorHandlerTest extends TestCase
{
    public function testMiddleware(): void
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

    public function testHttpException(): void
    {
        $response = Dispatcher::run([
            new ErrorHandler(),
            function ($request) {
                throw HttpErrorException::create(404);
            },
        ]);

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testHttpStatusException(): void
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

    public function testGifFormatter(): void
    {
        $request = Factory::createServerRequest('GET', '/');
        $request = $request->withheader('Accept', 'image/gif');

        $response = Dispatcher::run([
            (new ErrorHandler())->addFormatters(new ImageFormatter()),
            function ($request) {
                throw new Exception('Something went wrong');
            },
        ], $request);

        $this->assertEquals('image/gif', $response->getHeaderLine('Content-Type'));
    }

    public function testHtmlFormatter(): void
    {
        $request = Factory::createServerRequest('GET', '/');
        $request = $request->withheader('Accept', 'text/html');

        $response = Dispatcher::run([
            (new ErrorHandler())->addFormatters(new HtmlFormatter()),
            function ($request) {
                throw new Exception('Something went wrong');
            },
        ], $request);

        $this->assertEquals('text/html', $response->getHeaderLine('Content-Type'));
    }

    public function testJpegFormatter(): void
    {
        $request = Factory::createServerRequest('GET', '/');
        $request = $request->withheader('Accept', 'image/jpeg');

        $response = Dispatcher::run([
            (new ErrorHandler())->addFormatters(new ImageFormatter()),
            function ($request) {
                throw new Exception('Something went wrong');
            },
        ], $request);

        $this->assertEquals('image/jpeg', $response->getHeaderLine('Content-Type'));
    }

    public function testJsonFormatter(): void
    {
        $request = Factory::createServerRequest('GET', '/');
        $request = $request->withheader('Accept', 'application/json');

        $response = Dispatcher::run([
            (new ErrorHandler())->addFormatters(new JsonFormatter()),
            function ($request) {
                throw new Exception('Something went wrong');
            },
        ], $request);

        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
    }

    public function testPlainFormatter(): void
    {
        $request = Factory::createServerRequest('GET', '/');
        $request = $request->withheader('Accept', 'text/plain');

        $response = Dispatcher::run([
            (new ErrorHandler())->addFormatters(new PlainFormatter()),
            function ($request) {
                throw new Exception('Something went wrong');
            },
        ], $request);

        $this->assertEquals('text/plain', $response->getHeaderLine('Content-Type'));
    }

    public function testPngFormatter(): void
    {
        $request = Factory::createServerRequest('GET', '/');
        $request = $request->withheader('Accept', 'image/png');

        $response = Dispatcher::run([
            (new ErrorHandler())->addFormatters(new ImageFormatter()),
            function ($request) {
                throw new Exception('Something went wrong');
            },
        ], $request);

        $this->assertEquals('image/png', $response->getHeaderLine('Content-Type'));
    }

    public function testSvgFormatter(): void
    {
        $request = Factory::createServerRequest('GET', '/');
        $request = $request->withheader('Accept', 'image/svg+xml');

        $response = Dispatcher::run([
            (new ErrorHandler())->addFormatters(new SvgFormatter()),
            function ($request) {
                throw new Exception('Something went wrong');
            },
        ], $request);

        $this->assertEquals('image/svg+xml', $response->getHeaderLine('Content-Type'));
    }

    public function testXmlFormatter(): void
    {
        $request = Factory::createServerRequest('GET', '/');
        $request = $request->withheader('Accept', 'text/xml');

        $response = Dispatcher::run([
            (new ErrorHandler())->addFormatters(new XmlFormatter()),
            function ($request) {
                throw new Exception('Something went wrong');
            },
        ], $request);

        $this->assertEquals('text/xml', $response->getHeaderLine('Content-Type'));
    }
}
