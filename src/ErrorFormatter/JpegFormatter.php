<?php
declare(strict_types=1);

namespace Middlewares\ErrorFormatter;

use Throwable;

class JpegFormatter extends AbstractImageFormatter
{
    public function contentTypes(): array
    {
        return [
            'image/jpeg',
        ];
    }

    public function format(Throwable $error): string
    {
        ob_start();
        imagejpeg($this->createImage($error));
        return ob_get_clean();
    }
}
