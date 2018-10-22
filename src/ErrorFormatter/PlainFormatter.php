<?php
declare(strict_types = 1);

namespace Middlewares\ErrorFormatter;

use Throwable;

class PlainFormatter extends AbstractFormatter
{
    protected $contentTypes = [
        'text/plain',
    ];

    protected function format(Throwable $error): string
    {
        return sprintf("%s %s\n%s", get_class($error), $error->getCode(), $error->getMessage());
    }
}
