<?php

namespace Mostafaznv\Larupload\DTOs\Image;

use Mostafaznv\Larupload\Traits\Makable;


/**
 * @method static self make(int $width, int $height)
 */
class ImageMeta
{
    use Makable;


    public function __construct(
        public readonly ?int $width,
        public readonly ?int $height,
    ) {}
}
