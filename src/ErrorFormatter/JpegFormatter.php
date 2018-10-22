<?php
declare(strict_types = 1);

namespace Middlewares\ErrorFormatter;

use Throwable;

class JpegFormatter extends AbstractImageFormatter
{
    protected $contentTypes = [
        'image/jpeg',
    ];

    protected function format(Throwable $error): string
    {
        ob_start();
        imagejpeg($this->createImage($error));
        return ob_get_clean();
    }
}
