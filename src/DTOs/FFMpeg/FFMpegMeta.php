<?php

namespace Mostafaznv\Larupload\DTOs\FFMpeg;


class FFMpegMeta
{
    public bool $isLandscape;

    public function __construct(
        public readonly ?int $width,
        public readonly ?int $height,
        public readonly int $duration,
    ) {
        $this->isLandscape = $this->width >= $this->height;
    }

    public static function make(?int $width, ?int $height, int $duration): self
    {
        return new self($width, $height, $duration);
    }
}
