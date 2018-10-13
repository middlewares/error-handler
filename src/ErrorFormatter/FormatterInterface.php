<?php
declare(strict_types=1);

namespace Middlewares\ErrorFormatter;

use Throwable;

interface FormatterInterface
{
    /**
     * Get supported content types
     *
     * @return string[]
     */
    public function contentTypes(): array;

    /**
     * Format an error as a string
     */
    public function format(Throwable $error): string;
}
