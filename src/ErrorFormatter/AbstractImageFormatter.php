<?php
declare(strict_types = 1);

namespace Middlewares\ErrorFormatter;

use Throwable;

abstract class AbstractImageFormatter extends AbstractFormatter
{
    /**
     * Create an image resource from an error
     *
     * @return resource
     */
    protected function createImage(Throwable $error)
    {
        $type = get_class($error);
        $code = $error->getCode();
        $message = $error->getMessage();

        $size = 200;
        $image = imagecreatetruecolor($size, $size);
        $textColor = imagecolorallocate($image, 255, 255, 255);
        imagestring($image, 5, 10, 10, "$type $code", $textColor);

        foreach (str_split($message, intval($size / 10)) as $line => $text) {
            imagestring($image, 5, 10, ($line * 18) + 28, $text, $textColor);
        }

        return $image;
    }
}
