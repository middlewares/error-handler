<?php
declare(strict_types = 1);

namespace Middlewares\ErrorFormatter;

use Throwable;

class PngFormatter extends AbstractImageFormatter
{
    protected $contentTypes = [
        'image/png',
    ];

    protected function format(Throwable $error): string
    {
        ob_start();
        imagepng($this->createImage($error));
        return ob_get_clean();
    }
}
