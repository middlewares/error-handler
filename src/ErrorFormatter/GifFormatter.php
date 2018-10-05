<?php
declare(strict_types=1);

namespace Middlewares\ErrorFormatter;

use Throwable;

class GifFormatter extends AbstractImageFormatter
{
    public function contentTypes(): array
    {
        return [
            'image/gif',
        ];
    }

    public function format(Throwable $error): string
    {
        ob_start();
        imagegif($this->createImage($error));
        return ob_get_clean();
    }
}
