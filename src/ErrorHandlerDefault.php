<?php
declare(strict_types = 1);

namespace Middlewares;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ErrorHandlerDefault implements RequestHandlerInterface
{
    private $responseFactory;

    private $handlers = [
        'plain' => [
            'text/plain',
            'text/css',
            'text/javascript',
        ],
        'jpeg' => [
            'image/jpeg',
        ],
        'gif' => [
            'image/gif',
        ],
        'png' => [
            'image/png',
        ],
        'svg' => [
            'image/svg+xml',
        ],
        'json' => [
            'application/json',
        ],
        'xml' => [
            'text/xml',
        ],
    ];

    public function __construct(ResponseFactoryInterface $responseFactory = null)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * Execute the error handler.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $error = $request->getAttribute('error');
        $accept = $request->getHeaderLine('Accept');
        $responseFactory = $this->responseFactory ?: Utils\Factory::getResponseFactory();
        $response = $responseFactory->createResponse($error->getCode());

        foreach ($this->handlers as $method => $types) {
            foreach ($types as $type) {
                if (stripos($accept, $type) !== false) {
                    $response->getBody()->write(call_user_func(__CLASS__.'::'.$method, $error));

                    return $response->withHeader('Content-Type', $type);
                }
            }
        }

        $response->getBody()->write(static::html($error));

        return $response->withHeader('Content-Type', 'text/html');
    }

    /**
     * Return the error as plain text.
     */
    public static function plain(HttpErrorException $error): string
    {
        return sprintf("Error %s\n%s", $error->getCode(), $error->getMessage());
    }

    /**
     * Return the error as svg image.
     */
    public static function svg(HttpErrorException $error): string
    {
        return <<<EOT
<svg xmlns="http://www.w3.org/2000/svg" width="200" height="50" viewBox="0 0 200 50">
    <text x="20" y="30" font-family="sans-serif" title="{$error->getMessage()}">
        Error {$error->getCode()}
    </text>
</svg>
EOT;
    }

    /**
     * Return the error as html.
     */
    public static function html(HttpErrorException $error): string
    {
        return <<<EOT
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Error {$error->getCode()}</title>
    <style>html{font-family: sans-serif;}</style>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <h1>Error {$error->getCode()}</h1>
    {$error->getMessage()}
</body>
</html>
EOT;
    }

    /**
     * Return the error as json.
     */
    public static function json(HttpErrorException $error): string
    {
        return json_encode([
            'error' => [
                'code' => $error->getCode(),
                'message' => $error->getMessage(),
            ],
        ]);
    }

    /**
     * Return the error as xml.
     */
    public static function xml(HttpErrorException $error): string
    {
        return <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<error>
    <code>{$error->getCode()}</code>
    <message>{$error->getMessage()}</message>
</error>
EOT;
    }

    /**
     * Return the error as jpeg.
     */
    public static function jpeg(HttpErrorException $error): string
    {
        return self::getImage($error, 'imagejpeg');
    }

    /**
     * Return the error as gif.
     */
    public static function gif(HttpErrorException $error): string
    {
        return self::getImage($error, 'imagegif');
    }

    /**
     * Return the error as png.
     */
    public static function png(HttpErrorException $error): string
    {
        return self::getImage($error, 'imagepng');
    }

    /**
     * Create and return a image as string.
     */
    private static function getImage(HttpErrorException $error, callable $function): string
    {
        $size = 200;
        $image = imagecreatetruecolor($size, $size);
        $textColor = imagecolorallocate($image, 255, 255, 255);
        imagestring($image, 5, 10, 10, "Error {$error->getCode()}", $textColor);

        foreach (str_split($error->getMessage(), intval($size / 10)) as $line => $text) {
            imagestring($image, 5, 10, ($line * 18) + 28, $text, $textColor);
        }

        ob_start();
        $function($image);
        return ob_get_clean();
    }
}
