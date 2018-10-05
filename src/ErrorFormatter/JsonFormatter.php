<?php
declare(strict_types=1);

namespace Middlewares\ErrorFormatter;

use Throwable;

class JsonFormatter implements FormatterInterface
{
    public function contentTypes(): array
    {
        return [
            'application/json'
        ];
    }

    public function format(Throwable $error): string
    {
        $json = [
            'type' => get_class($error),
            'code' => $error->getCode(),
            'message' => $error->getMessage(),
        ];

        return json_encode($json);
    }
}
