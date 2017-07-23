<?php

namespace Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ErrorHandlerDefault
{
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

    /**
     * Execute the error handler.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request)
    {
        $error = $request->getAttribute('error');
        $accept = $request->getHeaderLine('Accept');
        $response = Utils\Factory::createResponse($error->getCode());

        foreach ($this->handlers as $method => $types) {
            foreach ($types as $type) {
                if (stripos($accept, $type) !== false) {
                    call_user_func(__CLASS__.'::'.$method, $error);

                    return $response->withHeader('Content-Type', $type);
                }
            }
        }

        static::html($error);

        return $response->withHeader('Content-Type', 'text/html');
    }

    /**
     * Output the error as plain text.
     *
     * @param HttpErrorException $error
     */
    public static function plain(HttpErrorException $error)
    {
        echo sprintf("Error %s\n%s", $error->getCode(), $error->getMessage());
    }

    /**
     * Output the error as svg image.
     *
     * @param HttpErrorException $error
     */
    public static function svg(HttpErrorException $error)
    {
        echo <<<EOT
<svg xmlns="http://www.w3.org/2000/svg" width="200" height="50" viewBox="0 0 200 50">
    <text x="20" y="30" font-family="sans-serif" title="{$error->getMessage()}">
        Error {$error->getCode()}
    </text>
</svg>
EOT;
    }

    /**
     * Output the error as html.
     *
     * @param HttpErrorException $error
     */
    public static function html(HttpErrorException $error)
    {
        echo <<<EOT
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
     * Output the error as json.
     *
     * @param HttpErrorException $error
     */
    public static function json(HttpErrorException $error)
    {
        echo json_encode([
            'error' => [
                'code' => $error->getCode(),
                'message' => $error->getMessage(),
            ],
        ]);
    }

    /**
     * Output the error as xml.
     *
     * @param HttpErrorException $error
     */
    public static function xml(HttpErrorException $error)
    {
        echo <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<error>
    <code>{$error->getCode()}</code>
    <message>{$error->getMessage()}</message>
</error>
EOT;
    }

    /**
     * Output the error as jpeg.
     *
     * @param HttpErrorException $error
     */
    public static function jpeg(HttpErrorException $error)
    {
        $image = self::createImage($error);

        imagejpeg($image);
    }

    /**
     * Output the error as gif.
     *
     * @param HttpErrorException $error
     */
    public static function gif(HttpErrorException $error)
    {
        $image = self::createImage($error);

        imagegif($image);
    }

    /**
     * Output the error as png.
     *
     * @param HttpErrorException $error
     */
    public static function png(HttpErrorException $error)
    {
        $image = self::createImage($error);

        imagepng($image);
    }

    /**
     * Creates a image resource with the error text.
     *
     * @param HttpErrorException $error
     *
     * @return resource
     */
    private static function createImage(HttpErrorException $error)
    {
        $size = 200;
        $image = imagecreatetruecolor($size, $size);
        $textColor = imagecolorallocate($image, 255, 255, 255);
        imagestring($image, 5, 10, 10, "Error {$error->getCode()}", $textColor);

        foreach (str_split($error->getMessage(), intval($size / 10)) as $line => $text) {
            imagestring($image, 5, 10, ($line * 18) + 28, $text, $textColor);
        }

        return $image;
    }
}
