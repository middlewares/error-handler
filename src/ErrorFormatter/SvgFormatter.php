<?php
declare(strict_types=1);

namespace Middlewares\ErrorFormatter;

use Throwable;

class SvgFormatter implements FormatterInterface
{
    public function contentTypes(): array
    {
        return [
            'image/svg+xml',
        ];
    }

    public function format(Throwable $error): string
    {
        $type = get_class($error);
        $code = $error->getCode();
        $message = $error->getMessage();

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="200" height="50" viewBox="0 0 200 50">
    <text x="20" y="30" font-family="sans-serif" title="$message">
        $type $code
    </text>
</svg>
SVG;
    }
}
