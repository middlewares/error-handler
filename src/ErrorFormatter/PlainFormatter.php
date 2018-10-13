<?php
declare(strict_types=1);

namespace Middlewares\ErrorFormatter;

use Throwable;

class PlainFormatter implements FormatterInterface
{
    public function contentTypes(): array
    {
        return [
            'text/plain',
        ];
    }

    public function format(Throwable $error): string
    {
        return sprintf("%s %s\n%s", get_class($error), $error->getCode(), $error->getMessage());
    }
}
