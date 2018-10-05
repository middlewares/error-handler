<?php
declare(strict_types=1);

namespace Middlewares\ErrorFormatter;

use Throwable;

class PngFormatter extends AbstractImageFormatter
{
    public function contentTypes(): array
    {
        return [
            'image/png',
        ];
    }

    public function format(Throwable $error): string
    {
        ob_start();
        imagepng($this->createImage($error));
        return ob_get_clean();
    }
}
